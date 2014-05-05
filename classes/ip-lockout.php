<?php
/**
 * Demo_WP_IP_Lockout
 *
 * This class handles all of our IP Lockout functions
 *
 * @package     Demo WP PRO
 * @copyright   Copyright (c) 2014, WP Ninjas
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

class Demo_WP_IP_Lockout {
	/**
	 * Get things started
	 * 
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		
	}

	/**
	 * Lockout an IP
	 * 
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function lockout_ip( $ip ) {
		global $wpdb;

		// Delete any lockouts that this IP may have.
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . DEMO_WP_IP_LOCKOUT_TABLE . ' WHERE ip = %s', $ip ) );

		// Set a lockout for this IP for twenty minutes.
		$wpdb->insert( DEMO_WP_IP_LOCKOUT_TABLE, array( 'ip' => $ip, 'time_set' => current_time( 'timestamp' ), 'time_expires' => strtotime( '+20 minutes', current_time( 'timestamp' ) ) ) );
	
		do_action( 'dwp_ip_lockout', $ip );
	}

	/**
	 * Check to see if the passed IP should be locked out
	 * 
	 * @access public
	 * @since 1.0
	 * @return string $expires
	 */
	public function check_ip_lockout( $ip ) {
		// Get the lockout info for the passed IP
		$expires = $this->get_ip_lockout( $ip );
		// If nothing was found, then no lockout was found. Return false.
		if ( ! $expires )
			return false;
		// If our expiration time has passed, then free the IP and return false.
		if ( current_time( 'timestamp' ) > $expires ) {
			$this->free_ip( $ip );
			return false;
		} else { // This IP is still locked out. Return the time the lockout expires.
			return $expires;
		}
	}

	/**
	 * Get the expiration timestamp for an IP lockout
	 * 
	 * @access public
	 * @since 1.0
	 * @return string $expires
	 */
	public function get_ip_lockout( $ip ) {
		global $wpdb;

		$expires = $wpdb->get_row( $wpdb->prepare( 'SELECT time_expires FROM ' . DEMO_WP_IP_LOCKOUT_TABLE . ' WHERE ip = %s', $ip ), ARRAY_A );
		return $expires['time_expires'];
	}

	/**
	 * Free an IP address lockout
	 * 
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function free_ip( $ip ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . DEMO_WP_IP_LOCKOUT_TABLE . ' WHERE ip = %s', $ip ) );
	}
}