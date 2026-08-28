<?php
/**
 * Class Test_Inline_Context_Frontend
 *
 * @package Inline_Context
 */

/**
 * Test frontend content rendering and the endnotes accessibility fallback.
 */
class Test_Inline_Context_Frontend extends WP_UnitTestCase {

	/**
	 * Frontend instance under test.
	 *
	 * @var Inline_Context_Frontend
	 */
	private $frontend;

	/**
	 * Set up a fresh frontend instance for each test.
	 */
	public function set_up() {
		parent::set_up();
		$this->frontend = new Inline_Context_Frontend();
	}

	/**
	 * Build post content containing a single inline context link.
	 *
	 * @return string Post content markup.
	 */
	private function get_content_with_note() {
		return '<p>The <a class="wp-inline-context" '
			. 'data-inline-context="&lt;p&gt;Note body.&lt;/p&gt;" '
			. 'data-anchor-id="context-note-abc" href="#context-note-abc">term</a> matters.</p>';
	}

	/**
	 * The endnotes fallback is appended whenever the content has notes.
	 *
	 * This section is the plugin's only no-JavaScript route to the note text,
	 * so it is unconditional by design - there is deliberately no setting or
	 * filter that removes it.
	 */
	public function test_endnotes_section_is_always_added() {
		$output = $this->frontend->add_noscript_content( $this->get_content_with_note() );

		$this->assertStringContainsString( 'wp-inline-context-noscript-notes', $output );
		$this->assertStringContainsString( 'Note body.', $output );
	}

	/**
	 * Each trigger links to an anchor that actually exists in the document.
	 *
	 * Without a matching target the trigger is a dead link for anyone whose
	 * browser never runs the frontend script.
	 */
	public function test_trigger_href_resolves_to_a_real_target() {
		$output = $this->frontend->add_noscript_content( $this->get_content_with_note() );

		$this->assertStringContainsString( 'href="#context-note-abc"', $output );
		$this->assertStringContainsString( 'id="context-note-abc"', $output );
	}

	/**
	 * Every note offers a labelled way back to its place in the text.
	 */
	public function test_notes_have_labelled_back_links() {
		$output = $this->frontend->add_noscript_content( $this->get_content_with_note() );

		$this->assertStringContainsString( 'id="trigger-context-note-abc"', $output );
		$this->assertStringContainsString( 'href="#trigger-context-note-abc"', $output );
		$this->assertStringContainsString( 'aria-label="Back to text"', $output );
	}

	/**
	 * The section is exposed to assistive technology as a labelled landmark.
	 */
	public function test_endnotes_section_is_labelled_for_assistive_tech() {
		$output = $this->frontend->add_noscript_content( $this->get_content_with_note() );

		$this->assertStringContainsString( '<section class="wp-inline-context-noscript-notes"', $output );
		$this->assertStringContainsString( 'aria-label="Context Notes"', $output );
		$this->assertStringContainsString( '<h2>Notes</h2>', $output );
	}

	/**
	 * Without JavaScript a trigger is a plain link, not a stale ARIA widget.
	 */
	public function test_trigger_is_a_plain_link_in_the_server_rendered_markup() {
		$content = '<p><a class="wp-inline-context" aria-expanded="false" '
			. 'data-inline-context="&lt;p&gt;Note body.&lt;/p&gt;" '
			. 'data-anchor-id="context-note-abc" href="#context-note-abc">term</a></p>';

		$output = $this->frontend->add_noscript_content( $content );

		$this->assertStringContainsString( 'role="link"', $output );
		$this->assertStringNotContainsString( 'aria-expanded', $output );
	}

	/**
	 * Multiple notes are numbered and ordered as they appear in the text.
	 */
	public function test_multiple_notes_keep_document_order() {
		$content = '<p><a class="wp-inline-context" data-inline-context="&lt;p&gt;First.&lt;/p&gt;" '
			. 'data-anchor-id="context-note-one" href="#context-note-one">one</a> and '
			. '<a class="wp-inline-context" data-inline-context="&lt;p&gt;Second.&lt;/p&gt;" '
			. 'data-anchor-id="context-note-two" href="#context-note-two">two</a></p>';

		$output = $this->frontend->add_noscript_content( $content );

		$this->assertLessThan(
			strpos( $output, 'id="context-note-two"' ),
			strpos( $output, 'id="context-note-one"' ),
			'Endnotes should follow the order the triggers appear in the text.'
		);
	}

	/**
	 * Content without our links is returned untouched.
	 */
	public function test_content_without_notes_is_untouched() {
		$content = '<p>Nothing to see here.</p>';

		$this->assertSame( $content, $this->frontend->add_noscript_content( $content ) );
	}

	/**
	 * The fallback is never emitted in the admin.
	 */
	public function test_no_endnotes_section_in_admin() {
		set_current_screen( 'edit-post' );

		$content = $this->get_content_with_note();
		$output  = $this->frontend->add_noscript_content( $content );

		set_current_screen( 'front' );

		$this->assertSame( $content, $output );
	}
}
