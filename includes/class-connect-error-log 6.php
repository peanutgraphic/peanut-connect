<?php
/**
 * Peanut Connect Error Logger
 *
 * Captures PHP errors, warnings, and fatals for monitoring.
 * Based on MCF Fatal Probe functionality.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Peanut_Connect_Error_Log {

    /**
     * Log file path
     */
    private static string $log_file;

    /**
     * Maximum log entries to keep
     */
    private const MAX_ENTRIES = 500;

    /**
     * Error types to capture
     */
    private const CAPTURED_ERRORS = [
        E_ERROR,
        E_WARNING,
        E_PARSE,
        E_NOTICE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_USER_ERROR,
        E_USER_WARNING,
        E_USER_NOTICE,
        E_RECOVERABLE_ERROR,
        E_DEPRECATED,
        E_USER_DEPRECATED,
    ];

    /**
     * Fatal error types
     */
    private const FATAL_ERRORS = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
    ];

    /**
     * Initialize error logging
     */
    public static function init(): void {
        self::$log_file = WP_CONTENT_DIR . '/peanut-error.log';

        // Only enable if setting is on (default: on)
        if (!get_option('peanut_connect_error_logging', true)) {
            return;
        }

        // Set up error handlers
        set_error_handler([self::class, 'handle_error']);
        register_shutdown_function([self::class, 'handle_shutdown']);
    }

    /**
     * Handle PHP errors
     */
    public static function handle_error(int $errno, string $errstr, string $errfile, int $errline): bool {
        // Don't log if error reporting is off for this type
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Only capture specific error types
        if (!in_array($errno, self::CAPTURED_ERRORS)) {
            return false;
        }

        self::log_entry([
            'type' => self::get_error_type_name($errno),
            'level' => self::get_error_level($errno),
            'message' => $errstr,
            'file' => self::sanitize_path($errfile),
            'line' => $errline,
            'timestamp' => current_time('mysql'),
            'url' => self::get_current_url(),
            'user_id' => get_current_user_id(),
        ]);

        // Return false to allow normal error handling to continue
        return false;
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handle_shutdown(): void {
        $error = error_get_last();

        if ($error && in_array($error['type'], self::FATAL_ERRORS)) {
            self::log_entry([
                'type' => 'FATAL',
                'level' => 'critical',
                'message' => $error['message'],
                'file' => self::sanitize_path($error['file']),
                'line' => $error['line'],
                'timestamp' => current_time('mysql'),
                'url' => self::get_current_url(),
                'user_id' => get_current_user_id(),
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_peak_usage(true),
            ]);
        }
    }

    /**
     * Log an entry to the file
     */
    private static function log_entry(array $entry): void {
        $entries = self::get_entries();

        // Add new entry at the beginning
        array_unshift($entries, $entry);

        // Trim to max entries
        $entries = array_slice($entries, 0, self::MAX_ENTRIES);

        // Save
        @file_put_contents(
            self::$log_file,
            json_encode($entries, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }

    /**
     * Get all log entries
     */
    public static function get_entries(int $limit = 0, int $offset = 0): array {
        if (!file_exists(self::$log_file)) {
            return [];
        }

        $content = @file_get_contents(self::$log_file);
        if (!$content) {
            return [];
        }

        $entries = json_decode($content, true);
        if (!is_array($entries)) {
            return [];
        }

        if ($offset > 0) {
            $entries = array_slice($entries, $offset);
        }

        if ($limit > 0) {
            $entries = array_slice($entries, 0, $limit);
        }

        return $entries;
    }

    /**
     * Get entries filtered by level
     */
    public static function get_entries_by_level(string $level, int $limit = 50): array {
        $entries = self::get_entries();

        $filtered = array_filter($entries, function($entry) use ($level) {
            return ($entry['level'] ?? '') === $level;
        });

        return array_slice(array_values($filtered), 0, $limit);
    }

    /**
     * Get error counts by level
     */
    public static function get_counts(): array {
        $entries = self::get_entries();

        $counts = [
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'total' => count($entries),
        ];

        foreach ($entries as $entry) {
            $level = $entry['level'] ?? 'notice';
            if (isset($counts[$level])) {
                $counts[$level]++;
            }
        }

        return $counts;
    }

    /**
     * Get counts for last 24 hours
     */
    public static function get_recent_counts(): array {
        $entries = self::get_entries();
        $cutoff = strtotime('-24 hours');

        $counts = [
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'total' => 0,
        ];

        foreach ($entries as $entry) {
            $timestamp = strtotime($entry['timestamp'] ?? '');
            if ($timestamp >= $cutoff) {
                $level = $entry['level'] ?? 'notice';
                if (isset($counts[$level])) {
                    $counts[$level]++;
                }
                $counts['total']++;
            }
        }

        return $counts;
    }

    /**
     * Clear all log entries
     */
    public static function clear(): bool {
        if (file_exists(self::$log_file)) {
            return @unlink(self::$log_file);
        }
        return true;
    }

    /**
     * Get error type name from error number
     */
    private static function get_error_type_name(int $errno): string {
        $types = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        return $types[$errno] ?? 'UNKNOWN';
    }

    /**
     * Get error level (for categorization)
     */
    private static function get_error_level(int $errno): string {
        if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            return 'critical';
        }

        if (in_array($errno, [E_RECOVERABLE_ERROR])) {
            return 'error';
        }

        if (in_array($errno, [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING])) {
            return 'warning';
        }

        return 'notice';
    }

    /**
     * Sanitize file path (remove server root for security)
     */
    private static function sanitize_path(string $path): string {
        $abspath = str_replace('\\', '/', ABSPATH);
        $path = str_replace('\\', '/', $path);

        return str_replace($abspath, '/', $path);
    }

    /**
     * Get current request URL
     */
    private static function get_current_url(): string {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return 'CLI';
        }

        $url = $_SERVER['REQUEST_URI'] ?? '';

        // Truncate long URLs
        if (strlen($url) > 200) {
            $url = substr($url, 0, 200) . '...';
        }

        return $url;
    }

    /**
     * Export log as CSV
     */
    public static function export_csv(): string {
        $entries = self::get_entries();

        $csv = "Timestamp,Level,Type,Message,File,Line,URL\n";

        foreach ($entries as $entry) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $entry['timestamp'] ?? '',
                $entry['level'] ?? '',
                $entry['type'] ?? '',
                str_replace('"', '""', $entry['message'] ?? ''),
                $entry['file'] ?? '',
                $entry['line'] ?? '',
                $entry['url'] ?? ''
            );
        }

        return $csv;
    }
}
