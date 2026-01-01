<?php
/**
 * Tests for Peanut_Connect_Activity_Log
 *
 * @package Peanut_Connect
 */

use PHPUnit\Framework\TestCase;

class ActivityLogTest extends TestCase {

    /**
     * Temporary log directory for tests
     */
    private string $temp_log_dir;

    /**
     * Original WP_CONTENT_DIR value
     */
    private string $original_content_dir;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        peanut_reset_test_state();

        // Create a temporary directory for logs
        $this->temp_log_dir = sys_get_temp_dir() . '/peanut-test-logs-' . uniqid();
        mkdir($this->temp_log_dir, 0755, true);

        // Store original and override WP_CONTENT_DIR for testing
        $this->original_content_dir = WP_CONTENT_DIR;

        // Re-initialize the activity log to use our temp directory
        Peanut_Connect_Activity_Log::init();
    }

    /**
     * Clean up after tests
     */
    protected function tearDown(): void {
        // Clean up temp files
        $log_file = WP_CONTENT_DIR . '/peanut-logs/activity-log.json';
        if (file_exists($log_file)) {
            @unlink($log_file);
        }

        $htaccess = WP_CONTENT_DIR . '/peanut-logs/.htaccess';
        if (file_exists($htaccess)) {
            @unlink($htaccess);
        }

        $log_dir = WP_CONTENT_DIR . '/peanut-logs';
        if (is_dir($log_dir)) {
            @rmdir($log_dir);
        }
    }

    // =========================================
    // Constants Tests
    // =========================================

    /**
     * Test that activity type constants are defined
     */
    public function test_type_constants_defined(): void {
        $this->assertEquals('health_check', Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK);
        $this->assertEquals('update_installed', Peanut_Connect_Activity_Log::TYPE_UPDATE_INSTALLED);
        $this->assertEquals('update_failed', Peanut_Connect_Activity_Log::TYPE_UPDATE_FAILED);
        $this->assertEquals('connection_established', Peanut_Connect_Activity_Log::TYPE_CONNECTION_ESTABLISHED);
        $this->assertEquals('connection_lost', Peanut_Connect_Activity_Log::TYPE_CONNECTION_LOST);
        $this->assertEquals('key_generated', Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED);
        $this->assertEquals('key_regenerated', Peanut_Connect_Activity_Log::TYPE_KEY_REGENERATED);
        $this->assertEquals('permission_changed', Peanut_Connect_Activity_Log::TYPE_PERMISSION_CHANGED);
        $this->assertEquals('settings_changed', Peanut_Connect_Activity_Log::TYPE_SETTINGS_CHANGED);
        $this->assertEquals('rate_limited', Peanut_Connect_Activity_Log::TYPE_RATE_LIMITED);
        $this->assertEquals('auth_failed', Peanut_Connect_Activity_Log::TYPE_AUTH_FAILED);
        $this->assertEquals('disconnect', Peanut_Connect_Activity_Log::TYPE_DISCONNECT);
    }

    /**
     * Test that status constants are defined
     */
    public function test_status_constants_defined(): void {
        $this->assertEquals('success', Peanut_Connect_Activity_Log::STATUS_SUCCESS);
        $this->assertEquals('warning', Peanut_Connect_Activity_Log::STATUS_WARNING);
        $this->assertEquals('error', Peanut_Connect_Activity_Log::STATUS_ERROR);
        $this->assertEquals('info', Peanut_Connect_Activity_Log::STATUS_INFO);
    }

    // =========================================
    // Log Method Tests
    // =========================================

    /**
     * Test that log creates an entry
     */
    public function test_log_creates_entry(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Test health check'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('health_check', $entries[0]['type']);
        $this->assertEquals('success', $entries[0]['status']);
        $this->assertEquals('Test health check', $entries[0]['message']);
    }

    /**
     * Test that log includes timestamp
     */
    public function test_log_includes_timestamp(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED,
            Peanut_Connect_Activity_Log::STATUS_INFO,
            'Key generated'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertArrayHasKey('timestamp', $entries[0]);
        $this->assertArrayHasKey('timestamp_gmt', $entries[0]);
        $this->assertNotEmpty($entries[0]['timestamp']);
    }

    /**
     * Test that log includes UUID
     */
    public function test_log_includes_uuid(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_DISCONNECT,
            Peanut_Connect_Activity_Log::STATUS_INFO,
            'Disconnected'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertArrayHasKey('id', $entries[0]);
        $this->assertNotEmpty($entries[0]['id']);
    }

    /**
     * Test that log includes user ID
     */
    public function test_log_includes_user_id(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_SETTINGS_CHANGED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Settings updated'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertArrayHasKey('user_id', $entries[0]);
        $this->assertEquals(1, $entries[0]['user_id']); // Mocked get_current_user_id returns 1
    }

    /**
     * Test that log includes metadata
     */
    public function test_log_includes_metadata(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_UPDATE_INSTALLED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Plugin updated',
            ['plugin' => 'test-plugin', 'version' => '2.0.0']
        );

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertArrayHasKey('meta', $entries[0]);
        $this->assertEquals('test-plugin', $entries[0]['meta']['plugin']);
        $this->assertEquals('2.0.0', $entries[0]['meta']['version']);
    }

    /**
     * Test that entries are ordered newest first
     */
    public function test_entries_ordered_newest_first(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
            Peanut_Connect_Activity_Log::STATUS_INFO,
            'First entry'
        );

        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Second entry'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(2, $entries);
        $this->assertEquals('Second entry', $entries[0]['message']);
        $this->assertEquals('First entry', $entries[1]['message']);
    }

    // =========================================
    // Get Entries Tests
    // =========================================

    /**
     * Test get_entries returns empty array when no log exists
     */
    public function test_get_entries_returns_empty_when_no_log(): void {
        $entries = Peanut_Connect_Activity_Log::get_entries();
        $this->assertIsArray($entries);
        $this->assertCount(0, $entries);
    }

    /**
     * Test get_entries with limit
     */
    public function test_get_entries_with_limit(): void {
        for ($i = 0; $i < 5; $i++) {
            Peanut_Connect_Activity_Log::log(
                Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
                Peanut_Connect_Activity_Log::STATUS_INFO,
                "Entry $i"
            );
        }

        $entries = Peanut_Connect_Activity_Log::get_entries(3);

        $this->assertCount(3, $entries);
    }

    /**
     * Test get_entries with offset
     */
    public function test_get_entries_with_offset(): void {
        for ($i = 1; $i <= 5; $i++) {
            Peanut_Connect_Activity_Log::log(
                Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
                Peanut_Connect_Activity_Log::STATUS_INFO,
                "Entry $i"
            );
        }

        $entries = Peanut_Connect_Activity_Log::get_entries(0, 2);

        $this->assertCount(3, $entries);
        $this->assertEquals('Entry 3', $entries[0]['message']);
    }

    /**
     * Test get_entries filters by type
     */
    public function test_get_entries_filters_by_type(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
            Peanut_Connect_Activity_Log::STATUS_INFO,
            'Health check'
        );

        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Key generated'
        );

        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
            Peanut_Connect_Activity_Log::STATUS_INFO,
            'Another health check'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries(0, 0, Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK);

        $this->assertCount(2, $entries);
        foreach ($entries as $entry) {
            $this->assertEquals('health_check', $entry['type']);
        }
    }

    /**
     * Test get_entries filters by status
     */
    public function test_get_entries_filters_by_status(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_UPDATE_INSTALLED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Update succeeded'
        );

        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_UPDATE_FAILED,
            Peanut_Connect_Activity_Log::STATUS_ERROR,
            'Update failed'
        );

        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Key generated'
        );

        $entries = Peanut_Connect_Activity_Log::get_entries(0, 0, '', Peanut_Connect_Activity_Log::STATUS_SUCCESS);

        $this->assertCount(2, $entries);
        foreach ($entries as $entry) {
            $this->assertEquals('success', $entry['status']);
        }
    }

    // =========================================
    // Counts Tests
    // =========================================

    /**
     * Test get_counts_by_type
     */
    public function test_get_counts_by_type(): void {
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK, Peanut_Connect_Activity_Log::STATUS_INFO, 'HC 1');
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK, Peanut_Connect_Activity_Log::STATUS_INFO, 'HC 2');
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED, Peanut_Connect_Activity_Log::STATUS_SUCCESS, 'Key 1');
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_UPDATE_FAILED, Peanut_Connect_Activity_Log::STATUS_ERROR, 'Update 1');

        $counts = Peanut_Connect_Activity_Log::get_counts_by_type();

        $this->assertEquals(2, $counts['health_check']);
        $this->assertEquals(1, $counts['key_generated']);
        $this->assertEquals(1, $counts['update_failed']);
    }

    /**
     * Test get_recent_counts returns last 24 hours
     */
    public function test_get_recent_counts(): void {
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK, Peanut_Connect_Activity_Log::STATUS_SUCCESS, 'Success');
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_AUTH_FAILED, Peanut_Connect_Activity_Log::STATUS_ERROR, 'Error');
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_RATE_LIMITED, Peanut_Connect_Activity_Log::STATUS_WARNING, 'Warning');
        Peanut_Connect_Activity_Log::log(Peanut_Connect_Activity_Log::TYPE_DISCONNECT, Peanut_Connect_Activity_Log::STATUS_INFO, 'Info');

        $counts = Peanut_Connect_Activity_Log::get_recent_counts();

        $this->assertEquals(4, $counts['total']);
        $this->assertArrayHasKey('success', $counts);
        $this->assertArrayHasKey('error', $counts);
        $this->assertArrayHasKey('warning', $counts);
        $this->assertArrayHasKey('info', $counts);
    }

    // =========================================
    // CSV Export Tests
    // =========================================

    /**
     * Test export_csv returns valid CSV
     */
    public function test_export_csv_returns_valid_csv(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Test entry'
        );

        $csv = Peanut_Connect_Activity_Log::export_csv();

        $this->assertStringContainsString('ID,Timestamp,Type,Status,Message,User ID,IP Address', $csv);
        $this->assertStringContainsString('health_check', $csv);
        $this->assertStringContainsString('success', $csv);
        $this->assertStringContainsString('Test entry', $csv);
    }

    /**
     * Test export_csv escapes quotes
     */
    public function test_export_csv_escapes_quotes(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_UPDATE_FAILED,
            Peanut_Connect_Activity_Log::STATUS_ERROR,
            'Error with "quotes" in message'
        );

        $csv = Peanut_Connect_Activity_Log::export_csv();

        // CSV escapes quotes by doubling them
        $this->assertStringContainsString('""quotes""', $csv);
    }

    // =========================================
    // Helper Method Tests
    // =========================================

    /**
     * Test log_health_check helper
     */
    public function test_log_health_check_helper(): void {
        Peanut_Connect_Activity_Log::log_health_check('https://manager.example.com');

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('health_check', $entries[0]['type']);
        $this->assertEquals('info', $entries[0]['status']);
        $this->assertEquals('https://manager.example.com', $entries[0]['meta']['manager_url']);
    }

    /**
     * Test log_update_installed helper
     */
    public function test_log_update_installed_helper(): void {
        Peanut_Connect_Activity_Log::log_update_installed('plugin', 'test-plugin', '1.0.0', '2.0.0');

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('update_installed', $entries[0]['type']);
        $this->assertEquals('success', $entries[0]['status']);
        $this->assertStringContainsString('test-plugin', $entries[0]['message']);
        $this->assertStringContainsString('1.0.0', $entries[0]['message']);
        $this->assertStringContainsString('2.0.0', $entries[0]['message']);
    }

    /**
     * Test log_update_failed helper
     */
    public function test_log_update_failed_helper(): void {
        Peanut_Connect_Activity_Log::log_update_failed('theme', 'test-theme', 'Permission denied');

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('update_failed', $entries[0]['type']);
        $this->assertEquals('error', $entries[0]['status']);
        $this->assertStringContainsString('test-theme', $entries[0]['message']);
        $this->assertStringContainsString('Permission denied', $entries[0]['message']);
    }

    /**
     * Test log_key_generated helper
     */
    public function test_log_key_generated_helper(): void {
        Peanut_Connect_Activity_Log::log_key_generated();

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('key_generated', $entries[0]['type']);
        $this->assertEquals('success', $entries[0]['status']);
    }

    /**
     * Test log_key_regenerated helper
     */
    public function test_log_key_regenerated_helper(): void {
        Peanut_Connect_Activity_Log::log_key_regenerated();

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('key_regenerated', $entries[0]['type']);
        $this->assertEquals('warning', $entries[0]['status']);
    }

    /**
     * Test log_permission_changed helper
     */
    public function test_log_permission_changed_helper(): void {
        Peanut_Connect_Activity_Log::log_permission_changed('perform_updates', true);

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('permission_changed', $entries[0]['type']);
        $this->assertEquals('info', $entries[0]['status']);
        $this->assertEquals('perform_updates', $entries[0]['meta']['permission']);
        $this->assertTrue($entries[0]['meta']['enabled']);
    }

    /**
     * Test log_auth_failed helper
     */
    public function test_log_auth_failed_helper(): void {
        Peanut_Connect_Activity_Log::log_auth_failed('Invalid token');

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('auth_failed', $entries[0]['type']);
        $this->assertEquals('error', $entries[0]['status']);
        $this->assertEquals('Invalid token', $entries[0]['meta']['reason']);
    }

    /**
     * Test log_rate_limited helper
     */
    public function test_log_rate_limited_helper(): void {
        Peanut_Connect_Activity_Log::log_rate_limited('/health');

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('rate_limited', $entries[0]['type']);
        $this->assertEquals('warning', $entries[0]['status']);
        $this->assertEquals('/health', $entries[0]['meta']['endpoint']);
    }

    /**
     * Test log_disconnect helper
     */
    public function test_log_disconnect_helper(): void {
        Peanut_Connect_Activity_Log::log_disconnect('manager');

        $entries = Peanut_Connect_Activity_Log::get_entries();

        $this->assertCount(1, $entries);
        $this->assertEquals('disconnect', $entries[0]['type']);
        $this->assertEquals('info', $entries[0]['status']);
        $this->assertEquals('manager', $entries[0]['meta']['initiated_by']);
    }

    // =========================================
    // Clear Tests
    // =========================================

    /**
     * Test clear keeps last entry
     */
    public function test_clear_keeps_clear_log_entry(): void {
        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_HEALTH_CHECK,
            Peanut_Connect_Activity_Log::STATUS_INFO,
            'Old entry 1'
        );

        Peanut_Connect_Activity_Log::log(
            Peanut_Connect_Activity_Log::TYPE_KEY_GENERATED,
            Peanut_Connect_Activity_Log::STATUS_SUCCESS,
            'Old entry 2'
        );

        Peanut_Connect_Activity_Log::clear();

        $entries = Peanut_Connect_Activity_Log::get_entries();

        // Should only have the "log cleared" entry
        $this->assertCount(1, $entries);
        $this->assertEquals('settings_changed', $entries[0]['type']);
        $this->assertStringContainsString('cleared', $entries[0]['message']);
    }
}
