# Peanut Connect - Hooks Reference

This document describes the WordPress hooks (actions and filters) available in Peanut Connect for extensibility.

## Filters

### `peanut_connect_health_data`

Filter the health data before it's returned to the API.

**Parameters:**
- `$health_data` (array) - Complete health data array

**Example:**
```php
add_filter('peanut_connect_health_data', function($health_data) {
    // Add custom health metric
    $health_data['custom_metric'] = [
        'value' => get_option('my_custom_value'),
        'status' => 'healthy',
    ];
    return $health_data;
});
```

### `peanut_connect_permissions`

Filter the default permissions array.

**Parameters:**
- `$permissions` (array) - Default permissions
  - `health_check` (bool) - Always true
  - `list_updates` (bool) - Always true
  - `perform_updates` (bool) - Default true
  - `access_analytics` (bool) - Default true

**Example:**
```php
add_filter('peanut_connect_permissions', function($permissions) {
    // Disable remote updates by default
    $permissions['perform_updates'] = false;
    return $permissions;
});
```

### `peanut_connect_rate_limits`

Filter rate limiting configuration per endpoint.

**Parameters:**
- `$limits` (array) - Rate limit configuration
  - `[endpoint]` => `['requests' => int, 'window' => int]`

**Example:**
```php
add_filter('peanut_connect_rate_limits', function($limits) {
    // Increase health check limit
    $limits['health'] = ['requests' => 60, 'window' => 60];
    return $limits;
});
```

### `peanut_connect_error_log_entry`

Filter error log entry before it's saved.

**Parameters:**
- `$entry` (array) - Error entry data
  - `level` (string) - Error level
  - `message` (string) - Error message
  - `timestamp` (string) - When error occurred
  - `file` (string) - Source file
  - `line` (int) - Source line

**Example:**
```php
add_filter('peanut_connect_error_log_entry', function($entry) {
    // Redact sensitive data from error messages
    $entry['message'] = preg_replace('/password=\S+/', 'password=***', $entry['message']);
    return $entry;
});
```

### `peanut_connect_activity_log_entry`

Filter activity log entry before it's saved.

**Parameters:**
- `$entry` (array) - Activity entry data
  - `type` (string) - Activity type constant
  - `status` (string) - success/warning/error/info
  - `message` (string) - Human-readable message
  - `meta` (array) - Additional metadata

**Example:**
```php
add_filter('peanut_connect_activity_log_entry', function($entry) {
    // Add site identifier to all entries
    $entry['meta']['site_id'] = get_current_blog_id();
    return $entry;
});
```

### `peanut_connect_analytics_data`

Filter analytics data from Peanut Suite before returning.

**Parameters:**
- `$analytics` (array) - Analytics data
  - `contacts` (int) - Contact count
  - `utm_clicks` (int) - UTM click count
  - `link_clicks` (int) - Link click count
  - `forms_submitted` (int) - Form submission count

**Example:**
```php
add_filter('peanut_connect_analytics_data', function($analytics) {
    // Add custom analytics
    $analytics['custom_events'] = my_get_custom_event_count();
    return $analytics;
});
```

### `peanut_connect_backup_plugins`

Filter the list of recognized backup plugins.

**Parameters:**
- `$plugins` (array) - Plugin file => Plugin name pairs

**Example:**
```php
add_filter('peanut_connect_backup_plugins', function($plugins) {
    // Add custom backup plugin detection
    $plugins['my-backup/my-backup.php'] = 'My Backup Plugin';
    return $plugins;
});
```

## Actions

### `peanut_connect_key_generated`

Fired when a new site key is generated.

**Parameters:**
- `$site_key` (string) - The newly generated key

**Example:**
```php
add_action('peanut_connect_key_generated', function($site_key) {
    // Send notification email
    wp_mail(get_option('admin_email'), 'Peanut Connect Key Generated',
        'A new site key has been generated for your WordPress site.');
});
```

### `peanut_connect_key_regenerated`

Fired when a site key is regenerated.

**Parameters:**
- `$new_key` (string) - The new site key
- `$old_key_hash` (string) - MD5 hash of the old key (for logging)

**Example:**
```php
add_action('peanut_connect_key_regenerated', function($new_key, $old_key_hash) {
    // Log security event
    error_log("Peanut Connect key regenerated. Old key hash: $old_key_hash");
}, 10, 2);
```

### `peanut_connect_connected`

Fired when connection to manager is established.

**Parameters:**
- `$manager_url` (string) - The manager site URL

**Example:**
```php
add_action('peanut_connect_connected', function($manager_url) {
    // Track connection event
    do_action('my_analytics_event', 'peanut_connect_connected', [
        'manager_url' => $manager_url,
    ]);
});
```

### `peanut_connect_disconnected`

Fired when disconnected from manager.

**Parameters:**
- `$initiated_by` (string) - 'admin' or 'manager'

**Example:**
```php
add_action('peanut_connect_disconnected', function($initiated_by) {
    // Log disconnection
    error_log("Peanut Connect disconnected by: $initiated_by");
});
```

### `peanut_connect_permission_changed`

Fired when a permission is changed.

**Parameters:**
- `$permission` (string) - Permission key
- `$enabled` (bool) - New value
- `$user_id` (int) - User who made the change

**Example:**
```php
add_action('peanut_connect_permission_changed', function($permission, $enabled, $user_id) {
    // Audit permission changes
    $user = get_userdata($user_id);
    error_log(sprintf(
        'Permission "%s" %s by %s',
        $permission,
        $enabled ? 'enabled' : 'disabled',
        $user->user_login
    ));
}, 10, 3);
```

### `peanut_connect_update_performed`

Fired after an update is performed successfully.

**Parameters:**
- `$type` (string) - 'plugin', 'theme', or 'core'
- `$slug` (string) - Item slug
- `$old_version` (string) - Previous version
- `$new_version` (string) - New version
- `$initiated_by` (string) - 'admin' or 'manager'

**Example:**
```php
add_action('peanut_connect_update_performed', function($type, $slug, $old, $new, $by) {
    // Send Slack notification
    wp_remote_post('https://hooks.slack.com/...', [
        'body' => json_encode([
            'text' => "Updated $type $slug: $old → $new (by $by)",
        ]),
    ]);
}, 10, 5);
```

### `peanut_connect_update_failed`

Fired when an update fails.

**Parameters:**
- `$type` (string) - 'plugin', 'theme', or 'core'
- `$slug` (string) - Item slug
- `$error` (string) - Error message

**Example:**
```php
add_action('peanut_connect_update_failed', function($type, $slug, $error) {
    // Log failure
    error_log("Update failed for $type $slug: $error");
}, 10, 3);
```

### `peanut_connect_rate_limited`

Fired when a request is rate limited.

**Parameters:**
- `$endpoint` (string) - API endpoint
- `$client_id` (string) - Client identifier (IP + user agent hash)
- `$remaining_time` (int) - Seconds until limit resets

**Example:**
```php
add_action('peanut_connect_rate_limited', function($endpoint, $client_id, $remaining) {
    // Alert on repeated rate limiting
    $count = get_transient("rate_limit_count_$client_id") ?: 0;
    if ($count > 10) {
        wp_mail(get_option('admin_email'), 'Potential API Abuse',
            "Client $client_id has been rate limited $count times.");
    }
    set_transient("rate_limit_count_$client_id", $count + 1, HOUR_IN_SECONDS);
}, 10, 3);
```

### `peanut_connect_auth_failed`

Fired when authentication fails.

**Parameters:**
- `$reason` (string) - Failure reason
- `$ip_address` (string) - Client IP

**Example:**
```php
add_action('peanut_connect_auth_failed', function($reason, $ip) {
    // Track failed auth attempts
    $attempts = get_transient("auth_fail_$ip") ?: 0;
    set_transient("auth_fail_$ip", $attempts + 1, HOUR_IN_SECONDS);

    if ($attempts >= 5) {
        // Block IP or send alert
    }
}, 10, 2);
```

## Usage Notes

1. **Priority:** Most actions run at default priority (10). Use lower numbers to run before, higher to run after.

2. **Performance:** Filters in the health check path run frequently. Keep filter callbacks fast.

3. **Security:** Never log full site keys or sensitive data. Use hashes for identification.

4. **Multisite:** On multisite, hooks fire per-site. Use `get_current_blog_id()` for context.

## Example: Custom Integration

```php
<?php
/**
 * Plugin Name: Peanut Connect Custom Integration
 * Description: Custom hooks for Peanut Connect
 */

// Add custom health metric
add_filter('peanut_connect_health_data', function($data) {
    $data['woocommerce'] = [
        'installed' => class_exists('WooCommerce'),
        'orders_today' => class_exists('WooCommerce')
            ? wc_orders_count('completed', strtotime('today'))
            : 0,
    ];
    return $data;
});

// Notify on remote updates
add_action('peanut_connect_update_performed', function($type, $slug, $old, $new, $by) {
    if ($by === 'manager') {
        wp_mail(
            get_option('admin_email'),
            "Remote Update: $slug",
            "The manager site updated $type '$slug' from $old to $new."
        );
    }
}, 10, 5);

// Block specific IPs from API
add_filter('peanut_connect_rate_limits', function($limits) {
    $blocked_ips = ['1.2.3.4', '5.6.7.8'];
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (in_array($client_ip, $blocked_ips)) {
        // Set extremely low limit to effectively block
        foreach ($limits as $endpoint => $config) {
            $limits[$endpoint] = ['requests' => 0, 'window' => 86400];
        }
    }

    return $limits;
});
```
