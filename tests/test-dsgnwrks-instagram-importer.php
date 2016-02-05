<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Instagram_Importer extends WP_UnitTestCase {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->importer = Test_DsgnWrksInstagram::get_instance();
		$this->post_id = $this->factory->post->create();
	}

	public function tearDown() {
		parent::tearDown();
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'DsgnWrksInstagram' ) );
	}

	function test_important_bits_exist() {
		$this->assertTrue(
			isset( $this->importer->plugin_version ) && $this->importer->plugin_version > 0
			&& isset( $this->importer->plugin_name )
			&& isset( $this->importer->settings_slug )
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

	public function test_upload_media() {
		update_option( 'uploads_use_yearmonth_folders', false );
		$wp_upload_dir = wp_upload_dir();
		$path = $wp_upload_dir['path'];
		foreach ( scandir( $path ) as $file ) {
			if ( 0 === strpos( $file, '.' ) ) {
				continue;
			}
			$file = $path . '/' . $file;
			if ( is_dir( $file ) ) {
				deleteDirectory( $file );
			} else {
				unlink( $file );
			}
		}

		// $media_url = 'https://scontent.cdninstagram.com/hphotos-xaf1/t51.2885-15/s320x320/sh0.08/e35/11849181_754675274659968_461486155_n.jpg';
		// $media_url = 'http://photos.jtsternberg.com/files/2015/09/IMG_3990-300x300.jpg';
		// $media_url = 'http://photos.jtsternberg.com/files/2015/09/IMG_3990.jpg';

		// $media_url = 'https://scontent.cdninstagram.com/hphotos-xaf1/s320x320/d.jpg';
		$media_urls = array(
			'http://jtsternberg.com/file-drop/test-image-v2.jpg',
			'http://jtsternberg.com/file-drop/test-image-v2.jpg',
		);

		$this->importer->import = array(
			'post_id'      => $this->post_id,
			'post_title'   => 'Test upload',
			'post_content' => '',
			'featured'     => false,
		);

		$result = $this->importer->upload_media( $media_urls, '1440878973' );

		$expected = version_compare( $GLOBALS['wp_version'], '4.4' ) < 0
			? '<img width="50" height="50" src="http://example.org/wp-content/uploads/1440878973.jpg" class="attachment-50x50" alt="Test upload" /><strong>&ldquo;Test upload&rdquo;</strong> <em> imported and created successfully.</em>'
			: '<img width="50" height="50" src="http://example.org/wp-content/uploads/1440878973.jpg" class="attachment-50x50 size-50x50" alt="Test upload" srcset="http://example.org/wp-content/uploads/1440878973.jpg 150w, http://example.org/wp-content/uploads/1440878973-300x300.jpg 300w, http://example.org/wp-content/uploads/1440878973.jpg 640w" sizes="(max-width: 50px) 85vw, 50px" /><strong>&ldquo;Test upload&rdquo;</strong> <em> imported and created successfully.</em>';

		$this->assertEquals( $expected, str_replace( '-150x150.jpg', '.jpg', $result ) );

		$expected = version_compare( $GLOBALS['wp_version'], '4.4' ) < 0
			? '<img width="640" height="640" src="http://example.org/wp-content/uploads/1440878973.jpg" class="insta-image" alt="Test upload" />'
			: '<img width="640" height="640" src="http://example.org/wp-content/uploads/1440878973.jpg" class="insta-image" alt="Test upload" srcset="http://example.org/wp-content/uploads/1440878973-150x150.jpg 150w, http://example.org/wp-content/uploads/1440878973-300x300.jpg 300w, http://example.org/wp-content/uploads/1440878973.jpg 640w" sizes="(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 61vw, (max-width: 1362px) 45vw, 600px" />';

		$this->assertEquals( $expected, $this->importer->insta_image );

		$this->assertEquals( 'http://example.org/wp-content/uploads/1440878973.jpg', $this->importer->img_src );
	}

}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

class Test_DsgnWrksInstagram extends DsgnWrksInstagram {

	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return DsgnWrksInstagram A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	protected function __construct() {
		parent::__construct();
	}

	public function upload_media( $media_url = '', $filename = '', $attach_title = '', $size = '' ) {
		return parent::upload_media( $media_url, $filename, $attach_title, $size );
	}

	public function debugEnabled() {
		return parent::debugEnabled();
	}

}
