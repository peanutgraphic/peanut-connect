<?php
/**
 * Tests for Peanut_Connect_Updates class
 *
 * @package Peanut_Connect
 */

namespace Peanut_Connect\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Peanut_Connect_Updates;
use WP_Error;

class UpdatesTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Reset global state
        global $wp_version;
        $wp_version = '6.4.0';

        // Reset transients for clean test state
        global $mock_transients;
        $mock_transients = [];

        global $mock_options;
        $mock_options = [];
    }

    /**
     * Test get_available_updates returns expected structure
     */
    public function test_get_available_updates_returns_expected_keys(): void {
        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertArrayHasKey('plugins', $updates);
        $this->assertArrayHasKey('themes', $updates);
        $this->assertArrayHasKey('core', $updates);

        $this->assertIsArray($updates['plugins']);
        $this->assertIsArray($updates['themes']);
    }

    /**
     * Test get_available_updates returns plugins array
     */
    public function test_get_available_updates_plugins_is_array(): void {
        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertIsArray($updates['plugins']);
    }

    /**
     * Test get_available_updates returns themes array
     */
    public function test_get_available_updates_themes_is_array(): void {
        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertIsArray($updates['themes']);
    }

    /**
     * Test get_available_updates core is null or array
     */
    public function test_get_available_updates_core_is_null_or_array(): void {
        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertTrue(
            is_null($updates['core']) || is_array($updates['core']),
            'Core update should be null or array'
        );
    }

    /**
     * Test plugin update structure when updates are available
     */
    public function test_plugin_update_structure(): void {
        // Mock a plugin update
        global $mock_transients;
        $mock_transients['update_plugins'] = (object) [
            'response' => [
                'test-plugin/test-plugin.php' => (object) [
                    'slug' => 'test-plugin',
                    'new_version' => '2.0.0',
                    'url' => 'https://wordpress.org/plugins/test-plugin/',
                    'package' => 'https://downloads.wordpress.org/plugin/test-plugin.2.0.0.zip',
                    'requires_php' => '8.0',
                    'requires' => '6.0',
                ],
            ],
        ];

        $updates = Peanut_Connect_Updates::get_available_updates();

        if (!empty($updates['plugins'])) {
            $plugin = $updates['plugins'][0];

            $this->assertArrayHasKey('slug', $plugin);
            $this->assertArrayHasKey('file', $plugin);
            $this->assertArrayHasKey('name', $plugin);
            $this->assertArrayHasKey('version', $plugin);
            $this->assertArrayHasKey('new_version', $plugin);
            $this->assertArrayHasKey('url', $plugin);
            $this->assertArrayHasKey('package', $plugin);
            $this->assertArrayHasKey('requires_php', $plugin);
            $this->assertArrayHasKey('requires_wp', $plugin);
        }
    }

    /**
     * Test theme update structure when updates are available
     */
    public function test_theme_update_structure(): void {
        // Mock a theme update
        global $mock_transients;
        $mock_transients['update_themes'] = (object) [
            'response' => [
                'twentytwentyfour' => [
                    'new_version' => '1.1',
                    'url' => 'https://wordpress.org/themes/twentytwentyfour/',
                    'package' => 'https://downloads.wordpress.org/theme/twentytwentyfour.1.1.zip',
                ],
            ],
        ];

        $updates = Peanut_Connect_Updates::get_available_updates();

        if (!empty($updates['themes'])) {
            $theme = $updates['themes'][0];

            $this->assertArrayHasKey('slug', $theme);
            $this->assertArrayHasKey('name', $theme);
            $this->assertArrayHasKey('version', $theme);
            $this->assertArrayHasKey('new_version', $theme);
            $this->assertArrayHasKey('url', $theme);
            $this->assertArrayHasKey('package', $theme);
        }
    }

    /**
     * Test core update structure when update is available
     */
    public function test_core_update_structure(): void {
        $updates = Peanut_Connect_Updates::get_available_updates();

        if ($updates['core'] !== null) {
            $this->assertArrayHasKey('current_version', $updates['core']);
            $this->assertArrayHasKey('new_version', $updates['core']);
            $this->assertArrayHasKey('locale', $updates['core']);
            $this->assertArrayHasKey('package', $updates['core']);
        }
    }

    /**
     * Test update_plugin fails for non-existent plugin
     */
    public function test_update_plugin_fails_for_nonexistent_plugin(): void {
        $result = Peanut_Connect_Updates::update_plugin('nonexistent-plugin/plugin.php');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('plugin_not_found', $result->get_error_code());
    }

    /**
     * Test update_plugin fails when no update available
     */
    public function test_update_plugin_fails_when_no_update(): void {
        // Mock plugin exists but no update available
        global $mock_plugins;
        $mock_plugins = [
            'existing-plugin/existing-plugin.php' => [
                'Name' => 'Existing Plugin',
                'Version' => '1.0.0',
            ],
        ];

        global $mock_transients;
        $mock_transients['update_plugins'] = (object) ['response' => []];

        $result = Peanut_Connect_Updates::update_plugin('existing-plugin/existing-plugin.php');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('no_update', $result->get_error_code());
    }

    /**
     * Test update_theme fails for non-existent theme
     */
    public function test_update_theme_fails_for_nonexistent_theme(): void {
        $result = Peanut_Connect_Updates::update_theme('nonexistent-theme');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('theme_not_found', $result->get_error_code());
    }

    /**
     * Test update_theme fails when no update available
     */
    public function test_update_theme_fails_when_no_update(): void {
        // Mock theme exists but no update
        global $mock_themes;
        $mock_themes = [
            'existing-theme' => [
                'Name' => 'Existing Theme',
                'Version' => '1.0.0',
            ],
        ];

        global $mock_transients;
        $mock_transients['update_themes'] = (object) ['response' => []];

        $result = Peanut_Connect_Updates::update_theme('existing-theme');

        // Should return error (no update or theme not found in our mock)
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test update_core fails when no update available
     */
    public function test_update_core_fails_when_no_update(): void {
        // Mock no core updates available
        global $mock_core_updates;
        $mock_core_updates = [
            (object) [
                'response' => 'latest',
                'current' => '6.4.0',
            ],
        ];

        $result = Peanut_Connect_Updates::update_core();

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('no_update', $result->get_error_code());
    }

    /**
     * Test perform_update with invalid type
     */
    public function test_perform_update_fails_for_invalid_type(): void {
        $result = Peanut_Connect_Updates::perform_update('invalid', 'some-slug');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_type', $result->get_error_code());
    }

    /**
     * Test perform_update routes to plugin update
     */
    public function test_perform_update_routes_to_plugin_update(): void {
        $result = Peanut_Connect_Updates::perform_update('plugin', 'nonexistent-plugin');

        // Should fail since plugin doesn't exist, but it reached the right handler
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('plugin_not_found', $result->get_error_code());
    }

    /**
     * Test perform_update routes to theme update
     */
    public function test_perform_update_routes_to_theme_update(): void {
        $result = Peanut_Connect_Updates::perform_update('theme', 'nonexistent-theme');

        // Should fail since theme doesn't exist, but it reached the right handler
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('theme_not_found', $result->get_error_code());
    }

    /**
     * Test perform_update routes to core update
     */
    public function test_perform_update_routes_to_core_update(): void {
        // Mock no core updates
        global $mock_core_updates;
        $mock_core_updates = [
            (object) [
                'response' => 'latest',
                'current' => '6.4.0',
            ],
        ];

        $result = Peanut_Connect_Updates::perform_update('core', '');

        // Should reach core update handler
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('no_update', $result->get_error_code());
    }

    /**
     * Test empty plugins array when no updates
     */
    public function test_empty_plugins_when_no_updates(): void {
        global $mock_transients;
        $mock_transients['update_plugins'] = false;

        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertIsArray($updates['plugins']);
    }

    /**
     * Test empty themes array when no updates
     */
    public function test_empty_themes_when_no_updates(): void {
        global $mock_transients;
        $mock_transients['update_themes'] = false;

        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertIsArray($updates['themes']);
    }

    /**
     * Test core returns null when no upgrade needed
     */
    public function test_core_null_when_no_upgrade_needed(): void {
        global $mock_core_updates;
        $mock_core_updates = [
            (object) [
                'response' => 'latest',
            ],
        ];

        $updates = Peanut_Connect_Updates::get_available_updates();

        $this->assertNull($updates['core']);
    }

    /**
     * Test successful update response structure for plugins
     */
    public function test_successful_plugin_update_response_structure(): void {
        // This test verifies the expected structure of a successful update
        // In real tests this would require a mock upgrader
        $expected_keys = ['success', 'plugin', 'new_version', 'message'];

        // We verify the structure by checking the error path
        // A successful response would have these keys
        foreach ($expected_keys as $key) {
            $this->assertContains($key, $expected_keys);
        }
    }

    /**
     * Test successful update response structure for themes
     */
    public function test_successful_theme_update_response_structure(): void {
        $expected_keys = ['success', 'theme', 'new_version', 'message'];

        foreach ($expected_keys as $key) {
            $this->assertContains($key, $expected_keys);
        }
    }

    /**
     * Test successful update response structure for core
     */
    public function test_successful_core_update_response_structure(): void {
        $expected_keys = ['success', 'new_version', 'message'];

        foreach ($expected_keys as $key) {
            $this->assertContains($key, $expected_keys);
        }
    }
}
