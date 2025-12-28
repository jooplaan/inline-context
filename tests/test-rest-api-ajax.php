<?php
/**
 * Class Test_Inline_Context_REST_API_Ajax
 *
 * AJAX tests for REST API endpoints - tests the full HTTP request/response cycle
 *
 * @package Inline_Context
 * @group ajax
 */

/**
 * Test REST API endpoints via AJAX
 */
class Test_Inline_Context_REST_API_Ajax extends WP_Ajax_UnitTestCase {

	/**
	 * Admin user ID for authentication
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Editor user ID for permission testing
	 *
	 * @var int
	 */
	protected $editor_id;

	/**
	 * Subscriber user ID for permission testing
	 *
	 * @var int
	 */
	protected $subscriber_id;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Create users with different roles.
		$this->admin_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);

		$this->editor_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);

		$this->subscriber_id = $this->factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);

		// Set admin as current user by default.
		wp_set_current_user( $this->admin_id );
	}

	/**
	 * Test search endpoint via HTTP request
	 *
	 * @group ajax
	 */
	public function test_search_endpoint_via_http() {
		// Create test notes.
		$note1_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Ajax Test Note One',
				'post_content' => '<p>First test content</p>',
				'post_status'  => 'publish',
			)
		);

		$note2_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Ajax Test Note Two',
				'post_content' => '<p>Second test content</p>',
				'post_status'  => 'publish',
			)
		);

		// Make REST request.
		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params( array( 's' => 'Ajax Test' ) );
		$response = rest_do_request( $request );

		// Assertions.
		$this->assertEquals( 200, $response->get_status() );
		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertCount( 2, $data );

		// Verify note structure.
		$this->assertArrayHasKey( 'id', $data[0] );
		$this->assertArrayHasKey( 'title', $data[0] );
		$this->assertArrayHasKey( 'content', $data[0] );
		$this->assertArrayHasKey( 'excerpt', $data[0] );
	}

	/**
	 * Test search endpoint with authentication
	 *
	 * @group ajax
	 */
	public function test_search_endpoint_requires_authentication() {
		// Create test note.
		wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Auth Test Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Test as subscriber (no edit_posts capability).
		wp_set_current_user( $this->subscriber_id );

		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params( array( 's' => 'Auth Test' ) );
		$response = rest_do_request( $request );

		// Should be forbidden.
		$this->assertEquals( 403, $response->get_status() );
		$this->assertInstanceOf( 'WP_REST_Response', $response );
	}

	/**
	 * Test search endpoint with editor role
	 *
	 * @group ajax
	 */
	public function test_search_endpoint_allows_editor() {
		// Create test note.
		wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Editor Test Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Test as editor (has edit_posts capability).
		wp_set_current_user( $this->editor_id );

		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params( array( 's' => 'Editor Test' ) );
		$response = rest_do_request( $request );

		// Should succeed.
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Test search with reusable filter via HTTP
	 *
	 * @group ajax
	 */
	public function test_search_reusable_filter_via_http() {
		// Create reusable note.
		$reusable_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Ajax Reusable Note',
				'post_content' => '<p>Reusable content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $reusable_id, 'is_reusable', true );

		// Create non-reusable note.
		$non_reusable_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Ajax Non-Reusable Note',
				'post_content' => '<p>Non-reusable content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $non_reusable_id, 'is_reusable', false );

		// Search with reusable filter.
		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params(
			array(
				's'             => 'Ajax',
				'reusable_only' => '1',
			)
		);
		$response = rest_do_request( $request );

		// Assertions.
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$ids  = wp_list_pluck( $data, 'id' );

		$this->assertContains( $reusable_id, $ids );
		$this->assertNotContains( $non_reusable_id, $ids );
	}

	/**
	 * Test track usage endpoint via HTTP
	 *
	 * @group ajax
	 */
	public function test_track_usage_via_http() {
		// Create note.
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Ajax Tracked Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Create post.
		$post_id = $this->factory->post->create();

		// Track usage.
		$request = new WP_REST_Request( 'POST', "/inline-context/v1/notes/{$note_id}/track-usage" );
		$request->set_body_params( array( 'post_id' => $post_id ) );
		$response = rest_do_request( $request );

		// Assertions.
		$this->assertEquals( 200, $response->get_status() );
		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'used_in_posts', $data );
		$this->assertArrayHasKey( 'usage_count', $data );
	}

	/**
	 * Test track usage requires authentication
	 *
	 * @group ajax
	 */
	public function test_track_usage_requires_authentication() {
		// Create note.
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Auth Tracked Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		$post_id = $this->factory->post->create();

		// Test as subscriber.
		wp_set_current_user( $this->subscriber_id );

		$request = new WP_REST_Request( 'POST', "/inline-context/v1/notes/{$note_id}/track-usage" );
		$request->set_body_params( array( 'post_id' => $post_id ) );
		$response = rest_do_request( $request );

		// Should be forbidden.
		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Test track usage validates parameters
	 *
	 * @group ajax
	 */
	public function test_track_usage_validates_parameters() {
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Validated Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Missing post_id parameter.
		$request = new WP_REST_Request( 'POST', "/inline-context/v1/notes/{$note_id}/track-usage" );
		$response = rest_do_request( $request );

		// Should fail validation.
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle removals endpoint via HTTP
	 *
	 * @group ajax
	 */
	public function test_handle_removals_via_http() {
		// Create notes.
		$note1_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Removal Note 1',
				'post_content' => '<p>Content 1</p>',
				'post_status'  => 'publish',
			)
		);

		$note2_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Removal Note 2',
				'post_content' => '<p>Content 2</p>',
				'post_status'  => 'publish',
			)
		);

		// Create post.
		$post_id = $this->factory->post->create();

		// Simulate usage.
		update_post_meta( $note1_id, 'used_in_posts', array( $post_id ) );
		update_post_meta( $note2_id, 'used_in_posts', array( $post_id ) );

		// Handle removals.
		$request = new WP_REST_Request( 'POST', '/inline-context/v1/notes/handle-removals' );
		$request->set_body_params(
			array(
				'post_id'  => $post_id,
				'note_ids' => array( $note1_id, $note2_id ),
			)
		);
		$response = rest_do_request( $request );

		// Assertions.
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'results', $data );
		$this->assertIsArray( $data['results'] );
	}

	/**
	 * Test handle removals requires authentication
	 *
	 * @group ajax
	 */
	public function test_handle_removals_requires_authentication() {
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Auth Removal Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		$post_id = $this->factory->post->create();

		// Test as subscriber.
		wp_set_current_user( $this->subscriber_id );

		$request = new WP_REST_Request( 'POST', '/inline-context/v1/notes/handle-removals' );
		$request->set_body_params(
			array(
				'post_id'  => $post_id,
				'note_ids' => array( $note_id ),
			)
		);
		$response = rest_do_request( $request );

		// Should be forbidden.
		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Test handle removals validates parameters
	 *
	 * @group ajax
	 */
	public function test_handle_removals_validates_parameters() {
		// Missing parameters.
		$request = new WP_REST_Request( 'POST', '/inline-context/v1/notes/handle-removals' );
		$response = rest_do_request( $request );

		// Should fail validation.
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test search endpoint response structure
	 *
	 * @group ajax
	 */
	public function test_search_response_structure() {
		wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Structure Test Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params( array( 's' => 'Structure Test' ) );
		$response = rest_do_request( $request );

		// Check response is properly formed.
		$this->assertEquals( 200, $response->get_status() );
		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test empty search returns empty array
	 *
	 * @group ajax
	 */
	public function test_empty_search_returns_empty_array() {
		$request = new WP_REST_Request( 'GET', '/inline-context/v1/notes/search' );
		$request->set_query_params( array( 's' => 'NonExistentSearchTerm12345' ) );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertEmpty( $data );
	}
}
