<?php
/**
 * Class that provides helper methods to send data to Simperium.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class WP_Simperium {

	/**
	 * Buffered array where elements get pushed by self::send_buffered_data. Array
	 * will be emptied after self::flush_buffer is called.
	 *
	 * @param array  $data   Data to send
	 * @param string $bucket Bucket's name
	 *
	 * @return string|null Object ID will be returned if succeed, otherwise null
	 */
	private static $buffer = array();

	/**
	 * Send data to Simperium.
	 *
	 * @param string $bucket Bucket's name
	 * @param array  $data   Data to send
	 */
	public static function send_data( $bucket, $data ) {
		$object_id  = ! empty( $data['id'] ) ? $data['id'] : uniqid();
		$data['id'] = $object_id;

		if ( empty( $data['_created'] ) ) {
			$data['_created'] = current_time( 'mysql', 1 );
		}

		$endpoint = sprintf( '%s/i/%s', $bucket, $object_id );
		$client   = self::_get_client();
		$query    = array( 'ccid' => uniqid() );

		$resp = $client->data_request( $endpoint, $data, $query );

		if ( is_wp_error( $resp ) ) {
			return null;
		}

		$code = intval( wp_remote_retrieve_response_code( $resp ) );
		if ( 200 !== $code ) {
			return null;
		}

		return $object_id;
	}

	/**
	 * Saves data to `self::$buffer` property. Also did processing on
	 * the data as required by `changes` endpoint.
	 *
	 * @param string $bucket Bucket's name
	 * @param array  $data   Data to send
	 */
	public static function send_buffered_data( $bucket, $data ) {
		if ( empty( self::$buffer[ $bucket ] ) ) {
			self::$buffer[ $bucket ] = array();
		}

		// Injects other properties. Simperium doesn't explicitly tell this in their
		// HTTP doc, but I got this from Simperium Python wrapper.
		//
		// @link https://github.com/Simperium/simperium-python/blob/master/simperium/core.py
		$change = array(
			'id'       => uniqid(),
			'o'        => 'M',
			'v'        => array(),
			'ccid'     => uniqid(),
		);

		if ( ! empty( $data['id'] ) ) {
			$change['id'] = $data['id'];
		}

		if ( empty( $data['_created'] ) ) {
			$data['_created'] = current_time( 'mysql', 1 );
		}

		foreach ( $data as $k => $v ) {
			$change['v'][ $k ] = array( 'o' => '+', 'v' => $v );
		}

		array_push( self::$buffer[ $bucket ], $change );
	}

	/**
	 * Flush the buffer.If `$bucket` is not empty then it will
	 * flush buffer for that bucket, othewise all buffer will be
	 * flushed.
	 *
	 * @param string $bucket
	 */
	public static function flush_buffer( $bucket = '' ) {
		if ( ! empty( $bucket ) ) {
			self::_send_buffered_bucket( $bucket );
		} else {
			foreach ( self::$buffer as $bucket => $changes ) {
				self::_send_buffered_bucket( $bucket );
			}
		}
	}

	/**
	 * Bulk post to Simperium bucket where its data comes from buffer.
	 *
	 * @param string $bucket
	 */
	private static function _send_buffered_bucket( $bucket ) {
		// Nothing to send.
		if ( empty( self::$buffer[ $bucket ] ) ) {
			return;
		}

		$client = self::_get_client();

		// Sends buffer targeted to specific bucket.
		$endpoint = $bucket . '/changes';
		$resp = $client->data_request( $endpoint, self::$buffer[ $bucket ] );

		unset( self::$buffer[ $bucket ] );
	}

	/**
	 * Gets the client.
	 *
	 * @return WP_Simperium_Client
	 */
	private static function _get_client() {
		$plugin = $GLOBALS['wp_simperium'];
		return $plugin->client;
	}
}
