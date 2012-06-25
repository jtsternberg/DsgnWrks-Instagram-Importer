<?php
global $user_ID;

if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

$opts = get_option( 'dsgnwrks_insta_options' );
$reg = get_option( 'dsgnwrks_insta_registration' );
$users = get_option( 'dsgnwrks_insta_users' );

$users = ( !empty( $users ) ) ? $users : array();

if ( !empty( $reg ) && $reg['badauth'] == 'good' && !in_array( $reg['user'], $users ) ) {
	$users[] = $reg['user'];
	$opts[$reg['user']]['pw'] = wp_hash_password( $reg['pw'] );

	update_option( 'dsgnwrks_insta_users', $users );
	update_option( 'dsgnwrks_insta_options', $opts );
	delete_option( 'dsgnwrks_insta_registration' );
	unset( $reg );
}

if ( !empty( $users ) && is_array( $users ) ) {
	foreach ( $users as $key => $user ) {

		if ( $opts[$user]['remove-date-filter'] == 'yes' ) {
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
		}

		if ( !empty( $opts[$user]['tag-filter'] ) ) {
			$opts[$user]['remove-tag-filter'] = '';
		}

		$complete[$user] = ( !empty( $opts[$user]['mm'] ) && !empty( $opts[$user]['dd'] ) && !empty( $opts[$user]['yy'] ) ) ? true : false;
	}

}

?>

<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2>DsgnWrks Instagram Importer Options</h2>
	<div id="screen-meta" style="display: block; ">
	<?php

	$nogo = $nofeed = false;
	// Setup our notifications
	if ( !empty( $reg['user'] ) && empty( $reg['pw'] ) ) {
		echo '<div id="message" class="error"><p>Please enter your Instagram password.</p></div>';
		$nofeed = true;
	} elseif ( empty( $reg['user'] ) && !empty( $reg['pw'] ) ) {
		echo '<div id="message" class="error"><p>Please enter your Instagram username.</p></div>';
		$nofeed = true;
	} elseif ( !empty( $reg['user'] ) && !empty( $reg['pw'] ) && !empty( $reg['badauth'] ) && $reg['badauth'] == 'error' ) {
		echo '<div id="message" class="error"><p>Couldn\'t find an instagram feed. Please check your username and password.</p></div>';
		$nofeed = true;
	} elseif ( empty( $reg['user'] ) && empty( $reg['pw'] ) && empty( $users ) ) {
		$nogo = true;
	} elseif ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
		echo '<div id="message" class="updated"><p>Settings Updated</p></div>';
	}

	?>
	<div class="clear"></div>

		<div id="contextual-help-wrap" class="hidden" style="display: block; ">
			<div id="contextual-help-back"></div>
			<div id="contextual-help-columns">
				<div class="contextual-help-tabs">
					<?php if ( empty( $reg ) && empty( $opts ) ) { ?>
						<h2>Get Started</h2>
					<?php } else { ?>
						<h2>Users</h2>
					<?php } ?>

					<ul>
						<?php
						if ( !empty( $users ) && is_array( $users ) ) {
							foreach ( $users as $key => $user ) {
								$id = str_replace( ' ', '', strtolower( $user ) );
								$class = ( !empty( $class ) || $nofeed == true ) ? '' : 'class="active"';
								?>
								<li id="tab-instagram-user-<?php echo $id; ?>" <?php echo $class; ?>>
									<a href="#instagram-user-<?php echo $id; ?>"><?php echo $user; ?></a>
								</li>
								<?php
							}
						} else {
							$user = 'Create User';
							$class = str_replace( ' ', '', strtolower( $user ) );
							?>
							<li id="tab-instagram-user-<?php echo $class; ?>" class="active">
								<a href="#instagram-user-<?php echo $class; ?>"><?php echo $user; ?></a>
							</li>
							<?php
						}

						if ( !$nogo ) { ?>
							<li id="tab-add-another-user" <?php echo ( $nofeed == true ) ? 'class="active"' : ''; ?>>
								<a href="#add-another-user">Add Another User</a>
							</li>
						<?php } ?>
					</ul>
				</div>

				<div class="contextual-help-tabs-wrap">

				<?php
				if ( !empty( $users ) && is_array( $users ) ) {
					?>
					<form class="instagram-importer" method="post" action="options.php">
					<?php settings_fields('dsgnwrks_instagram_importer_settings');

					foreach ( $users as $key => $user ) {
						$id = str_replace( ' ', '', strtolower( $user ) );
						$active = ( !empty( $active ) || $nofeed == true ) ? '' : ' active';
						?>
						<div id="instagram-user-<?php echo $id; ?>" class="help-tab-content<?php echo $active; ?>">

							<?php
							if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
								if ( !empty( $opts[$id]['mm'] ) || !empty( $opts[$id]['dd'] ) || !empty( $opts[$id]['yy'] ) ) {
									if ( !$complete[$id] ) echo '<div id="message" class="error"><p>Please select full date.</p></div>';
								}
							}

							?>

							<table class="form-table">

								<tr valign="top" class="info">
								<th colspan="2">
									<p>Successfully connected to Instagram &mdash; <span><a id="delete-<?php echo $id; ?>" class="delete-instagram-user" href="<?php echo add_query_arg( 'deleteuser', $id ); ?>">Delete User?</a></span></p>
									<p>Please select the import filter options below. If none of the options are selected, all photos for <strong><?php echo $id; ?></strong> will be imported. <em>(This could take a long time if you have a lot of shots)</em></p>
								</th>
								</tr>

								<tr valign="top">
								<th scope="row"><strong>Filter import by hashtag:</strong><br/>Will only import instagram shots with these hashtags.<br/>Please separate tags with commas.</th>
								<td><input type="text" placeholder="e.g. keeper, fortheblog" name="dsgnwrks_insta_options[<?php echo $id; ?>][tag-filter]" value="<?php echo $opts[$id]['tag-filter']; ?>" />
								<?php
									if ( !empty( $opts[$id]['tag-filter'] ) ) {
										echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options['.$id.'][remove-tag-filter]" value="yes" /> <em> Remove filter</em></label></p>';
									}
								?>
								</td>
								</tr>

								<tr valign="top">
								<th scope="row"><strong>Import from this date:</strong><br/>Select a date to begin importing your photos.</th>

								<td class="curtime">


									<?php
									global $wp_locale;

									if ( !empty( $opts[$id]['mm'] ) || !empty( $opts[$id]['dd'] ) || !empty( $opts[$id]['yy'] ) ) {
										if ( $complete[$id] ) {
											$date = '<strong>'. $wp_locale->get_month( $opts[$id]['mm'] ) .' '. $opts[$id]['dd'] .', '. $opts[$id]['yy'] .'</strong>';
												$opts[$id]['remove-date-filter'] = 'false';
												$opts[$id]['date-filter'] = strtotime( $opts[$id]['mm'] .'/'. $opts[$id]['dd'] .'/'. $opts[$id]['yy'] );
										} else {
											$date = '<span style="color: red;">Please select full date</span>';
										}
									}
									else $date = 'No date selected';
									$date = '<p style="padding-bottom: 2px; margin-bottom: 2px;" id="timestamp"> '. $date .'</p>';
									$date .= '<input type="hidden" name="dsgnwrks_insta_options['.$id.'][date-filter]" value="'. $opts[$id]['date-filter'] .'" />';

									$month = '<select id="instagram-mm" name="dsgnwrks_insta_options['.$id.'][mm]">\n';
									$month .= '<option value="">Month</option>';
									for ( $i = 1; $i < 13; $i = $i +1 ) {
										$monthnum = zeroise($i, 2);
										$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
										if ( $i == $opts[$id]['mm'] )
											$month .= ' selected="selected"';
										$month .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
									}
									$month .= '</select>';

									$day = '<select style="width: 5em;" id="instagram-dd" name="dsgnwrks_insta_options['.$id.'][dd]">\n';
									$day .= '<option value="">Day</option>';
									for ( $i = 1; $i < 32; $i = $i +1 ) {
										$daynum = zeroise($i, 2);
										$day .= "\t\t\t" . '<option value="' . $daynum . '"';
										if ( $i == $opts[$id]['dd'] )
											$day .= ' selected="selected"';
										$day .= '>' . $daynum;
									}
									$day .= '</select>';

									$year = '<select style="width: 5em;" id="instagram-yy" name="dsgnwrks_insta_options['.$id.'][yy]">\n';
									$year .= '<option value="">Year</option>';
									for ( $i = date( 'Y' ); $i >= 2010; $i = $i -1 ) {
										$yearnum = zeroise($i, 4);
										$year .= "\t\t\t" . '<option value="' . $yearnum . '"';
										if ( $i == $opts[$id]['yy'] )
											$year .= ' selected="selected"';
										$year .= '>' . $yearnum;
									}
									$year .= '</select>';


									echo '<div class="timestamp-wrap">';
									/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
									printf(__('%1$s %2$s %3$s %4$s'), $date, $month, $day, $year );

									if ( $complete[$id] == true ) {
										echo '<p><label><input type="checkbox" name="dsgnwrks_insta_options['.$id.'][remove-date-filter]" value="yes" /> <em> Remove filter</em></label></p>';
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
								//     <select id="instagram-image" name="dsgnwrks_insta_options['.$id.'][image]">';
								//         if ( $opts[$id]['image'] == 'feat-image') $selected1 = 'selected="selected"';
								//         echo '<option value="feat-image" '. $selected1 .'>Featured Image</option>';
								//         if ( $opts[$id]['image'] == 'content') $selected2 = 'selected="selected"';
								//         echo '<option value="content" '. $selected2 .'>Content</option>';
								//         if ( $opts[$id]['image'] == 'both') $selected3 = 'selected="selected"';
								//         echo '<option value="both" '. $selected3 .'>Both</option>';
								//     echo '</select>
								// </td>
								// </tr>';
								?>

								<tr valign="top">
								<th scope="row"><strong>Import to Post-Type:</strong></th>
								<td>
									<select id="instagram-post-type" name="dsgnwrks_insta_options[<?php echo $id; ?>][post-type]">
										<?php
										$args=array(
										  'public'   => true,
										);
										$post_types=get_post_types( $args );
										foreach ($post_types  as $post_type ) {
											?>
											<option value="<?php echo $post_type; ?>" <?php selected( $opts[$id]['post-type'], $post_type ); ?>><?php echo $post_type; ?></option>
											<?php
										}
										?>
									</select>
								</td>
								</tr>


								<tr valign="top">
								<th scope="row"><strong>Imported posts status:</strong></th>
								<td>
									<select id="instagram-draft" name="dsgnwrks_insta_options[<?php echo $id; ?>][draft]">
										<option value="draft" <?php selected( $opts[$id]['draft'], 'draft' ); ?>>Draft</option>
										<option value="publish" <?php selected( $opts[$id]['draft'], 'publish' ); ?>>Published</option>
										<option value="pending" <?php selected( $opts[$id]['draft'], 'pending' ); ?>>Pending</option>
										<option value="private" <?php selected( $opts[$id]['draft'], 'private' ); ?>>Private</option>
									</select>

								</td>
								</tr>

								<tr valign="top">
								<th scope="row"><strong>Assign posts to an existing user:</strong></th>
								<td>
									<?php
									wp_dropdown_users( array( 'name' => 'dsgnwrks_insta_options['.$id.'][author]', 'selected' => $opts[$id]['author'] ) );
									?>

								</td>
								</tr>

								<?php
								if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) {
									$post_formats = get_theme_support( 'post-formats' );

									if ( is_array( $post_formats[0] ) ) {
										$opts[$id]['post_format'] = !empty( $opts[$id]['post_format'] ) ? esc_attr( $opts[$id]['post_format'] ) : '';

										// Add in the current one if it isn't there yet, in case the current theme doesn't support it
										if ( $opts[$id]['post_format'] && !in_array( $opts[$id]['post_format'], $post_formats[0] ) )
											$post_formats[0][] = $opts[$id]['post_format'];
										?>
										<tr valign="top" class="taxonomies-add">
										<th scope="row"><strong>Select Imported Posts Format:</strong></th>
										<td>

											<select id="dsgnwrks_insta_options[<?php echo $id; ?>][post_format]" name="dsgnwrks_insta_options[<?php echo $id; ?>][post_format]">
												<option value="0" <?php selected( $opts[$id]['post_format'], '' ); ?>>Standard</option>
												<?php foreach ( $post_formats[0] as $format ) : ?>
												<option value="<?php echo esc_attr( $format ); ?>" <?php selected( $opts[$id]['post_format'], $format ); ?>><?php echo esc_html( get_post_format_string( $format ) ); ?></option>

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

									$opts[$id][$tax->name] = !empty( $opts[$id][$tax->name] ) ? esc_attr( $opts[$id][$tax->name] ) : '';

									$placeholder = 'e.g. Instagram, Life, dog, etc';

									if ( $tax->name == 'post_tag' )  $placeholder = 'e.g. beach, sunrise';
									?>
									<tr valign="top" class="taxonomies-add taxonomy-<?php echo $tax->name; ?>">
									<th scope="row"><strong><?php echo $tax->label; ?> to apply to imported posts.</strong><br/>Please separate <?php echo strtolower( $tax->label ); ?> with commas.</th>
									<td><input type="text" placeholder="<?php echo $placeholder; ?>" name="dsgnwrks_insta_options[<?php echo $id; ?>][<?php echo $tax->name; ?>]" value="<?php echo $opts[$id][''. $tax->name]; ?>" />
									</td>
									</tr>
									<?php

								}

								echo '<input type="hidden" name="dsgnwrks_insta_options['.$id.'][pw]" value="'. $opts[$id]['pw'] .'" />';

								$trans = get_transient( $id .'-instaimportdone' );

								if ( $trans ) { ?>
									<tr valign="top" class="info">
									<th colspan="2">
										<?php echo '<p>Last updated: '. $trans .'</p>'; ?>

									</th>
									</tr>
								<?php } ?>
							</table>

							<p class="submit">
								<input type="submit"  name="save" class="button-primary" value="<?php _e( 'Save' ) ?>" />
								<?php
								$importlink = dsgnwrks_get_instimport_link( $id );
								?>
								<a href="<?php echo $importlink; ?>" class="button-secondary import-button" id="import-<?php echo $id; ?>">Import</a>
							</p>
						</div>
						<?php
					}
					?>
					</form>
					<form method="post" action="<?php echo dsgnwrks_get_instimport_link( $id ); ?>" class="dw-pw-form">
						<label>Please enter your Instagram password again to import
						<input type="password" name="pwcheck" value=""></label>
						<input type="submit" value="Import" class="button-secondary">
					</form>
					<?php
				} else {
					$message = 'Welcome to Instagram Importer! Enter your Instagram username and password to authenticate the plugin, and we\'ll get started.';
					dsgnwrks_settings_user_form( $reg, true, $message );
				}

				if ( !$nogo ) { ?>
					<div id="add-another-user" class="help-tab-content <?php echo ( $nofeed == true ) ? ' active' : ''; ?>">
						<?php dsgnwrks_settings_user_form( $reg ); ?>
					</div>
				<?php } ?>
				</div>

				<div class="contextual-help-sidebar">
					<p class="jtsocial"><a class="jtpaypal" href="http://j.ustin.co/rYL89n" target="_blank">Contribute<span></span></a>
						<a class="jttwitter" href="http://j.ustin.co/wUfBD3" target="_blank">Follow me on Twitter<span></span></a>
						<a class="jtemail" href="http://j.ustin.co/scbo43" target="blank">Contact Me<span></span></a>
					</p>
				</div>

			</div>
		</div>
	</div>
</div>

<?php
function dsgnwrks_settings_user_form( $reg, $echo = true, $message = 'Enter the Instagram username and password of another user whose photos you would like to import.' ) {

	$id = 'dsgnwrks_insta_registration[user]';
	$id2 = 'dsgnwrks_insta_registration[pw]';

	?>
	<form class="instagram-importer" method="post" action="options.php">
		<?php settings_fields('dsgnwrks_instagram_importer_users'); ?>
		<table class="form-table">
			<p><?php echo $message; ?></p>
			<tr valign="top">
			<th scope="row"><label for="<?php echo $id; ?>"><strong>Instagram Username:</strong></label></th>
			<td><input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php if ( $echo == true ) echo esc_attr( $reg['user'] ); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row"><label for="<?php echo $id2; ?>"><strong>Instagram Password:</strong></label></th>
			<td><input type="password" id="<?php echo $id2; ?>" name="<?php echo $id2; ?>" value="<?php if ( $echo == true ) echo esc_attr( $reg['pw'] ); ?>" /></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button-primary" value="<?php echo _e( 'Authenticate' ) ?>" />
		</p>
	</form>

	<?php
}

function dsgnwrks_get_instimport_link( $id ) {
	return add_query_arg( 'instaimport', $id, add_query_arg( 'page', DSGNWRKSINSTA_ID, admin_url( $GLOBALS['pagenow'] ) ) );
}