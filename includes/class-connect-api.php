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

        // Update permissions
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/permissions', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_permissions'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Generate site key
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/generate-key', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'generate_site_key'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Regenerate site key
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/regenerate-key', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'regenerate_site_key'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Disconnect from manager (admin action)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/disconnect', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'admin_disconnect'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Hub settings - save
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/settings/hub', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'save_hub_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'hub_url' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'esc_url_raw',
                ],
                'api_key' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
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
        // Manager endpoints (require Bearer token)
        // =====================
        // Verify connection
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/verify', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'verify'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Health check
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/health', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_health'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Get available updates
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/updates', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_updates'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Perform update
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'perform_update'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
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

        // Get Peanut Suite analytics
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/analytics', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_analytics'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('access_analytics'),
        ]);

        // Disconnect notification
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/disconnect', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'handle_disconnect'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Get all plugins (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/plugins', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_plugins'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Get all themes (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/themes', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_themes'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Activate plugin (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/plugin/activate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'activate_plugin'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Deactivate plugin (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/plugin/deactivate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'deactivate_plugin'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete plugin (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/plugin/delete', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'delete_plugin'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
            'args' => [
                'plugin' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Activate theme (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/theme/activate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'activate_theme'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
            'args' => [
                'theme' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete theme (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/theme/delete', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'delete_theme'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
            'args' => [
                'theme' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Bulk update plugins (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/plugins/bulk-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'bulk_update_plugins'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
        ]);

        // Bulk update themes (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/themes/bulk-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'bulk_update_themes'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
        ]);

        // Toggle auto-update (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/auto-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'toggle_auto_update'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
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

        // Get security settings (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/security', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_security_settings'],
            'permission_callback' => [Peanut_Connect_Auth::class, 'permission_callback'],
        ]);

        // Update security settings (for manager/hub)
        register_rest_route(PEANUT_CONNECT_API_NAMESPACE, '/manager/security', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_security_settings'],
            'permission_callback' => Peanut_Connect_Auth::permission_callback_for('perform_updates'),
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
     * Verify connection endpoint
     */
    public function verify(WP_REST_Request $request): WP_REST_Response {
        $health_data = Peanut_Connect_Health::get_health_data();

        return new WP_REST_Response([
            'success' => true,
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'wp_version' => get_bloginfo('version'),
            'permissions' => Peanut_Connect_Auth::get_permissions(),
            'peanut_suite' => $health_data['peanut_suite'],
            'health' => [
                'wp_version' => $health_data['wp_version'],
                'php_version' => $health_data['php_version'],
                'ssl' => $health_data['ssl'],
            ],
        ], 200);
    }

    /**
     * Get health data endpoint
     */
    public function get_health(WP_REST_Request $request): WP_REST_Response {
        $health = Peanut_Connect_Health::get_health_data();

        return new WP_REST_Response($health, 200);
    }

    /**
     * Get available updates endpoint
     */
    public function get_updates(WP_REST_Request $request): WP_REST_Response {
        $updates = Peanut_Connect_Updates::get_available_updates();

        return new WP_REST_Response($updates, 200);
    }

    /**
     * Perform update endpoint
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

    /**
     * Get Peanut Suite analytics endpoint
     */
    public function get_analytics(WP_REST_Request $request): WP_REST_Response {
        // Check if Peanut Suite is installed
        if (!function_exists('peanut_is_module_active')) {
            return new WP_REST_Response([
                'success' => false,
                'code' => 'peanut_suite_not_installed',
                'message' => __('Peanut Suite is not installed on this site.', 'peanut-connect'),
            ], 404);
        }

        global $wpdb;

        $analytics = [
            'contacts' => 0,
            'utm_clicks' => 0,
            'link_clicks' => 0,
            'forms_submitted' => 0,
            'recent_leads' => [],
        ];

        // Date for 30-day lookback queries
        $thirty_days_ago = gmdate('Y-m-d H:i:s', strtotime('-30 days'));

        // Get contacts count if module active
        if (peanut_is_module_active('contacts')) {
            $contacts_table = $wpdb->prefix . 'peanut_contacts';

            $analytics['contacts'] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$contacts_table} WHERE created_at >= %s",
                $thirty_days_ago
            ));

            // Get recent leads
            $analytics['recent_leads'] = $wpdb->get_results($wpdb->prepare(
                "SELECT id, email, first_name, last_name, status, created_at
                 FROM {$contacts_table}
                 ORDER BY created_at DESC
                 LIMIT %d",
                5
            ), ARRAY_A);
        }

        // Get UTM clicks if module active
        if (peanut_is_module_active('utm')) {
            $utms_table = $wpdb->prefix . 'peanut_utms';
            $analytics['utm_clicks'] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(click_count) FROM {$utms_table} WHERE created_at >= %s",
                $thirty_days_ago
            ));
        }

        // Get link clicks if module active
        if (peanut_is_module_active('links')) {
            $clicks_table = $wpdb->prefix . 'peanut_link_clicks';
            $analytics['link_clicks'] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$clicks_table} WHERE clicked_at >= %s",
                $thirty_days_ago
            ));
        }

        // Check for FormFlow integration
        if (class_exists('FormFlow')) {
            $submissions_table = $wpdb->prefix . 'ff_submissions';
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $submissions_table
            ));
            if ($table_exists) {
                $analytics['forms_submitted'] = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$submissions_table} WHERE created_at >= %s",
                    $thirty_days_ago
                ));
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'period' => '30d',
            'data' => $analytics,
        ], 200);
    }

    /**
     * Handle disconnect notification from manager
     */
    public function handle_disconnect(WP_REST_Request $request): WP_REST_Response {
        // Clear manager URL but keep the site key (in case they want to reconnect)
        delete_option('peanut_connect_manager_url');
        delete_option('peanut_connect_last_sync');

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Disconnected from manager site.', 'peanut-connect'),
        ], 200);
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
     * Get settings for admin panel
     */
    public function get_settings(WP_REST_Request $request): WP_REST_Response {
        $site_key = get_option('peanut_connect_site_key');
        $manager_url = get_option('peanut_connect_manager_url');
        $last_sync = get_option('peanut_connect_last_sync');
        $permissions = get_option('peanut_connect_permissions', [
            'health_check' => true,
            'list_updates' => true,
            'perform_updates' => true,
            'access_analytics' => true,
        ]);

        // Get Peanut Suite info
        $peanut_suite = null;
        if (function_exists('peanut_is_module_active')) {
            $peanut_suite = [
                'installed' => true,
                'version' => defined('PEANUT_VERSION') ? PEANUT_VERSION : 'unknown',
                'modules' => function_exists('peanut_get_active_modules') ? peanut_get_active_modules() : [],
            ];
        }

        // Hub settings
        $hub_url = get_option('peanut_connect_hub_url');
        $hub_api_key = get_option('peanut_connect_hub_api_key');
        $hub_last_sync = get_option('peanut_connect_last_hub_sync');

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'connection' => [
                    'connected' => !empty($site_key) && !empty($manager_url),
                    'manager_url' => $manager_url,
                    'last_sync' => $last_sync,
                    'site_key' => $site_key,
                ],
                'hub' => [
                    'connected' => !empty($hub_url) && !empty($hub_api_key),
                    'url' => $hub_url ?: '',
                    'api_key' => $hub_api_key ? substr($hub_api_key, 0, 8) . '...' . substr($hub_api_key, -4) : '',
                    'api_key_set' => !empty($hub_api_key),
                    'last_sync' => $hub_last_sync,
                ],
                'permissions' => $permissions,
                'peanut_suite' => $peanut_suite,
            ],
        ], 200);
    }

    /**
     * Update permissions
     */
    public function update_permissions(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();

        $current = get_option('peanut_connect_permissions', [
            'health_check' => true,
            'list_updates' => true,
            'perform_updates' => true,
            'access_analytics' => true,
        ]);

        // Always keep health_check and list_updates enabled
        $updated = [
            'health_check' => true,
            'list_updates' => true,
            'perform_updates' => isset($params['perform_updates']) ? (bool) $params['perform_updates'] : $current['perform_updates'],
            'access_analytics' => isset($params['access_analytics']) ? (bool) $params['access_analytics'] : $current['access_analytics'],
        ];

        update_option('peanut_connect_permissions', $updated);

        // Log permission changes
        foreach (['perform_updates', 'access_analytics'] as $perm) {
            if ($current[$perm] !== $updated[$perm]) {
                Peanut_Connect_Activity_Log::log_permission_changed($perm, $updated[$perm]);
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $updated,
        ], 200);
    }

    /**
     * Save Hub settings
     */
    public function save_hub_settings(WP_REST_Request $request): WP_REST_Response {
        $hub_url = $request->get_param('hub_url');
        $api_key = $request->get_param('api_key');

        if (empty($hub_url) || empty($api_key)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Hub URL and API key are required.', 'peanut-connect'),
            ], 400);
        }

        // Validate URL format
        if (!filter_var($hub_url, FILTER_VALIDATE_URL)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Invalid Hub URL format.', 'peanut-connect'),
            ], 400);
        }

        // Save settings first (verification happens on heartbeat)
        update_option('peanut_connect_hub_url', $hub_url);
        update_option('peanut_connect_hub_api_key', $api_key);

        // Try to verify connection (optional - may fail due to server config)
        $test_result = Peanut_Connect_Hub_Sync::verify_hub_connection($hub_url, $api_key);
        $verified = $test_result['success'] ?? false;

        // Log activity
        Peanut_Connect_Activity_Log::log('hub_configured', $verified ? 'success' : 'pending', 0, [
            'hub_url' => $hub_url,
            'verified' => $verified,
        ]);

        // Trigger immediate heartbeat to sync with Hub
        if ($verified) {
            Peanut_Connect_Hub_Sync::send_heartbeat();
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => $verified
                ? __('Hub connection saved and verified.', 'peanut-connect')
                : __('Hub settings saved. Connection will be verified on next sync.', 'peanut-connect'),
            'verified' => $verified,
            'data' => [
                'site' => $test_result['site'] ?? [],
                'client' => $test_result['client'] ?? [],
                'agency' => $test_result['agency'] ?? [],
            ],
        ], 200);
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
        delete_option('peanut_connect_hub_url');
        delete_option('peanut_connect_hub_api_key');
        delete_option('peanut_connect_last_hub_sync');

        // Log activity
        Peanut_Connect_Activity_Log::log('hub_disconnected', 'info', 0, []);

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
     * Generate site key
     */
    public function generate_site_key(WP_REST_Request $request): WP_REST_Response {
        $site_key = get_option('peanut_connect_site_key');

        if ($site_key) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Site key already exists. Use regenerate to create a new one.', 'peanut-connect'),
            ], 400);
        }

        $site_key = wp_generate_password(64, false);
        update_option('peanut_connect_site_key', $site_key);

        // Log activity
        Peanut_Connect_Activity_Log::log_key_generated();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'site_key' => $site_key,
            ],
        ], 200);
    }

    /**
     * Regenerate site key
     */
    public function regenerate_site_key(WP_REST_Request $request): WP_REST_Response {
        $site_key = wp_generate_password(64, false);
        update_option('peanut_connect_site_key', $site_key);

        // Clear manager connection since the old key is now invalid
        delete_option('peanut_connect_manager_url');
        delete_option('peanut_connect_last_sync');

        // Log activity
        Peanut_Connect_Activity_Log::log_key_regenerated();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'site_key' => $site_key,
            ],
        ], 200);
    }

    /**
     * Admin disconnect from manager
     */
    public function admin_disconnect(WP_REST_Request $request): WP_REST_Response {
        delete_option('peanut_connect_site_key');
        delete_option('peanut_connect_manager_url');
        delete_option('peanut_connect_last_sync');

        // Log activity
        Peanut_Connect_Activity_Log::log_disconnect('admin');

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Disconnected from manager site.', 'peanut-connect'),
        ], 200);
    }

    /**
     * Get dashboard data
     */
    public function get_dashboard(WP_REST_Request $request): WP_REST_Response {
        $site_key = get_option('peanut_connect_site_key');
        $manager_url = get_option('peanut_connect_manager_url');
        $last_sync = get_option('peanut_connect_last_sync');

        // Get health summary
        $health_data = Peanut_Connect_Health::get_health_data();
        $issues = [];
        $status = 'healthy';

        // Check for issues
        if ($health_data['wordpress']['needs_update']) {
            $issues[] = 'WordPress core update available';
            $status = 'warning';
        }
        if (!$health_data['php']['is_recommended']) {
            $issues[] = 'PHP version is below recommended (' . $health_data['php']['recommended'] . ')';
            if (!$health_data['php']['is_minimum']) {
                $status = 'critical';
            } elseif ($status !== 'critical') {
                $status = 'warning';
            }
        }
        if (!$health_data['ssl']['enabled']) {
            $issues[] = 'SSL is not enabled';
            $status = 'critical';
        } elseif ($health_data['ssl']['days_until_expiry'] !== null && $health_data['ssl']['days_until_expiry'] < 14) {
            $issues[] = 'SSL certificate expiring soon (' . $health_data['ssl']['days_until_expiry'] . ' days)';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }
        if ($health_data['plugins']['updates_available'] > 0) {
            $issues[] = $health_data['plugins']['updates_available'] . ' plugin update(s) available';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }
        if ($health_data['themes']['updates_available'] > 0) {
            $issues[] = $health_data['themes']['updates_available'] . ' theme update(s) available';
            if ($status !== 'critical') {
                $status = 'warning';
            }
        }
        if ($health_data['disk_space']['used_percentage'] > 90) {
            $issues[] = 'Disk space is running low (' . $health_data['disk_space']['used_percentage'] . '% used)';
            $status = 'critical';
        } elseif ($health_data['disk_space']['used_percentage'] > 80) {
            $issues[] = 'Disk space is getting low (' . $health_data['disk_space']['used_percentage'] . '% used)';
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
                'connection' => [
                    'connected' => !empty($site_key) && !empty($manager_url),
                    'manager_url' => $manager_url,
                    'last_sync' => $last_sync,
                ],
                'health_summary' => [
                    'status' => $status,
                    'issues' => $issues,
                ],
                'updates' => [
                    'plugins' => count($updates['plugins']),
                    'themes' => count($updates['themes']),
                    'core' => $updates['core']['needs_update'],
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
     * Track event (pageview, click, etc.)
     */
    public function track_event(WP_REST_Request $request): WP_REST_Response {
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
}
