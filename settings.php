<?php
global $user_ID;

if ( !current_user_can( 'manage_options' ) )  {
wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

$settings = get_option( 'dsgnwrks_insta_options' );

if ( $settings['instagram-remove-date-filter'] == 'yes' ) {
    $settings['instagram-mm'] = '';
    $settings['instagram-dd'] = '';
    $settings['instagram-yy'] = '';
    $settings['instagram-date-filter'] = 0;
    $settings['instagram-remove-date-filter'] = '';
    update_option( 'dsgnwrks_insta_options', $settings );
}

if ( !empty( $settings['instagram-remove-tag-filter'] ) ) {
    $settings['instagram-tag-filter'] = '';
    $settings['instagram-remove-tag-filter'] = '';
}

if ( !empty( $settings['instagram-tag-filter'] ) ) {
    $settings['instagram-remove-tag-filter'] = '';
}

$complete = ( !empty( $settings['instagram-mm'] ) && !empty( $settings['instagram-dd'] ) && !empty( $settings['instagram-yy'] ) ) ? true : false;

?>

<div class="wrap">
    <div id="icon-tools" class="icon32"><br></div>
    <h2>DsgnWrks Instagram Importer Options</h2>
    <div id="screen-meta" style="display: block; ">
    <?php

    // Setup our notifications
    if ( !empty( $settings['instagram-username'] ) && empty( $settings['instagram-userpw'] ) ) {
        echo '<div id="message" class="error"><p>Please enter your Instagram password.</p></div>';
    } elseif ( empty( $settings['instagram-username'] ) && !empty( $settings['instagram-userpw'] ) ) {
        echo '<div id="message" class="error"><p>Please enter your Instagram username.</p></div>';
    } elseif ( !empty( $settings['instagram-username'] ) && !empty( $settings['instagram-userpw'] ) && !empty( $settings['instagram-badauth'] ) && $settings['instagram-badauth'] == 'error' ) {
        echo '<div id="message" class="error"><p>Couldn\'t find an instagram feed. Please check your username and password.</p></div>';
    } elseif ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' && empty( $settings['instagram-noauth'] ) ) {
        if ( !empty( $settings['instagram-mm'] ) || !empty( $settings['instagram-dd'] ) || !empty( $settings['instagram-yy'] ) ) {

            if ( !$complete ) echo '<div id="message" class="error"><p>Please select full date.</p></div>';
            else echo '<div id="message" class="updated"><p>Settings Updated</p></div>';
        } else {
            echo '<div id="message" class="updated"><p>Settings Updated</p></div>';

        }
    }

    ?>
    <div class="clear"></div>

        <div id="contextual-help-wrap" class="hidden" style="display: block; ">
            <div id="contextual-help-back"></div>
            <div id="contextual-help-columns">
                <div class="contextual-help-tabs">
                    <?php
                        $user = !empty( $settings['instagram-username'] ) ? $settings['instagram-username'] : 'Create User';
                        $class = str_replace( ' ', '', strtolower( $user ) );
                    ?>
                    <?php if ( $user != 'Create User' ) { ?>
                    <h2>Users</h2>
                    <?php } else { ?>
                    <h2>Get Started</h2>
                    <?php } ?>

                    <ul>
                        <li id="tab-instagram-user-<?php echo $class; ?>" class="active">
                            <a href="#instagram-user-<?php echo $class; ?>"><?php echo $user; ?></a>
                        </li>

                        <?php if ( $user != 'Create User' ) { ?>
                        <li id="tab-add-another-user">
                            <a href="#add-another-user">Add Another User</a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="contextual-help-sidebar">
                    <p class="jtsocial"><a class="jtpaypal" href="http://j.ustin.co/rYL89n" target="_blank">Contribute<span></span></a>
                        <a class="jttwitter" href="http://j.ustin.co/wUfBD3" target="_blank">Follow me on Twitter<span></span></a>
                        <a class="jtemail" href="http://j.ustin.co/scbo43" target="blank">Contact Me<span></span></a>
                    </p>

                </div>

                <div class="contextual-help-tabs-wrap">

                    <div id="instagram-user-<?php echo $class; ?>" class="help-tab-content active">
                        <form class="instagram-importer" method="post" action="options.php">
                            <?php settings_fields('dsgnwrks_instagram_importer_settings'); ?>

                            <table class="form-table">
                                <p>
                                    <?php if ( !empty( $settings['instagram-noauth'] ) ) { ?>
                                        Welcome to Instagram Importer! Enter your Instagram username and password to authenticate the plugin, and we'll get started.
                                    <?php } else { ?>
                                        These are your Instagram credentials, required for the plugin to work.
                                        <br/>
                                        <strong>You've successfully connected to Instagram!</strong>
                                    <?php } ?>
                                </p>

                                <tr valign="top">
                                <th scope="row"><label for="dsgnwrks_insta_options[instagram-username]"><strong>Instagram Username:</strong></label></th>
                                <td><input type="text" id="dsgnwrks_insta_options[instagram-username]" name="dsgnwrks_insta_options[instagram-username]" value="<?php echo esc_attr( $settings['instagram-username'] ); ?>" /></td>
                                </tr>

                                <tr valign="top">
                                <th scope="row"><label for="dsgnwrks_insta_options[instagram-userpw]"><strong>Instagram Password:</strong></label></th>
                                <td><input type="password" id="dsgnwrks_insta_options[instagram-userpw]" name="dsgnwrks_insta_options[instagram-userpw]" value="<?php echo esc_attr( $settings['instagram-userpw'] ); ?>" /></td>
                                </tr>


                            <?php if ( empty( $settings['instagram-noauth'] ) ) {

                                $button = 'Save Changes'; ?>


                                    <tr valign="top" class="info">
                                    <th colspan="2">
                                        <p>Please select the import filter options below. If none of the options are selected, all photos for the registered user will be imported. <em>(This could take a long time if you have a lot of shots)</em></p>
                                    </th>
                                    </tr>

                                    <tr valign="top">
                                    <th scope="row"><strong>Filter import by hashtag:</strong><br/>Will only import instagram shots with these hashtags.<br/>Please separate tags with commas.</th>
                                    <td><input type="text" placeholder="e.g. keeper, fortheblog" name="dsgnwrks_insta_options[instagram-tag-filter]" value="<?php echo $settings['instagram-tag-filter']; ?>" />
                                    <?php
                                        if ( !empty( $settings['instagram-tag-filter'] ) ) {
                                            echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options[instagram-remove-tag-filter]" value="yes" /> <em> Remove filter</em></label></p>';
                                        }
                                    ?>
                                    </td>
                                    </tr>

                                    <tr valign="top">
                                    <th scope="row"><strong>Import from this date:</strong><br/>Select a date to begin importing your photos.</th>

                                    <td class="curtime">


                                        <?php
                                        global $wp_locale;

                                        if ( !empty( $settings['instagram-mm'] ) || !empty( $settings['instagram-dd'] ) || !empty( $settings['instagram-yy'] ) ) {
                                            if ( $complete ) {
                                                $date = '<strong>'. $wp_locale->get_month( $settings['instagram-mm'] ) .' '. $settings['instagram-dd'] .', '. $settings['instagram-yy'] .'</strong>';
                                                    $settings['instagram-remove-date-filter'] = 'false';
                                                    $settings['instagram-date-filter'] = strtotime( $settings['instagram-mm'] .'/'. $settings['instagram-dd'] .'/'. $settings['instagram-yy'] );
                                            } else {
                                                $date = '<span style="color: red;">Please select full date</span>';
                                            }
                                        }
                                        else $date = 'No date selected';
                                        $date = '<p style="padding-bottom: 2px; margin-bottom: 2px;" id="timestamp"> '. $date .'</p>';
                                        $date .= '<input type="hidden" name="dsgnwrks_insta_options[instagram-date-filter]" value="'. $settings['instagram-date-filter'] .'" />';

                                        $month = '<select id="instagram-mm" name="dsgnwrks_insta_options[instagram-mm]">\n';
                                        $month .= '<option value="">Month</option>';
                                        for ( $i = 1; $i < 13; $i = $i +1 ) {
                                            $monthnum = zeroise($i, 2);
                                            $month .= "\t\t\t" . '<option value="' . $monthnum . '"';
                                            if ( $i == $settings['instagram-mm'] )
                                                $month .= ' selected="selected"';
                                            $month .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
                                        }
                                        $month .= '</select>';

                                        $day = '<select style="width: 5em;" id="instagram-dd" name="dsgnwrks_insta_options[instagram-dd]">\n';
                                        $day .= '<option value="">Day</option>';
                                        for ( $i = 1; $i < 32; $i = $i +1 ) {
                                            $daynum = zeroise($i, 2);
                                            $day .= "\t\t\t" . '<option value="' . $daynum . '"';
                                            if ( $i == $settings['instagram-dd'] )
                                                $day .= ' selected="selected"';
                                            $day .= '>' . $daynum;
                                        }
                                        $day .= '</select>';

                                        $year = '<select style="width: 5em;" id="instagram-yy" name="dsgnwrks_insta_options[instagram-yy]">\n';
                                        $year .= '<option value="">Year</option>';
                                        for ( $i = date( 'Y' ); $i >= 2010; $i = $i -1 ) {
                                            $yearnum = zeroise($i, 4);
                                            $year .= "\t\t\t" . '<option value="' . $yearnum . '"';
                                            if ( $i == $settings['instagram-yy'] )
                                                $year .= ' selected="selected"';
                                            $year .= '>' . $yearnum;
                                        }
                                        $year .= '</select>';


                                        echo '<div class="timestamp-wrap">';
                                        /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
                                        printf(__('%1$s %2$s %3$s %4$s'), $date, $month, $day, $year );

                                        if ( $complete == true ) {
                                            echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options[instagram-remove-date-filter]" value="yes" /> <em> Remove filter</em></label></p>';
                                        }
                                        ?>

                                    </td>
                                    </tr>

                                    <tr valign="top" class="info">
                                    <th colspan="2">
                                        <p>Please select the post options for the imported instagram shots below.</em></p>
                                    </th>
                                    </tr>

                                    <?php
                                    // echo '<tr valign="top">
                                    // <th scope="row"><strong>Insert Instagram photo into:</strong></th>
                                    // <td>
                                    //     <select id="instagram-image" name="dsgnwrks_insta_options[instagram-image]">';
                                    //         if ( $settings['instagram-image'] == 'feat-image') $selected1 = 'selected="selected"';
                                    //         echo '<option value="feat-image" '. $selected1 .'>Featured Image</option>';
                                    //         if ( $settings['instagram-image'] == 'content') $selected2 = 'selected="selected"';
                                    //         echo '<option value="content" '. $selected2 .'>Content</option>';
                                    //         if ( $settings['instagram-image'] == 'both') $selected3 = 'selected="selected"';
                                    //         echo '<option value="both" '. $selected3 .'>Both</option>';
                                    //     echo '</select>

                                    // </td>
                                    // </tr>';
                                    ?>

                                    <tr valign="top">
                                    <th scope="row"><strong>Import to Post-Type:</strong></th>
                                    <td>
                                        <select id="instagram-post-type" name="dsgnwrks_insta_options[instagram-post-type]">
                                            <?php
                                            $args=array(
                                              'public'   => true,
                                            );
                                            $post_types=get_post_types( $args );
                                            foreach ($post_types  as $post_type ) {
                                                ?>
                                                <option value="<?php echo $post_type; ?>" <?php selected( $settings['instagram-post-type'], $post_type ); ?>><?php echo $post_type; ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    </tr>


                                    <tr valign="top">
                                    <th scope="row"><strong>Imported posts status:</strong></th>
                                    <td>
                                        <select id="instagram-draft" name="dsgnwrks_insta_options[instagram-draft]">
                                            <option value="draft" <?php selected( $settings['instagram-draft'], 'draft' ); ?>>Draft</option>
                                            <option value="publish" <?php selected( $settings['instagram-draft'], 'publish' ); ?>>Published</option>
                                            <option value="pending" <?php selected( $settings['instagram-draft'], 'pending' ); ?>>Pending</option>
                                            <option value="private" <?php selected( $settings['instagram-draft'], 'private' ); ?>>Private</option>
                                        </select>

                                    </td>
                                    </tr>

                                    <tr valign="top">
                                    <th scope="row"><strong>Assign posts to an existing user:</strong></th>
                                    <td>
                                        <?php
                                        wp_dropdown_users( array( 'name' => 'dsgnwrks_insta_options[instagram-author]', 'selected' => $settings['instagram-author'] ) );
                                        ?>

                                    </td>
                                    </tr>

                                    <?php
                                    if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) {
                                        $post_formats = get_theme_support( 'post-formats' );

                                        if ( is_array( $post_formats[0] ) ) {
                                            $settings['instagram-post_format'] = !empty( $settings['instagram-post_format'] ) ? esc_attr( $settings['instagram-post_format'] ) : '';

                                            // Add in the current one if it isn't there yet, in case the current theme doesn't support it
                                            if ( $settings['instagram-post_format'] && !in_array( $settings['instagram-post_format'], $post_formats[0] ) )
                                                $post_formats[0][] = $settings['instagram-post_format'];
                                            ?>
                                            <tr valign="top" class="taxonomies-add">
                                            <th scope="row"><strong>Select Imported Posts Format:</strong></th>
                                            <td>

                                                <select id="dsgnwrks_insta_options[instagram-post_format]" name="dsgnwrks_insta_options[instagram-post_format]">
                                                    <option value="0" <?php selected( $settings['instagram-post_format'], '' ); ?>>Standard</option>
                                                    <?php foreach ( $post_formats[0] as $format ) : ?>
                                                    <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $settings['instagram-post_format'], $format ); ?>><?php echo esc_html( get_post_format_string( $format ) ); ?></option>

                                                    <?php endforeach; ?><br />

                                                </select>

                                            </td>
                                            </tr>


                                            <?php
                                        }
                                    }


                                    $args = array(
                                        'public' => true,
                                        );
                                    $taxs = get_taxonomies( $args, 'objects' );

                                    foreach ( $taxs as $key => $tax ) {

                                        if ( $tax->label == 'Format' ) continue;

                                        $settings['instagram-'. $tax->name] = !empty( $settings['instagram-'. $tax->name] ) ? esc_attr( $settings['instagram-'. $tax->name] ) : '';

                                        $placeholder = 'e.g. Instagram, Life, dog, etc';

                                        if ( $tax->name == 'post_tag' )  $placeholder = 'e.g. beach, sunrise';
                                        ?>
                                        <tr valign="top" class="taxonomies-add taxonomy-<?php echo $tax->name; ?>">
                                        <th scope="row"><strong><?php echo $tax->label; ?> to apply to imported posts.</strong><br/>Please separate <?php echo strtolower( $tax->label ); ?> with commas.</th>
                                        <td><input type="text" placeholder="<?php echo $placeholder; ?>" name="dsgnwrks_insta_options[instagram-<?php echo $tax->name; ?>]" value="<?php echo $settings['instagram-'. $tax->name]; ?>" />
                                        </td>
                                        </tr>
                                        <?php

                                    }
                                    ?>

                                    <!-- <tr valign="top">
                                    <th scope="row"><strong>Categories to apply to imported posts.</strong><br/>Please separate categories with commas.</th>
                                    <td><input type="text" placeholder="e.g. Instagram, Life" name="dsgnwrks_insta_options[instagram-categories]" value="<?php echo $instagram_cats; ?>" />
                                   </td>
                                    </tr>

                                    <tr valign="top">
                                    <th scope="row"><strong>Tags to apply to imported posts.</strong><br/>Please separate tags with commas.</th>
                                    <td><input type="text" placeholder="e.g. beach, sunrise" name="dsgnwrks_insta_options[instagram-add-tags]" value="<?php echo $instagram_add_tags; ?>" />
                                   </td>
                                    </tr> -->

                                    <?php
                                    $trans = get_transient( 'instaimportdone' );

                                    if ( $trans ) { ?>
                                        <tr valign="top" class="info">
                                        <th colspan="2">
                                            <?php echo '<p>Last updated: '. $trans .'</p>'; ?>

                                        </th>
                                        </tr>
                                    <?php } ?>

                            <?php } else { $button = 'Authenticate'; } ?>

                            </table>

                            <p class="submit">
                                <input type="submit"  name="save" class="button-primary" value="<?php echo _e( $button ) ?>" />

                                <?php if ( $button != 'Authenticate' ) {
                                    echo '<a href="'. add_query_arg( 'instaimport', 'true' ) .'" class="button-secondary">Import</a>';
                                }
                                ?>
                            </p>

                        </form>

                    </div>

                    <div id="add-another-user" class="help-tab-content">
                        <form class="instagram-importer" method="post" action="options.php">
                            <table class="form-table">
                                <p>Enter the Instagram username and password of another user whose photos you would like to import.</p>

                                <tr valign="top">
                                <th scope="row"><label for="dsgnwrks_insta_options[instagram-username]"><strong>Instagram Username:</strong></label></th>
                                <td><input type="text" id="dsgnwrks_insta_options[instagram-username]" name="dsgnwrks_insta_options[instagram-username]" value="<?php echo esc_attr( $settings['instagram-username'] ); ?>" /></td>
                                </tr>

                                <tr valign="top">
                                <th scope="row"><label for="dsgnwrks_insta_options[instagram-userpw]"><strong>Instagram Password:</strong></label></th>
                                <td><input type="password" id="dsgnwrks_insta_options[instagram-userpw]" name="dsgnwrks_insta_options[instagram-userpw]" value="<?php echo esc_attr( $settings['instagram-userpw'] ); ?>" /></td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

