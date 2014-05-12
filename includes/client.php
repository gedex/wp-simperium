<?php
/**
 * Simperium HTTP API client.
 *
 * @link https://simperium.com/docs/reference/http
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class WP_Simperium_Client {

	/**
	 * Simperium HTTP API version.
	 *
	 * @constant API_VERSION
	 */
	const API_VERSION = '1';

	/**
	 * Simperium Data API domain.
	 *
	 * @constant DATA_API_DOMAIN
	 */
	const DATA_API_DOMAIN = 'api.simperium.com';

	/**
	 * Simperium Auth API domain.
	 *
	 * @constant AUTH_API_DOMAIN
	 */
	const AUTH_API_DOMAIN = 'auth.simperium.com';

	/**
	 * @var WP_Simperium_Plugin
	 */
	private $plugin;


	/**
	 * Constructor.
	 *
	 * @param WP_Simperium_Plugin $plugin Array of configuration settings.
	 */
	public function __construct( WP_Simperium_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_filter( 'simperium_config', array( $this, 'simperium_config_injector' ), 9999 );
	}

	/**
	 * Makes request to AUTH_API_DOMAIN. You can manages users, creating them,
	 * deleting them, and signing them in and requires a valid app key which
	 * is defined in WP_Simperium_Config.
	 *
	 * @param string $endpoint URL endpoint
	 * @param array  $data     Data to send
	 * @param array  $headers  Request headers
	 * @param array  $config   To override plugin's config
	 *
	 * @return mixed WP_Error|array The response or WP_Error on failure.
	 */
	public function auth_request( $endpoint, array $data = array(), array $headers = array(), $config = array() ) {
		if ( isset( $endpoint[0] ) && '/' === $endpoint[0] ) {
			$endpoint = substr( $endpoint, 1 );
		}

		if ( empty( $config ) ) {
			$config = $this->plugin->config->get();
		}

		if ( empty( $config['app_id'] ) ) {
			return new WP_Error( 'simperium_missing_app_id', __( 'Missing app_id', 'simperium' ) );
		}

		if ( empty( $config['app_key'] ) ) {
			return new WP_Error( 'simperium_missing_app_key', __( 'Missing app_key', 'simperium' ) );
		}

		$url = sprintf(
			'https://%s/%s/%s/%s',
			self::AUTH_API_DOMAIN,
			self::API_VERSION,
			$config['app_id'],
			$endpoint
		);

		// Missing trailing slash will result in HTTP/1.1 405 Method Not Allowed.
		$url = trailingslashit( $url );

		$headers['X-Simperium-API-Key'] = $config['app_key'];

		return $this->_request( $url, $data, $headers );
	}

	/**
	 * Makes request to DATA_API_DOMAIN. You can retrieve, create, modifiy, and
	 * delete data stored in buckets. This requires valid access token which
	 * can be retrieved via `auth_request` metod.
	 *
	 * @param string $endpoint
	 * @param array  $data
	 * @param array  $query_params
	 * @param array  $headers
	 *
	 * @return mixed WP_Error|array The response or WP_Error on failure.
	 */
	public function data_request( $endpoint, array $data = array(), array $query_params = array(), array $headers = array(), $method = 'POST' ) {
		$config = $this->plugin->config->get();
		$url    = sprintf(
			'https://%s/%s/%s/%s',
			self::DATA_API_DOMAIN,
			self::API_VERSION,
			$config['app_id'],
			$endpoint
		);

		// On Auth request we prevents missing trailing slash, but on data request adding
		// trailing slash may result in 404.
		$url = untrailingslashit( $url );

		// clientid: a unique string identifying your client (useful for debugging
		// and tracking changes).
		if ( empty( $query_params['client_id'] ) ) {
			$query_params['clientid'] = $this->plugin->config->get( 'app_id' );
		}
		$url = add_query_arg( $query_params, $url );

		if ( empty( $config['access_token'] ) ) {
			return new WP_Error( 'simperium_missing_access_token', __( 'Missing access_token', 'simperium' ) );
		}
		$headers['X-Simperium-Token'] = $config['access_token'];

		return $this->_request( $url, $data, $headers, $method );
	}

	/**
	 * Makes a request to Simperium API.
	 *
	 * @return WP_Error|array The response or WP_Error on failure.
	 */
	protected function _request( $url, $data = array(), $headers = array(), $method = 'POST' ) {
		$body = '';
		if ( ! empty( $data ) ) {
			$body = json_encode( $data );
		}

		return wp_remote_request(
			$url,
			array(
				'method'     => $method,
				'user-agent' => $this->plugin->name . '/' . $this->plugin->version,
				'headers'    => $headers,
				'body'       => $body,
			)
		);
	}

	/**
	 * Inject username or access_token if necessary.
	 *
	 * @filter simperium_config
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function simperium_config_injector( $config ) {
		if ( empty( $config['username'] ) ) {
			$config['username'] = get_bloginfo( 'admin_email' );
		}

		if ( empty( $config['access_token'] ) ) {
			$config['access_token'] = $this->_get_access_token( $config );
		}

		return $config;
	}

	/**
	 * Gets access token, either from option or remotely to Simperium.
	 *
	 * @param array $config
	 *
	 * @return string Empty string will be returned if failed to retrieve access_token
	 */
	private function _get_access_token( array $config ) {
		// Check in option.
		$access_token = get_option( 'simperium_access_token', '' );
		if ( ! empty( $access_token ) ) {
			return $access_token;
		}

		// If we don't have username and its password stored in options, it's
		// assumed you're creating a new user.
		$endpoint = 'create';

		$username = $config['username'];
		$password = get_option( 'simperium_username_password', '' );

		// If we've password that's stored already.
		if ( ! empty( $password ) ) {
			// Username from option.
			$username_setting = get_option( 'simperium_username' );
			if ( $username_setting === $username ) {
				// Use 'authorize' endpoint to retrieve 'access_token'.
				$endpoint = 'authorize';
			}
		} else {
			$password = uniqid();
		}

		// Provides filter for you override the response. Might be helpful
		// in debugging.
		$resp = apply_filters(
			'simperium_get_access_token_response',

			// Request it from Simperium and save it into option.
			$this->auth_request( $endpoint, compact( 'username', 'password' ), array(), $config )
		);

		if ( is_wp_error( $resp ) ) {
			return '';
		}

		$code = intval( wp_remote_retrieve_response_code( $resp ) );
		if ( 200 === $code ) {
			$body         = json_decode( wp_remote_retrieve_body( $resp ), true );
			$access_token = '';
			if ( ! empty( $body['access_token'] ) ) {
				$access_token = $body['access_token'];

				update_option( 'simperium_username',          $username );
				update_option( 'simperium_username_password', $password );
				update_option( 'simperium_access_token',      $access_token );
			}

			return $access_token;
		}

		// There's edge case that access_token will be returned with empty string,
		// such as when username has already registered. I don't want to slow down
		// your site by making more than one request for the sake of requesting
		// access_token. In such case, you can supply access_token in config or
		// use username that hasn't registered yet.

		return '';
	}
}
