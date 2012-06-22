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

register_activation_hook( __FILE__, 'dsgnwrks_instagram_activate' );
function dsgnwrks_instagram_activate() {
global $user_ID;

    // delete_option( 'dsgnwrks_insta_options' );
    $settings = array(
        'instagram-username'=>'',
        'instagram-userpw'=>'',
        'instagram-noauth' => '',
        'instagram-badauth' => '',
        'instagram-remove-date-filter' => '',
        'instagram-remove-tag-filter'=> '',
        'instagram-image' => 'feat-image',
        'instagram-post-type' => 'post',
        'instagram-draft' => 'draft',
        'instagram-author' => $user_ID,
        'instagram-tag-filter' => '',
        'instagram-yy' => '',
        'instagram-mm' => '',
        'instagram-dd' => '',
        );
    if ( !get_option('dsgnwrks_insta_options')){
        add_option('dsgnwrks_insta_options' , $settings);
    }
}

add_action('admin_init','dsgnwrks_instagram_init');
function dsgnwrks_instagram_init() {

    if ( isset( $_GET['instaimport'] ) ) {
        set_transient( 'instaimportdone', date_i18n( 'l F jS, Y @ h:i:s A', strtotime( current_time('mysql') ) ), 14400 );
    }

    // delete_option( 'dsgnwrks_insta_options' );
    register_setting( 'dsgnwrks_instagram_importer_settings', 'dsgnwrks_insta_options', 'dsgnwrks_instagram_settings_validate' );

    $settings = get_option( 'dsgnwrks_insta_options' );
    if ( !$settings['instagram-username'] || !$settings['instagram-userpw'] ) {
        $settings['instagram-noauth'] = true;
        update_option( 'dsgnwrks_insta_options', $settings );
    }
}

function dsgnwrks_instagram_settings_validate( $settings ) {

    if ( !empty( $settings['instagram-username'] ) ) {
        $settings['instagram-username'] = esc_attr( $settings['instagram-username'] );
        $settings['instagram-badauth'] = 'check';
    } else {
        $settings['instagram-username'] = '';
        $settings['instagram-badauth'] = '';
    }
    if ( !empty( $settings['instagram-userpw'] ) ) {
        $settings['instagram-userpw'] = esc_attr( $settings['instagram-userpw'] );
        $settings['instagram-badauth'] = 'check';
    } else {
        $settings['instagram-userpw'] = '';
        $settings['instagram-badauth'] = '';
    }

    if ( !empty( $settings['instagram-badauth'] ) && $settings['instagram-badauth'] == 'check' ) {
        $response = (array)wp_remote_post(
            'https://api.instagram.com/oauth/access_token',
            array(
                'body' => array(
                    'username' => $settings['instagram-username'],
                    'password' => $settings['instagram-userpw'],
                    'grant_type' => 'password',
                    'client_id' => '90740ae6e5bd4676ad62563e9a31ac75',
                    'client_secret' => '21a67881c8af4abeacbd826cf9bec45e'
                )
            )
        );
        if ( isset( $response['response']['code'] ) ) $code = $response['response']['code'];

        if ( is_wp_error( $response ) ) {
            $response = null;
            $settings['instagram-badauth'] = 'error';
            $settings['instagram-noauth'] = true;
        } elseif ( isset( $code ) && $code < 400 && $code >= 200 ) {
            $settings['instagram-noauth'] = '';
            $settings['instagram-badauth'] = 'good';
        } else {
            $response = null;
            $settings['instagram-badauth'] = 'error';
            $settings['instagram-noauth'] = true;
        }

    }

    $settings['instagram-remove-date-filter'] = !empty( $settings['instagram-remove-date-filter'] ) ? esc_attr( $settings['instagram-remove-date-filter'] ) : '';
    $settings['instagram-date-filter'] = !empty( $settings['instagram-date-filter'] ) ? esc_attr( $settings['instagram-date-filter'] ) : '0';
    $settings['instagram-tag-filter'] = !empty( $settings['instagram-tag-filter'] ) ? esc_attr( $settings['instagram-tag-filter'] ) : '';
    $settings['instagram-remove-tag-filter'] = !empty( $settings['instagram-remove-tag-filter'] ) ? esc_attr( $settings['instagram-remove-tag-filter'] ) : '';
    $settings['instagram-image'] = !empty( $settings['instagram-image'] ) ? esc_attr( $settings['instagram-image'] ) : '';
    $settings['instagram-draft'] = !empty( $settings['instagram-draft'] ) ? esc_attr( $settings['instagram-draft'] ) : '';
    $settings['instagram-author'] = !empty( $settings['instagram-author'] ) ? esc_attr( $settings['instagram-author'] ) : '';
    $settings['instagram-post-type'] = !empty( $settings['instagram-post-type'] ) ? esc_attr( $settings['instagram-post-type'] ) : 'post';
    $settings['instagram-yy'] = !empty( $settings['instagram-yy'] ) ? absint( $settings['instagram-yy'] ) : '';
    $settings['instagram-mm'] = !empty( $settings['instagram-mm'] ) ? absint( $settings['instagram-mm'] ) : '';
    $settings['instagram-dd'] = !empty( $settings['instagram-dd'] ) ? absint( $settings['instagram-dd'] ) : '';
    if ( current_theme_supports( 'post-formats' ) && post_type_supports( $settings['instagram-post-type'], 'post-formats' ) ) {
        $settings['instagram-post_format'] = !empty( $settings['instagram-post_format'] ) ? esc_attr( $settings['instagram-post_format'] ) : '';
    }

    return $settings;
}

add_action('admin_menu', 'dsgnwrks_instagram_settings');
function dsgnwrks_instagram_settings() {
    $plugin_page = add_submenu_page( 'tools.php', 'DsgnWrks Instagram Import Settings', 'Instagram Importer', 'manage_options', 'dsgnwrks-instagram-importer-settings', 'dsgnwrks_instagram_importer_settings' );
    add_action('admin_print_styles-' . $plugin_page, 'dsgnwrks_instagram_importer_styles');
    add_action( 'admin_head-'. $plugin_page, 'dsgnwrks_instagram_fire_importer' );

}

function dsgnwrks_instagram_importer_settings() { require_once('settings.php'); }

function dsgnwrks_instagram_importer_styles() {
    wp_enqueue_style('dsgnwrks-instagram-importer-admin', plugins_url('css/admin.css', __FILE__));
}

function dsgnwrks_instagram_fire_importer() {

    $settings = get_option( 'dsgnwrks_insta_options' );

    if ( !empty( $settings['instagram-username'] ) && !empty( $settings['instagram-userpw'] ) && isset( $_GET['instaimport'] ) && $_GET['instaimport'] == 'true' ) {
        add_action('all_admin_notices','dsgnwrks_instagram_import');
    }
}

function dsgnwrks_instagram_import() {

    if ( $_GET['instaimport'] == 'true' ) {

        $settings = get_option( 'dsgnwrks_insta_options' );

        $response = (array)wp_remote_post(
            'https://api.instagram.com/oauth/access_token',
            array(
                'body' => array(
                    'username' => $settings['instagram-username'],
                    'password' => $settings['instagram-userpw'],
                    'grant_type' => 'password',
                    'client_id' => '90740ae6e5bd4676ad62563e9a31ac75',
                    'client_secret' => '21a67881c8af4abeacbd826cf9bec45e'
                )
            )
        );
        $body = null;
        // echo '<pre>'. htmlentities( print_r( $response, true ) ) .'</pre>';
        if ( isset( $response['response']['code'] ) ) $code = $response['response']['code'];

        if ( is_wp_error( $response ) ) {
           $response = null;
           echo '<div id="message" class="error"><p>Couldn\'t find an instagram feed. Please check your username and password.</p></div>';
           $settings['instagram-noauth'] = true;
           update_option( 'dsgnwrks_insta_options', $settings );

        } elseif ( isset( $code ) && $code < 400 && $code >= 200 ) {
            $settings['instagram-noauth'] = '';
            update_option( 'dsgnwrks_insta_options', $settings );

            $body = json_decode($response['body']);
            // echo $body->access_token .'<br/>';
            // echo $body->user->id;

            // echo '<pre>'. print_r($body, true) .'</pre>';

        } else {
           $response = null;
           echo '<div id="message" class="error"><p>Couldn\'t find an instagram feed. Please check your username and password.</p></div>';

           $settings['instagram-noauth'] = true;
           update_option( 'dsgnwrks_insta_options', $settings );

        }

        if ( isset( $body->user->id ) && isset( $body->access_token ) ) {
            // echo '<pre style="background: #fff; padding: 100px; font-size: 14px; font-family: arial; font-weight: normal; color: #000;">';
            echo '<div id="message" class="updated">';

            $messages = dsgnwrks_import_messages( 'https://api.instagram.com/v1/users/'. $body->user->id .'/media/recent?access_token='. $body->access_token .'&count=80', $settings );

            while ( !empty( $messages['next_url'] ) ) {
                $messages = dsgnwrks_import_messages( $messages['next_url'], $settings, $messages['message'] );
            }

            super_var_dump($messages);

            super_var_dump( $settings['instagram-date-filter'] );
            // die();

            // foreach ( $messages['message'] as $message ) {
            //     echo $message;
            // }
            echo '</div>';

        }

    }
}

function dsgnwrks_import_messages( $api_url = '', $settings = array(), $prevmessages = array() ) {

    $api = wp_remote_retrieve_body( wp_remote_get( $api_url ) );
    $data = json_decode( $api );

    // echo '<pre>'. print_r($data->pagination->next_url, true) .'</pre>';
    // die();

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    set_time_limit(300);

    // $tags = explode( ', ', $settings['tag-filter'] );
    // print_r($tags);

    // if ( $tags ) {
    //     foreach ($tags as $tag) {
    //         $tag = '#'. $tag;
    //         echo $tag;
    //         $title = str_replace( $tag, '', $title );
    //         echo $title;
    //     }
    // }

    // $test = array( 'bob', 'dog', 'cat' );
    // print_r($test);


    // if (in_array(array('cat', 'mouse'), $test)) { echo 'true'; }

    // $tags = check_array_with_array( $tags, $test );
    // print_r( $tags );

    $next_url = isset( $data->pagination->next_url ) ? $data->pagination->next_url : '';
    add_filter( 'jpeg_quality', 'dsgnwrks_max_quality' );
    $messages = dsgnwrks_pic_loop( $data, $settings );
    $messages = ( !empty( $prevmessages ) ) ? array_merge( $prevmessages, $messages ) : $messages;

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
    $messages = array();
    foreach ($data->data as $pics) {

        if ( $settings['instagram-date-filter'] > $pics->created_time ) {
            break;
        }

        $alreadyInSystem = new WP_Query(
            array(
                'post_type' => $settings['instagram-post-type'],
                'meta_query' =>
                    array(
                        array(
                            'key' => 'instagram_created_time',
                            'value' => $pics->created_time
                            )
                        )

                )
        );
        if( $alreadyInSystem->have_posts() ) {
            continue;
        } else {

            // // if ( $pics->created_time == '1319928937' || $pics->created_time == '1319928334' || $pics->created_time == '1319541753' ) {
            // if ( $pics->created_time == '1314374559' ) {

            // if ( $tags ) {
            //     if ( in_array( $tags, $pics->tags ) ) {
            //         jts_instagram_img( $pics, $tags, $settings );
            //     }
            // } else {
                // $messages[] = jts_instagram_img( $pics, $settings );

                $messages[dsgnwrks_wp_trim_words( $pics->caption->text, 12 )] = $pics;
                // echo '<a href="'.  $pics->link .'" target="_blank"><img width="100px" src="'. $pics->images->thumbnail->url .'"/></a>';
                // echo '<pre>'. print_r($pics, true) .'</pre>';

            // }

        }
    }
    return $messages;
}

// function check_array_with_array( $needlesarray='', $haystack='' ) {
//     if ( $needlesarray ) {
//         $result = '';
//         foreach ($needlesarray as $needle) {
//             if ( in_array( $needle, $haystack ) ) {
//                 $result[] = $needle;
//             }
//         }
//     }
//     return $result;
// }

function jts_instagram_img( $pics, $tags='', $settings=array() ) {

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
    if ( $settings['instagram-image'] == 'content' || $settings['instagram-image'] == 'both' )
        $content .= '<a href="'. $imgurl .'" ><img src="'. $imgurl .'"/></a>';
    $content .= '<p>'. $excerpt .'</p>';
    $content .= '<p>Instagram filter used: '. $pics->filter .'</p>';
    $content .= '<p><a href="'. esc_url( $pics->link ) .'" target="_blank">View in Instagram &rArr;</a></p>';

    if ( !$settings['instagram-draft'] ) $settings['instagram-draft'] = 'draft';
    if ( !$settings['instagram-author'] ) $settings['instagram-author'] = $user_ID;
    // $cats = explode( ', ', $settings['instagram-categories'] );
    // $post_tags = explode( ', ', $settings['instagram-add-tags'] );

    $post = array(
      // 'post_category' => array('Square'),
      'post_author' => $settings['instagram-author'],
      'post_content' => $content,
      'post_date' => date( 'Y-m-d H:i:s', $pics->created_time ),
      'post_date_gmt' => date( 'Y-m-d H:i:s', $pics->created_time ),
      'post_excerpt' => $excerpt,
      'post_status' => $settings['instagram-draft'],
      'post_title' => $title,
      // 'post_title' => 'This is a test',
      'post_type' => $settings['instagram-post-type'],
      // 'tags_input' => 'instagram',
    );
    $new_post_id = wp_insert_post( $post, true );

    apply_filters( 'dsgnwrks_instagram_post_save', $new_post_id, $pics );

    $args = array(
        'public' => true,
        );
    $taxs = get_taxonomies( $args, 'objects' );

    foreach ( $taxs as $key => $tax ) {

        if ( $tax->label == 'Format' && !current_theme_supports( 'post-formats' ) ) continue;

        $settings['instagram-'. $tax->name] = !empty( $settings['instagram-'. $tax->name] ) ? esc_attr( $settings['instagram-'. $tax->name] ) : '';

        $taxonomies = explode( ', ', $settings['instagram-'. $tax->name] );

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


    // wp_set_object_terms( $new_post_id, $cats, 'category' );
    // wp_set_post_terms( $new_post_id, $post_tags, 'post_tag' );
    update_post_meta( $new_post_id, 'instagram_created_time', $pics->created_time );
    update_post_meta( $new_post_id, 'dsgnwrks_instagram_id', $pics->id );
    update_post_meta( $new_post_id, 'instagram_filter_used', $pics->filter );
    update_post_meta( $new_post_id, 'instagram_location', $pics->location );
    update_post_meta( $new_post_id, 'instagram_link', esc_url( $pics->link ) );

    return dsgnwrks_instagram_upload_img( $imgurl, $new_post_id, $title );

}

function dsgnwrks_instagram_upload_img( $imgurl='', $post_id='', $title='' ) {

    if ( !empty( $imgurl ) ) {
        // Download file to temp location
        $tmp = download_url( $imgurl );

        // Set variables for storage
        // fix file filename for query strings
        preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $imgurl, $matches);
        $file_array['name'] = basename($matches[0]);
        $file_array['tmp_name'] = $tmp;
        // echo '<pre>'; print_r($file_array); echo '</pre>';

        // If error storing temporarily, unlink
        if ( is_wp_error( $tmp ) ) {
            @unlink($file_array['tmp_name']);
            $file_array['tmp_name'] = '';
        }

        // do the validation and storage stuff
        $img_id = media_handle_sideload($file_array, $post_id, $title );

        // If error storing permanently, unlink
        if ( is_wp_error($img_id) ) {
            @unlink($file_array['tmp_name']);
            return $img_id;
        }
        // set image as featured image
        set_post_thumbnail( $post_id, $img_id );
    }

    return '<p><strong>"<em>'. $title .'</em>" imported and created successfully.</strong></p>';

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

function get_insta_likes( $post_id = '', $echo = false ) {
    if ( empty( $post_id ) ) $post_id = get_the_ID();

    // delete_transient( 'dsgnwrks_instagram_likes-'. $post_id );
    $likes = get_transient( 'dsgnwrks_instagram_likes-'. $post_id );

    if ( empty( $likes ) ) {

        $insta_id = get_post_meta( $post_id, 'dsgnwrks_instagram_id' , true );
        if ( empty( $insta_id ) ) return;
        $settings = get_option( 'dsgnwrks_insta_options' );

        if ( !empty( $settings['instagram-username'] ) && !empty( $settings['instagram-userpw'] ) && !empty( $insta_id ) ) {

            $response = (array)wp_remote_get('https://api.instagram.com/v1/media/'. $insta_id .'/likes?access_token=63481.90740ae.daadcfe135f24dbe8b53e9eacca472af',
                array(
                    'body' => array(
                        'username' => $settings['instagram-username'],
                        'password' => $settings['instagram-userpw'],
                        'grant_type' => 'password',
                        'client_id' => '90740ae6e5bd4676ad62563e9a31ac75',
                        'client_secret' => '21a67881c8af4abeacbd826cf9bec45e'
                    )
                )
            );

            if ( isset( $response['response']['code'] ) ) $code = $response['response']['code'];

            if ( is_wp_error( $response ) ) {
                $response = null;
            } elseif ( isset( $code ) && $code < 400 && $code >= 200 ) {
                $api = wp_remote_retrieve_body( $response );
                $data = json_decode($api);
                $count = 0;
                $likes = array();
                foreach ( $data->data as $key => $user ) {
                    if ( $user->username == $settings['instagram-username'] ) continue;
                    $likes['data'][$key]['username'] = esc_attr( $user->username );
                    $likes['data'][$key]['bio'] = esc_attr( htmlentities( $user->bio ) );
                    $likes['data'][$key]['website'] = esc_attr( htmlentities( $user->website ) );
                    $likes['data'][$key]['profile_picture'] = esc_url( $user->profile_picture );
                    $likes['data'][$key]['full_name'] = esc_attr( $user->full_name );
                    $likes['data'][$key]['id'] = esc_attr( $user->id );
                    $count++;
                }
                $likes['count'] = $count;
                // echo '<pre>'. print_r( $likes, true ) .'</pre>';
                update_post_meta( $post_id, 'dsgnwrks_instagram_likes', $likes );

            } else {
                $response = null;
            }

        } else {
            $likes = get_post_meta( get_the_ID(), 'dsgnwrks_instagram_likes' , true );
        }

        if ( empty( $likes ) ) {
            $likes = '';
        }
        // echo '<pre>'. htmlentities( print_r( $likes, true ) ) .'</pre>';

        set_transient( 'dsgnwrks_instagram_likes-'. $post_id, $likes, 18000 );
    }

    if ( $echo == true ) echo $likes;
    else return $likes;

}

function dsgnwrks_max_quality($arg) {
    return (int) 100;
}

?>