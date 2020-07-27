<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://www.dotred.nl
 * @since 1.0.0
 *
 * @package    Wordpress_Nextjs
 * @subpackage Wordpress_Nextjs/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wordpress_Nextjs
 * @subpackage Wordpress_Nextjs/includes
 * @author     Koen Poelhekke <info@dotred.nl>
 */
class Wordpress_Nextjs {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Wordpress_Nextjs_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'WORDPRESS_NEXTJS_VERSION' ) ) {
			$this->version = WORDPRESS_NEXTJS_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'wordpress-nextjs';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}//end __construct()


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wordpress_Nextjs_Loader. Orchestrates the hooks of the plugin.
	 * - Wordpress_Nextjs_i18n. Defines internationalization functionality.
	 * - Wordpress_Nextjs_Admin. Defines all hooks for the admin area.
	 * - Wordpress_Nextjs_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/*
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-nextjs-loader.php';

		/*
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordpress-nextjs-i18n.php';

		/*
		 * The class responsible for defining all actions that occur in the settings area.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wordpress-nextjs-settings.php';

		/*
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wordpress-nextjs-public.php';

		$this->loader = new Wordpress_Nextjs_Loader();

	}//end load_dependencies()


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wordpress_Nextjs_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Wordpress_Nextjs_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}//end set_locale()


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		new Wordpress_Nextjs_Settings( $this->get_plugin_name() );

	}//end define_admin_hooks()


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new Wordpress_Nextjs_Public( $this->get_plugin_name() );

		$this->loader->add_filter( 'page_link', $plugin_public, 'remove_base_url' );
		$this->loader->add_filter( 'post_link', $plugin_public, 'remove_base_url' );
		$this->loader->add_filter( 'post_type_link', $plugin_public, 'remove_base_url' );

	}//end define_public_hooks()


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();

	}//end run()


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string    The name of the plugin.
	 * @since  1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;

	}//end get_plugin_name()


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Wordpress_Nextjs_Loader    Orchestrates the hooks of the plugin.
	 * @since  1.0.0
	 */
	public function get_loader() {
		return $this->loader;

	}//end get_loader()


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string    The version number of the plugin.
	 * @since  1.0.0
	 */
	public function get_version() {
		return $this->version;

	}//end get_version()


}//end class
