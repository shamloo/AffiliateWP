<?php
/**
 * Implements logic to display an earnings vs payouts graph in the Payouts reports tab.
 *
 * @since 2.1
 *
 * @see \Affiliate_WP_Graph
 */
class Affiliate_WP_Payouts_Graph extends \Affiliate_WP_Graph {

	/**
	 * Constructor for the graph.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @param array $_data Optional. Graph data. Default empty array.
	 */
	public function __construct( $_data = array() ) {

		$this->dates      = affwp_get_filter_dates();
		$this->date_range = affwp_get_filter_date_range();

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

	public function get_earnings_data() {
		$earnings = $totals = array();

		$referrals = affiliate_wp()->referrals->get_referrals( array(
			'orderby'      => 'date',
			'order'        => 'ASC',
			'date'         => $this->dates,
			'status'       => array( 'paid', 'unpaid', 'pending' ),
			'number'       => -1,
			'affiliate_id' => $this->get( 'affiliate_id' ),
			'fields'       => array( 'date', 'amount' ),
		) );

		$earnings[] = array( strtotime( $this->dates['start'] ) * 1000 );
		$earnings[] = array( strtotime( $this->dates['end'] ) * 1000 );

		if ( $referrals ) {

			$difference = ( strtotime( $date['end'] ) - strtotime( $date['start'] ) );

			foreach ( $referrals as $referral ) {
				// Can't use Referral->date() here because $referrals aren't full Referral objects.
				if ( in_array( $this->date_range, array( 'this_year', 'last_year' ), true )
				     || $difference >= YEAR_IN_SECONDS
				) {
					$date = date( 'Y-m', strtotime( $referral->date ) );
				} else {
					$date = date( 'Y-m-d', strtotime( $referral->date ) );
				}

				if ( empty( $paid[ $date ] ) ) {
					$totals[ $date ] = $referral->amount;
				} else {
					$totals[ $date ] += $referral->amount;
				}
			}

			if ( $totals ) {

				foreach ( $totals as $date => $amount ) {
					$earnings[] = array( strtotime( $date ) * 1000, $amount );
				}
			}

		}

		return $earnings;
	}

	/**
	 * Retrieves payouts (paid) data.
	 *
	 * @access public
	 * @sinec  2.1
	 *
	 * @return array Payouts data.
	 */
	public function get_payouts_data() {
		$paid = $totals = array();

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'orderby'      => 'date',
			'order'        => 'ASC',
			'date'         => $this->dates,
			'number'       => -1,
			'affiliate_id' => $this->get( 'affiliate_id' ),
			'fields'       => array( 'date', 'amount' ),
		) );

		$paid[] = array( strtotime( $this->dates['start'] ) * 1000 );
		$paid[] = array( strtotime( $this->dates['end'] ) * 1000 );

		if ( $payouts ) {

			$difference = ( strtotime( $this->dates['end'] ) - strtotime( $this->dates['start'] ) );

			foreach ( $payouts as $payout ) {
				// Can't use Payout->date() here because $referrals aren't full Payout objects.
				if ( in_array( $this->date_range, array( 'this_year', 'last_year' ), true )
				     || $difference >= YEAR_IN_SECONDS
				) {
					$date = date( 'Y-m', strtotime( $payout->date ) );
				} else {
					$date = date( 'Y-m-d', strtotime( $payout->date ) );
				}

				if ( empty( $totals[ $date ] ) ) {
					$totals[ $date ] = $payout->amount;
				} else {
					$totals[ $date ] += $payout->amount;
				}
			}

			if ( $totals ) {

				foreach ( $totals as $date => $amount ) {
					$paid[] = array( strtotime( $date ) * 1000, $amount );
				}
			}
		}

		return $paid;
	}

	/**
	 * Retrieves payouts and earnings data.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function get_data() {

		$data = array(
			__( 'Earnings Generated', 'affiliate-wp' ) => $this->get_earnings_data(),
			__( 'Earnings Paid', 'affiliate-wp' )      => $this->get_payouts_data()
		);

		return $data;

	}
}