<?php

class Wordpress_Nextjs_Admin {


	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		$this->options = get_option( WORDPRESS_NEXTJS_OPTIONS_KEY );


		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		add_options_page(
			__( 'NextJS', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			__( 'NextJS', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			'administrator',
			'wordpress-nextjs',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		?>
        <div class="wrap">
            <h1><?php _e( 'NextJS Settings', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ); ?></h1>
            <form method="post" action="<?= esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
				settings_fields( WORDPRESS_NEXTJS_OPTIONS_KEY );
				do_settings_sections( WORDPRESS_NEXTJS_OPTIONS_KEY );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		$key = WORDPRESS_NEXTJS_OPTIONS_KEY;

		register_setting(
			$key,
			$key,
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'wordpress-nextjs-images',
			__( 'Images', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			array( $this, 'print_section_info' ),
			$key
		);

		add_settings_field(
			'base64_preview',
			__( 'Enable image thumbnails', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			array( 'Wordpress_Nextjs_Fields', 'checkbox' ),
			$key,
			'wordpress-nextjs-images',
			array(
				'name'  => "{$key}[base64_preview]",
				'value' => isset( $this->options['base64_preview'] ) ? $this->options['base64_preview'] : 0
			)
		);

		add_settings_field(
			'image_srcsets',
			__( 'Enable image srcsets', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			array( 'Wordpress_Nextjs_Fields', 'checkbox' ),
			$key,
			'wordpress-nextjs-images',
			array(
				'name'  => "{$key}[image_srcsets]",
				'value' => isset( $this->options['image_srcsets'] ) ? $this->options['image_srcsets'] : 0
			)
		);
	}


	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		_e( 'WordPress NextJS can add base64 encoded image thumbnails to all images in the rest API. These can be used as a preview when your images are still loading.', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN );
	}
}
