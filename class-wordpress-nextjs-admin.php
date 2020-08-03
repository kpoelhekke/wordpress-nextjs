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
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			__( 'NextJS', 'wordpress-nextjs' ),
			__( 'NextJS', 'wordpress-nextjs' ),
			'administrator',
			'wordpress-nextjs',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'wordpress-nextjs' );

		?>
        <div class="wrap">
            <h1><?php _e( 'NextJS Settings', 'wordpress-nextjs' ); ?></h1>
            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'wordpress-nextjs' );
				do_settings_sections( 'wordpress-nextjs' );
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
		register_setting(
			'wordpress-nextjs',
			'wordpress-nextjs',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'wordpress-nextjs-images',
			__( 'Images', 'wordpress-nextjs' ),
			array( $this, 'print_section_info' ),
			'wordpress-nextjs'
		);

		add_settings_field(
			'base64_preview',
			__( 'Enable image thumbnails', 'wordpress-nextjs' ),
			array( $this, 'base64_preview_callback' ),
			'wordpress-nextjs',
			'wordpress-nextjs-images'
		);

		add_settings_field(
			'image_srcsets',
			__( 'Enable image srcsets ', 'wordpress-nextjs' ),
			array( $this, 'image_srset_callback' ),
			'wordpress-nextjs',
			'wordpress-nextjs-images'
		);

		add_settings_field(
			'title',
			'Title',
			array( $this, 'title_callback' ),
			'wordpress-nextjs',
			'wordpress-nextjs-images'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();

		if ( isset( $input['base64_preview'] ) ) {
			$new_input['base64_preview'] = 1;
		}

		if ( isset( $input['image_srcsets'] ) ) {
			$new_input['image_srcsets'] = 1;
		}

		if ( isset( $input['title'] ) ) {
			$new_input['title'] = sanitize_text_field( $input['title'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		_e( 'WordPress NextJS can add base64 encoded image thumbnails to all images in the rest API. These can be used as a preview when your images are still loading.', 'wordpress-nextjs' );
	}

	public function base64_preview_callback() {
		printf(
			'<input type="checkbox" id="base64_preview" name="%s[base64_preview]" value="true" %s />',
			'wordpress-nextjs',
			isset( $this->options['base64_preview'] ) ? checked( $this->options['base64_preview'], 1, false ) : ''
		);
	}

	public function image_srset_callback() {
		printf(
			'<input type="checkbox" id="base64_preview" name="%s[image_srcsets]" value="true" %s />',
			'wordpress-nextjs',
			isset( $this->options['image_srcsets'] ) ? checked( $this->options['image_srcsets'], 1, false ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function title_callback() {
		printf(
			'<input type="text" id="title" name="%s[title]" value="%s" />',
			'wordpress-nextjs',
			isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
		);
	}
}
