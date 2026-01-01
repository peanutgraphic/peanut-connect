<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the test environment with WordPress function mocks.
 *
 * @package Peanut_Connect
 */

// Define WordPress constants if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content/');
}

if (!defined('PEANUT_CONNECT_PLUGIN_DIR')) {
    define('PEANUT_CONNECT_PLUGIN_DIR', dirname(__DIR__, 2) . '/');
}

if (!defined('PEANUT_CONNECT_VERSION')) {
    define('PEANUT_CONNECT_VERSION', '2.1.3');
}

if (!defined('PEANUT_CONNECT_API_NAMESPACE')) {
    define('PEANUT_CONNECT_API_NAMESPACE', 'peanut-connect/v1');
}

// Time constants
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

// Storage for mocked options and transients
global $peanut_test_options, $peanut_test_transients;
$peanut_test_options = [];
$peanut_test_transients = [];

/**
 * Mock WordPress get_option function
 */
if (!function_exists('get_option')) {
    function get_option(string $option, $default = false) {
        global $peanut_test_options;
        return $peanut_test_options[$option] ?? $default;
    }
}

/**
 * Mock WordPress update_option function
 */
if (!function_exists('update_option')) {
    function update_option(string $option, $value, $autoload = null): bool {
        global $peanut_test_options;
        $peanut_test_options[$option] = $value;
        return true;
    }
}

/**
 * Mock WordPress delete_option function
 */
if (!function_exists('delete_option')) {
    function delete_option(string $option): bool {
        global $peanut_test_options;
        unset($peanut_test_options[$option]);
        return true;
    }
}

/**
 * Mock WordPress get_transient function
 */
if (!function_exists('get_transient')) {
    function get_transient(string $transient) {
        global $peanut_test_transients;
        $data = $peanut_test_transients[$transient] ?? null;
        if ($data === null) {
            return false;
        }
        if (isset($data['expiry']) && $data['expiry'] < time()) {
            unset($peanut_test_transients[$transient]);
            return false;
        }
        return $data['value'];
    }
}

/**
 * Mock WordPress set_transient function
 */
if (!function_exists('set_transient')) {
    function set_transient(string $transient, $value, int $expiration = 0): bool {
        global $peanut_test_transients;
        $peanut_test_transients[$transient] = [
            'value' => $value,
            'expiry' => $expiration > 0 ? time() + $expiration : 0,
        ];
        return true;
    }
}

/**
 * Mock WordPress delete_transient function
 */
if (!function_exists('delete_transient')) {
    function delete_transient(string $transient): bool {
        global $peanut_test_transients;
        unset($peanut_test_transients[$transient]);
        return true;
    }
}

/**
 * Mock WordPress __ function (translation)
 */
if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string {
        return $text;
    }
}

/**
 * Mock WordPress esc_url_raw function
 */
if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url, $protocols = null): string {
        return filter_var($url, FILTER_SANITIZE_URL) ?: '';
    }
}

/**
 * Mock WordPress current_time function
 */
if (!function_exists('current_time')) {
    function current_time(string $type, bool $gmt = false): string {
        return gmdate('Y-m-d H:i:s');
    }
}

/**
 * Mock WordPress get_current_user_id function
 */
if (!function_exists('get_current_user_id')) {
    function get_current_user_id(): int {
        return 1;
    }
}

/**
 * Mock WordPress wp_mkdir_p function
 */
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p(string $target): bool {
        if (file_exists($target)) {
            return is_dir($target);
        }
        return @mkdir($target, 0755, true);
    }
}

/**
 * Mock WP_Error class
 */
if (!class_exists('WP_Error')) {
    class WP_Error {
        private array $errors = [];
        private array $error_data = [];

        public function __construct(string $code = '', string $message = '', $data = '') {
            if (!empty($code)) {
                $this->errors[$code][] = $message;
                if (!empty($data)) {
                    $this->error_data[$code] = $data;
                }
            }
        }

        public function get_error_code(): string {
            $codes = array_keys($this->errors);
            return $codes[0] ?? '';
        }

        public function get_error_message(string $code = ''): string {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return $this->errors[$code][0] ?? '';
        }

        public function get_error_data(string $code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return $this->error_data[$code] ?? null;
        }
    }
}

/**
 * Check if value is a WP_Error
 */
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing): bool {
        return $thing instanceof WP_Error;
    }
}

/**
 * Mock WP_REST_Request class
 */
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        private array $headers = [];
        private array $params = [];
        private string $route = '';

        public function __construct(string $method = 'GET', string $route = '') {
            $this->route = $route;
        }

        public function set_header(string $key, string $value): void {
            $this->headers[strtolower($key)] = $value;
        }

        public function get_header(string $key): ?string {
            return $this->headers[strtolower($key)] ?? null;
        }

        public function set_param(string $key, $value): void {
            $this->params[$key] = $value;
        }

        public function get_param(string $key) {
            return $this->params[$key] ?? null;
        }

        public function get_json_params(): array {
            return $this->params;
        }

        public function get_route(): string {
            return $this->route;
        }

        public function set_route(string $route): void {
            $this->route = $route;
        }
    }
}

/**
 * Mock WP_REST_Response class
 */
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        public $data;
        public int $status;
        private array $headers = [];

        public function __construct($data = null, int $status = 200) {
            $this->data = $data;
            $this->status = $status;
        }

        public function header(string $key, string $value): void {
            $this->headers[$key] = $value;
        }

        public function get_headers(): array {
            return $this->headers;
        }
    }
}

/**
 * Helper function to reset test state
 */
function peanut_reset_test_state(): void {
    global $peanut_test_options, $peanut_test_transients;
    $peanut_test_options = [];
    $peanut_test_transients = [];
}

// ==========================================
// Additional mocks for Health/Updates tests
// ==========================================

/**
 * Mock global WordPress database object
 */
if (!isset($GLOBALS['wpdb'])) {
    $GLOBALS['wpdb'] = new class {
        public string $prefix = 'wp_';

        public function prepare(string $query, ...$args): string {
            return sprintf($query, ...$args);
        }

        public function get_results(string $query, $output = 'OBJECT'): array {
            // Return mock table data for database health check
            return [
                ['Data_length' => 1000000, 'Index_length' => 100000],
                ['Data_length' => 500000, 'Index_length' => 50000],
            ];
        }

        public function query(string $query): bool {
            return true;
        }

        public function esc_like(string $text): string {
            return addcslashes($text, '_%\\');
        }
    };
}

/**
 * Mock get_site_transient for WordPress multisite transients
 */
if (!function_exists('get_site_transient')) {
    function get_site_transient(string $transient) {
        global $mock_transients;
        return $mock_transients[$transient] ?? false;
    }
}

/**
 * Mock delete_site_transient
 */
if (!function_exists('delete_site_transient')) {
    function delete_site_transient(string $transient): bool {
        global $mock_transients;
        unset($mock_transients[$transient]);
        return true;
    }
}

/**
 * Mock wp_update_plugins - refreshes plugin update cache
 */
if (!function_exists('wp_update_plugins')) {
    function wp_update_plugins(): void {
        // No-op for tests
    }
}

/**
 * Mock wp_update_themes - refreshes theme update cache
 */
if (!function_exists('wp_update_themes')) {
    function wp_update_themes(): void {
        // No-op for tests
    }
}

/**
 * Mock get_plugins - returns list of installed plugins
 */
if (!function_exists('get_plugins')) {
    function get_plugins(string $plugin_folder = ''): array {
        global $mock_plugins;
        if (isset($mock_plugins)) {
            return $mock_plugins;
        }
        return [
            'peanut-connect/peanut-connect.php' => [
                'Name' => 'Peanut Connect',
                'Version' => '2.1.3',
            ],
        ];
    }
}

/**
 * Mock wp_get_themes
 */
if (!function_exists('wp_get_themes')) {
    function wp_get_themes(): array {
        return [
            'twentytwentyfour' => new class {
                public function get(string $key): string {
                    $data = [
                        'Name' => 'Twenty Twenty-Four',
                        'Version' => '1.0',
                    ];
                    return $data[$key] ?? '';
                }
                public function exists(): bool {
                    return true;
                }
            },
        ];
    }
}

/**
 * Mock wp_get_theme
 */
if (!function_exists('wp_get_theme')) {
    function wp_get_theme(string $stylesheet = '') {
        global $mock_themes;

        if (isset($mock_themes[$stylesheet])) {
            $theme_data = $mock_themes[$stylesheet];
            return new class($theme_data) {
                private array $data;
                public function __construct(array $data) {
                    $this->data = $data;
                }
                public function get(string $key): string {
                    return $this->data[$key] ?? '';
                }
                public function exists(): bool {
                    return true;
                }
            };
        }

        return new class($stylesheet) {
            private string $stylesheet;
            public function __construct(string $stylesheet) {
                $this->stylesheet = $stylesheet;
            }
            public function get(string $key): string {
                $data = [
                    'Name' => $this->stylesheet === 'twentytwentyfour' ? 'Twenty Twenty-Four' : 'Mock Theme',
                    'Version' => '1.0',
                ];
                return $data[$key] ?? '';
            }
            public function exists(): bool {
                return $this->stylesheet === 'twentytwentyfour';
            }
        };
    }
}

/**
 * Mock get_core_updates
 */
if (!function_exists('get_core_updates')) {
    function get_core_updates(): array {
        global $mock_core_updates;
        if (isset($mock_core_updates)) {
            return $mock_core_updates;
        }
        return [
            (object) [
                'response' => 'latest',
                'current' => '6.4.0',
            ],
        ];
    }
}

/**
 * Mock is_ssl
 */
if (!function_exists('is_ssl')) {
    function is_ssl(): bool {
        return true;
    }
}

/**
 * Mock get_site_url
 */
if (!function_exists('get_site_url')) {
    function get_site_url(): string {
        return 'https://example.com';
    }
}

/**
 * Mock wp_parse_url
 */
if (!function_exists('wp_parse_url')) {
    function wp_parse_url(string $url, int $component = -1) {
        return parse_url($url, $component);
    }
}

/**
 * Mock wp_upload_dir
 */
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir(): array {
        return [
            'basedir' => sys_get_temp_dir(),
            'baseurl' => 'https://example.com/wp-content/uploads',
        ];
    }
}

/**
 * Mock size_format
 */
if (!function_exists('size_format')) {
    function size_format($bytes, int $decimals = 0): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $decimals) . ' ' . $units[$pow];
    }
}

/**
 * Mock wp_max_upload_size
 */
if (!function_exists('wp_max_upload_size')) {
    function wp_max_upload_size(): int {
        return 104857600; // 100MB
    }
}

/**
 * Mock wp_generate_uuid4
 */
if (!function_exists('wp_generate_uuid4')) {
    function wp_generate_uuid4(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

/**
 * Mock register_rest_route
 */
if (!function_exists('register_rest_route')) {
    function register_rest_route(string $namespace, string $route, array $args): bool {
        return true;
    }
}

/**
 * Mock rest_ensure_response
 */
if (!function_exists('rest_ensure_response')) {
    function rest_ensure_response($response): WP_REST_Response {
        if ($response instanceof WP_REST_Response) {
            return $response;
        }
        return new WP_REST_Response($response);
    }
}

/**
 * Mock sanitize_text_field
 */
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $str): string {
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Mock absint
 */
if (!function_exists('absint')) {
    function absint($value): int {
        return abs((int) $value);
    }
}

/**
 * Mock current_user_can
 */
if (!function_exists('current_user_can')) {
    function current_user_can(string $capability): bool {
        global $mock_user_caps;
        if (isset($mock_user_caps[$capability])) {
            return $mock_user_caps[$capability];
        }
        return $capability === 'manage_options';
    }
}

/**
 * Mock add_action
 */
if (!function_exists('add_action')) {
    function add_action(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool {
        return true;
    }
}

/**
 * Mock add_filter
 */
if (!function_exists('add_filter')) {
    function add_filter(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool {
        return true;
    }
}

/**
 * Mock wp_remote_get
 */
if (!function_exists('wp_remote_get')) {
    function wp_remote_get(string $url, array $args = []) {
        global $mock_remote_response;
        if (isset($mock_remote_response)) {
            return $mock_remote_response;
        }
        return new WP_Error('http_request_failed', 'Mock: No response configured');
    }
}

/**
 * Mock wp_remote_retrieve_body
 */
if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response): string {
        if (is_wp_error($response)) {
            return '';
        }
        if (is_array($response) && isset($response['body'])) {
            return $response['body'];
        }
        return '';
    }
}

/**
 * Mock get_plugin_data
 */
if (!function_exists('get_plugin_data')) {
    function get_plugin_data(string $plugin_file, bool $markup = true, bool $translate = true): array {
        global $mock_plugin_data;
        if (isset($mock_plugin_data)) {
            return $mock_plugin_data;
        }
        return [
            'Name' => 'Peanut Connect',
            'Version' => '2.1.3',
            'Author' => 'Peanut Graphic',
        ];
    }
}

/**
 * Define WP_PLUGIN_DIR if not defined
 */
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', PEANUT_CONNECT_PLUGIN_DIR);
}

// Load the plugin classes
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-rate-limiter.php';
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-auth.php';
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-health.php';
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-updates.php';
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-activity-log.php';
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-error-log.php';
require_once PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-connect-self-updater.php';

// Autoloader for test cases
spl_autoload_register(function (string $class): void {
    if (strpos($class, 'Peanut_Connect') === 0) {
        $file = PEANUT_CONNECT_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $class)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
