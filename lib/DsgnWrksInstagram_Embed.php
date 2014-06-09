<?php

/**
 * Handles embed markup and shortcode for DsgnWrks Instagram Importer
 * @since  1.3.0
 */
class DsgnWrksInstagram_Embed {

	/**
	 * @since 1.3.0
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Hook our shotcode in
	 * @since 1.3.0
	 */
	public function hook_shortcode() {
		add_shortcode( 'dsgnwrks_instagram_embed', array( $this, 'shortcode_handler' ) );
	}

	/**
	 * Shortcode that displays Instagram embed iframe
	 * @since  1.2.6
	 * @param  array  $atts Attributes passed from shortcode
	 * @return string       Concatenated shortcode output (Iframe embed code)
	 */
	public function shortcode_handler( $atts ) {
		if ( ! isset( $atts['src'] ) ) {
			return '';
		}
		return $this->instagram_embed( $atts['src'] );
	}

	/**
	 * Wraps Instagram url in the iframe that Instagram provides for embedding
	 * @since  1.2.6
	 * @param  string $url Instagram media URL
	 * @return string      Instagram embed iframe code
	 */
	public function instagram_embed( $url = '' ) {

		if ( !$url && !isset( $this->core->pic->link ) )
			return false;
		if ( !$url && isset( $this->core->pic->link ) )
			$url = $this->core->pic->link;

		$url = str_replace( 'embed/', '', str_replace( 'http://', '//', esc_url( $url ) ) );

		return "\r\n".'<iframe src="'. $url .'embed/" width="612" height="710" frameborder="0" scrolling="no" allowtransparency="true"></iframe>'."\r\n";
	}

	/**
	 * Returns Instagram embed shortcode
	 * @since  1.2.6
	 * @return string Instagram embed shortcode
	 */
	public function instagram_embed_src( $type = 'video' ) {
		return '[dsgnwrks_instagram_embed src="'. esc_url( $this->core->pic->link ) .'" type="'. $type .'"]';
	}

	/**
	 * Checks if post content template contains the embed tags.
	 * @since  1.2.6
	 */
	public function has_embed( $type = '' ) {
		return false !== stripos( $this->core->import['post_content'], '**insta-embed-'. $type );
	}

}
