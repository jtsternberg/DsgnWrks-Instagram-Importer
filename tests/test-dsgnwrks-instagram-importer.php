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

	function test_debug_enabled() {
		$this->assertFalse( $this->importer->debugEnabled() );
	}

}
