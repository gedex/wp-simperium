<?php
/**
 * Plugin Name: Simperium
 * Plugin URI: https://github.com/x-team/wp-simperium
 * Description: This plugin contains easy-to-use API that uses WP HTTP API to send data to Simperium
 * Version: 0.1.0
 * Author: Akeda Bagus
 * Author URI: http://gedex.web.id
 * Text Domain: simperium
 * Domain Path: /languages
 * License: GPL v2 or later
 * Requires at least: 3.6
 * Tested up to: 3.9
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

require_once __DIR__ . '/includes/autoloader.php';

// Register the autoloader.
WP_Simperium_Autoloader::register( 'WP_Simperium', trailingslashit( plugin_dir_path( __FILE__ ) ) . '/includes/' );

// Runs this plugin.
$GLOBALS['wp_simperium'] = new WP_Simperium_Plugin();
$GLOBALS['wp_simperium']->run( __FILE__ );
