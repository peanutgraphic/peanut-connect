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

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'connection' => [
                    'connected' => !empty($site_key) && !empty($manager_url),
                    'manager_url' => $manager_url,
                    'last_sync' => $last_sync,
                    'site_key' => $site_key,
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
}
