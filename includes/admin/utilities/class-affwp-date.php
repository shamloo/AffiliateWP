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
		$this->timezone    = get_option( 'timezone_string' );
		$this->date_format = get_option( 'date_format' );
		$this->time_format = get_option( 'time_format' );
	}

	/**
	 * Formats a given date string according to WP date and time formats and timezone.
	 *
	 * @access public
	 * @since  2.2
	 *
	 * @param string $date_string Date string to format.
	 * @param string $type        Optional. Type of formatting or actual date format string.
	 *                            Accepts shorthand formats 'date', 'time', 'datetime', or
	 *                            'utc'. Also accepts any valid date_format() format string.
	 *                            Default 'datetime'.
	 * @return string Formatted date string.
	 */
	public function format( $date_string, $type = 'datetime' ) {
		$timezone = 'utc' === $type ? 'UTC' : $this->timezone;
		$date     = affiliate_wp()->utils->date( $date_string, $timezone );

		if ( empty( $type ) || 'utc' === $type ) {
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

			default:
				$formatted = $date->format( $type );
				break;
		}

		return $formatted;
	}


}