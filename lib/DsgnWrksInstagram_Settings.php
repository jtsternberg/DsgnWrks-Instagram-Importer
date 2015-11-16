<?php

/**
 * Handles settings page for DsgnWrks Instagram Importer
 * @since  1.3.0
 */
class DsgnWrksInstagram_Settings extends DsgnWrksInstagram_Debug {

	/**
	 * @since 1.3.0
	 */
	public function __construct( $core ) {
		$this->core = $core;
		$this->schedules = wp_get_schedules();
	}

	/**
	 * hooks to 'admin_menu', adds our submenu page and corresponding scripts/styles
	 * @since  1.1.0
	 */
	public function settings() {
		// create admin page
		$plugin_page_hook = add_submenu_page( 'tools.php', $this->core->plugin_name, __( 'Instagram Importer', 'dsgnwrks' ), 'manage_options', $this->core->plugin_id, array( $this, 'settings_page' ) );
		// enqueue styles
		add_action( 'admin_print_styles-' . $plugin_page_hook, array( $this, 'styles' ) );
		// enqueue scripts
		add_action( 'admin_print_scripts-' . $plugin_page_hook, array( $this, 'scripts' ) );
		// run our importer only on our admin page when clicking "import"
		add_action( 'admin_head-'. $plugin_page_hook, array( $this, 'fire_importer' ) );
		// run our importer only on our admin page when clicking "import"
		add_action( 'dsgnwrks_instagram_univeral_options', array( $this, 'universal_options_form' ) );
	}

	/**
	 * Enqueue our admin page's CSS
	 * @since  1.1.0
	 */
	public function styles() {
		wp_enqueue_style( 'dsgnwrks-instagram-importer-admin', $this->core->plugin_url .'css/admin.css', false, $this->core->plugin_version );
	}

	/**
	 * Enqueue our admin page's JS
	 * @since  1.1.0
	 */
	public function scripts() {
		wp_enqueue_script( 'dsgnwrks-instagram-importer-admin', $this->core->plugin_url .'js/admin.js', array( 'jquery' ), $this->core->plugin_version );

		$data = array(
			'delete_text' => __( 'Are you sure you want to delete user', 'dsgnwrks' ),
			'logout_text' => __( 'Logging out of Instagram', 'dsgnwrks' )
		);
		// get registered post-types
		$cpts = get_post_types( array( 'public' => true ) );
		foreach ( $cpts as $key => $cpt ) {
			// get registered taxonomies
			$taxes = get_object_taxonomies( $cpt );
			if ( ! empty( $taxes ) ) {
				$data['cpts'][ $cpt ][] = $taxes;
			}
		}
		// and save that data for use in our script
		if ( ! empty( $data ) ) {
			wp_localize_script( 'dsgnwrks-instagram-importer-admin', 'dwinstagram', $data );
		}
	}

	/**
	 * hooks into our admin page's head and fires the importer if requested
	 * @since  1.1.0
	 */
	public function fire_importer() {
		if ( isset( $_GET['instaimport'] ) ) {
			add_action( 'all_admin_notices', array( $this->core, 'import' ) );
		}
	}

	/**
	 * a link to instagram import admin page with user pre-selected
	 * @since  1.1.0
	 * @param  string $id Instagram user id
	 * @return string     Instagram importer options page with selected user
	 */
	protected function instimport_link( $id ) {
		return add_query_arg( array( 'page' => $this->core->plugin_id, 'instaimport' => urlencode( $id ) ), admin_url( $GLOBALS['pagenow'] ) );
	}

	/**
	 * Creates our admin page
	 * @since  1.1.0
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->normalize_user_settings();
		require_once( $this->core->plugin_path . 'lib/settings.php' );
	}

	public function display_notices() {
		$has_notice = get_transient( 'instagram_notification' );
		$notice = '';

		if ( $has_notice && isset( $_GET['notice'] ) && 'success' == $_GET['notice'] ) {
			$notice = __( 'You\'ve successfully connected to Instagram!', 'dsgnwrks' );
		} elseif ( $has_notice && isset( $_GET['class'] ) && 'error' == $_GET['class'] ) {
			$notice = __( 'There was an authorization error. Try again?', 'dsgnwrks' );
		}

		if ( $notice ) {
			echo '<div id="message" class="'. $class .'"><p>'. $notice .'</p></div>';
		}

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
	public function universal_options_form() {
		$remove_hashtags = $this->get_option( 'remove_hashtags' );
		?>
		<table class="form-table">
			<tbody>
				<tr valign="top" class="info">
					<th colspan="2">
						<h3><img class="alignleft" src="<?php echo $this->core->plugin_url .'images/merge.png'; ?>" width="83" height="66"><?php _e( 'Universal Import Options', 'dsgnwrks' ); ?></h3>
						<p><?php _e( 'Please select the general import options below.', 'dsgnwrks' ); ?></p>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Set Auto-import Frequency:', 'dsgnwrks' ); ?></strong></th>
					<td>
						<select name="dsgnwrks_insta_options[frequency]">
							<option value="never" <?php echo selected( $this->get_option( 'frequency' ), 'never' ); ?>><?php _e( 'Manual', 'dsgnwrks' ); ?></option>
							<?php
							foreach ( $this->schedules as $key => $value ) {
								echo '<option value="'. $key .'"'. selected( $this->get_option( 'frequency' ), $key, false ) .'>'. $value['display'] .'</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr valign="top" class="remove-hashtags">
					<th scope="row"><strong><?php _e( 'Remove #hashtags when saving post:', 'dsgnwrks' ); ?></strong></th>
					<td>
						<label><input type="checkbox" name="dsgnwrks_insta_options[remove_hashtags][post_title]" <?php checked( isset( $remove_hashtags['post_title'] ) && $remove_hashtags['post_title'] ); ?> value="yes"/>&nbsp;Title</label>
						<label><input type="checkbox" name="dsgnwrks_insta_options[remove_hashtags][post_content]" <?php checked( isset( $remove_hashtags['post_content'] ) && $remove_hashtags['post_content'] ); ?> value="yes"/>&nbsp;Content</label>
						<label><input type="checkbox" name="dsgnwrks_insta_options[remove_hashtags][post_excerpt]" <?php checked( isset( $remove_hashtags['post_excerpt'] ) && $remove_hashtags['post_excerpt'] ); ?> value="yes"/>&nbsp;Excerpt</label>

					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button-primary save" value="<?php _e( 'Save', 'dsgnwrks' ) ?>" />
		</p>
		<?php
	}

	function normalize_user_settings() {

		$update = false;

		foreach ( $this->get_users() as $key => $user ) {

			$remove_date_filter = $this->get_user_option( $user, 'remove-date-filter' );
			if ( 'yes' == $remove_date_filter ) {
				$this->all_opts[ $user ]['mm'] = '';
				$this->all_opts[ $user ]['dd'] = '';
				$this->all_opts[ $user ]['yy'] = '';
				$this->all_opts[ $user ]['date-filter'] = 0;
				$this->all_opts[ $user ]['remove-date-filter'] = '';
				update_option( 'dsgnwrks_insta_options', $this->all_opts );
				$update = true;
			}

			$remove_tag_filter = $this->get_user_option( $user, 'remove-tag-filter' );
			if ( $remove_tag_filter ) {
				$this->all_opts[ $user ]['tag-filter'] = '';
				$this->all_opts[ $user ]['remove-tag-filter'] = '';
				$update = true;
			}

			$tag_filter = $this->get_user_option( $user, 'tag-filter' );
			if ( $tag_filter ) {
				$this->all_opts[ $user ]['remove-tag-filter'] = '';
				$update = true;
			}
		}

		if ( $update ) {
			update_option( 'dsgnwrks_insta_options', $this->all_opts );
		}
	}

	public function user_option( $key, $default = false ) {
		if ( ! isset( $this->userid ) ) {
			wp_die( 'Missing User' );
		}

		$option = $this->get_user_option( $this->userid, $key );

		return $option ? $option : $default;
	}

	public function get_user_option( $user, $key ) {
		$opts = $this->get_option( $user );

		$user_data = $this->get_cached_user_data( $opts );

		if ( $user_data && isset( $user_data->{$key} ) ) {
			return $user_data->{$key};
		}

		return $opts && array_key_exists( $key, $opts ) ? $opts[ $key ] : false;
	}

	public function get_option( $key ) {
		$opts = $this->get_options();
		return array_key_exists( $key, $opts ) ? $opts[ $key ] : false;
	}

	public function get_options() {
		if ( isset( $this->all_opts ) ) {
			return $this->all_opts;
		}
		$this->all_opts = get_option( 'dsgnwrks_insta_options', array() );
		$this->all_opts = empty( $this->all_opts ) || ! is_array( $this->all_opts ) ? array() : (array) $this->all_opts;

		return $this->all_opts;
	}

	/**
	 * Attempt to get user profile data from actual API to augment the settings (and keep them updated)
	 *
	 * @since  1.3.3
	 *
	 * @param  array $opts  Array of user options
	 *
	 * @return object|false User data object or false
	 */
	public function get_cached_user_data( $opts ) {
		$user_data = false;

		if ( ! isset( $opts['access_token'], $opts['id'] ) ) {
			return $user_data;
		}

		if ( $user_data = get_transient( 'dw_instauser_'. $opts['id'] ) ) {
			return $user_data;
		}

		$url = $this->core->instagram_api . $opts['id'] . '?access_token=' . $opts['access_token'];
		$response = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( $response ) {
			$response = json_decode( $response );
			if ( isset( $response->data ) ) {
				$user_data = isset( $response->data );
				// Store to transient for a week.
				set_transient( 'dw_instauser_'. $opts['id'], $user_data, WEEK_IN_SECONDS );
			}
		}

		return $user_data;
	}

	public function get_users() {
		return $this->core->get_users();
	}

}
