<?php
/**
 * Peanut Connect REST API
 *
 * Exposes endpoints for the manager site to communicate with this child site.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Peanut_Connect_API {

    /**
     * Register all REST routes
     */
    public function register_routes(): void {
        // =====================
        // Admin endpoints (for React SPA)
        // =====================

        // Get settings for admin
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Hub settings - auto-connect (generates key and sends to Hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/hub/connect', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'auto_connect_to_hub'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'hub_url' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'esc_url_raw',
                ],
            ],
        ]);

        // Hub settings - test connection
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/hub/test', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'test_hub_connection'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Hub settings - disconnect
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/hub/disconnect', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'disconnect_hub'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Hub settings - trigger sync
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/hub/sync', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'trigger_hub_sync'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Hub settings - update mode (v2.6.0+)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/hub/mode', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_hub_mode'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'mode' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['standard', 'hide_suite', 'disable_suite'],
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Dashboard data
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/dashboard', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_dashboard'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin health endpoint (no Bearer token required)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/admin/health', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_admin_health'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin updates endpoint (no Bearer token required)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/admin/updates', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_admin_updates'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin perform update (no Bearer token required)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/admin/update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'admin_perform_update'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['plugin', 'theme', 'core'],
                ],
                'slug' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // =====================
        // Error Log endpoints
        // =====================

        // Get error log entries
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/errors', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_error_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'limit' => [
                    'type' => 'integer',
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 500,
                ],
                'offset' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
                'level' => [
                    'type' => 'string',
                    'enum' => ['critical', 'error', 'warning', 'notice', ''],
                ],
            ],
        ]);

        // Get error counts
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/errors/counts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_error_counts'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Clear error log
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/errors/clear', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'clear_error_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Export error log
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/errors/export', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'export_error_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Update error logging settings
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/errors/settings', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_error_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // =====================
        // Activity Log endpoints
        // =====================

        // Get activity log entries
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/activity', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_activity_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'limit' => [
                    'type' => 'integer',
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 500,
                ],
                'offset' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
                'type' => [
                    'type' => 'string',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['success', 'warning', 'error', 'info', ''],
                ],
            ],
        ]);

        // Get activity counts
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/activity/counts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_activity_counts'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Clear activity log
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/activity/clear', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'clear_activity_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Export activity log
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/activity/export', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'export_activity_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // =====================
        // Plugin Management endpoints (admin)
        // =====================

        // Get all plugins with full details
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/plugins', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_plugins'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Activate plugin
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/plugin/activate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'activate_plugin'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Deactivate plugin
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/plugin/deactivate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'deactivate_plugin'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete plugin
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/plugin/delete', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'delete_plugin'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Bulk update plugins
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/plugins/bulk-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'bulk_update_plugins'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // =====================
        // Theme Management endpoints (admin)
        // =====================

        // Get all themes with full details
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/themes', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_themes'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Activate theme
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/theme/activate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'activate_theme'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'theme' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete theme
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/theme/delete', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'delete_theme'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'theme' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Bulk update themes
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/themes/bulk-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'bulk_update_themes'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Toggle auto-update for plugin or theme
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/auto-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'toggle_auto_update'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['plugin', 'theme'],
                ],
                'item' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'enable' => [
                    'required' => true,
                    'type' => 'boolean',
                ],
            ],
        ]);

        // =====================
        // Security Settings endpoints (admin)
        // =====================

        // Get security settings
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/security', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_security_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Update security settings
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/security', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_security_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // =====================
        // Permissions endpoints (admin)
        // =====================

        // Get hub permissions
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/permissions', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_permissions'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Update hub permissions
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/permissions', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_permissions'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // =====================
        // Hub Management endpoints (authenticated via Hub API key)
        // =====================

        // Get all plugins for Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/plugins', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_plugins'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'hub_permission_callback'],
        ]);

        // Get all themes for Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/themes', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_themes'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'hub_permission_callback'],
        ]);

        // Update plugin from Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/plugin/update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'perform_update'],
            'permission_callback' => Peanut_Connect_Auth::hub_permission_callback_for('perform_updates'),
            'args' => [
                'type' => [
                    'default' => 'plugin',
                    'type' => 'string',
                ],
                'slug' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Update theme from Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/theme/update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'perform_update'],
            'permission_callback' => Peanut_Connect_Auth::hub_permission_callback_for('perform_updates'),
            'args' => [
                'type' => [
                    'default' => 'theme',
                    'type' => 'string',
                ],
                'slug' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Activate plugin from Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/plugin/activate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'activate_plugin'],
            'permission_callback' => Peanut_Connect_Auth::hub_permission_callback_for('perform_updates'),
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Deactivate plugin from Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/plugin/deactivate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'deactivate_plugin'],
            'permission_callback' => Peanut_Connect_Auth::hub_permission_callback_for('perform_updates'),
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Bulk update plugins from Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/plugins/bulk-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'bulk_update_plugins'],
            'permission_callback' => Peanut_Connect_Auth::hub_permission_callback_for('perform_updates'),
        ]);

        // Get error log entries for Hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/error-log', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_error_log_entries'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'hub_permission_callback'],
            'args' => [
                'limit' => [
                    'default' => 50,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Get health data for Hub (used to refresh health data after updates)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/health', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_hub_health'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'hub_permission_callback'],
        ]);

        // =====================
        // Hub Tracking endpoints (public, for frontend tracking)
        // =====================

        // Track event (pageview, click, etc.)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/track', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'track_event'],
            'permission_callback' => '__return_true',
            'args' => [
                'visitor_id' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'event_type' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Identify visitor (attach email/name)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/identify', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'identify_visitor'],
            'permission_callback' => '__return_true',
            'args' => [
                'visitor_id' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_email',
                ],
            ],
        ]);

        // Track conversion
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/conversion', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'track_conversion'],
            'permission_callback' => '__return_true',
            'args' => [
                'visitor_id' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Track popup interaction
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/popup-interaction', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'track_popup_interaction'],
            'permission_callback' => '__return_true',
            'args' => [
                'popup_id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'action' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['view', 'click', 'convert', 'dismiss', 'close'],
                ],
            ],
        ]);

        // =====================
        // Hub Settings endpoints (admin)
        // =====================

        // Get hub connection settings
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/settings', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_hub_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Update hub connection settings
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/settings', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_hub_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Test hub connection
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/test', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'test_hub_connection'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Disconnect from hub
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/hub/disconnect', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'disconnect_from_hub'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);
    }

    /**
     * Perform update endpoint (used by Hub)
     */
    public function perform_update(WP_REST_Request $request): WP_REST_Response {
        $type = $request->get_param('type');
        $slug = $request->get_param('slug');

        $result = Peanut_Connect_Updates::perform_update($type, $slug);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        return new WP_REST_Response($result, 200);
    }

    // =====================
    // Admin endpoint callbacks
    // =====================

    /**
     * Check if current user has admin permissions
     */
    public function admin_permission_check(): bool {
        return current_user_can('manage_options');
    }

    /**
     * Get settings for admin panel (Hub-only)
     */
    public function get_settings(WP_REST_Request $request): WP_REST_Response {
        // Hub settings
        $hub_url = get_option('peanut_connect_hub_url');
        $hub_api_key = get_option('peanut_connect_hub_api_key');
        $hub_last_sync = get_option('peanut_connect_last_hub_sync');
        $hub_mode = get_option('peanut_connect_hub_mode', 'standard');
        $tracking_enabled = get_option('peanut_connect_tracking_enabled', false);
        $track_logged_in = get_option('peanut_connect_track_logged_in', false);

        // Get Peanut Suite info
        $peanut_suite = null;
        if (function_exists('peanut_is_module_active')) {
            $peanut_suite = [
                'installed' => true,
                'version' => defined('PEANUT_VERSION') ? PEANUT_VERSION : 'unknown',
                'modules' => function_exists('peanut_get_active_modules') ? peanut_get_active_modules() : [],
            ];
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'hub' => [
                    'connected' => !empty($hub_url) && !empty($hub_api_key),
                    'url' => $hub_url ?: '',
                    'api_key_set' => !empty($hub_api_key),
                    'last_sync' => $hub_last_sync,
                    'mode' => $hub_mode,
                    'tracking_enabled' => (bool) $tracking_enabled,
                    'track_logged_in' => (bool) $track_logged_in,
                ],
                'peanut_suite' => $peanut_suite,
            ],
        ], 200);
    }

    /**
     * Auto-connect to Hub by generating a key locally and sending it to Hub
     *
     * This is the preferred connection method:
     * 1. WordPress generates a random 64-char API key
     * 2. WordPress sends the key to Hub's /api/v1/sites/connect endpoint
     * 3. Hub finds the site by URL (must already exist in Hub)
     * 4. Hub stores the key hash and activates the site
     * 5. WordPress saves both Hub URL and API key locally
     */
    public function auto_connect_to_hub(WP_REST_Request $request): WP_REST_Response {
        $hub_url = $request->get_param('hub_url');

        if (empty($hub_url)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Hub URL is required.', 'peanut-connect'),
            ], 400);
        }

        // Validate URL format
        if (!filter_var($hub_url, FILTER_VALIDATE_URL)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Invalid Hub URL format.', 'peanut-connect'),
            ], 400);
        }

        // Generate a random 64-character API key
        $api_key = wp_generate_password(64, false, false);

        // Build the connect endpoint URL
        $endpoint = rtrim($hub_url, '/') . '/api/v1/sites/connect';

        // Send the key to Hub
        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode([
                'site_url' => get_site_url(),
                'api_key' => $api_key,
                'connect_version' => PEANUT_CONNECT_VERSION,
                'wp_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => sprintf(
                    __('Failed to connect to Hub: %s', 'peanut-connect'),
                    $response->get_error_message()
                ),
            ], 400);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        // Check for specific error codes
        if (!empty($body['code'])) {
            $error_message = $body['message'] ?? __('Connection failed.', 'peanut-connect');

            switch ($body['code']) {
                case 'SITE_NOT_FOUND':
                    $error_message = __('This site is not registered in Hub. Please ask your agency to add this site first.', 'peanut-connect');
                    break;
                case 'ALREADY_CONNECTED':
                    $error_message = __('This site is already connected to Hub. Disconnect from Hub first to reconnect.', 'peanut-connect');
                    break;
            }

            return new WP_REST_Response([
                'success' => false,
                'message' => $error_message,
                'code' => $body['code'],
            ], $status_code);
        }

        // Check for success
        // Debug logging
        error_log('Peanut Connect: Hub response status=' . $status_code . ', success=' . ($body['success'] ?? 'null'));

        if ($status_code >= 200 && $status_code < 300 && ($body['success'] ?? false)) {
            // Save the Hub URL and API key locally
            error_log('Peanut Connect: Saving hub_url=' . $hub_url);
            $url_saved = update_option('peanut_connect_hub_url', $hub_url);
            error_log('Peanut Connect: hub_url saved=' . ($url_saved ? 'yes' : 'no'));

            error_log('Peanut Connect: Saving api_key (length=' . strlen($api_key) . ')');
            $key_saved = update_option('peanut_connect_hub_api_key', $api_key);
            error_log('Peanut Connect: api_key saved=' . ($key_saved ? 'yes' : 'no'));

            // Log activity
            Peanut_Connect_Activity_Log::log('hub_connected', 'success', 'Connected to Hub', [
                'hub_url' => $hub_url,
                'site_name' => $body['site']['name'] ?? '',
                'agency' => $body['agency']['name'] ?? '',
            ]);

            // Send initial heartbeat
            Peanut_Connect_Hub_Sync::send_heartbeat();

            return new WP_REST_Response([
                'success' => true,
                'message' => __('Successfully connected to Hub!', 'peanut-connect'),
                'data' => [
                    'site' => $body['site'] ?? [],
                    'client' => $body['client'] ?? [],
                    'agency' => $body['agency'] ?? [],
                ],
            ], 200);
        }

        // Generic error
        return new WP_REST_Response([
            'success' => false,
            'message' => $body['message'] ?? __('Failed to connect to Hub.', 'peanut-connect'),
        ], $status_code ?: 400);
    }

    /**
     * Test Hub connection
     */
    public function test_hub_connection(WP_REST_Request $request): WP_REST_Response {
        $hub_url = get_option('peanut_connect_hub_url');
        $api_key = get_option('peanut_connect_hub_api_key');

        if (empty($hub_url) || empty($api_key)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Hub not configured.', 'peanut-connect'),
            ], 400);
        }

        $result = Peanut_Connect_Hub_Sync::verify_hub_connection($hub_url, $api_key);

        if ($result['success']) {
            return new WP_REST_Response([
                'success' => true,
                'message' => __('Hub connection successful.', 'peanut-connect'),
                'data' => [
                    'site' => $result['site'] ?? [],
                    'client' => $result['client'] ?? [],
                    'agency' => $result['agency'] ?? [],
                ],
            ], 200);
        }

        return new WP_REST_Response([
            'success' => false,
            'message' => $result['message'] ?? __('Failed to connect to Hub.', 'peanut-connect'),
        ], 400);
    }

    /**
     * Disconnect from Hub
     */
    public function disconnect_hub(WP_REST_Request $request): WP_REST_Response {
        // Get current Hub URL and API key before deleting
        $hub_url = get_option('peanut_connect_hub_url');
        $api_key = get_option('peanut_connect_hub_api_key');

        // Notify Hub about disconnect (best effort - don't fail if Hub is unreachable)
        if (!empty($hub_url) && !empty($api_key)) {
            $disconnect_endpoint = rtrim($hub_url, '/') . '/api/v1/sites/disconnect';
            wp_remote_post($disconnect_endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => wp_json_encode([
                    'site_url' => get_site_url(),
                    'api_key' => $api_key,
                ]),
                'timeout' => 10,
                'blocking' => false, // Don't wait for response
            ]);
        }

        // Clear local options
        delete_option('peanut_connect_hub_url');
        delete_option('peanut_connect_hub_api_key');
        delete_option('peanut_connect_last_hub_sync');

        // Log activity
        Peanut_Connect_Activity_Log::log_disconnect('admin');

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Disconnected from Hub.', 'peanut-connect'),
        ], 200);
    }

    /**
     * Trigger Hub sync
     */
    public function trigger_hub_sync(WP_REST_Request $request): WP_REST_Response {
        // First send heartbeat with health data
        $heartbeat_result = Peanut_Connect_Hub_Sync::send_heartbeat();

        return new WP_REST_Response([
            'success' => $heartbeat_result['success'],
            'message' => $heartbeat_result['success']
                ? __('Sync completed successfully.', 'peanut-connect')
                : ($heartbeat_result['message'] ?? __('Sync failed.', 'peanut-connect')),
        ], $heartbeat_result['success'] ? 200 : 400);
    }

    /**
     * Update Hub Mode setting (v2.6.0+)
     *
     * Controls how Peanut Suite behaves when connected to Hub:
     * - standard: Suite works normally
     * - hide_suite: Suite menu hidden but still runs
     * - disable_suite: Suite fully disabled
     */
    public function update_hub_mode(WP_REST_Request $request): WP_REST_Response {
        $mode = $request->get_param('mode');

        // Validate mode (already validated by REST API args, but double-check)
        $valid_modes = ['standard', 'hide_suite', 'disable_suite'];
        if (!in_array($mode, $valid_modes, true)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Invalid hub mode.', 'peanut-connect'),
            ], 400);
        }

        update_option('peanut_connect_hub_mode', $mode);

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Hub mode updated.', 'peanut-connect'),
            'mode' => $mode,
        ], 200);
    }

    /**
     * Get dashboard data (Hub-focused)
     */
    public function get_dashboard(WP_REST_Request $request): WP_REST_Response {
        // Hub connection info
        $hub_url = get_option('peanut_connect_hub_url');
        $hub_api_key = get_option('peanut_connect_hub_api_key');
        $hub_last_sync = get_option('peanut_connect_last_hub_sync');

        // Get health summary
        $health_data = Peanut_Connect_Health::get_health_data();
        $issues = [];
        $status = 'healthy';

        // Check for issues (with null safety)
        if (!empty($health_data['wordpress']['needs_update'])) {
            $issues[] = 'WordPress core update available';
            $status = 'warning';
        }
        if (isset($health_data['php']) && !($health_data['php']['is_recommended'] ?? true)) {
            $issues[] = 'PHP version is below recommended (' . ($health_data['php']['recommended'] ?? '8.0') . ')';
            if (!($health_data['php']['is_minimum'] ?? true)) {
                $status = 'critical';
            } elseif ($status !== 'critical') {
                $status = 'warning';
            }
        }
        if (isset($health_data['ssl']) && !($health_data['ssl']['enabled'] ?? true)) {
            $issues[] = 'SSL is not enabled';
            $status = 'critical';
        } elseif (isset($health_data['ssl']['days_until_expiry']) && $health_data['ssl']['days_until_expiry'] < 14) {
            $issues[] = 'SSL certificate expiring soon (' . $health_data['ssl']['days_until_expiry'] . ' days)';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }
        $plugins_updates = $health_data['plugins']['updates_available'] ?? 0;
        if ($plugins_updates > 0) {
            $issues[] = $plugins_updates . ' plugin update(s) available';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }
        $themes_updates = $health_data['themes']['updates_available'] ?? 0;
        if ($themes_updates > 0) {
            $issues[] = $themes_updates . ' theme update(s) available';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }
        $disk_used = $health_data['disk_space']['used_percentage'] ?? 0;
        if ($disk_used > 90) {
            $issues[] = 'Disk space is running low (' . $disk_used . '% used)';
            $status = 'critical';
        } elseif ($disk_used > 80) {
            $issues[] = 'Disk space is getting low (' . $disk_used . '% used)';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }

        // Get updates summary
        $updates = Peanut_Connect_Updates::get_available_updates();

        // Get Peanut Suite info
        $peanut_suite = null;
        if (function_exists('peanut_is_module_active')) {
            $peanut_suite = [
                'installed' => true,
                'version' => defined('PEANUT_VERSION') ? PEANUT_VERSION : null,
            ];
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'hub' => [
                    'connected' => !empty($hub_url) && !empty($hub_api_key),
                    'url' => $hub_url ?: '',
                    'last_sync' => $hub_last_sync,
                ],
                'health_summary' => [
                    'status' => $status,
                    'issues' => $issues,
                ],
                'updates' => [
                    'plugins' => count($updates['plugins'] ?? []),
                    'themes' => count($updates['themes'] ?? []),
                    'core' => $updates['core']['needs_update'] ?? false,
                ],
                'peanut_suite' => $peanut_suite,
            ],
        ], 200);
    }

    /**
     * Get health data for admin (no Bearer token required)
     */
    public function get_admin_health(WP_REST_Request $request): WP_REST_Response {
        $health = Peanut_Connect_Health::get_health_data();

        return new WP_REST_Response([
            'success' => true,
            'data' => $health,
        ], 200);
    }

    /**
     * Get health data for Hub (requires Bearer token)
     * Used to refresh health data after plugin/theme updates
     */
    public function get_hub_health(WP_REST_Request $request): WP_REST_Response {
        $health = Peanut_Connect_Health::get_health_data();

        return new WP_REST_Response([
            'success' => true,
            'data' => $health,
        ], 200);
    }

    /**
     * Get updates for admin (no Bearer token required)
     */
    public function get_admin_updates(WP_REST_Request $request): WP_REST_Response {
        $updates = Peanut_Connect_Updates::get_available_updates();

        return new WP_REST_Response([
            'success' => true,
            'data' => $updates,
        ], 200);
    }

    /**
     * Perform update for admin (no Bearer token required)
     */
    public function admin_perform_update(WP_REST_Request $request): WP_REST_Response {
        $type = $request->get_param('type');
        $slug = $request->get_param('slug');

        $result = Peanut_Connect_Updates::perform_update($type, $slug);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    // =====================
    // Error Log endpoint callbacks
    // =====================

    /**
     * Get error log entries
     */
    public function get_error_log(WP_REST_Request $request): WP_REST_Response {
        $limit = $request->get_param('limit') ?? 50;
        $offset = $request->get_param('offset') ?? 0;
        $level = $request->get_param('level') ?? '';

        if ($level) {
            $entries = Peanut_Connect_Error_Log::get_entries_by_level($level, $limit);
        } else {
            $entries = Peanut_Connect_Error_Log::get_entries($limit, $offset);
        }

        $counts = Peanut_Connect_Error_Log::get_counts();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'entries' => $entries,
                'counts' => $counts,
                'logging_enabled' => get_option('peanut_connect_error_logging', true),
            ],
        ], 200);
    }

    /**
     * Get error counts
     */
    public function get_error_counts(WP_REST_Request $request): WP_REST_Response {
        $counts = Peanut_Connect_Error_Log::get_counts();
        $recent = Peanut_Connect_Error_Log::get_recent_counts();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'all_time' => $counts,
                'last_24h' => $recent,
                'logging_enabled' => get_option('peanut_connect_error_logging', true),
            ],
        ], 200);
    }

    /**
     * Clear error log
     */
    public function clear_error_log(WP_REST_Request $request): WP_REST_Response {
        $result = Peanut_Connect_Error_Log::clear();

        return new WP_REST_Response([
            'success' => $result,
            'message' => $result
                ? __('Error log cleared successfully.', 'peanut-connect')
                : __('Failed to clear error log.', 'peanut-connect'),
        ], $result ? 200 : 500);
    }

    /**
     * Export error log as CSV
     */
    public function export_error_log(WP_REST_Request $request): WP_REST_Response {
        $csv = Peanut_Connect_Error_Log::export_csv();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'csv' => $csv,
                'filename' => 'peanut-error-log-' . date('Y-m-d') . '.csv',
            ],
        ], 200);
    }

    /**
     * Update error logging settings
     */
    public function update_error_settings(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();

        if (isset($params['enabled'])) {
            update_option('peanut_connect_error_logging', (bool) $params['enabled']);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'logging_enabled' => get_option('peanut_connect_error_logging', true),
            ],
        ], 200);
    }

    // =====================
    // Activity Log Handlers
    // =====================

    /**
     * Get activity log entries
     */
    public function get_activity_log(WP_REST_Request $request): WP_REST_Response {
        $limit = $request->get_param('limit') ?? 50;
        $offset = $request->get_param('offset') ?? 0;
        $type = $request->get_param('type') ?? '';
        $status = $request->get_param('status') ?? '';

        $entries = Peanut_Connect_Activity_Log::get_entries($limit, $offset, $type, $status);
        $counts = Peanut_Connect_Activity_Log::get_recent_counts();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'entries' => $entries,
                'counts' => $counts,
            ],
        ], 200);
    }

    /**
     * Get activity log counts
     */
    public function get_activity_counts(WP_REST_Request $request): WP_REST_Response {
        $by_type = Peanut_Connect_Activity_Log::get_counts_by_type();
        $recent = Peanut_Connect_Activity_Log::get_recent_counts();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'by_type' => $by_type,
                'last_24h' => $recent,
            ],
        ], 200);
    }

    /**
     * Clear activity log
     */
    public function clear_activity_log(WP_REST_Request $request): WP_REST_Response {
        $result = Peanut_Connect_Activity_Log::clear();

        return new WP_REST_Response([
            'success' => $result,
            'message' => $result
                ? __('Activity log cleared.', 'peanut-connect')
                : __('Failed to clear activity log.', 'peanut-connect'),
        ], $result ? 200 : 500);
    }

    /**
     * Export activity log as CSV
     */
    public function export_activity_log(WP_REST_Request $request): WP_REST_Response {
        $csv = Peanut_Connect_Activity_Log::export_csv();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'csv' => $csv,
                'filename' => 'peanut-activity-log-' . date('Y-m-d') . '.csv',
            ],
        ], 200);
    }

    // =====================
    // Hub Tracking endpoint callbacks
    // =====================

    /**
     * Check rate limit for tracking endpoints
     *
     * @param WP_REST_Request $request The request object.
     * @param string $endpoint Endpoint identifier for rate limiting.
     * @return WP_REST_Response|null Returns response if rate limited, null if allowed.
     */
    private function check_tracking_rate_limit(WP_REST_Request $request, string $endpoint): ?WP_REST_Response {
        $client_id = Peanut_Connect_Rate_Limiter::get_client_identifier($request);
        $result = Peanut_Connect_Rate_Limiter::check($client_id, $endpoint);

        if (is_wp_error($result)) {
            $data = $result->get_error_data();
            $response = new WP_REST_Response([
                'success' => false,
                'code' => 'rate_limit_exceeded',
                'message' => $result->get_error_message(),
            ], 429);
            $response->header('Retry-After', $data['retry_after'] ?? 60);
            return $response;
        }

        return null;
    }

    /**
     * Track event (pageview, click, etc.)
     */
    public function track_event(WP_REST_Request $request): WP_REST_Response {
        // Check rate limit
        $rate_limited = $this->check_tracking_rate_limit($request, 'track');
        if ($rate_limited) {
            return $rate_limited;
        }

        if (!class_exists('Peanut_Connect_Tracker')) {
            return new WP_REST_Response(['success' => false, 'message' => 'Tracking not initialized'], 500);
        }

        $visitor_id = $request->get_param('visitor_id');
        $event_type = $request->get_param('event_type');

        $data = [
            'page_url' => $request->get_param('page_url'),
            'page_title' => $request->get_param('page_title'),
            'referrer' => $request->get_param('referrer'),
            'metadata' => $request->get_param('metadata'),
        ];

        $event_id = Peanut_Connect_Tracker::record_event($visitor_id, $event_type, $data);

        return new WP_REST_Response([
            'success' => true,
            'event_id' => $event_id,
        ], 201);
    }

    /**
     * Identify visitor (attach email/name)
     */
    public function identify_visitor(WP_REST_Request $request): WP_REST_Response {
        // Check rate limit
        $rate_limited = $this->check_tracking_rate_limit($request, 'identify');
        if ($rate_limited) {
            return $rate_limited;
        }

        if (!class_exists('Peanut_Connect_Tracker')) {
            return new WP_REST_Response(['success' => false, 'message' => 'Tracking not initialized'], 500);
        }

        $visitor_id = $request->get_param('visitor_id');
        $email = $request->get_param('email');
        $name = $request->get_param('name');

        Peanut_Connect_Tracker::identify_visitor($visitor_id, $email, $name);

        return new WP_REST_Response([
            'success' => true,
        ], 200);
    }

    /**
     * Track conversion
     */
    public function track_conversion(WP_REST_Request $request): WP_REST_Response {
        // Check rate limit
        $rate_limited = $this->check_tracking_rate_limit($request, 'conversion');
        if ($rate_limited) {
            return $rate_limited;
        }

        if (!class_exists('Peanut_Connect_Tracker')) {
            return new WP_REST_Response(['success' => false, 'message' => 'Tracking not initialized'], 500);
        }

        $visitor_id = $request->get_param('visitor_id');
        $type = $request->get_param('type');

        $data = [
            'value' => $request->get_param('value'),
            'currency' => $request->get_param('currency') ?? 'USD',
            'email' => $request->get_param('email'),
            'name' => $request->get_param('name'),
            'order_id' => $request->get_param('order_id'),
            'metadata' => $request->get_param('metadata'),
        ];

        $conversion_id = Peanut_Connect_Tracker::record_conversion($visitor_id, $type, $data);

        return new WP_REST_Response([
            'success' => true,
            'conversion_id' => $conversion_id,
        ], 201);
    }

    /**
     * Track popup interaction
     */
    public function track_popup_interaction(WP_REST_Request $request): WP_REST_Response {
        // Check rate limit
        $rate_limited = $this->check_tracking_rate_limit($request, 'popup_interaction');
        if ($rate_limited) {
            return $rate_limited;
        }

        if (!class_exists('Peanut_Connect_Tracker')) {
            return new WP_REST_Response(['success' => false, 'message' => 'Tracking not initialized'], 500);
        }

        $popup_id = (int) $request->get_param('popup_id');
        $action = $request->get_param('action');
        $visitor_id = $request->get_param('visitor_id');

        $data = [
            'page_url' => $request->get_param('page_url'),
            'form_data' => $request->get_param('data'),
        ];

        $interaction_id = Peanut_Connect_Tracker::record_popup_interaction($popup_id, $action, $visitor_id, $data);

        return new WP_REST_Response([
            'success' => true,
            'interaction_id' => $interaction_id,
        ], 201);
    }

    // =====================
    // Hub Settings endpoint callbacks
    // =====================

    /**
     * Get hub connection settings
     */
    public function get_hub_settings(WP_REST_Request $request): WP_REST_Response {
        $hub_url = get_option('peanut_connect_hub_url', '');
        $api_key = get_option('peanut_connect_hub_api_key', '');
        $tracking_enabled = get_option('peanut_connect_tracking_enabled', false);
        $last_sync = get_option('peanut_connect_last_hub_sync', '');

        // Get unsynced counts if database class is loaded
        $pending = [];
        if (class_exists('Peanut_Connect_Database')) {
            $pending = Peanut_Connect_Database::get_unsynced_counts();
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'hub_url' => $hub_url,
                'connected' => !empty($hub_url) && !empty($api_key),
                'api_key_set' => !empty($api_key),
                'tracking_enabled' => $tracking_enabled,
                'last_sync' => $last_sync,
                'pending_sync' => $pending,
            ],
        ], 200);
    }

    /**
     * Update hub connection settings
     */
    public function update_hub_settings(WP_REST_Request $request): WP_REST_Response {
        $hub_url = $request->get_param('hub_url');
        $api_key = $request->get_param('api_key');
        $tracking_enabled = $request->get_param('tracking_enabled');
        $hub_mode = $request->get_param('mode');

        // Validate and save hub URL
        if ($hub_url !== null) {
            $hub_url = esc_url_raw($hub_url);
            update_option('peanut_connect_hub_url', $hub_url);
        }

        // Save API key (only if provided, don't clear existing)
        if (!empty($api_key)) {
            update_option('peanut_connect_hub_api_key', sanitize_text_field($api_key));
        }

        // Save tracking enabled setting
        if ($tracking_enabled !== null) {
            update_option('peanut_connect_tracking_enabled', (bool) $tracking_enabled);
        }

        // Save track logged-in users setting
        $track_logged_in = $request->get_param('track_logged_in');
        if ($track_logged_in !== null) {
            update_option('peanut_connect_track_logged_in', (bool) $track_logged_in);
        }

        // Save Hub Mode setting (v2.6.0+)
        if ($hub_mode !== null) {
            $valid_modes = ['standard', 'hide_suite', 'disable_suite'];
            if (in_array($hub_mode, $valid_modes, true)) {
                update_option('peanut_connect_hub_mode', $hub_mode);
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Hub settings updated.', 'peanut-connect'),
        ], 200);
    }

    // =====================
    // Plugin Management Handlers
    // =====================

    /**
     * Get all plugins with full details
     */
    public function get_all_plugins(WP_REST_Request $request): WP_REST_Response {
        $plugins = Peanut_Connect_Updates::get_all_plugins();

        return new WP_REST_Response([
            'success' => true,
            'data' => $plugins,
            'total' => count($plugins),
            'active' => count(array_filter($plugins, fn($p) => $p['active'])),
            'updates_available' => count(array_filter($plugins, fn($p) => $p['has_update'])),
        ], 200);
    }

    /**
     * Activate a plugin
     */
    public function activate_plugin(WP_REST_Request $request): WP_REST_Response {
        $plugin_file = $request->get_param('plugin');
        $result = Peanut_Connect_Updates::activate_plugin($plugin_file);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('plugin_activated', 'success', $result['name'], [
                'plugin' => $result['plugin'],
            ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate_plugin(WP_REST_Request $request): WP_REST_Response {
        $plugin_file = $request->get_param('plugin');
        $result = Peanut_Connect_Updates::deactivate_plugin($plugin_file);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('plugin_deactivated', 'success', $result['name'], [
                'plugin' => $result['plugin'],
            ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    /**
     * Delete a plugin
     */
    public function delete_plugin(WP_REST_Request $request): WP_REST_Response {
        $plugin_file = $request->get_param('plugin');
        $result = Peanut_Connect_Updates::delete_plugin($plugin_file);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('plugin_deleted', 'warning', $result['name'], [
                'plugin' => $result['plugin'],
            ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    /**
     * Bulk update all plugins
     */
    public function bulk_update_plugins(WP_REST_Request $request): WP_REST_Response {
        $results = Peanut_Connect_Updates::bulk_update_plugins();

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            $success_count = count($results['success']);
            $failed_count = count($results['failed']);
            Peanut_Connect_Activity_Log::log(
                'bulk_plugin_update',
                $failed_count > 0 ? 'warning' : 'success',
                sprintf('%d updated, %d failed', $success_count, $failed_count),
                $results
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $results,
        ], 200);
    }

    // =====================
    // Theme Management Handlers
    // =====================

    /**
     * Get all themes with full details
     */
    public function get_all_themes(WP_REST_Request $request): WP_REST_Response {
        $themes = Peanut_Connect_Updates::get_all_themes();

        return new WP_REST_Response([
            'success' => true,
            'data' => $themes,
            'total' => count($themes),
            'updates_available' => count(array_filter($themes, fn($t) => $t['has_update'])),
        ], 200);
    }

    /**
     * Activate a theme
     */
    public function activate_theme(WP_REST_Request $request): WP_REST_Response {
        $stylesheet = $request->get_param('theme');
        $result = Peanut_Connect_Updates::activate_theme($stylesheet);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('theme_activated', 'success', $result['name'], [
                'theme' => $result['theme'],
            ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    /**
     * Delete a theme
     */
    public function delete_theme(WP_REST_Request $request): WP_REST_Response {
        $stylesheet = $request->get_param('theme');
        $result = Peanut_Connect_Updates::delete_theme($stylesheet);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('theme_deleted', 'warning', $result['name'], [
                'theme' => $result['theme'],
            ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    /**
     * Bulk update all themes
     */
    public function bulk_update_themes(WP_REST_Request $request): WP_REST_Response {
        $results = Peanut_Connect_Updates::bulk_update_themes();

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            $success_count = count($results['success']);
            $failed_count = count($results['failed']);
            Peanut_Connect_Activity_Log::log(
                'bulk_theme_update',
                $failed_count > 0 ? 'warning' : 'success',
                sprintf('%d updated, %d failed', $success_count, $failed_count),
                $results
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $results,
        ], 200);
    }

    /**
     * Toggle auto-update for plugin or theme
     */
    public function toggle_auto_update(WP_REST_Request $request): WP_REST_Response {
        $type = $request->get_param('type');
        $item = $request->get_param('item');
        $enable = $request->get_param('enable');

        $result = Peanut_Connect_Updates::toggle_auto_update($type, $item, $enable);

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'code' => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], 400);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    // =====================
    // Security Settings Handlers
    // =====================

    /**
     * Get security settings
     */
    public function get_security_settings(WP_REST_Request $request): WP_REST_Response {
        if (!class_exists('Peanut_Connect_Security')) {
            return new WP_REST_Response([
                'success' => true,
                'data' => [
                    'hide_login' => [
                        'enabled' => false,
                        'custom_slug' => '',
                        'available' => false,
                    ],
                    'disable_comments' => [
                        'enabled' => false,
                        'hide_existing' => false,
                    ],
                    'disable_xmlrpc' => get_option('peanut_connect_disable_xmlrpc', false),
                    'disable_file_editing' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT,
                    'remove_version' => get_option('peanut_connect_remove_version', false),
                ],
            ], 200);
        }

        $settings = Peanut_Connect_Security::get_settings();

        return new WP_REST_Response([
            'success' => true,
            'data' => $settings,
        ], 200);
    }

    /**
     * Update security settings
     */
    public function update_security_settings(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();
        $updated = [];

        // Disable XML-RPC
        if (isset($params['disable_xmlrpc'])) {
            update_option('peanut_connect_disable_xmlrpc', (bool) $params['disable_xmlrpc']);
            $updated['disable_xmlrpc'] = (bool) $params['disable_xmlrpc'];
        }

        // Remove WordPress version
        if (isset($params['remove_version'])) {
            update_option('peanut_connect_remove_version', (bool) $params['remove_version']);
            $updated['remove_version'] = (bool) $params['remove_version'];
        }

        // Disable comments
        if (isset($params['disable_comments'])) {
            update_option('peanut_connect_disable_comments', (bool) $params['disable_comments']);
            $updated['disable_comments'] = (bool) $params['disable_comments'];
        }

        if (isset($params['hide_existing_comments'])) {
            update_option('peanut_connect_hide_existing_comments', (bool) $params['hide_existing_comments']);
            $updated['hide_existing_comments'] = (bool) $params['hide_existing_comments'];
        }

        // Hide login - requires additional class
        if (isset($params['hide_login_enabled'])) {
            update_option('peanut_connect_hide_login', (bool) $params['hide_login_enabled']);
            $updated['hide_login_enabled'] = (bool) $params['hide_login_enabled'];
        }

        if (isset($params['hide_login_slug'])) {
            $slug = sanitize_title($params['hide_login_slug']);
            if (!empty($slug) && $slug !== 'wp-admin' && $slug !== 'wp-login') {
                update_option('peanut_connect_login_slug', $slug);
                $updated['hide_login_slug'] = $slug;
            }
        }

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('security_settings_updated', 'info', 'Security settings changed', $updated);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Security settings updated.', 'peanut-connect'),
            'data' => $updated,
        ], 200);
    }

    // =====================
    // Permissions Handlers
    // =====================

    /**
     * Get hub permissions
     */
    public function get_permissions(WP_REST_Request $request): WP_REST_Response {
        $permissions = get_option('peanut_connect_permissions', [
            'perform_updates' => false,
            'access_analytics' => false,
        ]);

        return new WP_REST_Response([
            'perform_updates' => !empty($permissions['perform_updates']),
            'access_analytics' => !empty($permissions['access_analytics']),
        ], 200);
    }

    /**
     * Update hub permissions
     */
    public function update_permissions(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();
        $permissions = get_option('peanut_connect_permissions', [
            'perform_updates' => false,
            'access_analytics' => false,
        ]);

        if (isset($params['perform_updates'])) {
            $permissions['perform_updates'] = (bool) $params['perform_updates'];
        }

        if (isset($params['access_analytics'])) {
            $permissions['access_analytics'] = (bool) $params['access_analytics'];
        }

        update_option('peanut_connect_permissions', $permissions);

        // Log activity
        if (class_exists('Peanut_Connect_Activity_Log')) {
            Peanut_Connect_Activity_Log::log('permissions_updated', 'info', 'Hub permissions changed', $permissions);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Hub permissions updated.', 'peanut-connect'),
        ], 200);
    }

    // =====================
    // Hub Error Log Handlers
    // =====================

    /**
     * Get error log entries for Hub
     *
     * Returns PHP error log entries captured by Peanut Connect's error logger.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response Response with error log entries.
     */
    public function get_error_log_entries(WP_REST_Request $request): WP_REST_Response {
        $limit = $request->get_param('limit') ?: 50;

        // Initialize the error log class to set up paths
        Peanut_Connect_Error_Log::init();

        // Get entries
        $entries = Peanut_Connect_Error_Log::get_entries($limit);

        // Get counts for summary
        $counts = Peanut_Connect_Error_Log::get_recent_counts();

        return new WP_REST_Response([
            'success' => true,
            'entries' => $entries,
            'total' => count($entries),
            'counts' => $counts,
        ], 200);
    }
}
