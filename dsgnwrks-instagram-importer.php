<?php
/*
Plugin Name: DsgnWrks Instagram Importer
Plugin URI: http://dsgnwrks.pro/instagram-importer/
Description: Allows you to backup your instagram photos while allowing you to have a site to display your instagram archive.
Author URI: http://dsgnwrks.pro
Author: DsgnWrks
Donate link: http://dsgnwrks.pro/give/
Version: 1.0
*/

define( 'DSGNWRKSINSTA_ID', 'dsgnwrks-instagram-importer-settings');


add_action('admin_init','dsgnwrks_instagram_init');
function dsgnwrks_instagram_init() {

	if ( isset( $_GET['instaimport'] ) ) {
		set_transient( $_GET['instaimport'] .'-instaimportdone', date_i18n( 'l F jS, Y @ h:i:s A', strtotime( current_time('mysql') ) ), 14400 );
	}

	// delete_option( 'dsgnwrks_insta_options' );
	register_setting(
		'dsgnwrks_instagram_importer_users',
		'dsgnwrks_insta_registration',
		'dsgnwrks_instagram_users_validate'
	);
	register_setting(
		'dsgnwrks_instagram_importer_settings',
		'dsgnwrks_insta_options',
		'dsgnwrks_instagram_settings_validate'
	);

}

function dsgnwrks_instagram_users_validate( $opts ) {

	if ( !empty( $opts['user'] ) && !empty( $opts['pw'] ) ) {

		$response = dsgnwrks_insta_authenticate(
			$opts['user'],
			$opts['pw'],
			false
		);

		$opts['badauth'] = $response['badauth'];
		$opts[$i.'noauth'] = $response['noauth'];
	}
	return $opts;
}

function dsgnwrks_instagram_settings_validate( $opts ) {

	foreach ( $opts as $user => $useropts ) {
		foreach ( $useropts as $key => $opt ) {

			if ( $key === 'date-filter' ) {
				$opts[$user][$key] = dsgnwrks_filter( $opt, '', '0' );
			} elseif ( $key === 'pw' ) {
				continue;
			} elseif ( $key === 'post-type' ) {
				$opts[$user][$key] = dsgnwrks_filter( $opt, '', 'post' );
			} elseif ( $key === 'draft' ) {
				$opts[$user][$key] = dsgnwrks_filter( $opt, '', 'draft' );
			} elseif ( $key === 'yy' || $key === 'mm' || $key === 'dd' ) {
				$opts[$user][$key] = dsgnwrks_filter( $opt, 'absint', '' );
			} else {
				$opts[$user][$key] = dsgnwrks_filter( $opt );
			}

		}
	}

	return $opts;
}

add_action('admin_menu', 'dsgnwrks_instagram_settings');
function dsgnwrks_instagram_settings() {

	$plugin_page = add_submenu_page( 'tools.php', 'DsgnWrks Instagram Import Settings', 'Instagram Importer', 'manage_options', DSGNWRKSINSTA_ID, 'dsgnwrks_instagram_importer_settings' );
	add_action('admin_print_styles-' . $plugin_page, 'dsgnwrks_instagram_importer_styles');
	add_action('admin_print_scripts-' . $plugin_page, 'dsgnwrks_instagram_importer_scripts');
	add_action( 'admin_head-'. $plugin_page, 'dsgnwrks_instagram_fire_importer' );
}

function dsgnwrks_instagram_importer_settings() { require_once('settings.php'); }

function dsgnwrks_instagram_importer_styles() {
	wp_enqueue_style( 'dsgnwrks-instagram-importer-admin', plugins_url( 'css/admin.css', __FILE__ ) );
}

function dsgnwrks_instagram_importer_scripts() {
	wp_enqueue_script( 'dsgnwrks-instagram-importer-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ) );

	$args = array(
	  'public'   => true,
	);
	$cpts = get_post_types( $args );
	foreach ($cpts as $key => $cpt) {
		$taxes = get_object_taxonomies( $cpt );
		if ( !empty( $taxes ) ) $data['cpts'][$cpt][] = $taxes;
	}

	if ( !empty( $data ) ) wp_localize_script( 'dsgnwrks-instagram-importer-admin', 'dwinstagram', $data );

}

function dsgnwrks_instagram_fire_importer() {

	if ( isset( $_GET['instaimport'] ) ) {
		add_action('all_admin_notices','dsgnwrks_instagram_import');
	}
}

function dsgnwrks_instagram_import() {

	$settings = get_option( 'dsgnwrks_insta_options' );
	$id = $_GET['instaimport'];

	if ( !wp_check_password( $_POST['pwcheck'], $settings[$id]['pw'] ) ) {
		echo '<div id="message" class="error"><p>That is the incorrect password</p></div>';
		return;
	}

	$response = dsgnwrks_insta_authenticate( $id, $_POST['pwcheck'] );

	if ( empty( $response['response'] ) ) {
		echo '<div id="message" class="error"><p>Couldn\'t find an instagram feed. Please check your username and password.</p></div>';
		$settings[$id]['noauth'] = true;
		update_option( 'dsgnwrks_insta_options', $settings );
		return;
	} else {

		$body = apply_filters( 'dsgnwrks_instagram_api', $response['response'] );
	}

	if ( isset( $body->user->id ) && isset( $body->access_token ) ) {
		echo '<div id="message" class="updated">';

		$messages = dsgnwrks_import_messages( 'https://api.instagram.com/v1/users/'. $body->user->id .'/media/recent?access_token='. $body->access_token .'&count=80', $settings[$id] );

		while ( !empty( $messages['next_url'] ) ) {
			$messages = dsgnwrks_import_messages( $messages['next_url'], $settings[$id], $messages['message'] );
		}

		foreach ( $messages['message'] as $key => $message ) {
			echo $message;
		}
		echo '</div>';

	}

}

function dsgnwrks_import_messages( $api_url, $settings, $prevmessages = array() ) {

	$api = wp_remote_retrieve_body( wp_remote_get( $api_url ) );
	$data = json_decode( $api );

	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	set_time_limit(300);

	add_filter( 'jpeg_quality', 'dsgnwrks_max_quality' );
	$messages = dsgnwrks_pic_loop( $data, $settings );

	$next_url = ( !isset( $data->pagination->next_url ) || $messages['nexturl'] == 'halt' ) ? '' : $data->pagination->next_url;

	$messages = ( isset( $messages['messages'] ) ) ? array_merge( $prevmessages, $messages['messages'] ) : $prevmessages;

	remove_filter( 'jpeg_quality', 'dsgnwrks_max_quality' );
	if ( empty( $messages ) && empty( $prevmessages ) ) {
		return array(
			'message' => array( '<p>No new Instagram shots to import</p>' ),
			'next_url' => $next_url,
		);
	} else {
		return array(
			'message' => $messages,
			'next_url' => $next_url,
		);
	}
}

function dsgnwrks_pic_loop( $data = array(), $settings = array() ) {

	foreach ($data->data as $pics) {

		if ( $settings['date-filter'] > $pics->created_time ) {
			$messages['nexturl'] = 'halt';
			break;
		}

		if ( !empty( $settings['tag-filter'] ) ) {
			$tags = explode( ', ', $settings['tag-filter'] );
			$in_title = false;
			if ( $tags ) {
			    foreach ($tags as $tag) {
			        if ( strpos( $pics->caption->text, $tag ) ) $in_title = true;
			    }
			}

			if ( !$in_title ) continue;
		}


		$alreadyInSystem = new WP_Query(
			array(
				'post_type' => $settings['post-type'],
				'meta_query' => array(
					array(
						'key' => 'instagram_created_time',
						'value' => $pics->created_time
					)
				)
			)
		);
		if ( $alreadyInSystem->have_posts() ) {
			continue;
		}

		$messages['messages'][] = jts_instagram_img( $pics, $settings );
	}
	return $messages;
}

function jts_instagram_img( $pics, $settings = array(), $tags='' ) {

	global $user_ID;

	$settings = ( empty( $settings ) ) ? get_option( 'dsgnwrks_insta_options' ) : $settings;

	$loc = ( isset( $pics->location->name ) ) ? $pics->location->name : null;

	if ( $loc ) $loc = ' at '. $loc;
	$title = dsgnwrks_wp_trim_words( $pics->caption->text, 12 );
	if ( $tags ) {
		$tags = '#'. $tags;
		$title = str_replace( $tags, '', $title );
	}
	$title = ($title) ? $title : 'Untitled';
	$imgurl = $pics->images->standard_resolution->url;

	$excerpt = $pics->caption->text;
	if ( $tags ) {
		$tags = '#'. $tags;
		$excerpt = str_replace( $tags, '', $excerpt );
	}
	$excerpt .= ' (Taken with Instagram'. $loc .')';

	$content = '';
	if ( $settings['image'] == 'content' || $settings['image'] == 'both' )
		$content .= '<a href="'. $imgurl .'" ><img src="'. $imgurl .'"/></a>';
	$content .= '<p>'. $excerpt .'</p>';
	$content .= '<p>Instagram filter used: '. $pics->filter .'</p>';
	$content .= '<p><a href="'. esc_url( $pics->link ) .'" target="_blank">View in Instagram &rArr;</a></p>';

	if ( !$settings['draft'] ) $settings['draft'] = 'draft';
	if ( !$settings['author'] ) $settings['author'] = $user_ID;

	$post = array(
	  'post_author' => $settings['author'],
	  'post_content' => $content,
	  'post_date' => date( 'Y-m-d H:i:s', $pics->created_time ),
	  'post_date_gmt' => date( 'Y-m-d H:i:s', $pics->created_time ),
	  'post_excerpt' => $excerpt,
	  'post_status' => $settings['draft'],
	  'post_title' => $title,
	  'post_type' => $settings['post-type'],
	);
	$new_post_id = wp_insert_post( $post, true );

	apply_filters( 'dsgnwrks_instagram_post_save', $new_post_id, $pics );

	$args = array(
		'public' => true,
		);
	$taxs = get_taxonomies( $args, 'objects' );

	foreach ( $taxs as $key => $tax ) {

		if ( $tax->label == 'Format' && !current_theme_supports( 'post-formats' ) ) continue;

		$settings[$tax->name] = !empty( $settings[$tax->name] ) ? esc_attr( $settings[$tax->name] ) : '';

		$taxonomies = explode( ', ', $settings[$tax->name] );

		if ( !empty( $taxonomies ) )
		wp_set_object_terms( $new_post_id, $taxonomies, $tax->name );

	}

	$insta_data = array( 'count' => $pics->likes->count );
	if ( !empty( $pics->likes->data ) ) {
		foreach ( $pics->likes->data as $key => $user ) {
			$insta_data['data'][$key] = $user;
		}
	}

	update_post_meta( $new_post_id, 'dsgnwrks_instagram_likes', $insta_data );
	update_post_meta( $new_post_id, 'instagram_created_time', $pics->created_time );
	update_post_meta( $new_post_id, 'dsgnwrks_instagram_id', $pics->id );
	update_post_meta( $new_post_id, 'instagram_filter_used', $pics->filter );
	update_post_meta( $new_post_id, 'instagram_location', $pics->location );
	update_post_meta( $new_post_id, 'instagram_link', esc_url( $pics->link ) );

	return dsgnwrks_instagram_upload_img( $imgurl, $new_post_id, $title );
}

function dsgnwrks_instagram_upload_img( $imgurl='', $post_id='', $title='' ) {

	if ( !empty( $imgurl ) ) {
		$tmp = download_url( $imgurl );

		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $imgurl, $matches);
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $tmp;

		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
		}

		$img_id = media_handle_sideload($file_array, $post_id, $title );

		if ( is_wp_error($img_id) ) {
			@unlink($file_array['tmp_name']);
			return $img_id;
		}

		set_post_thumbnail( $post_id, $img_id );
	}

	return '<p><strong><em>&ldquo;'. $title .'&rdquo; </em> imported and created successfully.</strong></p>';
}

add_action('current_screen','redirect_on_deleteuser');
function redirect_on_deleteuser() {

	if ( isset( $_GET['deleteuser'] ) ) {
		$users = get_option( 'dsgnwrks_insta_users' );
		foreach ( $users as $key => $user ) {
			if ( $user == $_GET['deleteuser'] ) $delete = $key;
		}
		unset( $users[$delete] );
		update_option( 'dsgnwrks_insta_users', $users );

		$opts = get_option( 'dsgnwrks_insta_options' );
		unset( $opts[$_GET['deleteuser']] );
		update_option( 'dsgnwrks_insta_options', $opts );

		wp_redirect( remove_query_arg( 'deleteuser' ), 307 );
		exit;
	}
}

function dsgnwrks_wp_trim_words( $text, $num_words = 55, $more = null ) {
	if ( null === $more )
		$more = __( '...' );
	$original_text = $text;
	$text = wp_strip_all_tags( $text );
	$words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
	if ( count( $words_array ) > $num_words ) {
		array_pop( $words_array );
		$text = implode( ' ', $words_array );
		$text = $text . $more;
	} else {
		$text = implode( ' ', $words_array );
	}
	return apply_filters( 'wp_trim_words', $text, $num_words, $more, $original_text );
}

function dsgnwrks_filter( $opt = '', $filter = '', $else = '' ) {

	if ( empty( $opt ) ) return $else;

	if ( $filter == 'absint' ) return absint( $opt );
	else return esc_attr( $opt );
}

function dsgnwrks_insta_authenticate( $user, $pw, $return = true, $url = 'https://api.instagram.com/oauth/access_token', $get_or_post = 'post' ) {

	$body = array(
		'body' => array(
			'username' => $user,
			'password' => $pw,
			'grant_type' => 'password',
			'client_id' => '90740ae6e5bd4676ad62563e9a31ac75',
			'client_secret' => '21a67881c8af4abeacbd826cf9bec45e'
		)
	);
	if ( $get_or_post == 'post' )
		$response = (array)wp_remote_post( $url, $body );
	elseif ( $get_or_post == 'get' )
		$response = (array)wp_remote_get( $url, $body );

	$code = ( isset( $response['response']['code'] ) ) ? $response['response']['code'] : '';

	if ( is_wp_error( $response ) ) {
		$response = null;
		$badauth = 'error';
		$noauth = true;
	} elseif ( isset( $code ) && $code < 400 && $code >= 200 ) {
		if ( $return == false ) {
			$response = null;
		} else {
			$body = wp_remote_retrieve_body( $response );
			$response = json_decode( $body );
		}
		$noauth = '';
		$badauth = 'good';
	} else {
		$response = null;
		$badauth = 'error';
		$noauth = true;
	}

	return array(
		'response' => $response,
		'badauth' => $badauth,
		'noauth' => $noauth,
	);

}

function dsgnwrks_max_quality($arg) {
	return (int) 100;
}