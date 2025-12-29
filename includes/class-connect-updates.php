<?php
/**
 * Peanut Connect Updates Handler
 *
 * Manages plugin and theme updates on the child site.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Peanut_Connect_Updates {

    /**
     * Get all available updates
     */
    public static function get_available_updates(): array {
        // Force update check
        wp_update_plugins();
        wp_update_themes();

        return [
            'plugins' => self::get_plugin_updates(),
            'themes' => self::get_theme_updates(),
            'core' => self::get_core_update(),
        ];
    }

    /**
     * Get plugin updates
     */
    private static function get_plugin_updates(): array {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $update_plugins = get_site_transient('update_plugins');
        $updates = [];

        if ($update_plugins && !empty($update_plugins->response)) {
            foreach ($update_plugins->response as $plugin_file => $plugin_data) {
                $updates[] = [
                    'slug' => $plugin_data->slug ?? dirname($plugin_file),
                    'file' => $plugin_file,
                    'name' => $all_plugins[$plugin_file]['Name'] ?? $plugin_file,
                    'version' => $all_plugins[$plugin_file]['Version'] ?? 'unknown',
                    'new_version' => $plugin_data->new_version,
                    'url' => $plugin_data->url ?? '',
                    'package' => $plugin_data->package ?? '',
                    'requires_php' => $plugin_data->requires_php ?? '',
                    'requires_wp' => $plugin_data->requires ?? '',
                ];
            }
        }

        return $updates;
    }

    /**
     * Get theme updates
     */
    private static function get_theme_updates(): array {
        $update_themes = get_site_transient('update_themes');
        $updates = [];

        if ($update_themes && !empty($update_themes->response)) {
            foreach ($update_themes->response as $stylesheet => $theme_data) {
                $theme = wp_get_theme($stylesheet);
                $updates[] = [
                    'slug' => $stylesheet,
                    'name' => $theme->get('Name'),
                    'version' => $theme->get('Version'),
                    'new_version' => $theme_data['new_version'],
                    'url' => $theme_data['url'] ?? '',
                    'package' => $theme_data['package'] ?? '',
                ];
            }
        }

        return $updates;
    }

    /**
     * Get WordPress core update
     */
    private static function get_core_update(): ?array {
        require_once ABSPATH . 'wp-admin/includes/update.php';

        $updates = get_core_updates();

        if (empty($updates) || $updates[0]->response !== 'upgrade') {
            return null;
        }

        global $wp_version;

        return [
            'current_version' => $wp_version,
            'new_version' => $updates[0]->current,
            'locale' => $updates[0]->locale,
            'package' => $updates[0]->package ?? '',
        ];
    }

    /**
     * Perform a plugin update
     */
    public static function update_plugin(string $plugin_file): array|WP_Error {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Verify plugin exists and has update
        $all_plugins = get_plugins();
        if (!isset($all_plugins[$plugin_file])) {
            return new WP_Error('plugin_not_found', __('Plugin not found.', 'peanut-connect'));
        }

        $update_plugins = get_site_transient('update_plugins');
        if (!isset($update_plugins->response[$plugin_file])) {
            return new WP_Error('no_update', __('No update available for this plugin.', 'peanut-connect'));
        }

        // Perform the update
        $skin = new Automatic_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);

        $result = $upgrader->upgrade($plugin_file);

        if (is_wp_error($result)) {
            return $result;
        }

        if ($result === false) {
            return new WP_Error('update_failed', __('Plugin update failed.', 'peanut-connect'));
        }

        // Clear update cache
        delete_site_transient('update_plugins');
        wp_update_plugins();

        // Get new version
        $all_plugins = get_plugins();
        $new_version = $all_plugins[$plugin_file]['Version'] ?? 'unknown';

        return [
            'success' => true,
            'plugin' => $plugin_file,
            'new_version' => $new_version,
            'message' => sprintf(__('Plugin updated to version %s.', 'peanut-connect'), $new_version),
        ];
    }

    /**
     * Perform a theme update
     */
    public static function update_theme(string $stylesheet): array|WP_Error {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Verify theme exists
        $theme = wp_get_theme($stylesheet);
        if (!$theme->exists()) {
            return new WP_Error('theme_not_found', __('Theme not found.', 'peanut-connect'));
        }

        // Check for update
        $update_themes = get_site_transient('update_themes');
        if (!isset($update_themes->response[$stylesheet])) {
            return new WP_Error('no_update', __('No update available for this theme.', 'peanut-connect'));
        }

        // Perform the update
        $skin = new Automatic_Upgrader_Skin();
        $upgrader = new Theme_Upgrader($skin);

        $result = $upgrader->upgrade($stylesheet);

        if (is_wp_error($result)) {
            return $result;
        }

        if ($result === false) {
            return new WP_Error('update_failed', __('Theme update failed.', 'peanut-connect'));
        }

        // Clear update cache
        delete_site_transient('update_themes');
        wp_update_themes();

        // Get new version
        $theme = wp_get_theme($stylesheet);
        $new_version = $theme->get('Version');

        return [
            'success' => true,
            'theme' => $stylesheet,
            'new_version' => $new_version,
            'message' => sprintf(__('Theme updated to version %s.', 'peanut-connect'), $new_version),
        ];
    }

    /**
     * Perform WordPress core update
     */
    public static function update_core(): array|WP_Error {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/update.php';

        // Check for core update
        $updates = get_core_updates();
        if (empty($updates) || $updates[0]->response !== 'upgrade') {
            return new WP_Error('no_update', __('WordPress is already up to date.', 'peanut-connect'));
        }

        $update = $updates[0];

        // Perform the update
        $skin = new Automatic_Upgrader_Skin();
        $upgrader = new Core_Upgrader($skin);

        $result = $upgrader->upgrade($update);

        if (is_wp_error($result)) {
            return $result;
        }

        global $wp_version;

        return [
            'success' => true,
            'new_version' => $update->current,
            'message' => sprintf(__('WordPress updated to version %s.', 'peanut-connect'), $update->current),
        ];
    }

    /**
     * Perform update based on type
     */
    public static function perform_update(string $type, string $slug): array|WP_Error {
        switch ($type) {
            case 'plugin':
                // Find plugin file from slug
                $plugin_file = self::find_plugin_file($slug);
                if (!$plugin_file) {
                    return new WP_Error('plugin_not_found', __('Plugin not found.', 'peanut-connect'));
                }
                return self::update_plugin($plugin_file);

            case 'theme':
                return self::update_theme($slug);

            case 'core':
                return self::update_core();

            default:
                return new WP_Error('invalid_type', __('Invalid update type.', 'peanut-connect'));
        }
    }

    /**
     * Find plugin file from slug
     */
    private static function find_plugin_file(string $slug): ?string {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();

        // Try direct match first
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if ($slug === $plugin_file || $slug === dirname($plugin_file)) {
                return $plugin_file;
            }
        }

        // Try matching by slug from update data
        $update_plugins = get_site_transient('update_plugins');
        if ($update_plugins && !empty($update_plugins->response)) {
            foreach ($update_plugins->response as $plugin_file => $plugin_data) {
                if (isset($plugin_data->slug) && $plugin_data->slug === $slug) {
                    return $plugin_file;
                }
            }
        }

        return null;
    }
}
