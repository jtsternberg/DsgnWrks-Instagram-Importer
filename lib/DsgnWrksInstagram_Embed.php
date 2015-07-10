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
		add_shortcode( 'dsgnwrks_instagram_embed', array( $this, 'instagram_embed' ) );
	}

	/**
	 * Shortcode handler that displays Instagram embed iframe
	 * Wraps Instagram url in the iframe that Instagram provides for embedding
	 *
	 * @since  1.2.6
	 * @param  array  $atts Attributes passed from shortcode
	 * @return string       Instagram embed iframe code
	 */
	public function instagram_embed( $atts = array() ) {
		if ( ! isset( $atts['src'] ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'src'               => '',
			'width'             => '612',
			'height'            => '710',
			'frameborder'       => '0',
			'scrolling'         => 'no',
			'allowtransparency' => 'true',
			'class'             => 'insta-image-embed',
		), $atts, 'dsgnwrks_instagram_embed' );

		$atts['src'] = esc_url( str_replace( 'embed/', '', str_replace( 'http://', '//', $atts['src'] ) ) . 'embed/' );

		if ( isset( $atts['type'] ) ) {
			$atts['class'] = 'video' == $atts['type'] ? str_replace( 'insta-image-embed', 'insta-video-embed', $atts['class'] ) : $atts['class'];
			unset( $atts['type'] );
		}

		$iframe_attributes = '';

		foreach ( $atts as $key => $value ) {
			$iframe_attributes .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		$embed_iframe = "\r\n".'<iframe'. $iframe_attributes .'></iframe>'."\r\n";

		return apply_filters( 'dsgnwrks_instagram_embed', $embed_iframe, $atts );
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
