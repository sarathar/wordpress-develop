<?php

/**
 * @group formatting
 * @group emoji
 */
class Tests_Formatting_Emoji extends WP_UnitTestCase {

	private $png_cdn = 'https://s.w.org/images/core/emoji/2.3/72x72/';
	private $svn_cdn = 'https://s.w.org/images/core/emoji/2.3/svg/';

	/**
	 * @ticket 36525
	 */
	public function test_unfiltered_emoji_cdns() {
		$output = get_echo( '_print_emoji_detection_script' );

		$this->assertContains( wp_json_encode( $this->png_cdn ), $output );
		$this->assertContains( wp_json_encode( $this->svn_cdn ), $output );
	}

	public function _filtered_emoji_svn_cdn( $cdn = '' ) {
		return 'https://s.wordpress.org/images/core/emoji/svg/';
	}

	/**
	 * @ticket 36525
	 */
	public function test_filtered_emoji_svn_cdn() {
		$filtered_svn_cdn = $this->_filtered_emoji_svn_cdn();

		add_filter( 'emoji_svg_url', array( $this, '_filtered_emoji_svn_cdn' ) );

		$output = get_echo( '_print_emoji_detection_script' );

		$this->assertContains( wp_json_encode( $this->png_cdn ), $output );
		$this->assertNotContains( wp_json_encode( $this->svn_cdn ), $output );
		$this->assertContains( wp_json_encode( $filtered_svn_cdn ), $output );

		remove_filter( 'emoji_svg_url', array( $this, '_filtered_emoji_svn_cdn' ) );
	}

	public function _filtered_emoji_png_cdn( $cdn = '' ) {
		return 'https://s.wordpress.org/images/core/emoji/png_cdn/';
	}

	/**
	 * @ticket 36525
	 */
	public function test_filtered_emoji_png_cdn() {
		$filtered_png_cdn = $this->_filtered_emoji_png_cdn();

		add_filter( 'emoji_url', array( $this, '_filtered_emoji_png_cdn' ) );

		$output = get_echo( '_print_emoji_detection_script' );

		$this->assertContains( wp_json_encode( $filtered_png_cdn ), $output );
		$this->assertNotContains( wp_json_encode( $this->png_cdn ), $output );
		$this->assertContains( wp_json_encode( $this->svn_cdn ), $output );

		remove_filter( 'emoji_url', array( $this, '_filtered_emoji_png_cdn' ) );
	}

	/**
	 * @ticket 35293
	 */
	public function test_wp_emoji_regex_returns_regexen() {
		$default = wp_emoji_regex();
		$this->assertNotEmpty( $default );

		$codepoints = wp_emoji_regex( 'codepoints' );
		$this->assertNotEmpty( $codepoints );

		$this->assertSame( $default, $codepoints );

		$entities = wp_emoji_regex( 'entities' );
		$this->assertNotEmpty( $entities );

		$this->assertNotSame( $default, $entities );
	}

	public function data_wp_encode_emoji() {
		return array(
			array(
				// Not emoji
				'’',
				'’',
			),
			array(
				// Simple emoji
				'🙂',
				'&#x1f642;',
			),
			array(
				// Skin tone, gender, ZWJ, emoji selector
				'👮🏼‍♀️',
				'&#x1f46e;&#x1f3fc;&#x200d;&#x2640;&#xfe0f;',
			),
			array(
				// Unicode 10
				'🧚',
				'&#x1f9da;',
			),
		);
	}

	/**
	 * @ticket 35293
	 * @dataProvider data_wp_encode_emoji
	 */
	public function test_wp_encode_emoji( $emoji, $expected ) {
		$this->assertSame( $expected, wp_encode_emoji( $emoji ) );
	}

	public function data_wp_staticize_emoji() {
		$data = array(
			array(
				// Not emoji
				'’',
				'’',
			),
			array(
				// Simple emoji
				'🙂',
				'<img src="' . $this->png_cdn . '1f642.png" alt="" class="wp-smiley" style="height: 1em; max-height: 1em;" />',
			),
			array(
				// Skin tone, gender, ZWJ, emoji selector
				'👮🏼‍♀️',
				'<img src="' . $this->png_cdn . '1f46e-1f3fc-200d-2640-fe0f.png" alt="" class="wp-smiley" style="height: 1em; max-height: 1em;" />',
			),
			array(
				// Unicode 10
				'🧚',
				'<img src="' . $this->png_cdn . '1f9da.png" alt="" class="wp-smiley" style="height: 1em; max-height: 1em;" />',
			),
		);

		// Older versions of PHP don't html_entity_decode() emoji, so we need to make sure they're testing in the expected form.
		foreach ( $data as $key => $datum ) {
			$emoji = html_entity_decode( wp_encode_emoji( $datum[0] ) );
			$data[ $key ][1] = str_replace( 'alt=""', 'alt="' . $emoji . '"', $datum[1] );
		}

		return $data;
	}

	/**
	 * @ticket 35293
	 * @dataProvider data_wp_staticize_emoji
	 */
	public function test_wp_staticize_emoji( $emoji, $expected ) {
		$this->assertSame( $expected, wp_staticize_emoji( $emoji ) );
	}
}
