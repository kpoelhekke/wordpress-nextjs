<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wordpress_Nextjs
 * @subpackage Wordpress_Nextjs/public
 * @author     Koen Poelhekke <info@dotred.nl>
 */
class Wordpress_Nextjs_Api {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	public function __construct() {
		$this->options = get_option( 'wordpress-nextjs' );

		add_filter( 'page_link', array( $this, 'remove_base_url' ) );
		add_filter( 'post_link', array( $this, 'remove_base_url' ) );
		add_filter( 'post_type_link', array( $this, 'remove_base_url' ) );
	}

	/**
	 * Remove the base url from the permalinks
	 *
	 * @param string $url
	 *
	 * @since 1.0.0
	 */
	public function remove_base_url( $url ) {
		return untrailingslashit( str_replace( home_url(), '', $url ) );
	}

	/**
	 * Adds a base64 preview image to the image response
	 *
	 * @param $image
	 *
	 * @since 1.0.0
	 */
	public function add_base64_image_preview( $image ) {
		if ( isset( $this->options['base64_preview'] ) && $this->options['base64_preview'] ) {
			if ( is_array( $image ) ) {
				$image_src = wp_get_attachment_image_src( $image['id'], 'base64' );

				if ( $image_src ) {
					$upload_dir = wp_upload_dir();
					$image_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $image_src[0] );
					$mime_type  = get_post_mime_type( $image['id'] );
					$imgbinary  = fread( fopen( $image_path, 'r' ), filesize( $image_path ) );

					$image['base64'] = 'data:' . $mime_type . ';base64,' . base64_encode( $imgbinary );
				}
			}
		}

		return $image;
	}

	/**
	 * Adds a srcset and sizes property to the image response
	 *
	 * @param $image
	 *
	 * @since 1.0.0
	 */
	public function add_image_srcsets( $image ) {
		if ( isset( $this->options['image_srcsets'] ) && $this->options['image_srcsets'] ) {
			if ( is_array( $image ) ) {
				$srcset       = wp_get_attachment_image_srcset( $image['id'] );
				$srcset_sizes = wp_get_attachment_image_sizes( $image['id'], 'full' );

				$image['srcset']       = $srcset;
				$image['srcset_sizes'] = $srcset_sizes;
			}
		}

		return $image;
	}
}
