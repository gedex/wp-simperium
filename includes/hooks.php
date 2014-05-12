<?php
/**
 * Class that provides action hooks to send data, send buffered data,
 * and flush the buffer.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class WP_Simperium_Hooks {

	/**
	 * @var WP_Simperium_Plugin
	 */
	private $plugin;

	/**
	 * Constructor. Do the followings:
	 *
	 * - Store plugin reference
	 * - Hooks into exposed Simperium actions
	 *
	 * @param WP_Simperium_Plugin $plugin
	 */
	public function __construct( WP_Simperium_Plugin $plugin ) {
		$this->plugin = $plugin;

		// Provides a way for plugin/theme to invoke the action
		// with `do_action( ... )`.
		add_action( 'simperium_send_data',          array( $this, 'send_data' ),          10, 2 );
		add_action( 'simperium_send_buffered_data', array( $this, 'send_buffered_data' ), 10, 2 );
		add_action( 'simperium_flush_buffer',       array( $this, 'flush_buffer' ),       10, 1 );
	}

	/**
	 * @param string $bucket Bucket's name
	 * @param array  $data   Data to send
	 */
	public function send_data( $bucket, array $data ) {
		WP_Simperium::send_data( $bucket, $data );
	}

	/**
	 * @param string $bucket Bucket's name
	 * @param array  $data   Data to send
	 */
	public function send_buffered_data( $bucket, array $data ) {
		WP_Simperium::send_buffered_data( $bucket, $data );
	}

	/**
	 *
	 * @param  string $bucket Bucket's name
	 */
	public function flush_buffer( $bucket = '' ) {
		WP_Simperium::flush_buffer( $bucket );
	}
}
