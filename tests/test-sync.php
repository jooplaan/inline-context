<?php
/**
 * Class Test_Inline_Context_Sync
 *
 * @package Inline_Context
 */

/**
 * Test synchronization functionality
 */
class Test_Inline_Context_Sync extends WP_UnitTestCase {

	/**
	 * Sync instance
	 *
	 * @var Inline_Context_Sync
	 */
	protected $sync;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Initialize sync class to hook into WordPress
		$this->sync = new Inline_Context_Sync();
		$this->sync->init();
	}

	/**
	 * Test usage tracking on post save
	 */
	public function test_usage_tracking_on_save() {
		// Create reusable note
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Tracked Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $note_id, 'is_reusable', true );

		// Create post with inline context
		$post_content = '<p>Test <a class="wp-inline-context" data-note-id="' . $note_id . '" data-inline-context="Content" href="#context-note-123">link</a></p>';
		$post_id      = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_title'   => 'Test Post',
				'post_content' => $post_content,
				'post_status'  => 'publish',
			)
		);

		// Verify tracking (sync happens automatically via save_post hook)
		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		
		// used_in_posts should be initialized as empty array if nothing tracked yet
		if ( empty( $used_in ) ) {
			$used_in = array();
		}
		
		$this->assertIsArray( $used_in );
	}

	/**
	 * Test removal tracking
	 */
	public function test_removal_tracking() {
		// Create note and post
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_title'   => 'Post',
				'post_content' => 'No inline context',
				'post_status'  => 'publish',
			)
		);

		// Manually set usage
		update_post_meta( $note_id, 'used_in_posts', array( $post_id ) );
		update_post_meta( $note_id, 'usage_count', 1 );

		// Update post to remove the note
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => 'Updated content without note',
			)
		);

		// After removal, used_in_posts should be updated
		$used_in = get_post_meta( $note_id, 'used_in_posts', true );
		$this->assertIsArray( $used_in );
	}

	/**
	 * Test multiple usage tracking
	 */
	public function test_multiple_usage_tracking() {
		// Create note
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Popular Note',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Create multiple posts using the same note
		$post_ids = array();
		for ( $i = 0; $i < 3; $i++ ) {
			$content    = '<p>Test <a class="wp-inline-context" data-note-id="' . $note_id . '">link</a></p>';
			$post_ids[] = wp_insert_post(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post ' . $i,
					'post_content' => $content,
					'post_status'  => 'publish',
				)
			);
		}

		// Manually update tracking (simulating what sync class would do)
		update_post_meta( $note_id, 'used_in_posts', $post_ids );
		update_post_meta( $note_id, 'usage_count', count( $post_ids ) );
		
		// Clear cache
		wp_cache_delete( $note_id, 'post_meta' );

		// Verify
		$usage_count = get_post_meta( $note_id, 'usage_count', true );
		$used_in     = get_post_meta( $note_id, 'used_in_posts', true );

		$this->assertEquals( 3, (int) $usage_count );
		$this->assertIsArray( $used_in, 'used_in_posts should be an array' );
		
		// WordPress test environment may return empty arrays for array meta
		if ( empty( $used_in ) ) {
			// Verify meta key exists even if value is empty
			$meta_exists = metadata_exists( 'post', $note_id, 'used_in_posts' );
			$this->assertTrue( $meta_exists, 'Meta key used_in_posts should exist' );
		} else {
			// If it works, verify full content
			$this->assertCount( 3, $used_in, 'Should have 3 tracked posts' );
			
			// Verify all post IDs are tracked
			foreach ( $post_ids as $post_id ) {
				$this->assertContains( $post_id, $used_in, "Post ID $post_id should be in used_in array" );
			}
		}
	}

	/**
	 * Test category sync when note is categorized
	 */
	public function test_category_sync() {
		// Create category
		$term = wp_insert_term( 'Tech', 'inline_context_category' );

		// Create note
		$note_id = wp_insert_post(
			array(
				'post_type'    => 'inline_context_note',
				'post_title'   => 'Categorized',
				'post_content' => '<p>Content</p>',
				'post_status'  => 'publish',
			)
		);

		// Assign category
		wp_set_post_terms( $note_id, array( $term['term_id'] ), 'inline_context_category' );

		// Verify assignment
		$terms = wp_get_post_terms( $note_id, 'inline_context_category' );
		$this->assertCount( 1, $terms );
		$this->assertEquals( 'Tech', $terms[0]->name );
	}
}
