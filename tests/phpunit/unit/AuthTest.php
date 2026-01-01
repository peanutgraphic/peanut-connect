<?php
/**
 * Tests for Peanut_Connect_Auth
 *
 * @package Peanut_Connect
 */

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {

    /**
     * Reset test state before each test
     */
    protected function setUp(): void {
        peanut_reset_test_state();
    }

    /**
     * Test that verify_request fails without authorization header
     */
    public function test_verify_request_fails_without_auth_header(): void {
        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');

        $result = Peanut_Connect_Auth::verify_request($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('missing_authorization', $result->get_error_code());
    }

    /**
     * Test that verify_request fails with invalid authorization format
     */
    public function test_verify_request_fails_with_invalid_auth_format(): void {
        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Basic dXNlcjpwYXNz');

        $result = Peanut_Connect_Auth::verify_request($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_authorization', $result->get_error_code());
    }

    /**
     * Test that verify_request fails when site key not configured
     */
    public function test_verify_request_fails_without_site_key(): void {
        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Bearer some-random-key');

        $result = Peanut_Connect_Auth::verify_request($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('not_configured', $result->get_error_code());
    }

    /**
     * Test that verify_request fails with invalid site key
     */
    public function test_verify_request_fails_with_invalid_key(): void {
        // Set up a valid site key
        update_option('peanut_connect_site_key', 'correct-site-key-12345');

        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Bearer wrong-key');

        $result = Peanut_Connect_Auth::verify_request($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_key', $result->get_error_code());
    }

    /**
     * Test that verify_request succeeds with valid key
     */
    public function test_verify_request_succeeds_with_valid_key(): void {
        $site_key = 'valid-site-key-abcdef123456';
        update_option('peanut_connect_site_key', $site_key);

        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Bearer ' . $site_key);

        $result = Peanut_Connect_Auth::verify_request($request);

        $this->assertTrue($result);
    }

    /**
     * Test that manager URL is stored from header
     */
    public function test_verify_request_stores_manager_url(): void {
        $site_key = 'valid-site-key-abcdef123456';
        $manager_url = 'https://manager.example.com';
        update_option('peanut_connect_site_key', $site_key);

        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Bearer ' . $site_key);
        $request->set_header('X-Peanut-Manager', $manager_url);

        $result = Peanut_Connect_Auth::verify_request($request);

        $this->assertTrue($result);
        $this->assertEquals($manager_url, get_option('peanut_connect_manager_url'));
    }

    /**
     * Test that last sync time is updated
     */
    public function test_verify_request_updates_last_sync(): void {
        $site_key = 'valid-site-key-abcdef123456';
        update_option('peanut_connect_site_key', $site_key);

        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Bearer ' . $site_key);

        Peanut_Connect_Auth::verify_request($request);

        $last_sync = get_option('peanut_connect_last_sync');
        $this->assertNotEmpty($last_sync);
    }

    /**
     * Test has_permission returns true for always-allowed permissions
     */
    public function test_has_permission_allows_health_check(): void {
        $this->assertTrue(Peanut_Connect_Auth::has_permission('health_check'));
    }

    /**
     * Test has_permission returns true for always-allowed permissions
     */
    public function test_has_permission_allows_list_updates(): void {
        $this->assertTrue(Peanut_Connect_Auth::has_permission('list_updates'));
    }

    /**
     * Test has_permission checks optional permissions
     */
    public function test_has_permission_checks_optional_permissions(): void {
        // Without any permissions set
        $this->assertFalse(Peanut_Connect_Auth::has_permission('perform_updates'));

        // With permission enabled
        update_option('peanut_connect_permissions', [
            'perform_updates' => true,
        ]);
        $this->assertTrue(Peanut_Connect_Auth::has_permission('perform_updates'));
    }

    /**
     * Test get_permissions returns defaults
     */
    public function test_get_permissions_returns_defaults(): void {
        $permissions = Peanut_Connect_Auth::get_permissions();

        $this->assertIsArray($permissions);
        $this->assertArrayHasKey('health_check', $permissions);
        $this->assertArrayHasKey('list_updates', $permissions);
        $this->assertArrayHasKey('perform_updates', $permissions);
        $this->assertArrayHasKey('access_analytics', $permissions);
    }

    /**
     * Test rate limit headers are added to response
     */
    public function test_add_rate_limit_headers(): void {
        $site_key = 'valid-site-key-abcdef123456';
        update_option('peanut_connect_site_key', $site_key);

        $request = new WP_REST_Request('GET', '/peanut-connect/v1/health');
        $request->set_header('Authorization', 'Bearer ' . $site_key);

        $response = new WP_REST_Response(['status' => 'ok'], 200);

        $response = Peanut_Connect_Auth::add_rate_limit_headers($response, $request, 'health');

        $headers = $response->get_headers();
        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }
}
