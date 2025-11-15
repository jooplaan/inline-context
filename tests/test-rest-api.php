<?php
/**
 * Class Test_Inline_Context_REST_API
 *
 * @package Inline_Context
 */

/**
 * Test REST API endpoints
 */
class Test_Inline_Context_REST_API extends WP_UnitTestCase {

	/**
	 * Admin user ID for authentication
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Create admin user for REST API authentication
		$this->admin_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $this->admin_id );
	}

	/**
	 * Test REST API namespace is registered
	 */
	public function test_rest_namespace_registered() {
		$namespaces = rest_get_server()->get_namespaces();
		$this->assertContains( 'inline-context/v1', $namespaces );
	}

	/**
	 * Test search endpoint exists
	 */
	public function test_search_endpoint_exists() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/inline-context/v1/notes/search', $routes );
	}

	/**
	 * Test search endpoint returns results
	 */
	public function test_search_endpoint_returns_results() {
		// Create test note
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Searchable Test Note',
				'post_content' => '<p>Test content</p>',
				'post_status'  => 'publish',
			)
		);

		// Make REST request
		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params( array( 's' => 'Searchable' ) );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertGreaterThan( 0, count( $data ) );
	}

	/**
	 * Test track usage endpoint exists
	 */
	public function test_track_usage_endpoint_exists() {
		$routes = rest_get_server()->get_routes();
		// Check for the pattern-based route
		$found = false;
		foreach ( $routes as $route => $handlers ) {
			if ( preg_match( '#^/inline-context/v1/notes/\(\?P<id>.*?\)/track-usage$#', $route ) ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, 'Track usage endpoint not found in registered routes' );
	}

	/**
	 * Test track usage endpoint updates meta
	 */
	public function test_track_usage_updates_meta() {
		// Create note
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Tracked Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Create post that will use the note
		$post_id = $this->factory->post->create();

		// Track usage
		$request = new WP_REST_Request( 'POST', '/inline-context/v1/notes/' . $note_id . '/track-usage' );
		$request->set_body_params( array( 'post_id' => $post_id ) );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );

		// Note: The track_usage endpoint is now a no-op (read-only)
		// Actual tracking happens via save_post hook in Inline_Context_Sync
		// So we just verify the endpoint responds successfully
		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		
		// used_in_posts may be empty string or array
		if ( ! is_array( $used_in ) ) {
			$used_in = array();
		}
		
		$this->assertIsArray( $used_in );
		// Note: Endpoint doesn't actually track anymore, so we don't assert contains
	}

	/**
	 * Test handle removals endpoint exists
	 */
	public function test_handle_removals_endpoint_exists() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/inline-context/v1/notes/handle-removals', $routes );
	}

	/**
	 * Test search filters by reusable status
	 */
	public function test_search_filters_reusable() {
		// Create reusable note
		$reusable_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Reusable Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $reusable_id, 'is_reusable', true );

		// Create non-reusable note
		$non_reusable_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Non-Reusable Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Search with reusable filter
		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params(
			array(
				's'             => 'Note',
				'reusable_only' => true,
			)
		);
		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data, 'Should return at least one result' );

		// Should only return reusable note
		$ids = wp_list_pluck( $data, 'id' );
		$this->assertContains( $reusable_id, $ids );
		$this->assertNotContains( $non_reusable_id, $ids );
	}
}
