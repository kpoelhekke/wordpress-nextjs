<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.dotred.nl
 * @since      1.0.0
 *
 * @package    Wordpress_Nextjs
 * @subpackage Wordpress_Nextjs/public
 */

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
class Wordpress_Nextjs_Public {


	/**
	 * Remove the base url from the permalinks
	 *
	 * @param string $url
	 */
	public function remove_base_url( $url ) {

		return untrailingslashit( str_replace( home_url(), '', $url ) );
	}
}
