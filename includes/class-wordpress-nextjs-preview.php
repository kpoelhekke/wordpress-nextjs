<?php

use Firebase\JWT\JWT;

class Wordpress_Nextjs_Preview {

	/**
	 * The REST API slug.
	 *
	 * @var string
	 */
	private $rest_api_slug = 'wp-json';

	/**
	 * Store errors to display if the JWT is wrong
	 *
	 * @var WP_REST_Response
	 */
	private $jwt_error = null;

	/**
	 * Collection of translate-able messages.
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	public function __construct() {
		$this->options = get_option( WORDPRESS_NEXTJS_OPTIONS_KEY );

		add_filter( 'rest_pre_dispatch', array( $this, 'rest_pre_dispatch' ), 10, 3 );
		add_filter( 'determine_current_user', array( $this, 'determine_current_user' ) );

		$this->messages = array(
			'wordpress_nextjs_no_auth_header'  => __( 'Authorization header not found.', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			'wordpress_nextjs_bad_auth_header' => __( 'Authorization header malformed.', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
		);

		if ( function_exists( 'has_blocks' ) ) {
			add_action( 'init', array( $this, 'set_preview_redirect' ) );
		} else {
			add_filter( 'preview_post_link', array( $this, 'set_preview_link' ) );
		}
	}

	/**
	 * Gutenberg doesn't use the preview_post_link filter. So instead we want to catch all routes with the preview=true parameter
	 * If found, we can redirect manually to the /api/preview NextJS page
	 *
	 * @see https://github.com/WordPress/gutenberg/issues/13998
	 */
	public function set_preview_redirect() {
		if ( isset( $_GET['preview'] ) && isset( $_GET['preview_id'] ) && $_GET['preview'] === 'true' ) {
			global $post;

			$post = get_post( intval( $_GET['preview_id'] ) );

			if ( $post ) {
				wp_redirect( $this->set_preview_link( get_permalink( $post ), $post ), 302 );
				exit();
			}
		}
	}

	/**
	 * Customize the preview button in the WordPress admin to point to the headless client.
	 *
	 * @param $preview_link
	 * @param $post
	 *
	 * @return string
	 */
	public function set_preview_link( $preview_link, $post ) {
		$user = wp_get_current_user();

		$token = $this->generate_token( $user );

		return add_query_arg( apply_filters( 'wordpress_nextjs_preview_link_args', array(
			'post_type' => $post->post_type,
			'post_id'   => $post->ID,
			'token'     => $token
		) ), home_url( '/api/preview' ) );
	}

	/**
	 * Generate token
	 *
	 * @param WP_User $user The WP_User object.
	 * @param bool $return_raw Whether or not to return as raw token string.
	 *
	 * @return WP_REST_Response|string Return as raw token string or as a formatted WP_REST_Response.
	 */
	public function generate_token( $user, $return_raw = true ) {
		$secret_key = isset( $this->options['auth_secret'] ) ? $this->options['auth_secret'] : false;
		$issued_at  = time();
		$not_before = $issued_at;
		$not_before = apply_filters( 'wordpress_nextjs_not_before', $not_before, $issued_at );
		$expire     = $issued_at + ( DAY_IN_SECONDS * 7 );
		$expire     = apply_filters( 'wordpress_nextjs_expire', $expire, $issued_at );

		$payload = array(
			'iss'  => $this->get_iss(),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => array(
				'user' => array(
					'id' => $user->ID,
				),
			),
		);

		$alg = $this->get_alg();

		// Let the user modify the token data before the sign.
		$token = JWT::encode( apply_filters( 'wordpress_nextjs_payload', $payload, $user ), $secret_key, $alg );

		// If return as raw token string.
		if ( $return_raw ) {
			return $token;
		}

		// The token is signed, now create object with basic info of the user.
		$response = array(
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'wordpress_nextjs_valid_credential',
			'message'    => __( 'Credential is valid', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
			'data'       => array(
				'token'       => $token,
				'id'          => $user->ID,
				'email'       => $user->user_email,
				'nicename'    => $user->user_nicename,
				'firstName'   => $user->first_name,
				'lastName'    => $user->last_name,
				'displayName' => $user->display_name,
			),
		);

		// Let the user modify the data before send it back.
		return apply_filters( 'wordpress_nextjs_valid_credential_response', $response, $user );
	}

	/**
	 * Get the token issuer.
	 *
	 * @return string The token issuer (iss).
	 */
	public function get_iss() {
		return apply_filters( 'wordpress_nextjs_iss', get_bloginfo( 'url' ) );
	}

	/**
	 * Get the supported jwt auth signing algorithm.
	 *
	 * @see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
	 *
	 * @return string $alg
	 */
	public function get_alg() {
		return apply_filters( 'wordpress_nextjs_alg', 'HS256' );
	}

	/**
	 * Determine if given response is an error response.
	 *
	 * @param mixed $response The response.
	 *
	 * @return boolean
	 */
	public function is_error_response( $response ) {
		if ( ! empty( $response ) && property_exists( $response, 'data' ) && is_array( $response->data ) ) {
			if ( ! isset( $response->data['success'] ) || ! $response->data['success'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Main validation function, this function try to get the Autentication
	 * headers and decoded.
	 *
	 * @param bool $output Whether to only return the payload or not.
	 *
	 * @return Object Returns WP_REST_Response or token's $payload.
	 */
	public function validate_token() {
		/**
		 * Looking for the HTTP_AUTHORIZATION header, if not present just
		 * return the user.
		 */

		$auth = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

		// Double check for different auth header string (server dependent).
		if ( ! $auth ) {
			$auth = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}

		if ( ! $auth ) {
			return new WP_REST_Response(
				array(
					'success'    => false,
					'statusCode' => 403,
					'code'       => 'wordpress_nextjs_no_auth_header',
					'message'    => $this->messages['wordpress_nextjs_no_auth_header'],
					'data'       => array(),
				)
			);
		}

		/**
		 * The HTTP_AUTHORIZATION is present, verify the format.
		 * If the format is wrong return the user.
		 */
		list( $token ) = sscanf( $auth, 'Bearer %s' );

		if ( ! $token ) {
			return new WP_REST_Response(
				array(
					'success'    => false,
					'statusCode' => 403,
					'code'       => 'wordpress_nextjs_bad_auth_header',
					'message'    => $this->messages['wordpress_nextjs_bad_auth_header'],
					'data'       => array(),
				)
			);
		}

		// Get the Secret Key.
		$secret_key = isset( $this->options['auth_secret'] ) ? $this->options['auth_secret'] : false;

		if ( ! $secret_key ) {
			return new WP_REST_Response(
				array(
					'success'    => false,
					'statusCode' => 403,
					'code'       => 'wordpress_nextjs_bad_config',
					'message'    => __( 'JWT is not configurated properly.', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
					'data'       => array(),
				)
			);
		}

		// Try to decode the token.
		try {
			$alg     = $this->get_alg();
			$payload = JWT::decode( $token, $secret_key, array( $alg ) );

			// The Token is decoded now validate the iss.
			if ( $payload->iss !== $this->get_iss() ) {
				// The iss do not match, return error.
				return new WP_REST_Response(
					array(
						'success'    => false,
						'statusCode' => 403,
						'code'       => 'wordpress_nextjs_bad_iss',
						'message'    => __( 'The iss do not match with this server.', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
						'data'       => array(),
					)
				);
			}

			// Check the user id existence in the token.
			if ( ! isset( $payload->data->user->id ) ) {
				// No user id in the token, abort!!
				return new WP_REST_Response(
					array(
						'success'    => false,
						'statusCode' => 403,
						'code'       => 'wordpress_nextjs_bad_request',
						'message'    => __( 'User ID not found in the token.', WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
						'data'       => array(),
					)
				);
			}

			// So far so good, check if the given user id exists in db.
			$user = get_user_by( 'id', $payload->data->user->id );

			if ( ! $user ) {
				// No user id in the token, abort!!
				return new WP_REST_Response(
					array(
						'success'    => false,
						'statusCode' => 403,
						'code'       => 'wordpress_nextjs_user_not_found',
						'message'    => __( "User doesn't exist", WORDPRESS_NEXTJS_LANGUAGE_DOMAIN ),
						'data'       => array(),
					)
				);
			}

			return $payload;
		} catch ( Exception $e ) {
			// Something is wrong when trying to decode the token, return error response.
			return new WP_REST_Response(
				array(
					'success'    => false,
					'statusCode' => 403,
					'code'       => 'wordpress_nextjs_invalid_token',
					'message'    => $e->getMessage(),
					'data'       => array(),
				)
			);
		}
	}

	/**
	 * This is our Middleware to try to authenticate the user according to the token sent.
	 *
	 * @param int|bool $user_id User ID if one has been determined, false otherwise.
	 *
	 * @return int|bool User ID if one has been determined, false otherwise.
	 */
	public function determine_current_user( $user_id ) {
		/**
		 * This hook only should run on the REST API requests to determine
		 * if the user in the Token (if any) is valid, for any other
		 * normal call ex. wp-admin/.* return the user.
		 *
		 * @since 1.2.3
		 */
		$this->rest_api_slug = get_option( 'permalink_structure' ) ? rest_get_url_prefix() : '?rest_route=/';

		$valid_api_uri = strpos( $_SERVER['REQUEST_URI'], $this->rest_api_slug );

		if ( ! $valid_api_uri ) {
			return $user_id;
		}

		$payload = $this->validate_token();

		// If $payload is an error response, then return the default $user_id.
		if ( $this->is_error_response( $payload ) ) {
			$this->jwt_error = $payload;

			return $user_id;
		}

		// Everything is ok here, return the user ID stored in the token.
		return $payload->data->user->id;
	}

	/**
	 * Filter to hook the rest_pre_dispatch, if there is an error in the request
	 * send it, if there is no error just continue with the current request.
	 *
	 * @param mixed $result Can be anything a normal endpoint can return, or null to not hijack the request.
	 * @param WP_REST_Server $server Server instance.
	 * @param WP_REST_Request $request The request.
	 *
	 * @return mixed $result
	 */
	public function rest_pre_dispatch( $result, WP_REST_Server $server, WP_REST_Request $request ) {
		if ( $this->is_error_response( $this->jwt_error ) ) {
			return $this->jwt_error;
		}

		if ( empty( $result ) ) {
			return $result;
		}

		return $result;
	}
}
