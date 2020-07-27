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
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Remove the base url from the permalinks
     *
     * @param string $url
     */
	public function remove_base_url($url) {

        return untrailingslashit(str_replace(home_url(), '', $url));

    }
}
