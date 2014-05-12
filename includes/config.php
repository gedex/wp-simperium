<?php
/**
 * Simperium Config.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class WP_Simperium_Config {

	/**
	 * @var WP_Simperium_Plugin
	 */
	private $plugin;

	/**
	 * @var    array
	 * @access private
	 */
	private $config = array(
		'app_id'       => '',
		'app_key'      => '',
		'username'     => '',
		'access_token' => '',
	);

	public function __construct( WP_Simperium_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get config.
	 *
	 * @param string $key Optional key for single config retrieval
	 *
	 * @return mixed  Returns string for single config retrieval, otherwise array
	 */
	public function get( $key = '', $auth_request = false ) {
		$this->config = apply_filters( 'simperium_config', $this->config );

		// Single config retrieval.
		if ( ! empty( $key ) && isset( $this->config[ $key ] ) ) {
			return apply_filters( 'simperium_config_' . $key, $this->config[ $key ] );
		}

		return $this->config;
	}
}
