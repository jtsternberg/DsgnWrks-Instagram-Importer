<?php

/**
 * Settings Validation class for DsgnWrks Instagram Importer
 * @since  1.3.0
 */
class DsgnWrksInstagram_Settings_Validation {

	/**
	 * Buld the object
	 * @since 1.3.0
	 * @param array $opts Options array to validate
	 */
	public function __construct( $core, $opts ) {
		$this->core          = $core;
		$this->validate_opts = $opts;
		// get existing saved options to check against
		$this->old_options = $this->core->settings->get_options();
	}
	/**
	 * Validate each of our user options with an appropriate filter
	 * @since  1.1.0
	 * @param  array  $opts   array of options to be saved
	 * @return array          sanitized options array
	 */
	public function clean() {

		if ( empty( $this->validate_opts ) || ! is_array( $this->validate_opts ) ) {
			return $this->validate_opts;
		}

		// loop through options (users)
		foreach ( $this->validate_opts as $user => $useropts ) {

			$this->sanitize_all_user_settings( $user, $useropts );

			// if our 'frequency' interval was set
			if ( 'frequency' === $user ) {
				$this->save_frequency_setting( $user, $useropts );
			}
			// if our 'remove_hashtags' was set
			elseif ( 'remove_hashtags' === $user ) {
				$this->save_hashtag_setting( $user, $useropts );
			}
		}
		// allow plugins to add options to save
		$this->validate_opts = apply_filters( 'dsgnwrks_instagram_option_save', $this->validate_opts, $this->old_options );

		// ok, we're done validating the options, so give them back
		return $this->validate_opts;
	}

	public function sanitize_all_user_settings( $user, $useropts ) {
		// loop through options (user's options)
		if ( empty( $useropts ) || ! is_array( $useropts ) ) {
			return;
		}

		foreach ( $useropts as $key => $opt ) {
			$this->validate_opts[ $user ][ $key ] = $this->sanitize_user_setting( $user, $key, $opt );
		}
	}

	public function sanitize_user_setting( $user, $key, $opt ) {

		switch ( $key ) {
			case 'date-filter' :
				if (
					empty( $this->validate_opts[ $user ]['mm'] )
					&& empty( $this->validate_opts[ $user ]['dd'] )
					&& empty( $this->validate_opts[ $user ]['yy'] )
					|| ! empty( $this->validate_opts[ $user ]['remove-date-filter'] )
				) {
					return 0;
				}

				return strtotime( $this->validate_opts[ $user ]['mm'] .'/'. $this->validate_opts[ $user ]['dd'] .'/'. $this->validate_opts[ $user ]['yy'] );

			case 'yy' :
			case 'mm' :
			case 'dd' :
				if (
					empty( $this->validate_opts[ $user ]['mm'] )
					&& empty( $this->validate_opts[ $user ]['dd'] )
					&& empty( $this->validate_opts[ $user ]['yy'] )
					|| !empty( $this->validate_opts[ $user ]['remove-date-filter'] ) ) {
					return '';
				}

				return $this->filter( $opt, 'absint', '' );

			case 'pw' :
				return $opt;

			case 'post-type' :
				return $this->filter( $opt, '', 'post' );

			case 'draft' :
				return $this->filter( $opt, '', 'draft' );

			case 'post_content' :
				return $this->filter( $opt, 'wp_kses_post' );

			case 'feat_image' :
			case 'auto_import' :
				// checkboxes
				return 'yes' == $opt ? 'yes' : false;

			default:
				// defaults to esc_attr() validation
				return $this->filter( $opt );
		}

	}

	public function save_frequency_setting( $user, $useropts ) {
		$this->validate_opts[ $user ] = $this->filter( $useropts );

		// and if our newly saved 'frequency' is different
		// clear the previously scheduled hook
		if ( ! isset( $this->old_options['frequency'] ) || $this->validate_opts[ $user ] != $this->old_options['frequency'] ) {
			wp_clear_scheduled_hook( $this->core->getter( 'pre' ) .'cron' );
		}
	}

	public function save_hashtag_setting( $user, $useropts ) {
		// checkboxes
		foreach ( $useropts as $filter => $value ) {
			$this->validate_opts[ $user ][ $filter ] = $value == 'yes' ? 'yes' : false;
		}
	}

	/**
	 * Helper function to sanitized variables using whitelisted filters and set a default
	 * @since  1.1.0
	 * @param  mixed  $opt    option value to be saved
	 * @param  string $filter filter to run option through
	 * @param  mixed  $else   default value if no option value
	 * @return mixed          sanitized option value
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
				return ! empty( $opt );
			default:
				return esc_attr( $opt );
		}
	}

}
