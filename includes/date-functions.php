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
 * @return array {
 *     Query date range for the current graph filter request.
 *
 *     @type string $start Start day and time (based on the beginning of the given day).
 *     @type string $end   End day and time (based on the end of the given day).
 * }
 */
function affwp_get_filter_dates() {

	$filter_from = empty( $_REQUEST['filter_from'] ) ? 'now' : $_REQUEST['filter_from'];
	$filter_to   = empty( $_REQUEST['filter_to'] )   ? 'now' : $_REQUEST['filter_to'];

	return array(
		'start' => affiliate_wp()->utils->date( $filter_from )->startOfDay()->toDateTimeString(),
		'end'   => affiliate_wp()->utils->date( $filter_to )->endOfDay()->toDateTimeString()
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