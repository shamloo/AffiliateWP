<?php
namespace AffWP\Utils;

use \Carbon\Carbon;

/**
 * Implements date formatting helpers for AffiliateWP.
 *
 * @since 2.2
 * @final
 */
final class Date {

	/**
	 * The current WordPress timezone.
	 *
	 * @access public
	 * @since  2.2
	 * @var    string
	 */
	public $timezone;

	/**
	 * The current WordPress date format.
	 *
	 * @access public
	 * @since  2.2
	 * @var    string
	 */
	public $date_format;

	/**
	 * The current WordPress time format.
	 *
	 * @access public
	 * @since  2.2
	 * @var    string
	 */
	public $time_format;

	/**
	 * Sets up the class.
	 *
	 * @access public
	 * @since  2.2
	 */
	public function __construct() {
		$this->timezone    = get_option( 'timezone_string', 'utc' );
		$this->date_format = get_option( 'date_format' );
		$this->time_format = get_option( 'time_format' );
	}

	/**
	 * Formats a given date string according to WP date and time formats and timezone.
	 *
	 * @access public
	 * @since  2.2
	 *
	 * @param string      $date_string Date string to format.
	 * @param string|true $type        Optional. How to format the date string.  Accepts 'date',
	 *                                 'time', 'datetime', 'utc', 'timestamp', 'object', or any
	 *                                 valid date_format() string. If true, 'datetime' will be
	 *                                 used. Default 'datetime'.
	 * @return string|int|\Carbon\Carbon Formatted date string, timestamp if `$type` is timestamp,
	 *                                   or a Carbon object if `$type` is 'object'.
	 */
	public function format( $date_string, $type = 'datetime' ) {
		$timezone = 'utc' === $type ? 'UTC' : $this->timezone;
		$date     = affiliate_wp()->utils->date( $date_string, $timezone );

		if ( empty( $type ) || 'utc' === $type || true === $type ) {
			$type = 'datetime';
		}

		switch( $type ) {
			case 'date':
				$formatted = $date->format( $this->date_format );
				break;

			case 'time':
				$formatted = $date->format( $this->time_format );
				break;

			case 'datetime':
				$formatted = $date->format( $this->date_format . ' ' . $this->time_format );
				break;

			case 'object':
				$formatted = $date;
				break;

			case 'timestamp':
				$formatted = $date->timestamp;
				break;

			default:
				$formatted = $date->format( $type );
				break;
		}

		return $formatted;
	}


}