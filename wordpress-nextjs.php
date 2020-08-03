<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://www.dotred.nl
 * @since   1.0.0
 * @package Wordpress_Nextjs
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress NextJS
 * Plugin URI:        https://wordpress-nextjs.dev
 * Description:       WordPress NextJS plugin. Transforms rest API responses and preview authentication integration.
 * Version:           1.0.0
 * Author:            Koen Poelhekke
 * Author URI:        https://www.dotred.nl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordpress-nextjs
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WORDPRESS_NEXTJS_VERSION', '1.0.0' );
define( 'WORDPRESS_NEXTJS_OPTIONS_KEY', 'wordpress-nextjs' );
define( 'WORDPRESS_NEXTJS_LANGUAGE_DOMAIN', 'wordpress-nextjs' );

require __DIR__ . '/includes/class-wordpress-nextjs-fields.php';

require __DIR__ . '/class-wordpress-nextjs-admin.php';
require __DIR__ . '/class-wordpress-nextjs-api.php';

if ( is_admin() ) {
	new Wordpress_Nextjs_Admin();
} else {
	new Wordpress_Nextjs_Api();
}
