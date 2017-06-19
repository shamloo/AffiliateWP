<?php
/**
 * Date-related functions
 *
 * @package     AffiliateWP
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
 */

/**
 * Retrieves the start and end date filters for use with the Graphs API.
 *
 * @since 2.2
 *
 * @param string $values Optional. What format to retrieve dates in the resulting array in.
 *                       Accepts 'strings' or 'objects'. Default 'strings'.
 *
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
function affwp_get_filter_dates( $values = 'strings' ) {

	$date       = affiliate_wp()->utils->date();
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
			$filter_from = empty( $_REQUEST['filter_from'] ) ? 'now' : $_REQUEST['filter_from'];
			$filter_to   = empty( $_REQUEST['filter_to'] )   ? 'now' : $_REQUEST['filter_to'];

			$dates = array(
				'start' => affiliate_wp()->utils->date( $filter_from )->startOfDay(),
				'end'   => affiliate_wp()->utils->date( $filter_to )->endOfDay(),
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