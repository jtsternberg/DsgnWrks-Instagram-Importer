<?php
add_thickbox();

$users        = $this->core->get_users();
$class        = isset( $_GET['class'] ) ? $_GET['class'] : 'updated';
$nogo         = empty( $users ) || ! is_array( $users );
$nofeed       = $class == 'error';
$current_user = $this->get_option( 'username' );

?>
<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2>DsgnWrks <?php _e( 'Instagram Importer Options', 'dsgnwrks' ); ?></h2>
	<div class="updated instagram-import-message hidden"><ol></ol><div class="spinner"></div></div>
	<div id="dw-instagram-wrap" style="display: block; ">
	<?php $this->display_notices(); ?>
	<div class="clear"></div>

		<div id="contextual-help-wrap" class="hidden" style="display: block; ">
			<div id="contextual-help-back"></div>
			<div id="contextual-help-columns">
				<div class="contextual-help-tabs">
					<?php if ( ! $this->get_options() ) { ?>
						<h2>Get Started</h2>
					<?php } else { ?>
						<h2>Users</h2>
					<?php } ?>

					<ul>
						<?php
						if ( !empty( $users ) && is_array( $users ) ) {
							foreach ( $users as $key => $user ) {
								$user = str_replace( ' ', '', strtolower( $user ) );
								$class = ( !empty( $class ) || $nofeed == true ) ? '' : ' active';
								if ( $current_user ) {
									$class = $user == $current_user ? ' active' : '';
								}

								$this->userid = $user;

								if ( ! $this->user_option( 'full_username' ) ) {
									// something's wrong, skip this user
									$this->debugsend( '91' );
									continue;
								}

								?>
								<li class="instagram-tab<?php echo $class; ?>" id="tab-instagram-user-<?php echo $user; ?>">
									<a href="#instagram-user-<?php echo $user; ?>"><?php echo $this->user_option( 'full_username' ); ?></a>
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

						if ( ! $nogo ) {?>
							<li id="tab-add-another-user" <?php echo ( $nofeed == true ) ? 'class="active"' : ''; ?>>
								<a href="#add-another-user"><?php _e( 'Add Another User', 'dsgnwrks' ); ?></a>
							</li>
							<li class="instagram-tab <?php echo __( 'Plugin Options', 'dsgnwrks' ) == $current_user ? ' active' : ''; ?>" id="tab-universal-options">
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
					<?php settings_fields( 'dsgnwrks_instagram_importer_settings' );

					foreach ( $users as $key => $user ) {
						$user = str_replace( ' ', '', strtolower( $user ) );
						$active = ! empty( $active ) || $nofeed == true ? '' : ' active';
						if ( $current_user ) {
							$active = $current_user == $user ? ' active' : '';
						}

						$this->userid = $user;

						// somthing's wrong, don't continue
						if ( ! $this->get_option( $user ) ) {
							$this->debugsend( '139' );
							continue;
						}

						$has_partial_date = $this->user_option( 'mm' ) || $this->user_option( 'dd' ) || $this->user_option( 'yy' );
						$complete_date = $this->user_option( 'mm' ) && $this->user_option( 'dd' ) && $this->user_option( 'yy' );

						?>
						<div id="instagram-user-<?php echo $user; ?>" class="help-tab-content<?php echo $active; ?>">

							<?php
							if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
								if ( $has_partial_date && ! $complete_date ) {
									echo '<div id="message" class="error"><p>'. __( 'Please select full date.', 'dsgnwrks' ) .'</p></div>';
								}
							}
							?>

							<table class="form-table">

								<tr valign="top" class="info">
								<th colspan="2">
									<p><img class="alignleft" src="<?php echo esc_url( $this->user_option( 'profile_picture' ) ); ?>" width="66" height="66"/><?php _e( 'Successfully connected to Instagram' ); ?> &mdash; <span><a id="delete-<?php echo $user; ?>" class="delete-instagram-user" href="<?php echo add_query_arg( array( 'page' => $this->core->plugin_id, 'delete-insta-user' => urlencode( $user ) ), admin_url( $GLOBALS['pagenow'] ) ); ?>"><?php _e( 'Delete User?', 'dsgnwrks' ); ?></a></span></p>
									<p>
										<?php
										printf( __( 'Please select the import filter options below. If none of the options are selected, all photos for %s will be imported.', 'dsgnwrks' ), '<strong id="full-username-'. $user .'">'. $this->user_option( 'full_username' ) .'</strong>' );
										?>
										<em><?php _e( '(This could take a long time if you have a lot of photos)', 'dsgnwrks' ); ?></em>
									</p>
								</th>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Filter import by hashtag:', 'dsgnwrks' ); ?></strong><br/><?php printf( __( 'Will only import instagram photos with these hashtags.%sPlease separate tags with commas.', 'dsgnwrks' ), '<br/>' ); ?></th>
								<td><input type="text" placeholder="<?php _e( 'e.g. keeper, fortheblog', 'dsgnwrks' ); ?>" name="dsgnwrks_insta_options[<?php echo $user; ?>][tag-filter]" value="<?php echo $this->user_option( 'tag-filter', '' ); ?>" />
								<?php
									if ( $this->user_option( 'tag-filter' ) ) {
										echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options['.$user.'][remove-tag-filter]" value="yes" /> <em> '. __( 'Remove filter', 'dsgnwrks' ) .'</em></label></p>';
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

									if ( $has_partial_date ) {
										if ( $complete_date ) {
											$date = '<strong>'. $wp_locale->get_month( $this->user_option( 'mm' ) ) .' '. $this->user_option( 'dd' ) .', '. $this->user_option( 'yy' ) .'</strong>';
												$this->all_opts[ $user ]['remove-date-filter'] = 'false';
												$date_filter = strtotime( $this->user_option( 'mm' ) .'/'. $this->user_option( 'dd' ) .'/'. $this->user_option( 'yy' ) );
										} else {
											$date = '<span class="warning">'. __( 'Please select full date', 'dsgnwrks' ) .'</span>';
										}
									}
									else { $date = __( 'No date selected', 'dsgnwrks' ); }
									$date = '<p style="padding-bottom: 2px; margin-bottom: 2px;" id="timestamp"> '. $date .'</p>';
									$date .= '<input type="hidden" name="dsgnwrks_insta_options['.$user.'][date-filter]" value="'. $date_filter .'" />';

									$month = '<select id="instagram-mm" name="dsgnwrks_insta_options['.$user.'][mm]">\n';
									$month .= '<option value="">'. __( 'Month', 'dsgnwrks' ) .'</option>';
									for ( $i = 1; $i < 13; $i = $i +1 ) {
										$monthnum = zeroise($i, 2);
										$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
										if ( $this->user_option( 'mm' ) && $i == $this->user_option( 'mm' ) )
											$month .= ' selected="selected"';
										$month .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
									}
									$month .= '</select>';

									$day = '<select style="width: 5em;" id="instagram-dd" name="dsgnwrks_insta_options['.$user.'][dd]">\n';
									$day .= '<option value="">'. __( 'Day', 'dsgnwrks' ) .'</option>';
									for ( $i = 1; $i < 32; $i = $i +1 ) {
										$daynum = zeroise($i, 2);
										$day .= "\t\t\t" . '<option value="' . $daynum . '"';
										if ( $this->user_option( 'dd' ) && $i == $this->user_option( 'dd' ) )
											$day .= ' selected="selected"';
										$day .= '>' . $daynum;
									}
									$day .= '</select>';

									$year = '<select style="width: 5em;" id="instagram-yy" name="dsgnwrks_insta_options['.$user.'][yy]">\n';
									$year .= '<option value="">'. __( 'Year', 'dsgnwrks' ) .'</option>';
									for ( $i = date( 'Y' ); $i >= 2010; $i = $i -1 ) {
										$yearnum = zeroise($i, 4);
										$year .= "\t\t\t" . '<option value="' . $yearnum . '"';
										if ( $this->user_option( 'yy' ) && $i == $this->user_option( 'yy' ) )
											$year .= ' selected="selected"';
										$year .= '>' . $yearnum;
									}
									$year .= '</select>';


									echo '<div class="timestamp-wrap">';
									/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
									printf(__('%1$s %2$s %3$s %4$s'), $date, $month, $day, $year );

									if ( $complete_date ) {
										echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options['.$user.'][remove-date-filter]" value="yes" /> <em> '. __( 'Remove filter', 'dsgnwrks' ) .'</em></label></p>';
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
									<input type="checkbox" name="dsgnwrks_insta_options[<?php echo $user; ?>][feat_image]" <?php checked( 'yes' === $this->user_option( 'feat_image' ) ); ?> value="yes"/>
								</td>
								</tr>

								<?php
								// Our auto-import interval text. "Manual" if not set
								$interval = ! $this->get_option( 'frequency' ) || 'never' == $this->get_option( 'frequency' ) ? 'Manual' : strtolower( $this->schedules[ $this->get_option( 'frequency' ) ]['display'] );
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
									<input type="checkbox" name="dsgnwrks_insta_options[<?php echo $user; ?>][auto_import]" <?php checked( 'yes' === $this->user_option( 'auto_import' ) ); ?> value="yes"/>
								</td>
								</tr>

								<tr valign="top">
								<th scope="row">
									<strong><?php _e( 'Post Title:', 'dsgnwrks' ); ?></strong><br/><?php _e( 'Add the imported Instagram data using these custom tags:', 'dsgnwrks' ); ?><br/><code>**insta-text**</code>, <code>**insta-location**</code>, <code>**insta-filter**</code>
								</th>
								<td><input type="text" name="dsgnwrks_insta_options[<?php echo $user; ?>][post-title]" value="<?php echo $this->user_option( 'post-title', '**insta-text**' ); ?>" />
								</td>
								</tr>

								<tr valign="top">
								<td colspan="2">
									<p><strong><?php _e( 'Post Content:', 'dsgnwrks' ); ?></strong><br/><?php _e( 'Add the imported Instagram data using these custom tags:', 'dsgnwrks' ); ?><br/><code>**insta-text**</code>, <code>**insta-image**</code>, <code>**insta-embed-image**</code>, <code>**insta-embed-video**</code>, <code>**insta-image-link**</code>, <code>**insta-link**</code>, <code>**insta-location**</code>, <code>**insta-filter**</code></p>
									<p><?php _e( 'Or use these conditional tags:', 'dsgnwrks' ); ?><br/><code>[if-insta-text]<?php _e( 'Photo Caption:', 'dsgnwrks' ); ?> **insta-text**[/if-insta-text]</code><br/><code>[if-insta-location]<?php _e( 'Photo taken at:', 'dsgnwrks' ); ?> **insta-location**[/if-insta-location]</code></p>
									<?php
									$args = array(
										'textarea_name' => 'dsgnwrks_insta_options['.$user.'][post_content]',
										'editor_class' => 'post_text',
										'textarea_rows' => 6,
										'wpautop' => false
									);
									wp_editor( $this->user_option( 'post_content', '' ), 'dsgnwrks_insta_options_'.$user.'_post_content', $args );
									?>

								</td>
								</tr>

								<tr valign="top">
								<th scope="row"><strong><?php _e( 'Import to Post-Type:', 'dsgnwrks' ); ?></strong></th>
								<td>
									<select class="instagram-post-type" id="instagram-post-type-<?php echo $user; ?>" name="dsgnwrks_insta_options[<?php echo $user; ?>][post-type]">
										<?php
										$args = array(
										  'public' => true,
										);
										$post_types = get_post_types( $args );
										$cur_post_type = $this->user_option( 'post-type', '' );
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
									<select id="instagram-draft-<?php echo $user; ?>" name="dsgnwrks_insta_options[<?php echo $user; ?>][draft]">
										<?php
										$draft_status = $this->user_option( 'draft', '' );
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
									wp_dropdown_users( array( 'name' => 'dsgnwrks_insta_options['.$user.'][author]', 'selected' => $this->user_option( 'author', '' ) ) );
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
									$hash_tax = $this->user_option( 'hashtags_as_tax', '' );

									echo '<select id="dsgnwrks_insta_options-'.$user.'-hashtags_as_tax" name="dsgnwrks_insta_options['.$user.'][hashtags_as_tax]">';
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
								if ( current_theme_supports( 'post-formats' ) && post_type_supports( ( $cur_post_type ? $cur_post_type : 'post' ), 'post-formats' ) ) {
									$post_formats = get_theme_support( 'post-formats' );

									if ( is_array( $post_formats[0] ) ) {
										$post_format = $this->user_option( 'post_format', '' );

										// Add in the current one if it isn't there yet, in case the current theme doesn't support it
										if ( $post_format && ! in_array( $post_format, $post_formats[0] ) ) {
											$post_formats[0][] = $post_format;
										}
										?>
										<tr valign="top" class="taxonomies-add taxonomy-post_format">
										<th scope="row"><strong><?php _e( 'Select Imported Posts Format:', 'dsgnwrks' );?></strong></th>
										<td>
											<select id="dsgnwrks_insta_options[<?php echo $user; ?>][post_format]" name="dsgnwrks_insta_options[<?php echo $user; ?>][post_format]">
												<option value="0" <?php selected( ! $post_format ); ?>><?php _e( 'Standard', 'dsgnwrks' ); ?></option>
												<?php foreach ( $post_formats[0] as $format ) : ?>
												<option value="<?php echo esc_attr( $format ); ?>" <?php selected( $post_format, $format ); ?>><?php echo esc_html( get_post_format_string( $format ) ); ?></option>

												<?php endforeach; ?><br />
											</select>
										</td>
										</tr>
										<?php
									}
								}


								$placeholder = __( 'e.g. Instagram, Life, dog, etc', 'dsgnwrks' );
								foreach ( $taxonomies as $key => $tax ) {

									if ( $tax->label == __( 'Format' ) ) {
										continue;
									}

									if ( $tax->name == 'post_tag' ) {
										$placeholder = __( 'e.g. beach, sunrise', 'dsgnwrks' );
									}

									$tax_section_label = '<strong>'. sprintf( __( '%s to apply to imported posts.', 'dsgnwrks' ), $tax->label ) .'</strong><br/>'. sprintf( __( 'Please separate %s with commas', 'dsgnwrks' ), strtolower( $tax->label ) ) ."\n";
									$tax_section_input = '<input type="text" placeholder="'.$placeholder.'" name="dsgnwrks_insta_options['.$user.']['.$tax->name.']" value="'. esc_attr( $this->user_option( $tax->name, '' ) ) .'" />'."\n";

									?>
									<tr valign="top" class="taxonomies-add taxonomy-<?php echo $tax->name; ?>">
									<th scope="row">
										<?php echo apply_filters( 'dsgnwrks_instagram_tax_section_label', $tax_section_label, $tax ); ?>
									</th>
									<td>
										<?php echo apply_filters( 'dsgnwrks_instagram_tax_section_input', $tax_section_input, $tax, $user, $this->get_options() ); ?>
									</td>
									</tr>
									<?php
								}

								// Add extra fields to the user settings page.
								// ie. $extra_fields[] = array( 'title' => 'My Title', 'input' => '<input />' );
								if ( $extra_fields = apply_filters( 'dsgnwrks_instagram_extra_user_fields', array(), $this->get_option( $user ), $user ) ) {
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
								$trans = get_transient( $user .'-instaimportdone' );

								?>
								<tr valign="top" class="info">
									<th colspan="2">
									<input type="hidden" name="dsgnwrks_insta_options[username]" value="replaceme" />
									<?php
									foreach ( $userdata as $data ) {
										echo '<input type="hidden" name="dsgnwrks_insta_options['.$user.']['.$data.']" value="'. $this->user_option( $data , '' ) .'" />';
									}

									if ( $trans ) {
										echo '<p>'. sprintf( __( 'Last updated: %s', 'dsgnwrks' ), $trans ) .'</p>';
									}
									?>
									</th>
								</tr>
							</table>
							<p class="save-warning warning user-<?php echo $user; ?>"><?php _e( 'You\'ve changed settings.', 'dsgnwrks' );?> <strong><?php _e( 'please "Save" them before importing.', 'dsgnwrks' ); ?></strong></p>
							<p class="submit">
								<input type="submit" id="save-<?php echo sanitize_title( $user ); ?>" name="save" class="button-primary save" value="<?php _e( 'Save' ) ?>" />
								<a href="<?php echo $this->instimport_link( $this->user_option( 'full_username' ) ); ?>" class="button-secondary import-button" id="import-<?php echo $user; ?>" data-instagramuser="<?php echo urlencode( $this->user_option( 'full_username' ) ); ?>"><?php _e( 'Import', 'dsgnwrks' ); ?></a>
								<p class="spinner-wrap hidden"><?php _e( '...Importing. This could take a while.', 'dsgnwrks' ); ?><span class="spinner"></span></p><strong class="warning hidden"><?php _e( 'ERROR!', 'dsgnwrks' ); ?></strong>
							</p>
						</div>
						<?php
					}

					?>
					<div id="universal-options" class="help-tab-content instagram-importer <?php echo __( 'Plugin Options', 'dsgnwrks' ) == $current_user ? ' active' : ''; ?>">
						<?php do_action( 'dsgnwrks_instagram_univeral_options', $this->get_options() ); ?>
					</div>
					</form>
					<?php
				} else {
					$message = '<p>'. __( 'Welcome to the Instagram Importer! Click to be taken to Instagram\'s site to securely authorize this plugin for use with your account.', 'dsgnwrks' ) .'</p>';
					$this->settings_user_form( $users, $message );
				}

				if ( ! $nogo ) { ?>
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
