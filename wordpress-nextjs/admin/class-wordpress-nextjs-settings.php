<?php

class Wordpress_Nextjs_Settings
{

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * Start up
     */
    public function __construct($plugin_name)
    {
        $this->plugin_name = $plugin_name;

        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            __('NextJS', $this->plugin_name),
            __('NextJS', $this->plugin_name),
            'administrator',
            $this->plugin_name,
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option($this->plugin_name);

        ?>
        <div class="wrap">
            <h1><?php _e('NextJS Settings', $this->plugin_name) ?></h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields($this->plugin_name);
                do_settings_sections($this->plugin_name);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            $this->plugin_name,
            $this->plugin_name,
            array($this, 'sanitize')
        );

        add_settings_section(
            'wordpress-nextjs-images',
            __('Images', $this->plugin_name),
            array($this, 'print_section_info'),
            $this->plugin_name
        );

        add_settings_field(
            'base64_images',
            __('Enable image thumbnails', $this->plugin_name),
            array($this, 'base64_images_callback'),
            $this->plugin_name,
            'wordpress-nextjs-images'
        );

        add_settings_field(
            'title',
            'Title',
            array($this, 'title_callback'),
            $this->plugin_name,
            'wordpress-nextjs-images'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['base64_images'])) {
            $new_input['base64_images'] = 1;
        }

        if (isset($input['title'])) {
            $new_input['title'] = sanitize_text_field($input['title']);
        }

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        _e('Wordpress NextJS can add base64 encoded image thumbnails to all images in the rest API. These can be used as a preview when your images are still loading.', $this->plugin_name);
    }

    public function base64_images_callback()
    {

        printf(
            '<input type="checkbox" id="base64_images" name="%s[base64_images]" value="true" %s />',
            $this->plugin_name,
            isset($this->options['base64_images']) ? checked($this->options['base64_images'], 1, false) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="%s[title]" value="%s" />',
            $this->plugin_name,
            isset($this->options['title']) ? esc_attr($this->options['title']) : ''
        );
    }
}
