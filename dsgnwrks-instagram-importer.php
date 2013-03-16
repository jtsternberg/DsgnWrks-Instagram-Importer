<?php
/*
Plugin Name: DsgnWrks Instagram Importer
Plugin URI: http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer
Description: Allows you to backup your instagram photos while allowing you to have a site to display your instagram archive. Allows you to import to custom post types and attach custom taxonomies.
Author URI: http://dsgnwrks.pro
Author: DsgnWrks
Donate link: http://dsgnwrks.pro/give/
Version: 1.2.1
*/

class DsgnWrksInstagram {

	protected $plugin_name = 'DsgnWrks Instagram Importer';
	protected $plugin_id = 'dsgnwrks-instagram-importer-settings';
	protected $pre = 'dsgnwrks_instagram_';
	protected $plugin_page;
	protected $defaults;
	protected $import = array();

	function __construct() {

		// user option defaults
		$this->defaults = array(
			'tag-filter' => false,
			'feat_image' => 'yes',
			'auto_import' => 'yes',
			'date-filter' => 0,
			'mm' => date( 'm', strtotime( '-1 month' ) ),
			'dd' => date( 'd', strtotime( '-1 month' ) ),
			'yy' => date( 'Y', strtotime( '-1 month' ) ),
			'post-title' => '**insta-text**',
			'post_content' => '<p><a href="**insta-link**" target="_blank">**insta-image**</a></p>'."\n".'<p>'. __( 'Instagram filter used:', 'dsgnwrks' ) .' **insta-filter**</p>'."\n".'[if-insta-location]<p>'. __( 'Photo taken at:', 'dsgnwrks' ) .' **insta-location**</p>[/if-insta-location]'."\n".'<p><a href="**insta-link**" target="_blank">'. __( 'View in Instagram', 'dsgnwrks' ) .' &rArr;</a></p>',
			'post-type' => 'post',
			'draft' => 'draft',
		);

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( $this->pre.'cron', array( $this, 'cron_callback' ) );
		add_action( 'admin_menu', array( $this, 'settings' ) );
		// @TODO
		// add_action( 'before_delete_post', array( $this, 'save_id_on_delete' ), 10, 1 );
		add_action( 'current_screen', array( $this, 'redirects' ) );
		add_filter( 'wp_default_editor', array( $this, 'html_default' ) );
		// @DEV adds a minutely schedule for testing cron
		// add_filter( 'cron_schedules', array( $this, 'minutely' ) );
		add_action( 'all_admin_notices', array( $this, 'show_cron_notice' ) );
	}

	/**
	 * Internationalize the plugin name
	 */
	function pluginName() {
		// i18n
		$this->plugin_name = 'DsgnWrks '. __( 'Instagram Importer', 'dsgnwrks' );
		return $this->plugin_name;
	}

	/**
	 * Hooks to 'all_admin_notices' and displays auto-imported photo messages
	 */
	function show_cron_notice() {

		// check if we have any saved notices from our cron auto-import
		$notices = get_option( 'dsgnwrks_imported_photo_notices' );
		if ( !$notices )
			return;

		// if so, loop through and display them
		echo '<div id="message" class="updated">';
		foreach ( $notices as $userid => $notice ) {
			echo '<h3>'. $userid .' &mdash; '. __( 'imported:', 'dsgnwrks' ) .' '. $notice['time'] .'</h3>';
			echo $notice['notice'];
			echo '<hr/>';
		}
		echo '<br><a href="'. add_query_arg( array() ) .'">'. __( 'Hide', 'dsgnwrks' ) .'</a></div>';
		// reset notices
		update_option( 'dsgnwrks_imported_photo_notices', '' );
	}

	/**
	 * Add import function to cron
	 */
	public function cron_callback() {
		$opts = get_option( 'dsgnwrks_insta_options' );

		if ( !empty( $opts ) && is_array( $opts ) ) : foreach ( $opts as $user => $useropts ) {
			if ( isset( $useropts['auto_import'] ) && $useropts['auto_import'] == 'yes' )
				$this->import( $user );
		} endif;
	}

	/**
	 * @DEV Adds once minutely to the existing schedules for easier cron testing.
	 */
	function minutely( $schedules ) {
		$schedules['minutely'] = array(
			'interval' => 60,
			'display' => 'Once Every Minute'
		);
		return $schedules;
	}

	/**
	 * Get the party started
	 */
	public function init() {

		// Set our plugin page parameter
		$this->plugin_page = add_query_arg( 'page', $this->plugin_id, admin_url( '/tools.php' ) );

		// A pseudo setting. redirects to instagram oauth
		register_setting(
			$this->pre .'importer_users',
			'dsgnwrks_insta_registration',
			array( $this, 'users_validate' )
		);
		// validate user options settings
		register_setting(
			$this->pre .'importer_settings',
			'dsgnwrks_insta_options',
			array( $this, 'settings_validate' )
		);

		$opts = get_option( 'dsgnwrks_insta_options' );
		if ( empty( $opts['frequency'] ) || $opts['frequency'] == 'never' )
			return;

		// if a auto-import frequency interval was saved,
		if ( !wp_next_scheduled( $this->pre.'cron' ) ) {
			// schedule a cron to pull updates from instagram
			wp_schedule_event( time(), $opts['frequency'], $this->pre.'cron' );
		}
	}

	/**
	 * A pseudo setting validation. Sends user on to instagram to be authenticated.
	 */
	public function users_validate( $opts ) {
		$return = add_query_arg( array( 'page' => $this->plugin_id ), admin_url('/tools.php') );
		$uri = add_query_arg( 'return_uri', urlencode( $return ), 'http://dsgnwrks.pro/insta_oauth/' );
		// Send them on with our redirect uri set.
		wp_redirect( $uri, 307 );
		exit;
	}

	/**
	 * Validate each of our user options with an appropriate filter
	 */
	public function settings_validate( $opts ) {

		// get existing saved options to check against
		$old_opts = get_option( 'dsgnwrks_insta_options' );

		// loop through options (users)
		if ( !empty( $opts ) && is_array( $opts ) ) :
			foreach ( $opts as $user => $useropts ) {
				// loop through options (user's options)
				if ( !empty( $useropts ) && is_array( $useropts ) ) : foreach ( $useropts as $key => $opt ) {
					if ( $key === 'date-filter' ) {
						if ( empty( $opts[$user]['mm'] ) && empty( $opts[$user]['dd'] ) && empty( $opts[$user]['yy'] ) || !empty( $opts[$user]['remove-date-filter'] ) ) {
							$opts[$user][$key] = 0;
						}
						else {
							$opts[$user][$key] = strtotime( $opts[$user]['mm'] .'/'. $opts[$user]['dd'] .'/'. $opts[$user]['yy'] );
						}
					} elseif ( $key === 'pw' ) {
						continue;
					} elseif ( $key === 'post-type' ) {
						$opts[$user][$key] = $this->filter( $opt, '', 'post' );
					} elseif ( $key === 'draft' ) {
						$opts[$user][$key] = $this->filter( $opt, '', 'draft' );
					} elseif ( $key === 'yy' || $key === 'mm' || $key === 'dd' ) {
						if ( empty( $opts[$user]['mm'] ) && empty( $opts[$user]['dd'] ) && empty( $opts[$user]['yy'] ) || !empty( $opts[$user]['remove-date-filter'] ) ) {
							$opts[$user][$key] = '';
						}
						else {
							$opts[$user][$key] = $this->filter( $opt, 'absint', '' );
						}
					} elseif ( $key === 'post_content' ) {
						$opts[$user][$key] = $this->filter( $opt, 'wp_kses_post' );
					} elseif ( $key === 'feat_image' || $key === 'auto_import' ) {
						// checkboxes
						$opts[$user][$key] = $opts[$user][$key] == 'yes' ? 'yes' : false;
					} else {
						// defaults to esc_attr() validation
						$opts[$user][$key] = $this->filter( $opt );
					}

				} endif;

				// if our 'frequency' interval was set
				if ( $user === 'frequency' ) {
					$opts[$user] = $this->filter( $useropts );
					// and if our newly saved 'frequency' is different
					// clear the previously scheduled hook
					if ( $opts[$user] != $old_opts['frequency'] )
						wp_clear_scheduled_hook( $this->pre.'cron' );
				}
			}
		// allow plugins to add options to save
		$opts = apply_filters( 'dsgnwrks_instagram_option_save', $opts, $old_opts );
		endif;

		// ok, we're done validating the options, so give them back
		return $opts;
	}

	/**
	 * hooks to 'admin_menu', adds our submenu page and corresponding scripts/styles
	 */
	public function settings() {
		// create admin page
		$plugin_page = add_submenu_page( 'tools.php', $this->pluginName(), __( 'Instagram Importer', 'dsgnwrks' ), 'manage_options', $this->plugin_id, array( $this, 'settings_page' ) );
		// enqueue styles
		add_action( 'admin_print_styles-' . $plugin_page, array( $this, 'styles' ) );
		// enqueue scripts
		add_action( 'admin_print_scripts-' . $plugin_page, array( $this, 'scripts' ) );
		// run our importer only on our admin page when clicking "import"
		add_action( 'admin_head-'. $plugin_page, array( $this, 'fire_importer' ) );
	}

	/**
	 * Creates our admin page
	 */
	public function settings_page() { require_once('settings.php'); }

	/**
	 * Enqueue our admin page's CSS
	 */
	public function styles() {
		wp_enqueue_style( 'dsgnwrks-instagram-importer-admin', plugins_url( 'css/admin.css', __FILE__ ), false, '1.1' );
	}

	/**
	 * Enqueue our admin page's JS
	 */
	public function scripts() {
		wp_enqueue_script( 'dsgnwrks-instagram-importer-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ) );

		$data = array(
			'delete_text' => __( 'Are you sure you want to delete user', 'dsgnwrks' ),
			'logout_text' => __( 'Logging out of Instagram', 'dsgnwrks' )
		);
		// get registered post-types
		$cpts = get_post_types( array( 'public' => true ) );
		foreach ($cpts as $key => $cpt) {
			// get registered taxonomies
			$taxes = get_object_taxonomies( $cpt );
			if ( !empty( $taxes ) )
				$data['cpts'][$cpt][] = $taxes;
		}
		// and save that data for use in our script
		if ( !empty( $data ) )
			wp_localize_script( 'dsgnwrks-instagram-importer-admin', 'dwinstagram', $data );
	}

	/**
	 * hooks into our admin page's head and fires the importer if requested
	 */
	public function fire_importer() {
		if ( isset( $_GET['instaimport'] ) )
			add_action( 'all_admin_notices', array( $this, 'import' ) );
	}

	/**
	 * Start the engine. begins our import and generates feedback messages
	 */
	public function import( $userid = false ) {

		// Only import if the correct flags have been set
		if ( !$userid && !isset( $_GET['instaimport'] ) )
			return;
		// get our options for use in the import
		$opts = get_option( 'dsgnwrks_insta_options' );
		$this->opts = &$opts;
		// instagram user id for pinging instagram
		$this->userid = $id = $userid ? $userid : sanitize_title( urldecode( $_GET['instaimport'] ) );
		// if a $userid was passed in, we know we're doing a cron scheduled event
		$this->doing_cron = $userid ? true : false;

		// We need an id and access token to keep going
		if ( !( isset( $opts[$id]['id'] ) && isset( $opts[$id]['access_token'] ) ) )
			return;

		// if a timezone string was saved
		if ( $tz_string = get_option(' timezone_string ') ) {
			// save our current date to a var
			$pre = date('e');
		 	// and tell php to use WP's timezone string
			date_default_timezone_set( get_option( 'timezone_string' ) );
		}

		// ok, let's access instagram's api
		$messages = $this->import_messages( 'https://api.instagram.com/v1/users/'. $opts[$id]['id'] .'/media/recent?access_token='. $opts[$id]['access_token'] .'&count=80', $opts[$id] );
		// if the api gave us a "next" url, let's loop through till we've hit all pages
		while ( !empty( $messages['next_url'] ) ) {
			$messages = $this->import_messages( $messages['next_url'], $opts[$id], $messages['message'] );
		}

		// debug sent?
		$this->importDebugSet();

		// return php's timezone to its previously set value
		if ( $tz_string )
			date_default_timezone_set( $pre );

		// init our variable
		$notice = '';
		foreach ( $messages['message'] as $key => $message ) {
			// build our $notice variable
			$notice .= $message;
		}

		// get our current time
		$time = date_i18n( 'l F jS, Y @ h:i:s A', strtotime( current_time('mysql') ) );

		// if we're not doing cron, show our notice now
		if ( !$userid ) {
			echo '<div id="message" class="updated">'. $notice .'</div>';
		}
		// otherwise, save our imported photo notices to an option to be displayed later
		elseif ( stripos( $notice, __( 'No new Instagram shots to import', 'dsgnwrks' ) ) === false ) {
			// check if we already have some notices saved
			$notices = get_option( 'dsgnwrks_imported_photo_notices' );
			// if so, add to them
			if ( is_array( $notices ) )
				$notices[$userid] = array( 'notice' => $notice, 'time' => $time );
			// if not, create a new one
			else
				$notices = array( $userid => array( 'notice' => $notice, 'time' => $time ) );
			// save our option
			update_option( 'dsgnwrks_imported_photo_notices', $notices );
		}

		// Save the date/time to notify users of last import time
		set_transient( sanitize_title( urldecode( $_GET['instaimport'] ) ) .'-instaimportdone', $time, 14400 );
	}

	/**
	 * pings instagram with our user's feed url to retrieve photos
	 */
	protected function import_messages( $api_url, $settings, $prevmessages = array() ) {

		// our individual user's settings
		$this->settings = $settings;
		// get instagram feed
		$api = wp_remote_retrieve_body( wp_remote_get( $api_url ) );
		// format our data to be useable
		$data = json_decode( $api );

		if ( !$this->importDebugCheck() )
			$this->debugsend( 'import_messages', $this->userid .' - $data', array( '$this->userid' => $this->userid, '$data' => $data ) );

		// load WP files to use functions in them
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		set_time_limit(300);

		// let's leave our instagram images as full-quality. You'll thank me later ;)
		add_filter( 'wp_editor_set_quality', array( $this, 'max_quality' ) );
		add_filter( 'jpeg_quality', array( $this, 'max_quality' ) );

		// now that we have our feed data, let's loop it
		$messages = $this->pic_loop( $data );
		// if the api gave us a "next" url add it, if not, 'halt' our progress
		$next_url = ( !isset( $data->pagination->next_url ) || isset( $messages['nexturl'] ) && $messages['nexturl'] == 'halt' ) ? '' : $data->pagination->next_url;

		// merge previous messages
		$messages = ( isset( $messages['messages'] ) ) ? array_merge( $prevmessages, $messages['messages'] ) : $prevmessages;

		// Remove our max quality filter
		remove_filter( 'wp_editor_set_quality', array( $this, 'max_quality' ) );
		remove_filter( 'jpeg_quality', array( $this, 'max_quality' ) );

		// return an array of messages and our "next" url
		if ( empty( $messages ) && empty( $prevmessages ) )
			return array(
				'message' => array( '<p>'. __( 'No new Instagram shots to import', 'dsgnwrks' ) .'</p>' ),
				'next_url' => $next_url,
			);

		return array(
			'message' => $messages,
			'next_url' => $next_url,
		);
	}

	/**
	 * Loops through instagram api data
	 */
	protected function pic_loop( $data = array() ) {

		// our individual user's settings
		$settings = &$this->settings;

		// if we have invalid data, bail here
		if ( !isset( $data->data ) || !is_array( $data->data ) )
			return array();

		// loop!
		foreach ( $data->data as $this->pic ) {

			// $this->pic is for other functions, $pic is for this function
			$pic = &$this->pic;

			// if user has a date filter set, check it
			if ( isset( $settings['date-filter'] ) && $settings['date-filter'] > $pic->created_time ) {
				// and stop if we've passed the date filter time
				$messages['nexturl'] = 'halt';
				break;
			}

			// if user has a tag filter set, check it
			if ( !empty( $settings['tag-filter'] ) ) {
				// get all tags saved for filtering
				$tags = explode( ', ', $settings['tag-filter'] );
				// init our var
				$in_title = false;
				// if we have tags...
				if ( $tags ) {
					// loop through them
					foreach ($tags as $tag) {
						// if we find one of them in the caption, we should import this one
						if ( strpos( $pic->caption->text, $tag ) ) $in_title = true;
					}
				}
				// if no tags are in the caption, move on to the next photo
				if ( !$in_title ) continue;
			}

			// get user's post-type setting or default to 'post'
			$pt = isset( $settings['post-type'] ) ? $settings['post-type'] : 'post';
			$alreadyInSystem = new WP_Query(
				array(
					'post_type' => $pt,
					'post_status' => 'any',
					'meta_query' => array(
						array(
							'key' => 'instagram_created_time',
							'value' => $pic->created_time
						)
					)
				)
			);
			// if the photo is already saved, move on
			if ( $alreadyInSystem->have_posts() )
				continue;

			// if we've made it this far, let's save our post
			$messages['messages'][] = $this->save_img_post();
		}

		// return our $messages array
		return !empty( $messages ) ? $messages : array();
	}

	/**
	 * Saves a WP post with our instagram photo data
	 */
	protected function save_img_post() {

		$settings = &$this->settings;
		$p = &$this->pic;

		// init our $import settings array var
		$import = &$this->import;

		global $user_ID;

		// in case we haven't gotten our settings yet. (unlikely)
		$settings = ( empty( $settings ) ) ? get_option( 'dsgnwrks_insta_options' ) : $settings;

		// check for a location saved
		$loc = ( isset( $p->location->name ) ) ? $p->location->name : '';

		// Check for a title, or use 'Untitled'
		$insta_title = !empty( $p->caption->text ) ? $p->caption->text : __( 'Untitled', 'dsgnwrks' );

		// Set post title to caption by default
		$import['post_title'] = $insta_title;

		// if our user's post-title option is saved
		if ( !empty( $settings['post-title'] ) ) {
			// check for insta-text conditionals
			$import['post_title'] = $this->conditional( 'insta-text', $settings['post-title'], $import['post_title'] );
			// check for insta-location conditionals
			$import['post_title'] = $this->conditional( 'insta-location', $import['post_title'], $loc );
			// Add the instagram filter name if requested
			$import['post_title'] = str_replace( '**insta-filter**', $p->filter, $import['post_title'] );
		}

		// get large image url (612x612)
		$imgurl = $p->images->standard_resolution->url;
		// url to photo on instagram
		$insta_url = esc_url( $p->link );
		// save photo as featured?
		$import['featured'] = ( isset( $settings['feat_image'] ) && $settings['feat_image'] == true ) ? true : false;
		// save instagram photo caption as post excerpt
		$import['post_excerpt'] = !empty( $p->caption->text ) ? $p->caption->text : '';

		// if our user's post-content option is NOT saved
		if ( empty( $settings['post_content'] ) ) {
			// we'll add some default content
			$content  = '<p><a href="'. $imgurl .'" target="_blank"><img src="'. $imgurl .'"/></a></p>'."\n";
			$content .= '<p>'. $import['post_excerpt'];
			if ( !empty( $loc ) )
				$content .= sprintf( __( ' (Taken with Instagram at %s)', 'dsgnwrks' ), $loc );
			$content .= '</p>'."\n";
			$content .= '<p>'. __( 'Instagram filter used:', 'dsgnwrks' ) .' '. $p->filter .'</p>'."\n";
			$content .= '<p><a href="'. $insta_url .'" target="_blank">'. __( 'View in Instagram', 'dsgnwrks' ) .' &rArr;</a></p>'."\n";
		}
		// if our user's post-content option is saved
		else {
			$content = $settings['post_content'];
			// Add the instagram photo url if requested
			$content = str_replace( '**insta-link**', $insta_url, $content );
			// check for insta-text conditionals
			$content = $this->conditional( 'insta-text', $content, $insta_title );
			// check for insta-location conditionals
			$content = $this->conditional( 'insta-location', $content, $loc );
			// Add the instagram filter name if requested
			$content = str_replace( '**insta-filter**', $p->filter, $content );
		}

		// post author, deafault to current user
		$import['post_author'] = isset( $settings['author'] ) ? $settings['author'] : $user_ID;
		$import['post_content'] = $content;
		// post date, default to photo's created time
		$import['post_date'] = date( 'Y-m-d H:i:s', $p->created_time );
		$import['post_date_gmt'] = $import['post_date'];
		// post status, default to 'draft'
		$import['post_status'] = isset( $settings['draft'] ) ? $settings['draft'] : 'draft';
		// post type, default to 'post'
		$import['post_type'] = isset( $settings['post-type'] ) ? $settings['post-type'] : 'post';

		// A filter so filter-savvy devs can modify the data before the post is created
		$import = apply_filters( 'dsgnwrks_instagram_pre_save', $import, $p, $settings );

		// Setup our new post's data
		$post = array(
		  'post_author' => $import['post_author'],
		  'post_content' => $import['post_content'],
		  'post_date' => $import['post_date'],
		  'post_date_gmt' => $import['post_date_gmt'],
		  'post_excerpt' => $import['post_excerpt'],
		  'post_status' => $import['post_status'],
		  'post_title' => $import['post_title'],
		  'post_type' => $import['post_type'],
		);
		// and insert our new post
		$new_post_id = wp_insert_post( $post, true );

		// grab our new post ID
		$import['post_id'] = $new_post_id;

		// Another filter to modify post after it's created.
		do_action( 'dsgnwrks_instagram_post_save', $new_post_id, $p );

		// loop through our taxonomies
		$taxs = get_taxonomies( array(
			'public' => true,
		), 'objects' );
		foreach ( $taxs as $tax ) {
			// only save post-formats on themes which support them
			if ( $tax->label == __( 'Format' ) && !current_theme_supports( 'post-formats' ) )
				continue;
			// get user saved taxonomy terms
			$settings[$tax->name] = !empty( $settings[$tax->name] ) ? esc_attr( $settings[$tax->name] ) : '';
			$taxonomies = explode( ', ', $settings[$tax->name] );
			// if user set taxonomy terms to be saved, save them now
			if ( !empty( $taxonomies ) )
				wp_set_object_terms( $new_post_id, $taxonomies, $tax->name );
		}

		// get instagram likes data
		$insta_likes_data = array( 'count' => $p->likes->count );
		if ( !empty( $p->likes->data ) ) {
			foreach ( $p->likes->data as $key => $user ) {
				$insta_likes_data['data'][$key] = $user;
			}
		}

		update_post_meta( $new_post_id, 'dsgnwrks_instagram_likes', $insta_likes_data );
		update_post_meta( $new_post_id, 'instagram_created_time', $p->created_time );
		update_post_meta( $new_post_id, 'dsgnwrks_instagram_id', $p->id );
		update_post_meta( $new_post_id, 'instagram_filter_used', $p->filter );
		update_post_meta( $new_post_id, 'instagram_location', $p->location );
		update_post_meta( $new_post_id, 'instagram_link', esc_url( $p->link ) );

		// our post is properly saved, now let's bring the image over to WordPress
		return $this->upload_img( $imgurl );
	}

	/**
	 * Sideloads an image to the currrent WordPress post
	 */
	protected function upload_img( $imgurl = '' ) {

		// get our import data
		$import = &$this->import;

		// bail here if we don't have an image url
		if ( empty( $imgurl ) )
			return $this->upload_error();

		if ( $this->doing_cron ) {
			require_once (ABSPATH.'/wp-admin/includes/file.php');
			require_once (ABSPATH.'/wp-admin/includes/media.php');
			require_once (ABSPATH.'/wp-admin/includes/image.php');
		}

		$tmp = download_url( $imgurl );

		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $imgurl, $matches);
		$file_array['name'] = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;

		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}

		$img_id = media_handle_sideload( $file_array, $import['post_id'], $import['post_title'] );

		if ( is_wp_error( $img_id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $this->upload_error( $imgurl );
		}

		if ( $import['featured'] )
			set_post_thumbnail( $import['post_id'], $img_id );

		// Replace URLs in post with uploaded image
		$thumburl = wp_get_attachment_url( $img_id );
		$imgurl = wp_get_attachment_thumb_url( $img_id );

		// init our var
		$content = &$import['post_content'];
		// Add the instagram image source if requested
		$content = str_replace( '**insta-image**', '<img src="'. $thumburl .'"/>', $content );
		// Add the instagram image url if requested
		$content = str_replace( '**insta-image-link**', $imgurl, $content );

		// Update the post with updated image URLs
		wp_update_post( array(
			'ID' => $import['post_id'],
			'post_content' => $content,
		) );

		// return a success message
		return '<p><strong><em>&ldquo;'. $import['post_title'] .'&rdquo; </em> '. __( 'imported and created successfully.', 'dsgnwrks' ) .'</strong></p>';
	}

	/**
	 * Returns an error message
	 */
	protected function upload_error( $imgurl = false ) {

		$import = &$this->import;

		if ( !$imgurl ) {
			$import['post_content'] = str_replace( '**insta-image**', __( 'image error', 'dsgnwrks' ), $import['post_content'] );
			$import['post_content'] = str_replace( '**insta-image-link**', __( 'image error', 'dsgnwrks' ), $import['post_content'] );
		} else {
			// Add the instagram image source if requested
			$content = str_replace( '**insta-image**', '<img src="'. $imgurl .'"/>', $content );
			// Add the instagram image url if requested
			$content = str_replace( '**insta-image-link**', $imgurl, $content );
		}

		// Update the post with updated image URLs or errors
		wp_update_post( array(
			'ID' => $import['post_id'],
			'post_content' => $import['post_content'],
		) );

		// return an image upload error message
		return '<p><strong><em>&ldquo;'. $import['post_title'] .'&rdquo; </em> '. __( 'created successfully but there was an error with the image upload.', 'dsgnwrks' ) .'</strong></p>';
	}

	/**
	 * Checks for conditionals, runs them, and removes the conditional markup
	 */
	protected function conditional( $tag, $content, $replace ) {

		$open = '[if-'.$tag.']';
		$close = '[/if-'.$tag.']';
		$tag = '**'.$tag.'**';

		// if we have conditional markup
		if ( ( $pos1 = strpos( $content, $open ) ) && ( $pos2 = strpos( $content, $close ) ) ) {

			if ( !empty( $replace ) ) {
				// replace tag with our photo data
				$content = str_replace( $tag, $replace, $content );
				// remove shortcode markup
				$content = str_replace( $open, '', $content );
				$content = str_replace( $close, '', $content );
			} else {
				// if no replace data is provided, just remove our shortcode markup
				$length = ( $pos2 + strlen( $close ) ) - $pos1;
				$content = substr_replace( $content, '', $pos1, $length );
			}

		}
		// Otherwise disregard conditional and just replace tag
		else {
			$content = str_replace( $tag, $replace, $content );
		}
		// return our modified data
		return $content;
	}

	/**
	 * Checks for query parameters and does subsequent redirects
	 */
	public function redirects() {

		// if we have an error or access token
		if ( isset( $_GET['error'] ) || isset( $_GET['access_token'] ) )  {

			$opts = get_option( 'dsgnwrks_insta_options' );
			$users = get_option( 'dsgnwrks_insta_users' );
			$users = ( !empty( $users ) ) ? $users : array();

			$notice = array(
				'notice' => false,
				'class' => 'updated',
			);

			if ( isset( $_GET['error'] ) || isset( $_GET['error_reason'] ) || isset( $_GET['error_description'] ) ) {
				$notice['class'] = 'error';
			} else {
				$notice['notice'] = 'success';

				// setup our user data and save it
				if ( isset( $_GET['username'] ) && !in_array( $_GET['username'], $users ) ) {
					$sanitized_user = sanitize_title( $_GET['username'] );
					$users[] = $sanitized_user;
					$opts[$sanitized_user]['access_token'] = $_GET['access_token'];
					$opts[$sanitized_user]['bio'] = isset( $_GET['bio'] ) ? $_GET['bio'] : '';
					$opts[$sanitized_user]['website'] = isset( $_GET['website'] ) ? $_GET['website'] : '';
					$opts[$sanitized_user]['profile_picture'] = isset( $_GET['profile_picture'] ) ? $_GET['profile_picture'] : '';
					$opts[$sanitized_user]['full_name'] = isset( $_GET['full_name'] ) ? $_GET['full_name'] : '';
					$opts[$sanitized_user]['id'] = isset( $_GET['id'] ) ? $_GET['id'] : '';
					$opts[$sanitized_user]['full_username'] = $_GET['username'];

					foreach ( $this->defaults as $key => $default ) {
						$opts[$sanitized_user][$key] = $default;
					}

					$opts['username'] = $sanitized_user;
					$opts['frequency'] = 'daily';

					update_option( 'dsgnwrks_insta_users', $users );
					update_option( 'dsgnwrks_insta_options', $opts );
					delete_option( 'dsgnwrks_insta_registration' );
				}

			}
			// So notice isn't persistent past 60 seconds
			set_transient( 'instagram_notification', true, 60 );
			// redirect with notices
			wp_redirect( add_query_arg( 'query_arg', 'updated', $this->plugin_page ), 307 );
			exit;
		}

		if ( !isset( $_GET['delete-insta-user'] ) )
			return;

		// if we're requesting to delete a user

		// delete the user
		$users = get_option( 'dsgnwrks_insta_users' );
		foreach ( $users as $key => $user ) {
			if ( $user == urldecode( $_GET['delete-insta-user'] ) ) $delete = $key;
		}
		unset( $users[$delete] );
		update_option( 'dsgnwrks_insta_users', $users );

		// delete the user's data
		$opts = get_option( 'dsgnwrks_insta_options' );
		unset( $opts[urldecode( $_GET['delete-insta-user'] )] );
		if ( isset( $opts['username'] ) && $opts['username'] == sanitize_title( urldecode( $_GET['delete-insta-user'] ) ) )
		unset( $opts['username'] );
		update_option( 'dsgnwrks_insta_options', $opts );

		// redirect to remove the query arg (to keep from repeat-deleting)
		wp_redirect( remove_query_arg( 'delete-insta-user' ), 307 );
		exit;
	}

	/**
	 * Helper function to sanitized variables using whitelisted filters and set a default
	 */
	protected function filter( $opt = '', $filter = '', $else = '' ) {
		// if $opt is empty, return our default if set, or nothing
		if ( empty( $opt ) )
			return $else;

		// do our filters
		switch ( $filter ) {
			case 'absint':
				return absint( $opt );
			case 'esc_textarea':
				return esc_textarea( $opt );
			case 'wp_kses_post':
				return wp_kses_post( $opt );
			case 'bool' :
				return !empty( $opt );
			default:
				return esc_attr( $opt );
		}
	}

	/**
	 * Sets maximum quality for WP image saving
	 */
	public function max_quality($arg) {
		return (int) 100;
	}

	/**
	 * @TODO When deleteing a post, importer should not import them again
	 */
	public function save_id_on_delete( $post_id ) {
		// get_post_meta( $post_id, 'instagram_created_time', true );
	}

	/**
	 * Form element for an "add a user" admin section with a user authentication button
	 */
	protected function settings_user_form( $users = array(), $message = '' ) {

		$message = $message ? $message : '<p>'. __( 'Click to be taken to Instagram\'s site to securely authorize this plugin for use with your account.', 'dsgnwrks' ) .'</p><p><em>'. __( '(If you have already authorized an account, You will first be logged out of Instagram.)', 'dsgnwrks' ) .'</em></p>'; ?>
		<form class="instagram-importer user-authenticate" method="post" action="options.php">
			<?php
			settings_fields('dsgnwrks_instagram_importer_users');
			echo $message;
			$class = !empty( $users ) ? 'logout' : '';
			?>
			<p class="submit">
				<input type="submit" name="save" class="button-primary authenticate <?php echo $class; ?>" value="<?php _e( 'Secure Authentication with Instagram', 'dsgnwrks' ); ?>" />
			</p>
		</form>
		<?php
	}

	/**
	 * Form element for setting universal plugin options (auto-import frequency, debug mode)
	 */
	protected function universal_options_form() {
		?>
		<table class="form-table">
			<tbody>
				<tr valign="top" class="info">
					<th colspan="2">
						<h3><img class="alignleft" src="<?php echo plugins_url( 'images/merge.png', __FILE__ ); ?>" width="83" height="66"><?php _e( 'Universal Import Options', 'dsgnwrks' ); ?></h3>
						<p><?php _e( 'Please select the general import options below.', 'dsgnwrks' ); ?></p>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Set Auto-import Frequency:', 'dsgnwrks' ); ?></strong></th>
					<td>
						<select name="dsgnwrks_insta_options[frequency]">
							<option value="never" <?php echo selected( $this->opts['frequency'], 'never' ); ?>><?php _e( 'Manual', 'dsgnwrks' ); ?></option>
							<?php
							foreach ( $this->schedules as $key => $value ) {
								echo '<option value="'. $key .'"'. selected( $this->opts['frequency'], $key, false ) .'>'. $value['display'] .'</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<?php do_action( 'dsgnwrks_instagram_univeral_options', $this->opts ); ?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button-primary save" value="<?php _e( 'Save', 'dsgnwrks' ) ?>" />
		</p>
		<?php
	}

	/**
	 * a link to instagram import admin page with user pre-selected
	 */
	protected function instimport_link( $id ) {
		return add_query_arg( array( 'page' => $this->plugin_id, 'instaimport' => urlencode( $id ) ), admin_url( $GLOBALS['pagenow'] ) );
	}

	/**
	 * Set wp_editor default to 'html' on our admin page
	 */
	public function html_default( $default ) {
		if ( get_current_screen()->id == 'tools_page_dsgnwrks-instagram-importer-settings' )
			$default = 'html';
		return $default;
	}

	/**
	 * Checks if user has enabled Debug Mode
	 * Requires DsgnWrks Instagram Debug plugin.
	 */
	public function debugEnabled() {
		return isset( $this->opts['debugmode'] ) && $this->opts['debugmode'] == 'on';
	}

	/**
	 * Sets option stating user just sent an import debug (only want to send once!)
	 * Requires DsgnWrks Instagram Debug plugin.
	 */
	public function importDebugSet() {
		if ( !$this->debugEnabled() )
			return;
		update_option( 'dsgnwrks-import-debug-sent', 'sent' );
	}

	/**
	 * Checks if user sent an import debug already (only want to send once!)
	 * Requires DsgnWrks Instagram Debug plugin.
	 */
	public function importDebugCheck() {
		if ( !$this->debugEnabled() )
			return true;
		return get_option( 'dsgnwrks-import-debug-sent' ) ? true : false;
	}

	/**
	 * Sends me a debug report if Debug Mode is enabled
	 * Requires DsgnWrks Instagram Debug plugin.
	 */
	public function debugsend( $line, $title = false, $data = false ) {
		if ( !$this->debugEnabled() )
			return;
		// default $data is options and users
		$data = !$data ? print_r( array( 'opts' => $this->opts, 'users' => $this->users ), true ) : print_r( $data, true );
		// default title
		$title = !$title ? 'no $opts[$id] - $opts & $users' : esc_attr( $title );
		wp_mail( 'justin@dsgnwrks.pro', 'Instagram Debug - '. $title .' - line '. $line, $data );
	}

}

// init our class
$DsgnWrksInstagram = new DsgnWrksInstagram;
