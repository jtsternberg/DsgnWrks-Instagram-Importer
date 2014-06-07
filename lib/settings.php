<?php
if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
add_thickbox();

$this->opts = get_option( 'dsgnwrks_insta_options' );
$this->users = get_option( 'dsgnwrks_insta_users' );
$opts = &$this->opts;
$users = &$this->users;
$this->schedules = wp_get_schedules();
$users = ( !empty( $users ) ) ? $users : array();

$has_notice = get_transient( 'instagram_notification' );
$notice = '';
if ( isset( $_GET['notice'] ) && $_GET['notice'] == 'success' && $has_notice ){
	$notice = __( 'You\'ve successfully connected to Instagram!', 'dsgnwrks' );
} elseif ( isset( $_GET['class'] ) && $_GET['class'] == 'error' && $has_notice ) {
	$notice = __( 'There was an authorization error. Try again?', 'dsgnwrks' );
}

$class = isset( $_GET['class'] ) ? $_GET['class'] : 'updated';
$nogo = false;
$nofeed = ( $class == 'error' ) ? true : false;

if ( !empty( $users ) && is_array( $users ) ) {
	foreach ( $users as $key => $user ) {

		if ( isset( $opts[$user]['remove-date-filter'] ) && $opts[$user]['remove-date-filter'] == 'yes' ) {
			$opts[$user]['mm'] = '';
			$opts[$user]['dd'] = '';
			$opts[$user]['yy'] = '';
			$opts[$user]['date-filter'] = 0;
			$opts[$user]['remove-date-filter'] = '';
			update_option( 'dsgnwrks_insta_options', $opts );
		}

		if ( !empty( $opts[$user]['remove-tag-filter'] ) ) {
			$opts[$user]['tag-filter'] = '';
			$opts[$user]['remove-tag-filter'] = '';
			update_option( 'dsgnwrks_insta_options', $opts );
		}

		if ( !empty( $opts[$user]['tag-filter'] ) ) {
			$opts[$user]['remove-tag-filter'] = '';
		}

		$complete[$user] = ( !empty( $opts[$user]['mm'] ) && !empty( $opts[$user]['dd'] ) && !empty( $opts[$user]['yy'] ) ) ? true : false;
	}

} elseif ( empty( $users ) ) {
	$nogo = true;
}

?>

<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2>DsgnWrks <?php _e( 'Instagram Importer Options', 'dsgnwrks' ); ?></h2>
	<div class="updated instagram-import-message hidden"><ol></ol><div class="spinner"></div></div>
	<div id="screen-meta" style="display: block; ">
	<?php
	if ( !empty( $notice ) ) {
		echo '<div id="message" class="'.$class.'"><p>'.$notice.'</p></div>';
	}
	?>
	<div class="clear"></div>

		<div id="contextual-help-wrap" class="hidden" style="display: block; ">
			<div id="contextual-help-back"></div>
			<div id="contextual-help-columns">
				<div class="contextual-help-tabs">
					<?php if ( empty( $opts ) ) { ?>
						<h2>Get Started</h2>
					<?php } else { ?>
						<h2>Users</h2>
					<?php } ?>

					<ul>
						<?php
						if ( !empty( $users ) && is_array( $users ) ) {
							foreach ( $users as $key => $user ) {
								$id = str_replace( ' ', '', strtolower( $user ) );
								$class = ( !empty( $class ) || $nofeed == true ) ? '' : ' active';
								if ( isset( $opts['username'] ) ) {
									$class = ( $opts['username'] == $id ) ? ' active' : '';
								}

								// somthing's wrong, don't continue
								if ( !isset( $opts[$id] ) || !isset( $opts[$id]['full_username'] ) ) {
									$this->debugsend( '91' );
									continue;
								}

								?>
								<li class="instagram-tab<?php echo $class; ?>" id="tab-instagram-user-<?php echo $id; ?>">
									<a href="#instagram-user-<?php echo $id; ?>"><?php echo $opts[$id]['full_username']; ?></a>
								</li>
								<?php
							}
						} else {
							$user = 'Create User';
							$class = str_replace( ' ', '', strtolower( $user ) );
							?>
							<li class="instagram-tab active" id="tab-instagram-user-<?php echo $class; ?>">
								<a href="#instagram-user-<?php echo $class; ?>"><?php echo $user; ?></a>
							</li>
							<?php
						}

						if ( !$nogo ) { ?>
							<li id="tab-add-another-user" <?php echo ( $nofeed == true ) ? 'class="active"' : ''; ?>>
								<a href="#add-another-user"><?php _e( 'Add Another User', 'dsgnwrks' ); ?></a>
							</li>
							<li class="instagram-tab <?php echo isset( $opts['username'] ) && $opts['username'] == __( 'Plugin Options', 'dsgnwrks' ) ? ' active' : ''; ?>" id="tab-universal-options">
								<a href="#universal-options"><?php _e( 'Plugin Options', 'dsgnwrks' ); ?></a>
							</li>
						<?php } ?>
					</ul>
				</div>

				<div class="contextual-help-tabs-wrap">

				<?php
				if ( !empty( $users ) && is_array( $users ) ) {
					?>
					<form class="instagram-importer user-options" method="post" action="options.php">
					<?php settings_fields('dsgnwrks_instagram_importer_settings');

					foreach ( $users as $key => $user ) {
						$id = str_replace( ' ', '', strtolower( $user ) );
						$active = ( !empty( $active ) || $nofeed == true ) ? '' : ' active';
						if ( isset( $opts['username'] ) ) {
							$active = ( $opts['username'] == $id ) ? ' active' : '';
						}

						// somthing's wrong, don't continue
						if ( !isset( $opts[$id] ) ) {
							$this->debugsend( '139' );
							continue;
						}

						$o = &$opts[$id];
						?>
						<div id="instagram-user-<?php echo $id; ?>" class="help-tab-content<?php echo $active; ?>">

							<?php
							if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
								if ( !empty( $o['mm'] ) || !empty( $o['dd'] ) || !empty( $o['yy'] ) ) {
									if ( !$complete[$id] ) echo '<div id="message" class="error"><p>'. __( 'Please select full date.', 'dsgnwrks' ) .'</p></div>';
								}
							}
							?>

							<table class="form-table">

								<tr valign="top" class="info">
								<th colspan="2">
									<p><img class="alignleft" src="<?php echo esc_url( $o['profile_picture'] ); ?>" width="66" height="66"/><?php _e( 'Successfully connected to Instagram' ); ?> &mdash; <span><a id="delete-<?php echo $id; ?>" class="delete-instagram-user" href="<?php echo add_query_arg( array( 'page' => $this->plugin_id, 'delete-insta-user' => urlencode( $id ) ), admin_url( $GLOBALS['pagenow'] ) ); ?>"><?php _e( 'Delete User?', 'dsgnwrks' ); ?></a></span></p>
									<p>
										<?php
										printf( __( 'Please select the import filter options below. If none of the options are selected, all photos for %s will be imported.', 'dsgnwrks' ), '<strong id="full-username-'. $id .'">'. $o['full_username'] .'</strong>' );
										?>
										<em><?php _e( '(This could take a long time if you have a lot of photos)', 'dsgnwrks' ); ?></em>
									</p>
								</th>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Filter import by hashtag:', 'dsgnwrks' ); ?></strong><br/><?php printf( __( 'Will only import instagram photos with these hashtags.%sPlease separate tags with commas.', 'dsgnwrks' ), '<br/>' ); ?></th>
								<?php $tag_filter = isset( $o['tag-filter'] ) ? $o['tag-filter'] : ''; ?>
								<td><input type="text" placeholder="<?php _e( 'e.g. keeper, fortheblog', 'dsgnwrks' ); ?>" name="dsgnwrks_insta_options[<?php echo $id; ?>][tag-filter]" value="<?php echo $tag_filter; ?>" />
								<?php
									if ( !empty( $o['tag-filter'] ) ) {
										echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options['.$id.'][remove-tag-filter]" value="yes" /> <em> '. __( 'Remove filter', 'dsgnwrks' ) .'</em></label></p>';
									}
								?>
								</td>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e(  'Import from this date:', 'dsgnwrks' ); ?></strong><br/><?php _e(  'Select a date to begin importing your photos.', 'dsgnwrks' ); ?></th>

								<td class="curtime">


									<?php
									global $wp_locale;

									$date_filter = 0;
									if ( !empty( $o['mm'] ) || !empty( $o['dd'] ) || !empty( $o['yy'] ) ) {
										if ( $complete[$id] ) {
											$date = '<strong>'. $wp_locale->get_month( $o['mm'] ) .' '. $o['dd'] .', '. $o['yy'] .'</strong>';
												$o['remove-date-filter'] = 'false';
												$date_filter = strtotime( $o['mm'] .'/'. $o['dd'] .'/'. $o['yy'] );
										} else {
											$date = '<span class="warning">'. __( 'Please select full date', 'dsgnwrks' ) .'</span>';
										}
									}
									else { $date = __( 'No date selected', 'dsgnwrks' ); }
									$date = '<p style="padding-bottom: 2px; margin-bottom: 2px;" id="timestamp"> '. $date .'</p>';
									$date .= '<input type="hidden" name="dsgnwrks_insta_options['.$id.'][date-filter]" value="'. $date_filter .'" />';

									$month = '<select id="instagram-mm" name="dsgnwrks_insta_options['.$id.'][mm]">\n';
									$month .= '<option value="">'. __( 'Month', 'dsgnwrks' ) .'</option>';
									for ( $i = 1; $i < 13; $i = $i +1 ) {
										$monthnum = zeroise($i, 2);
										$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
										if ( isset( $o['mm'] ) && $i == $o['mm'] )
											$month .= ' selected="selected"';
										$month .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
									}
									$month .= '</select>';

									$day = '<select style="width: 5em;" id="instagram-dd" name="dsgnwrks_insta_options['.$id.'][dd]">\n';
									$day .= '<option value="">'. __( 'Day', 'dsgnwrks' ) .'</option>';
									for ( $i = 1; $i < 32; $i = $i +1 ) {
										$daynum = zeroise($i, 2);
										$day .= "\t\t\t" . '<option value="' . $daynum . '"';
										if ( isset( $o['dd'] ) && $i == $o['dd'] )
											$day .= ' selected="selected"';
										$day .= '>' . $daynum;
									}
									$day .= '</select>';

									$year = '<select style="width: 5em;" id="instagram-yy" name="dsgnwrks_insta_options['.$id.'][yy]">\n';
									$year .= '<option value="">'. __( 'Year', 'dsgnwrks' ) .'</option>';
									for ( $i = date( 'Y' ); $i >= 2010; $i = $i -1 ) {
										$yearnum = zeroise($i, 4);
										$year .= "\t\t\t" . '<option value="' . $yearnum . '"';
										if ( isset( $o['yy'] ) && $i == $o['yy'] )
											$year .= ' selected="selected"';
										$year .= '>' . $yearnum;
									}
									$year .= '</select>';


									echo '<div class="timestamp-wrap">';
									/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
									printf(__('%1$s %2$s %3$s %4$s'), $date, $month, $day, $year );

									if ( $complete[$id] == true ) {
										echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options['.$id.'][remove-date-filter]" value="yes" /> <em> '. __( 'Remove filter', 'dsgnwrks' ) .'</em></label></p>';
									}
									?>

								</td>
								</tr>

								<tr valign="top" class="info">
								<th colspan="2">
									<p><?php _e( 'Please select the post options for the imported instagram photos below.', 'dsgnwrks' ); ?></p>
								</th>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Save Instagram photo as post\'s featured image:', 'dsgnwrks' ); ?></strong></th>
								<td>
									<input type="checkbox" name="dsgnwrks_insta_options[<?php echo $id; ?>][feat_image]" <?php checked( isset( $o['feat_image'] ) ); ?> value="yes"/>
								</td>
								</tr>

								<?php
								// Our auto-import interval text. "Manual" if not set
								$interval = empty( $opts['frequency'] ) || $opts['frequency'] == 'never' ? 'Manual' : strtolower( $this->schedules[$opts['frequency']]['display'] );
								?>
								<tr valign="top"<?php echo $interval == 'Manual' ? ' class="disabled"' : ''; ?>>
								<th scope="row">
									<strong><?php _e( 'Auto-import future photos:', 'dsgnwrks' ); ?></strong><br/>
									<?php if ( $interval == 'Manual' ) : ?>
									<em><?php _e( 'Change import interval from "Manual" in the "Plugin Options" tab for this option to take effect.', 'dsgnwrks' ); ?></em>
									<?php
									else :
									printf( __( 'Change import interval (%s) in the "Plugin Options" tab.', 'dsgnwrks' ), $interval );
									endif; ?>
								</th>
								<td>
									<input type="checkbox" name="dsgnwrks_insta_options[<?php echo $id; ?>][auto_import]" <?php checked( isset( $o['auto_import'] ) ); ?> value="yes"/>
								</td>
								</tr>

								<tr valign="top">
								<th scope="row">
									<strong><?php _e( 'Post Title:', 'dsgnwrks' ); ?></strong><br/><?php _e( 'Add the imported Instagram data using these custom tags:', 'dsgnwrks' ); ?><br/><code>**insta-text**</code>, <code>**insta-location**</code>, <code>**insta-filter**</code>
								</th>
								<?php $post_title = isset( $o['post-title'] ) ? $o['post-title'] : '**insta-text**'; ?>
								<td><input type="text" name="dsgnwrks_insta_options[<?php echo $id; ?>][post-title]" value="<?php echo $post_title; ?>" />
								</td>
								</tr>

								<tr valign="top">
								<td colspan="2">
									<p><strong><?php _e( 'Post Content:', 'dsgnwrks' ); ?></strong><br/><?php _e( 'Add the imported Instagram data using these custom tags:', 'dsgnwrks' ); ?><br/><code>**insta-text**</code>, <code>**insta-image**</code>, <code>**insta-embed-image**</code>, <code>**insta-embed-video**</code>, <code>**insta-image-link**</code>, <code>**insta-link**</code>, <code>**insta-location**</code>, <code>**insta-filter**</code></p>
									<p><?php _e( 'Or use these conditional tags:', 'dsgnwrks' ); ?><br/><code>[if-insta-text]<?php _e( 'Photo Caption:', 'dsgnwrks' ); ?> **insta-text**[/if-insta-text]</code><br/><code>[if-insta-location]<?php _e( 'Photo taken at:', 'dsgnwrks' ); ?> **insta-location**[/if-insta-location]</code></p>
									<?php
									$post_text = isset( $o['post_content'] ) ? $o['post_content'] : '';
									$args = array(
										'textarea_name' => 'dsgnwrks_insta_options['.$id.'][post_content]',
										'editor_class' => 'post_text',
										'textarea_rows' => 6,
										'wpautop' => false
									);
									wp_editor( $post_text, 'dsgnwrks_insta_options_'.$id.'_post_content', $args );
									?>

								</td>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Import to Post-Type:', 'dsgnwrks' ); ?></strong></th>
								<td>
									<select class="instagram-post-type" id="instagram-post-type-<?php echo $id; ?>" name="dsgnwrks_insta_options[<?php echo $id; ?>][post-type]">
										<?php
										$args = array(
										  'public' => true,
										);
										$post_types = get_post_types( $args );
										$cur_post_type = isset( $o['post-type'] ) ? $o['post-type'] : '';
										foreach ($post_types  as $post_type ) {
											?>
											<option value="<?php echo $post_type; ?>" <?php selected( $cur_post_type, $post_type ); ?>><?php echo $post_type; ?></option>
											<?php
										}
										?>
									</select>
								</td>
								</tr>


								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Imported posts status:', 'dsgnwrks' ); ?></strong></th>
								<td>
									<select id="instagram-draft-<?php echo $id; ?>" name="dsgnwrks_insta_options[<?php echo $id; ?>][draft]">
										<?php
										$draft_status = isset( $o['draft'] ) ? $o['draft'] : '';
										?>
										<option value="draft" <?php selected( $draft_status, 'draft' ); ?>><?php _e( 'Draft', 'dsgnwrks' ); ?></option>
										<option value="publish" <?php selected( $draft_status, 'publish' ); ?>><?php _e( 'Published', 'dsgnwrks' ); ?></option>
										<option value="pending" <?php selected( $draft_status, 'pending' ); ?>><?php _e( 'Pending', 'dsgnwrks' ); ?></option>
										<option value="private" <?php selected( $draft_status, 'private' ); ?>><?php _e( 'Private', 'dsgnwrks' ); ?></option>
									</select>

								</td>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Assign posts to an existing user:', 'dsgnwrks' ); ?></strong></th>
								<td>
									<?php
									$author = isset( $o['author'] ) ? $o['author'] : '';
									wp_dropdown_users( array( 'name' => 'dsgnwrks_insta_options['.$id.'][author]', 'selected' => $author ) );
									?>

								</td>
								</tr>


								<?php
								$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
								$taxes = array();

								?>
								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Save photo hashtags as taxonomy terms (tags, categories, etc):', 'dsgnwrks' ); ?></strong></th>
								<td>
									<?php
									$hash_tax = isset( $o['hashtags_as_tax'] ) ? $o['hashtags_as_tax'] : '';

									echo '<select id="dsgnwrks_insta_options-'.$id.'-hashtags_as_tax" name="dsgnwrks_insta_options['.$id.'][hashtags_as_tax]">';
										echo '<option class="empty" value="" '. selected( $hash_tax, '', false ) .'>'. __( '&mdash; Select &mdash;', 'dsgnwrks' ) .'</option>';
										foreach ( $taxonomies as $key => $tax ) {

											if ( $tax->label == __( 'Format' ) )
												continue;

											$pt_taxes = get_object_taxonomies( $cur_post_type ? $cur_post_type : 'post' );
											$disabled = !in_array( $tax->name, $pt_taxes );
											echo '<option class="taxonomy-'. $tax->name .'" value="'. esc_attr( $tax->name ) .'" ', selected( $hash_tax, $tax->name ), ' ', disabled( $disabled ) ,'>'. esc_html( $tax->label ) .'</option>';

										}
									echo '</select>';
									?>
								</td>
								</tr>


								<?php
								if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) {
									$post_formats = get_theme_support( 'post-formats' );

									if ( is_array( $post_formats[0] ) ) {
										$o['post_format'] = !empty( $o['post_format'] ) ? esc_attr( $o['post_format'] ) : '';

										// Add in the current one if it isn't there yet, in case the current theme doesn't support it
										if ( $o['post_format'] && !in_array( $o['post_format'], $post_formats[0] ) )
											$post_formats[0][] = $o['post_format'];
										?>
										<tr valign="top" class="taxonomies-add taxonomy-post_format">
										<th scope="row"><strong><?php _e( 'Select Imported Posts Format:', 'dsgnwrks' );?></strong></th>
										<td>
											<select id="dsgnwrks_insta_options[<?php echo $id; ?>][post_format]" name="dsgnwrks_insta_options[<?php echo $id; ?>][post_format]">
												<option value="0" <?php selected( $o['post_format'], '' ); ?>><?php _e( 'Standard', 'dsgnwrks' ); ?></option>
												<?php foreach ( $post_formats[0] as $format ) : ?>
												<option value="<?php echo esc_attr( $format ); ?>" <?php selected( $o['post_format'], $format ); ?>><?php echo esc_html( get_post_format_string( $format ) ); ?></option>

												<?php endforeach; ?><br />
											</select>
										</td>
										</tr>
										<?php
									}
								}


								$placeholder = __( 'e.g. Instagram, Life, dog, etc', 'dsgnwrks' );
								foreach ( $taxonomies as $key => $tax ) {

									if ( $tax->label == __( 'Format' ) )
										continue;

									$o[$tax->name] = !empty( $o[$tax->name] ) ? esc_attr( $o[$tax->name] ) : '';

									if ( $tax->name == 'post_tag' )  $placeholder = __( 'e.g. beach, sunrise', 'dsgnwrks' );

									$tax_section_label = '<strong>'. sprintf( __( '%s to apply to imported posts.', 'dsgnwrks' ), $tax->label ) .'</strong><br/>'. sprintf( __( 'Please separate %s with commas', 'dsgnwrks' ), strtolower( $tax->label ) ) ."\n";
									$tax_section_input = '<input type="text" placeholder="'.$placeholder.'" name="dsgnwrks_insta_options['.$id.']['.$tax->name.']" value="'.$o[$tax->name].'" />'."\n";

									?>
									<tr valign="top" class="taxonomies-add taxonomy-<?php echo $tax->name; ?>">
									<th scope="row">
										<?php echo apply_filters( 'dsgnwrks_instagram_tax_section_label', $tax_section_label, $tax ); ?>
									</th>
									<td>
										<?php echo apply_filters( 'dsgnwrks_instagram_tax_section_input', $tax_section_input, $tax, $id, $opts ); ?>
									</td>
									</tr>
									<?php
								}

								// Add extra fields to the user settings page.
								// ie. $extra_fields[] = array( 'title' => 'My Title', 'input' => '<input />' );
								if ( $extra_fields = apply_filters( 'dsgnwrks_instagram_extra_user_fields', array(), $o, $id ) ) {
									foreach ( $extra_fields as $field ) {
										if ( !is_array( $field ) || !isset( $field['title'], $field['input'] ) )
											continue;
										?>
										<tr valign="top">
											<th scope="row">
												<strong><?php echo $field['title']; ?></strong>
												<?php
												if ( isset( $field['desc'] ) )
													echo '<br/>'. $field['desc'];
												?>
											</th>
											<td>
												<?php echo $field['input']; ?>
											</td>
										</tr>
										<?php
									}
								}

								$userdata = array( 'access_token', 'bio', 'website', 'profile_picture', 'full_name', 'id', 'full_username' ) ;
								$trans = get_transient( $id .'-instaimportdone' );

								?>
								<tr valign="top" class="info">
									<th colspan="2">
									<input type="hidden" name="dsgnwrks_insta_options[username]" value="replaceme" />
									<?php
									foreach ( $userdata as $data ) {
										$val = isset( $o[$data] ) ? $o[$data] : '';
										echo '<input type="hidden" name="dsgnwrks_insta_options['.$id.']['.$data.']" value="'. $val .'" />';
									}

									if ( $trans ) {
										echo '<p>'. sprintf( __( 'Last updated: %s', 'dsgnwrks' ), $trans ) .'</p>';
									}
									?>
									</th>
								</tr>
							</table>
							<p class="save-warning warning user-<?php echo $id; ?>"><?php _e( 'You\'ve changed settings.', 'dsgnwrks' );?> <strong><?php _e( 'please "Save" them before importing.', 'dsgnwrks' ); ?></strong></p>
							<p class="submit">
								<input type="submit" id="save-<?php echo sanitize_title( $id ); ?>" name="save" class="button-primary save" value="<?php _e( 'Save' ) ?>" />
								<?php
								$importlink = $this->instimport_link( $o['full_username'] );
								?>
								<a href="<?php echo $importlink; ?>" class="button-secondary import-button" id="import-<?php echo $id; ?>" data-instagramuser="<?php echo urlencode( $o['full_username'] ); ?>"><?php _e( 'Import', 'dsgnwrks' ); ?></a>
								<p class="spinner-wrap hidden"><?php _e( '...Importing. This could take a while.', 'dsgnwrks' ); ?><span class="spinner"></span></p><strong class="warning hidden"><?php _e( 'ERROR!', 'dsgnwrks' ); ?></strong>
							</p>
						</div>
						<?php
					}

					?>
					<div id="universal-options" class="help-tab-content instagram-importer <?php echo isset( $opts['username'] ) && $opts['username'] == __( 'Plugin Options', 'dsgnwrks' ) ? ' active' : ''; ?>">
						<?php $this->universal_options_form(); ?>
					</div>
					</form>
					<?php
				} else {
					$message = '<p>'. __( 'Welcome to the Instagram Importer! Click to be taken to Instagram\'s site to securely authorize this plugin for use with your account.', 'dsgnwrks' ) .'</p>';
					$this->settings_user_form( $users, $message );
				}

				if ( !$nogo ) { ?>
					<div id="add-another-user" class="help-tab-content <?php echo ( $nofeed == true ) ? ' active' : ''; ?>">
						<?php $this->settings_user_form( $users ); ?>
					</div>
				<?php } ?>
				</div>

				<div class="contextual-help-sidebar">
					<p class="jtsocial"><a class="jtpaypal" href="http://j.ustin.co/rYL89n" target="_blank"><?php _e( 'Contribute', 'dsgnwrks' ); ?><span></span></a>
						<a class="jttwitter" href="http://j.ustin.co/wUfBD3" target="_blank"><?php _e( 'Follow me on Twitter', 'dsgnwrks' ); ?><span></span></a>
						<a class="jtemail" href="http://j.ustin.co/scbo43" target="_blank"><?php _e( 'Contact Me', 'dsgnwrks' ); ?><span></span></a>
					</p>
				</div>

			</div>
		</div>
	</div>
</div>
