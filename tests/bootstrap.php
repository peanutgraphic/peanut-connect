<?php
/**
 * PHPUnit bootstrap file for Peanut Connect tests.
 *
 * @package Peanut_Connect
 */

// Define test mode.
define('PEANUT_CONNECT_TESTING', true);

// Composer autoloader.
$composer_autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

// Try to load WordPress test environment.
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Check if WordPress test suite is available.
if (file_exists($_tests_dir . '/includes/functions.php')) {
    require_once $_tests_dir . '/includes/functions.php';

    function _manually_load_plugin() {
        require dirname(__DIR__) . '/peanut-connect.php';
    }
    tests_add_filter('muplugins_loaded', '_manually_load_plugin');

    require $_tests_dir . '/includes/bootstrap.php';
} else {
    // Load mocks for standalone testing.
    require_once __DIR__ . '/mocks/wordpress-mocks.php';
}

/**
 * Base test case class for Peanut Connect.
 */
abstract class Peanut_Connect_TestCase extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        parent::setUp();
    }

    protected function tearDown(): void {
        parent::tearDown();
    }
}
