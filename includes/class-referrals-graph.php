<?php

class Affiliate_WP_Referrals_Graph extends Affiliate_WP_Graph {

	/**
	 * Get things started
	 *
	 * @since 1.0
	 */
	public function __construct( $_data = array() ) {

		// Generate unique ID
		$this->id = md5( rand() );

		// Setup default options;
		$this->options = array(
			'y_mode'          => null,
			'y_decimals'      => 0,
			'x_decimals'      => 0,
			'y_position'      => 'right',
			'time_format'     => '%d/%b',
			'ticksize_unit'   => 'day',
			'ticksize_num'    => 1,
			'multiple_y_axes' => false,
			'bgcolor'         => '#f9f9f9',
			'bordercolor'     => '#ccc',
			'color'           => '#bbb',
			'borderwidth'     => 2,
			'bars'            => false,
			'lines'           => true,
			'points'          => true,
			'affiliate_id'    => false,
			'show_controls'   => true,
			'form_wrapper'    => true,
		);

	}

	/**
	 * Retrieve referral data
	 *
	 * @since 1.0
	 */
	public function get_data() {

		$paid     = array();
		$unpaid   = array();
		$rejected = array();
		$pending  = array();

		$dates      = affwp_get_filter_dates();
		$date_range = affwp_get_filter_date_range();
		$difference = ( strtotime( $date['end'] ) - strtotime( $date['start'] ) );

		$referrals = affiliate_wp()->referrals->get_referrals( array(
			'orderby'      => 'date',
			'order'        => 'ASC',
			'date'         => $dates,
			'number'       => -1,
			'affiliate_id' => $this->get( 'affiliate_id' )
		) );

		$pending[] = array( strtotime( $start ) * 1000 );
		$pending[] = array( strtotime( $end ) * 1000 );

		if( $referrals ) {

			$referrals_by_status = array(
				'paid'     => wp_list_filter( $referrals, array( 'status' => 'paid' ) ),
				'unpaid'   => wp_list_filter( $referrals, array( 'status' => 'unpaid' ) ),
				'rejected' => wp_list_filter( $referrals, array( 'status' => 'rejected' ) ),
				'pending'  => wp_list_filter( $referrals, array( 'status' => 'pending' ) ),
			);

			$totals = array();

			foreach ( $referrals_by_status as $status => $referrals ) {
				foreach ( $referrals as $referral ) {
					if ( empty( $totals[ $status ] ) ) {
						$totals[ $status ] = array();
					}

					if ( in_array( $date_range, array( 'this_year', 'last_year' ), true )
					     || $difference >= YEAR_IN_SECONDS
					) {
						$date = $referral->date( 'Y-m' );
					} else {
						$date = $referral->date( 'Y-m-d' );
					}

					if ( empty( $totals[ $status ][ $date ] ) ) {
						$totals[ $status ][ $date ] = $referral->amount;
					} else {
						$totals[ $status ][ $date ] += $referral->amount;
					}

				}
			}

			foreach ( $totals as $status => $dates ) {
				switch( $status ) {
					case 'paid':
						foreach ( $dates as $date => $total ) {
							$paid[] = array( strtotime( $date ) * 1000, $total );
						}
						break;

					case 'unpaid':
						foreach ( $dates as $date => $total ) {
							$unpaid[] = array( strtotime( $date ) * 1000, $total );
						}
						break;

					case 'rejected':
						foreach ( $dates as $date => $total ) {
							$rejected[] = array( strtotime( $date ) * 1000, $total );
						}
						break;

					case 'pending':
						foreach ( $dates as $date => $total ) {
							$pending[] = array( strtotime( $date ) * 1000, $total );
						}
						break;

				}
			}

		}

		$data = array(
			__( 'Unpaid Referral Earnings', 'affiliate-wp' )   => $unpaid,
			__( 'Pending Referral Earnings', 'affiliate-wp' )  => $pending,
			__( 'Rejected Referral Earnings', 'affiliate-wp' ) => $rejected,
			__( 'Paid Referral Earnings', 'affiliate-wp' )     => $paid,
		);

		return $data;

	}

}