<?php

class Wordpress_Nextjs_Activation {
	public static function activate() {
		$initial_options  = array(
			'base64_preview' => 'on',
			'image_srcsets'  => 'on',
			'auth_secret'    => md5( rand() )
		);
		$existing_options = ! empty( get_option( WORDPRESS_NEXTJS_OPTIONS_KEY ) ) ? get_option( WORDPRESS_NEXTJS_OPTIONS_KEY ) : [];

		update_option( WORDPRESS_NEXTJS_OPTIONS_KEY, array_merge( $initial_options, $existing_options ) );
	}

	public static function deactivate() {

	}
}
