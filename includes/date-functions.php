<?php
/**
 * Date-related functions
 *
 * @package     AffiliateWP
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2017, AffiliateWP, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
 */

/**
 * Retrieves the start and end date filters for use with the Graphs API.
 *
 * @since 2.2
 *
 * @param string $values   Optional. What format to retrieve dates in the resulting array in.
 *                         Accepts 'strings' or 'objects'. Default 'strings'.
 * @param string $timezone Optional. Timezone to force for filter dates. Primarily used for
 *                         legacy testing purposes. Default empty.
 * @return array {
 *     Query date range for the current graph filter request.
 *
 *     @type string|\Carbon\Carbon $start Start day and time (based on the beginning of the given day).
 *                                        If `$values` is 'objects', a Carbon object, otherwise a date
 *                                        time string.
 *     @type string|\Carbon\Carbon $end   End day and time (based on the end of the given day). If `$values`
 *                                        is 'objects', a Carbon object, otherwise a date time string.
 * }
 */
function affwp_get_filter_dates( $values = 'strings', $timezone = '' ) {

	$date       = affiliate_wp()->utils->date( 'now', $timezone );
	$date_range = affwp_get_filter_date_range();

	/** @var \Carbon\Carbon[] $dates */
	$dates = array();

	switch( $date_range ) {
		case 'this_month':
			$dates = array(
				'start' => $date->copy()->startOfMonth(),
				'end'   => $date->copy()->endOfMonth(),
			);
			break;

		case 'last_month':
			$dates = array(
				'start' => $date->copy()->subMonth( 1 )->startOfMonth(),
				'end'   => $date->copy()->subMonth( 1 )->endOfMonth(),
			);
			break;

		case 'today':
			$dates = array(
				'start' => $date->copy()->startOfDay(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'yesterday':
			$dates = array(
				'start' => $date->copy()->subDay( 1 )->startOfDay(),
				'end'   => $date->copy()->subDay( 1 )->endOfDay(),
			);
			break;

		case 'this_week':
			$dates = array(
				'start' => $date->copy()->startOfWeek(),
				'end'   => $date->copy()->endOfWeek(),
			);
			break;

		case 'this_quarter':
			$dates = array(
				'start' => $date->copy()->startOfQuarter(),
				'end'   => $date->copy()->endOfQuarter(),
			);
			break;

		case 'last_quarter':
			$dates = array(
				'start' => $date->copy()->subQuarter( 1 )->startOfQuarter(),
				'end'   => $date->copy()->subQuarter( 1 )->endOfQuarter(),
			);
			break;

		case 'this_year':
			$dates = array(
				'start' => $date->copy()->startOfYear(),
				'end'   => $date->copy()->endOfYear(),
			);
			break;

		case 'last_year':
			$dates = array(
				'start' => $date->copy()->subYear( 1 )->startOfYear(),
				'end'   => $date->copy()->subYear( 1 )->endOfYear(),
			);
			break;


		case 'other':
		default:
			$filter_dates = affwp_get_filter_date_values( true );

			$dates = array(
				'start' => affiliate_wp()->utils->date( $filter_dates['start'] )->startOfDay(),
				'end'   => affiliate_wp()->utils->date( $filter_dates['end'] )->endOfDay(),
			);
			break;

	}

	if ( 'strings' === $values ) {
		if ( ! empty( $dates['start'] ) ) {
			$dates['start'] = $dates['start']->toDateTimeString();
		}

		if ( ! empty( $dates['end'] ) ) {
			$dates['end'] = $dates['end']->toDateTimeString();
		}
	}

	return $dates;

}

/**
 * Retrieves values of the filter_from and filter_to request variables.
 *
 * @since 2.2
 *
 * @param bool $now Optional. Whether to default to 'now' when retrieving empty values. Default false.
 * @return array {
 *     Query date range for the current date filter request.
 *
 *     @type string $start Start day and time string based on the WP timezone.
 *     @type string $end   End day and time string based on the WP timezone.
 * }
 */
function affwp_get_filter_date_values( $now = false ) {
	if ( true === $now ) {
		$default = 'now';
	} else {
		$default = '';
	}

	return array(
		'start' => empty( $_REQUEST['filter_from'] ) ? $default : $_REQUEST['filter_from'],
		'end'   => empty( $_REQUEST['filter_to'] )   ? $default : $_REQUEST['filter_to']
	);

}

/**
 * Retrieves the date range value for the current filter.
 *
 * @since 2.2
 *
 * @return string Date range filter key. Default 'this_month';
 */
function affwp_get_filter_date_range() {
	$range = 'this_month';

	if ( isset( $_REQUEST['range'] ) ) {
		$range = sanitize_key( $_REQUEST['range'] );
	}

	return $range;
}

/**
 * Retrieves a localized, formatted date based on the WP timezone rather than UTC.
 *
 * @since 2.2
 *
 * @see \AffWP\Utils\Date::$timezone
 *
 * @param int    $timestamp Timestamp. Can either be based on UTC or WP settings.
 * @param string $format    Optional. Any valid date format string. Default is the value
 *                          of `\AffWP\Utils\Date::$timezone`.
 *
 * @return string The formatted date, translated if locale specifies it.
 */
function affwp_date_i18n( $timestamp, $format = '' ) {

	if ( empty( $format ) ) {
		$format = affiliate_wp()->utils->date->date_format;
	}

	// Ensure timestamp based on WP timezone.
	$date = \Carbon\Carbon::createFromTimestamp( $timestamp, affiliate_wp()->utils->date->timezone );

	return date_i18n( $format, $date->timestamp, false );
}
