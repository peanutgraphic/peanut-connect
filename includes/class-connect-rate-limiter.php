<?php
/**
 * Peanut Connect Rate Limiter
 *
 * Implements rate limiting for API endpoints to prevent abuse.
 * Uses WordPress transients for storage, making it work across
 * different hosting environments without external dependencies.
 *
 * @package Peanut_Connect
 * @since 2.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Peanut_Connect_Rate_Limiter {

    /**
     * Default rate limit: requests per window
     */
    private const DEFAULT_LIMIT = 60;

    /**
     * Default window size in seconds (1 minute)
     */
    private const DEFAULT_WINDOW = 60;

    /**
     * Stricter limit for authentication endpoints
     */
    private const AUTH_LIMIT = 10;

    /**
     * Window for auth endpoints (1 minute)
     */
    private const AUTH_WINDOW = 60;

    /**
     * Check if request is rate limited
     *
     * @param string $identifier Unique identifier (IP address or API key hash)
     * @param string $endpoint   The endpoint being accessed
     * @return bool|WP_Error True if allowed, WP_Error if rate limited
     */
    public static function check(string $identifier, string $endpoint = 'default'): bool|WP_Error {
        $config = self::get_endpoint_config($endpoint);
        $key = self::get_cache_key($identifier, $endpoint);

        $data = get_transient($key);

        if ($data === false) {
            // First request in this window
            $data = [
                'count' => 1,
                'window_start' => time(),
            ];
            set_transient($key, $data, $config['window']);
            return true;
        }

        // Check if we're still in the same window
        $window_elapsed = time() - $data['window_start'];

        if ($window_elapsed >= $config['window']) {
            // Window has expired, start fresh
            $data = [
                'count' => 1,
                'window_start' => time(),
            ];
            set_transient($key, $data, $config['window']);
            return true;
        }

        // Increment counter
        $data['count']++;

        if ($data['count'] > $config['limit']) {
            $retry_after = $config['window'] - $window_elapsed;

            return new WP_Error(
                'rate_limit_exceeded',
                sprintf(
                    /* translators: %d: seconds until rate limit resets */
                    __('Rate limit exceeded. Please try again in %d seconds.', 'peanut-connect'),
                    $retry_after
                ),
                [
                    'status' => 429,
                    'retry_after' => $retry_after,
                ]
            );
        }

        // Update counter
        set_transient($key, $data, $config['window']);
        return true;
    }

    /**
     * Get rate limit headers for response
     *
     * @param string $identifier Unique identifier
     * @param string $endpoint   The endpoint being accessed
     * @return array Headers to add to response
     */
    public static function get_headers(string $identifier, string $endpoint = 'default'): array {
        $config = self::get_endpoint_config($endpoint);
        $key = self::get_cache_key($identifier, $endpoint);
        $data = get_transient($key);

        $remaining = $config['limit'];
        $reset = time() + $config['window'];

        if ($data !== false) {
            $remaining = max(0, $config['limit'] - $data['count']);
            $reset = $data['window_start'] + $config['window'];
        }

        return [
            'X-RateLimit-Limit' => $config['limit'],
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $reset,
        ];
    }

    /**
     * Get client identifier from request
     *
     * Uses a combination of IP address and API key (if present) to identify clients.
     * This helps prevent bypass attempts while still being fair to legitimate users.
     *
     * @param WP_REST_Request $request The incoming request
     * @return string Unique client identifier
     */
    public static function get_client_identifier(WP_REST_Request $request): string {
        $parts = [];

        // Get IP address
        $ip = self::get_client_ip();
        if ($ip) {
            $parts[] = $ip;
        }

        // Include hashed API key if present (for more granular limiting)
        $auth_header = $request->get_header('Authorization');
        if ($auth_header && preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
            // Hash the key to use as identifier (don't store actual key)
            $parts[] = substr(md5($matches[1]), 0, 8);
        }

        return implode('_', $parts) ?: 'unknown';
    }

    /**
     * Get client IP address
     *
     * Attempts to get the real client IP, accounting for proxies and load balancers.
     *
     * @return string|null Client IP address or null if not determinable
     */
    private static function get_client_ip(): ?string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Standard proxy header
            'HTTP_X_REAL_IP',            // Nginx proxy
            'REMOTE_ADDR',               // Direct connection
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // X-Forwarded-For can contain multiple IPs, get the first one
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                // Validate IP format
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    /**
     * Get configuration for specific endpoint
     *
     * Different endpoints can have different rate limits based on their
     * sensitivity and expected usage patterns.
     *
     * @param string $endpoint Endpoint identifier
     * @return array Configuration with 'limit' and 'window' keys
     */
    private static function get_endpoint_config(string $endpoint): array {
        $configs = [
            // Authentication-related endpoints (stricter)
            'verify' => [
                'limit' => self::AUTH_LIMIT,
                'window' => self::AUTH_WINDOW,
            ],
            'disconnect' => [
                'limit' => self::AUTH_LIMIT,
                'window' => self::AUTH_WINDOW,
            ],

            // Tracking endpoints (higher limits for frontend JS)
            'track' => [
                'limit' => 120,  // 2 events per second max
                'window' => 60,
            ],
            'identify' => [
                'limit' => 30,
                'window' => 60,
            ],
            'conversion' => [
                'limit' => 30,
                'window' => 60,
            ],
            'popup_interaction' => [
                'limit' => 60,
                'window' => 60,
            ],

            // Standard endpoints
            'health' => [
                'limit' => 30,
                'window' => 60,
            ],
            'updates' => [
                'limit' => 30,
                'window' => 60,
            ],
            'update' => [
                'limit' => 10,  // Performing updates should be less frequent
                'window' => 60,
            ],
            'analytics' => [
                'limit' => 30,
                'window' => 60,
            ],

            // Default fallback
            'default' => [
                'limit' => self::DEFAULT_LIMIT,
                'window' => self::DEFAULT_WINDOW,
            ],
        ];

        return $configs[$endpoint] ?? $configs['default'];
    }

    /**
     * Generate cache key for rate limit data
     *
     * @param string $identifier Client identifier
     * @param string $endpoint   Endpoint identifier
     * @return string Cache key
     */
    private static function get_cache_key(string $identifier, string $endpoint): string {
        // Create a short, unique key
        $hash = substr(md5($identifier . $endpoint), 0, 12);
        return 'peanut_rl_' . $hash;
    }

    /**
     * Clear rate limit for a specific client
     *
     * Useful for administrative purposes or after successful authentication.
     *
     * @param string $identifier Client identifier
     * @param string $endpoint   Endpoint identifier (or 'all' for all endpoints)
     */
    public static function clear(string $identifier, string $endpoint = 'all'): void {
        if ($endpoint === 'all') {
            $endpoints = ['verify', 'disconnect', 'health', 'updates', 'update', 'analytics', 'default'];
            foreach ($endpoints as $ep) {
                delete_transient(self::get_cache_key($identifier, $ep));
            }
        } else {
            delete_transient(self::get_cache_key($identifier, $endpoint));
        }
    }
}
