<?php
/**
 * Tests for Peanut_Connect_Health class
 *
 * @package Peanut_Connect
 */

namespace Peanut_Connect\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Peanut_Connect_Health;

class HealthTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Reset global state
        global $wp_version;
        $wp_version = '6.4.0';
    }

    /**
     * Test get_health_data returns expected structure
     */
    public function test_get_health_data_returns_expected_keys(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $expected_keys = [
            'wp_version',
            'php_version',
            'ssl',
            'plugins',
            'themes',
            'disk_space',
            'database',
            'debug_mode',
            'backup',
            'file_permissions',
            'server',
            'peanut_suite',
            'error_log',
        ];

        foreach ($expected_keys as $key) {
            $this->assertArrayHasKey($key, $data, "Health data should contain '$key'");
        }
    }

    /**
     * Test PHP version data structure
     */
    public function test_php_version_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('php_version', $data);
        $this->assertArrayHasKey('version', $data['php_version']);
        $this->assertArrayHasKey('recommended', $data['php_version']);
        $this->assertArrayHasKey('minimum_met', $data['php_version']);

        $this->assertIsBool($data['php_version']['recommended']);
        $this->assertIsBool($data['php_version']['minimum_met']);
    }

    /**
     * Test PHP version checks correctly
     */
    public function test_php_version_checks_are_correct(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $version = phpversion();
        $this->assertEquals($version, $data['php_version']['version']);

        // PHP 8.1+ should be recommended
        $expected_recommended = version_compare($version, '8.1', '>=');
        $this->assertEquals($expected_recommended, $data['php_version']['recommended']);

        // PHP 8.0+ should meet minimum
        $expected_minimum = version_compare($version, '8.0', '>=');
        $this->assertEquals($expected_minimum, $data['php_version']['minimum_met']);
    }

    /**
     * Test WordPress version data structure
     */
    public function test_wp_version_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('wp_version', $data);
        $this->assertArrayHasKey('version', $data['wp_version']);
        $this->assertArrayHasKey('latest_version', $data['wp_version']);
        $this->assertArrayHasKey('needs_update', $data['wp_version']);

        $this->assertIsBool($data['wp_version']['needs_update']);
    }

    /**
     * Test SSL data structure
     */
    public function test_ssl_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('ssl', $data);
        $this->assertArrayHasKey('enabled', $data['ssl']);
        $this->assertArrayHasKey('valid', $data['ssl']);
        $this->assertArrayHasKey('days_until_expiry', $data['ssl']);
        $this->assertArrayHasKey('issuer', $data['ssl']);

        $this->assertIsBool($data['ssl']['enabled']);
        $this->assertIsBool($data['ssl']['valid']);
    }

    /**
     * Test plugins data structure
     */
    public function test_plugins_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('plugins', $data);
        $this->assertArrayHasKey('total', $data['plugins']);
        $this->assertArrayHasKey('active', $data['plugins']);
        $this->assertArrayHasKey('inactive', $data['plugins']);
        $this->assertArrayHasKey('updates_available', $data['plugins']);
        $this->assertArrayHasKey('needing_update', $data['plugins']);

        $this->assertIsInt($data['plugins']['total']);
        $this->assertIsInt($data['plugins']['active']);
        $this->assertIsInt($data['plugins']['inactive']);
        $this->assertIsInt($data['plugins']['updates_available']);
        $this->assertIsArray($data['plugins']['needing_update']);
    }

    /**
     * Test plugins inactive count is calculated correctly
     */
    public function test_plugins_inactive_calculation(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $expected_inactive = $data['plugins']['total'] - $data['plugins']['active'];
        $this->assertEquals($expected_inactive, $data['plugins']['inactive']);
    }

    /**
     * Test themes data structure
     */
    public function test_themes_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('themes', $data);
        $this->assertArrayHasKey('total', $data['themes']);
        $this->assertArrayHasKey('active', $data['themes']);
        $this->assertArrayHasKey('active_version', $data['themes']);
        $this->assertArrayHasKey('updates_available', $data['themes']);
        $this->assertArrayHasKey('needing_update', $data['themes']);

        $this->assertIsInt($data['themes']['total']);
        $this->assertIsInt($data['themes']['updates_available']);
        $this->assertIsArray($data['themes']['needing_update']);
    }

    /**
     * Test disk space data structure when available
     */
    public function test_disk_space_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('disk_space', $data);
        $this->assertArrayHasKey('available', $data['disk_space']);

        if ($data['disk_space']['available']) {
            $this->assertArrayHasKey('total', $data['disk_space']);
            $this->assertArrayHasKey('total_formatted', $data['disk_space']);
            $this->assertArrayHasKey('used', $data['disk_space']);
            $this->assertArrayHasKey('used_formatted', $data['disk_space']);
            $this->assertArrayHasKey('free', $data['disk_space']);
            $this->assertArrayHasKey('free_formatted', $data['disk_space']);
            $this->assertArrayHasKey('used_percent', $data['disk_space']);

            // Validate disk space calculations
            $this->assertEquals(
                $data['disk_space']['total'] - $data['disk_space']['free'],
                $data['disk_space']['used']
            );

            $this->assertGreaterThanOrEqual(0, $data['disk_space']['used_percent']);
            $this->assertLessThanOrEqual(100, $data['disk_space']['used_percent']);
        }
    }

    /**
     * Test database data structure
     */
    public function test_database_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('database', $data);
        $this->assertArrayHasKey('size', $data['database']);
        $this->assertArrayHasKey('size_formatted', $data['database']);
        $this->assertArrayHasKey('tables_count', $data['database']);
        $this->assertArrayHasKey('prefix', $data['database']);

        $this->assertIsInt($data['database']['size']);
        $this->assertIsString($data['database']['size_formatted']);
        $this->assertIsInt($data['database']['tables_count']);
        $this->assertIsString($data['database']['prefix']);
    }

    /**
     * Test debug mode reflects WP_DEBUG constant
     */
    public function test_debug_mode_reflects_constant(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $expected = defined('WP_DEBUG') && WP_DEBUG;
        $this->assertEquals($expected, $data['debug_mode']);
    }

    /**
     * Test backup data structure
     */
    public function test_backup_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('backup', $data);
        $this->assertArrayHasKey('plugin_detected', $data['backup']);
        $this->assertArrayHasKey('last_backup', $data['backup']);
        $this->assertArrayHasKey('days_since_last', $data['backup']);
    }

    /**
     * Test file permissions data structure
     */
    public function test_file_permissions_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('file_permissions', $data);
        $this->assertArrayHasKey('secure', $data['file_permissions']);
        $this->assertArrayHasKey('checks', $data['file_permissions']);

        $this->assertIsBool($data['file_permissions']['secure']);
        $this->assertIsArray($data['file_permissions']['checks']);
    }

    /**
     * Test server data structure
     */
    public function test_server_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('server', $data);
        $this->assertArrayHasKey('software', $data['server']);
        $this->assertArrayHasKey('php_sapi', $data['server']);
        $this->assertArrayHasKey('max_upload_size', $data['server']);
        $this->assertArrayHasKey('max_upload_size_formatted', $data['server']);
        $this->assertArrayHasKey('memory_limit', $data['server']);
        $this->assertArrayHasKey('max_execution_time', $data['server']);
        $this->assertArrayHasKey('php_extensions', $data['server']);
    }

    /**
     * Test PHP extensions are correctly detected
     */
    public function test_php_extensions_detection(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $extensions = $data['server']['php_extensions'];

        $this->assertIsBool($extensions['curl']);
        $this->assertIsBool($extensions['imagick']);
        $this->assertIsBool($extensions['gd']);
        $this->assertIsBool($extensions['zip']);
        $this->assertIsBool($extensions['openssl']);

        // These should match actual extension status
        $this->assertEquals(extension_loaded('curl'), $extensions['curl']);
        $this->assertEquals(extension_loaded('openssl'), $extensions['openssl']);
        $this->assertEquals(extension_loaded('zip'), $extensions['zip']);
    }

    /**
     * Test Peanut Suite data returns null when not installed
     */
    public function test_peanut_suite_null_when_not_installed(): void {
        $data = Peanut_Connect_Health::get_health_data();

        // Since peanut_is_module_active won't exist in test environment
        $this->assertNull($data['peanut_suite']);
    }

    /**
     * Test error log data structure
     */
    public function test_error_log_data_structure(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertArrayHasKey('error_log', $data);

        // When Peanut_Connect_Error_Log is loaded
        if (class_exists('Peanut_Connect_Error_Log')) {
            $this->assertArrayHasKey('enabled', $data['error_log']);
            $this->assertArrayHasKey('available', $data['error_log']);
            $this->assertArrayHasKey('total_entries', $data['error_log']);
            $this->assertArrayHasKey('counts', $data['error_log']);
            $this->assertArrayHasKey('last_24h', $data['error_log']);
            $this->assertArrayHasKey('has_critical', $data['error_log']);
            $this->assertArrayHasKey('has_errors', $data['error_log']);
        } else {
            $this->assertFalse($data['error_log']['enabled']);
            $this->assertFalse($data['error_log']['available']);
        }
    }

    /**
     * Test server PHP SAPI name matches runtime
     */
    public function test_server_php_sapi_matches_runtime(): void {
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertEquals(php_sapi_name(), $data['server']['php_sapi']);
    }

    /**
     * Test database prefix matches $wpdb
     */
    public function test_database_prefix_matches_wpdb(): void {
        global $wpdb;
        $data = Peanut_Connect_Health::get_health_data();

        $this->assertEquals($wpdb->prefix, $data['database']['prefix']);
    }

    /**
     * Test used_percent is rounded to 2 decimal places
     */
    public function test_disk_space_used_percent_is_rounded(): void {
        $data = Peanut_Connect_Health::get_health_data();

        if ($data['disk_space']['available']) {
            $used_percent = $data['disk_space']['used_percent'];

            // Check if it's rounded to at most 2 decimal places
            $rounded = round($used_percent, 2);
            $this->assertEquals($rounded, $used_percent);
        } else {
            $this->markTestSkipped('Disk space data not available');
        }
    }
}
