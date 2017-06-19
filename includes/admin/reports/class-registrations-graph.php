<?php

class Affiliate_WP_Registrations_Graph extends Affiliate_WP_Graph {

	/**
	 * Runs during instantiation of the affiliate registrations graph.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @param array $_data Data for initializing the graph instance.
	 */
	public function __construct( $_data = array() ) {
		parent::__construct( $_data );

		$this->options['form_wrapper'] = false;
	}

	/**
	 * Retrieve referral data
	 *
	 * @since 1.1
	 */
	public function get_data() {

		$dates      = affwp_get_filter_dates();
		$date_range = affwp_get_filter_date_range();

		$affiliates = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby'  => 'date_registered',
			'order'    => 'ASC',
			'number'   => -1,
			'date'     => $dates,
			'fields'   => 'date_registered',
		) );

		$affiliate_data = array();
		$affiliate_data[] = strtotime( $dates['start'] ) * 1000;
		$affiliate_data[] = strtotime( $dates['end'] ) * 1000;

		if( $affiliates ) {

			foreach( $affiliates as $affiliate_date_registered ) {

				if( 'today' == $date_range || 'yesterday' == $date_range ) {

					$point = strtotime( $affiliate_date_registered ) * 1000;

					$affiliate_data[ $point ] = array( $point, 1 );

				} else {

					$time      = date( 'Y-n-d', strtotime( $affiliate_date_registered ) );
					$timestamp = strtotime( $time ) * 1000;

					if( array_key_exists( $time, $affiliate_data ) && isset( $affiliate_data[ $time ][1] ) ) {

						$count = $affiliate_data[ $time ][1] += 1;

						$affiliate_data[ $time ] = array( $timestamp, $count );
					
					} else {

						$affiliate_data[ $time ] = array( $timestamp, 1 );
						
					}

					
				}


			}

		}

		$data = array(
			__( 'Affiliate Registrations', 'affiliate-wp' ) => $affiliate_data
		);

		return $data;

	}

}