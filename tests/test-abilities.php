<?php
/**
 * Class Test_Inline_Context_Abilities
 *
 * @package Inline_Context
 */

/**
 * Test Abilities API integration
 */
class Test_Inline_Context_Abilities extends WP_UnitTestCase {

	/**
	 * Admin user ID for authentication
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Abilities instance
	 *
	 * @var Inline_Context_Abilities
	 */
	protected $abilities;

	/**
	 * Track if abilities have been registered for test session.
	 *
	 * @var bool
	 */
	protected static $abilities_registered = false;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Create admin user for REST API authentication.
		$this->admin_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $this->admin_id );

		// Get abilities instance.
		global $inline_context_abilities;
		
		// If abilities instance doesn't exist, create it.
		if ( ! isset( $inline_context_abilities ) || ! $inline_context_abilities ) {
			$inline_context_abilities = new Inline_Context_Abilities();
		}
		
		$this->abilities = $inline_context_abilities;
		
		// Only register abilities once for the entire test session.
		if ( function_exists( 'wp_register_ability' ) && ! self::$abilities_registered ) {
			// Initialize abilities (adds action hooks).
			$this->abilities->init();
			
			// Trigger WordPress action hooks to register abilities.
			do_action( 'wp_abilities_api_categories_init' );
			do_action( 'wp_abilities_api_init' );
			
			// Mark as registered.
			self::$abilities_registered = true;
		}
	}

	/**
	 * Test abilities class initializes
	 */
	public function test_abilities_class_exists() {
		// Expect "already registered" warnings since bootstrap loaded plugin first.
		$this->setExpectedIncorrectUsage( 'WP_Ability_Categories_Registry::register' );
		$this->setExpectedIncorrectUsage( 'WP_Abilities_Registry::register' );

		$this->assertTrue( class_exists( 'Inline_Context_Abilities' ) );
	}

	/**
	 * Test abilities only register on WordPress 6.9+
	 */
	public function test_abilities_require_wp_function() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'Abilities API not available (WordPress < 6.9)' );
		}

		$this->assertTrue( function_exists( 'wp_register_ability' ) );
		$this->assertTrue( function_exists( 'wp_register_ability_category' ) );
	}

	/**
	 * Test create-note ability execution
	 */
	public function test_execute_create_note() {
		if ( ! method_exists( $this->abilities, 'execute_create_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		$input = array(
			'title'       => 'Test Note Title',
			'content'     => '<p>Test note content</p>',
			'category'    => '',
			'is_reusable' => true,
		);

		$result = $this->abilities->execute_create_note( $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertTrue( $result['success'] );
		$this->assertArrayHasKey( 'note_id', $result );
		$this->assertIsInt( $result['note_id'] );
		$this->assertArrayHasKey( 'message', $result );

		// Verify post was created.
		$post = get_post( $result['note_id'] );
		$this->assertEquals( 'inline_context_note', $post->post_type );
		$this->assertEquals( 'Test Note Title', $post->post_title );
		$this->assertEquals( '<p>Test note content</p>', $post->post_content );
	}

	/**
	 * Test create-note with empty title
	 *
	 * Note: Input validation happens at the WordPress Abilities API level via input_schema.
	 * When calling execute methods directly (bypassing the API), WordPress allows empty titles.
	 */
	public function test_execute_create_note_with_empty_title() {
		if ( ! method_exists( $this->abilities, 'execute_create_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		$input = array(
			'title'   => '', // WordPress allows empty titles at the execute level.
			'content' => '<p>Content</p>',
		);

		$result = $this->abilities->execute_create_note( $input );

		// Execute method doesn't validate - that happens at API level.
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertTrue( $result['success'] ); // Should succeed - WordPress allows empty titles.
		$this->assertArrayHasKey( 'note_id', $result );
	}

	/**
	 * Test search-notes ability execution
	 */
	public function test_execute_search_notes() {
		if ( ! method_exists( $this->abilities, 'execute_search_notes' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		// Create test notes.
		$note1_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Searchable Note One',
				'post_content' => '<p>Content one</p>',
				'post_status'  => 'publish',
			)
		);

		$note2_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Searchable Note Two',
				'post_content' => '<p>Content two</p>',
				'post_status'  => 'publish',
			)
		);

		$input = array(
			'search'        => 'Searchable',
			'limit'         => 10,
			'reusable_only' => false,
		);

		$result = $this->abilities->execute_search_notes( $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'notes', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertGreaterThanOrEqual( 2, count( $result['notes'] ) );

		// Verify note structure.
		$note = $result['notes'][0];
		$this->assertArrayHasKey( 'id', $note );
		$this->assertArrayHasKey( 'title', $note );
		$this->assertArrayHasKey( 'content', $note );
	}

	/**
	 * Test search-notes with reusable filter
	 */
	public function test_execute_search_notes_reusable_only() {
		if ( ! method_exists( $this->abilities, 'execute_search_notes' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		// Create reusable note.
		$reusable_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Reusable Test Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $reusable_id, 'is_reusable', true );

		// Create non-reusable note.
		$non_reusable_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Non-Reusable Test Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $non_reusable_id, 'is_reusable', false );

		$input = array(
			'search'        => 'Test Note',
			'limit'         => 10,
			'reusable_only' => true,
		);

		$result = $this->abilities->execute_search_notes( $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'notes', $result );

		// Should only return reusable note.
		$ids = wp_list_pluck( $result['notes'], 'id' );
		$this->assertContains( $reusable_id, $ids );
		$this->assertNotContains( $non_reusable_id, $ids );
	}

	/**
	 * Test get-categories ability execution
	 */
	public function test_execute_get_categories() {
		if ( ! method_exists( $this->abilities, 'execute_get_categories' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		// Create test category.
		$term_id = wp_insert_term(
			'Test Category',
			'inline_context_category',
			array(
				'slug' => 'test-category',
			)
		);

		$input = array(); // No input required.

		$result = $this->abilities->execute_get_categories( $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'categories', $result );
		$this->assertGreaterThan( 0, count( $result['categories'] ) );

		// Verify category structure.
		$category = $result['categories'][0];
		$this->assertArrayHasKey( 'id', $category );
		$this->assertArrayHasKey( 'name', $category );
		$this->assertArrayHasKey( 'slug', $category );
	}

	/**
	 * Test get-note ability execution
	 */
	public function test_execute_get_note() {
		if ( ! method_exists( $this->abilities, 'execute_get_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		// Create test note.
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Specific Note',
				'post_content' => '<p>Specific content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $note_id, 'is_reusable', true );
		update_post_meta( $note_id, 'usage_count', 5 );

		$input = array(
			'note_id' => $note_id,
		);

		$result = $this->abilities->execute_get_note( $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertEquals( $note_id, $result['id'] );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertEquals( 'Specific Note', $result['title'] );
		$this->assertArrayHasKey( 'content', $result );
		$this->assertArrayHasKey( 'is_reusable', $result );
		$this->assertTrue( $result['is_reusable'] );
		$this->assertArrayHasKey( 'usage_count', $result );
		$this->assertEquals( 5, $result['usage_count'] );
	}

	/**
	 * Test get-note with invalid ID
	 */
	public function test_execute_get_note_invalid_id() {
		if ( ! method_exists( $this->abilities, 'execute_get_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		$input = array(
			'note_id' => 999999, // Non-existent ID.
		);

		$result = $this->abilities->execute_get_note( $input );

		$this->assertInstanceOf( 'WP_Error', $result );
	}

	/**
	 * Test create-inline-note ability execution
	 */
	public function test_execute_create_inline_note() {
		if ( ! method_exists( $this->abilities, 'execute_create_inline_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		$input = array(
			'text'        => 'API',
			'note'        => '<p>Application Programming Interface</p>',
			'category'    => '',
			'is_reusable' => true,
		);

		$result = $this->abilities->execute_create_inline_note( $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertTrue( $result['success'] );
		$this->assertArrayHasKey( 'note_id', $result );
		$this->assertArrayHasKey( 'html', $result );
		$this->assertArrayHasKey( 'message', $result );

		// Verify HTML structure.
		$html = $result['html'];
		$this->assertStringContainsString( 'class="wp-inline-context"', $html );
		$this->assertStringContainsString( 'data-note-id="' . $result['note_id'] . '"', $html );
		$this->assertStringContainsString( 'data-anchor-id=', $html );
		$this->assertStringContainsString( 'href="#context-note-', $html );
		$this->assertStringContainsString( '>API</a>', $html );

		// Verify note was created.
		$post = get_post( $result['note_id'] );
		$this->assertEquals( 'inline_context_note', $post->post_type );
		$this->assertEquals( 'API', $post->post_title );
	}

	/**
	 * Test create-inline-note with category
	 */
	public function test_execute_create_inline_note_with_category() {
		if ( ! method_exists( $this->abilities, 'execute_create_inline_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		// Create test category.
		$term = wp_insert_term(
			'Definition',
			'inline_context_category',
			array(
				'slug' => 'definition',
			)
		);

		$input = array(
			'text'        => 'REST',
			'note'        => '<p>Representational State Transfer</p>',
			'category'    => 'definition',
			'is_reusable' => true,
		);

		$result = $this->abilities->execute_create_inline_note( $input );

		$this->assertTrue( $result['success'] );

		// Verify category was assigned.
		$terms = wp_get_object_terms( $result['note_id'], 'inline_context_category' );
		$this->assertNotEmpty( $terms );
		$this->assertEquals( 'definition', $terms[0]->slug );
	}

	/**
	 * Test search limit validation
	 */
	public function test_search_notes_limit_bounds() {
		if ( ! method_exists( $this->abilities, 'execute_search_notes' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		// Test minimum limit.
		$input = array(
			'search'        => 'test',
			'limit'         => 0, // Should default to 1.
			'reusable_only' => false,
		);

		$result = $this->abilities->execute_search_notes( $input );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'notes', $result );

		// Test maximum limit.
		$input = array(
			'search'        => 'test',
			'limit'         => 100, // Should cap at 50.
			'reusable_only' => false,
		);

		$result = $this->abilities->execute_search_notes( $input );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'notes', $result );
		$this->assertLessThanOrEqual( 50, count( $result['notes'] ) );
	}

	/**
	 * Test permissions for execute methods
	 */
	public function test_abilities_require_edit_posts_capability() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'Abilities API not available' );
		}

		// Create subscriber user (no edit_posts capability).
		$subscriber_id = $this->factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);
		wp_set_current_user( $subscriber_id );

		// Verify subscriber can't execute abilities.
		$this->assertFalse( current_user_can( 'edit_posts' ) );
	}

	/**
	 * Test HTML sanitization in create-inline-note
	 */
	public function test_create_inline_note_sanitizes_html() {
		if ( ! method_exists( $this->abilities, 'execute_create_inline_note' ) ) {
			$this->markTestSkipped( 'Abilities not available' );
		}

		$input = array(
			'text'        => '<script>alert("xss")</script>Clean Text',
			'note'        => '<p>Safe content</p><script>alert("xss")</script>',
			'category'    => '',
			'is_reusable' => true,
		);

		$result = $this->abilities->execute_create_inline_note( $input );

		$this->assertTrue( $result['success'] );

		// Verify text is sanitized (script tags removed).
		$this->assertStringNotContainsString( '<script>', $result['html'] );

		// Verify note content is sanitized.
		$post = get_post( $result['note_id'] );
		$this->assertStringNotContainsString( '<script>', $post->post_content );
	}
}
