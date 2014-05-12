<?php
/**
 * Class that refreshes stored access_token per 29 days.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class WP_Simperium_Cron {

	/**
	 * @var WP_Simperium_Plugin
	 */
	private $plugin;

	public function __construct( WP_Simperium_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'cron_schedules',                array( $this, 'per_29_days' ) );
		add_action( 'wp',                            array( $this, 'setup_cron' ) );
		add_action( 'simperium_update_access_token', array( $this, 'update_access_token' ) );
	}

	public function per_29_days( $schedules ) {
		$schedules['per_29_days'] = array(
			'interval' => 2505600, // 60 * 60 * 24 * 29
			'display'  => __( 'Per 29 days', 'simperium' ),
		);

		return $schedules;
	}

	public function setup_cron() {
		if ( ! wp_next_scheduled( 'simperium_update_access_token' ) ) {
			wp_schedule_event( time(), 'per_29_days', 'simperium_update_access_token' );
		}
	}

	public function update_access_token() {
		delete_option( 'simperium_access_token' );

		// This will trigger re-fetching access_token.
		$this->plugin->config->get();
	}
}
