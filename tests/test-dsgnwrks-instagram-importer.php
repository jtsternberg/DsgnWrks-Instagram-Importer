<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Instagram_Importer extends WP_UnitTestCase {

	function __construct() {
		require_once( dirname( __FILE__ ) . '/../dsgnwrks-instagram-importer.php' );
		$this->importer = new DsgnWrksInstagram;
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'DsgnWrksInstagram' ) );
	}

	function test_important_bits_exist() {
		$this->assertTrue(
			isset( $this->importer->plugin_version ) && $this->importer->plugin_version > 0
			&& isset( $this->importer->plugin_name )
			&& isset( $this->importer->plugin_id )
		);
	}

	function test_debug_disabled() {
		$this->assertFalse( $this->importer->debugEnabled() );
	}

	function test_settings_validation() {
		$pre_opts = array(
			'jtsternberg' => array(
				'tag-filter' => '',
				'date-filter' => 1397347200,
				'mm' => 4,
				'dd' => 13,
				'yy' => 2014,
				'feat_image' => 'yes',
				'auto_import' => 'yes',
				'post-title' => '**insta-text**',
				'post_content' => '<p><a href="**insta-link**" target="_blank">**insta-image**</a></p>
				<p>Instagram filter used: **insta-filter**</p>
				[if-insta-location]<p>Photo taken at: **insta-location**</p>[/if-insta-location]
				<p><a href="**insta-link**" target="_blank">View in Instagram &rArr;</a></p>',
				'post-type' => 'post',
				'draft' => 'draft',
				'author' => '1',
				'hashtags_as_tax' => '',
				'category' => '',
				'post_tag' => '',
				'orientation' => '',
				'access_token' => '63481.9a9ab54.fa2ed7d2dc8f4003880adc30a4d0abf7',
				'bio' => '',
				'website' => 'http://jtsternberg.com/about',
				'profile_picture' => 'http://images.ak.instagram.com/profiles/profile_63481_75sq_1378078332.jpg',
				'full_name' => 'Justin Sternberg',
				'id' => '63481',
				'full_username' => 'jtsternberg',
			),
			'username'  => 'jtsternberg',
			'frequency' => 'never',
		);

		$opts = $this->importer->settings_validate( $pre_opts );
		$this->assertEquals( $pre_opts, $opts );
	}

}
