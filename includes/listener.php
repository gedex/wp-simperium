<?php
/**
 * Simple tool for listener.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class WP_Simperium_Listener {

	/**
	 * @var WP_Simperium_Plugin
	 */
	private $plugin;

	public function __construct( WP_Simperium_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	/**
	 * Create menu and page for plugin tools.
	 *
	 * @action admin_menu
	 */
	public function menu() {
		add_management_page(
			__( 'Simperium Listener', 'simperium' ), // Page title.
			__( 'Simperium Listener', 'simperium' ), // Menu title.

			// Cap to view the listener.
			'manage_options',

			// Menus slug.
			'simperium_listener',

			// Page setting renderer.
			array( $this, 'render' )
		);
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'simperium' ) );
		}

		// Declare vars to make it available on view.
		$page  = 'simperium_listener';
		$title = __( 'Simperium Listener', 'simperium' );

		// @todo rendering
	}
}
