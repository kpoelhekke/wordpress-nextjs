<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.dotred.nl
 * @since             1.0.0
 * @package           Wordpress_Nextjs
 *
 * @wordpress-plugin
 * Plugin Name:       Wordpress NextJS
 * Plugin URI:        https://wordpress-nextjs.dev
 * Description:       Wordpress NextJS plugin. Transforms rest API responses and preview authentication integration.
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

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WORDPRESS_NEXTJS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordpress-nextjs-activator.php
 */
function activate_wordpress_nextjs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-nextjs-activator.php';
	Wordpress_Nextjs_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordpress-nextjs-deactivator.php
 */
function deactivate_wordpress_nextjs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-nextjs-deactivator.php';
	Wordpress_Nextjs_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wordpress_nextjs' );
register_deactivation_hook( __FILE__, 'deactivate_wordpress_nextjs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-nextjs.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wordpress_nextjs() {

	$plugin = new Wordpress_Nextjs();
	$plugin->run();

}
run_wordpress_nextjs();
