<?php
/**
 * Peanut Connect Authentication
 *
 * Handles request verification from manager sites.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Peanut_Connect_Auth {

    /**
     * Verify incoming request from manager
     */
    public static function verify_request(WP_REST_Request $request): bool|WP_Error {
        $auth_header = $request->get_header('Authorization');

        if (empty($auth_header)) {
            return new WP_Error(
                'missing_authorization',
                __('Authorization header is required.', 'peanut-connect'),
                ['status' => 401]
            );
        }

        // Extract Bearer token
        if (!preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
            return new WP_Error(
                'invalid_authorization',
                __('Invalid authorization format. Use Bearer token.', 'peanut-connect'),
                ['status' => 401]
            );
        }

        $provided_key = $matches[1];
        $stored_key = get_option('peanut_connect_site_key');

        if (empty($stored_key)) {
            return new WP_Error(
                'not_configured',
                __('Site key not configured. Please generate a site key first.', 'peanut-connect'),
                ['status' => 403]
            );
        }

        // Timing-safe comparison
        if (!hash_equals($stored_key, $provided_key)) {
            return new WP_Error(
                'invalid_key',
                __('Invalid site key.', 'peanut-connect'),
                ['status' => 401]
            );
        }

        // Store manager URL from header
        $manager_url = $request->get_header('X-Peanut-Manager');
        if ($manager_url) {
            update_option('peanut_connect_manager_url', esc_url_raw($manager_url));
        }

        // Update last sync time
        update_option('peanut_connect_last_sync', current_time('mysql'));

        return true;
    }

    /**
     * Check if a specific permission is allowed
     */
    public static function has_permission(string $permission): bool {
        $permissions = get_option('peanut_connect_permissions', []);

        // Health check and list updates are always allowed
        if (in_array($permission, ['health_check', 'list_updates'])) {
            return true;
        }

        return !empty($permissions[$permission]);
    }

    /**
     * Get all permissions
     */
    public static function get_permissions(): array {
        return get_option('peanut_connect_permissions', [
            'health_check' => true,
            'list_updates' => true,
            'perform_updates' => true,
            'access_analytics' => true,
        ]);
    }

    /**
     * Permission callback for REST endpoints
     */
    public static function permission_callback(WP_REST_Request $request): bool|WP_Error {
        return self::verify_request($request);
    }

    /**
     * Permission callback requiring specific permission
     */
    public static function permission_callback_for(string $permission): callable {
        return function(WP_REST_Request $request) use ($permission): bool|WP_Error {
            $verified = self::verify_request($request);

            if (is_wp_error($verified)) {
                return $verified;
            }

            if (!self::has_permission($permission)) {
                return new WP_Error(
                    'permission_denied',
                    sprintf(__('Permission "%s" is not allowed on this site.', 'peanut-connect'), $permission),
                    ['status' => 403]
                );
            }

            return true;
        };
    }
}
