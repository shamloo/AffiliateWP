<?php
namespace AffWP\Graph;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Affiliate_WP_Graph.
 *
 * @covers Affiliate_WP_Graph
 * @group graph
 */
class Tests extends UnitTestCase {

	/**
	 * @covers ::affwp_get_report_dates()
	 */
	public function test_get_report_dates_correct_this_month_at_the_end_of_the_month_utc() {

		date_default_timezone_set( 'UTC' );

		$dates = affwp_get_report_dates();

		$this->assertEquals( $dates['day'], 1 );
		$this->assertEquals( $dates['m_start'], date( 'n' ) );
		$this->assertEquals( $dates['year'], date( 'Y' ) );
		$this->assertEquals( $dates['day_end'], cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) );
		$this->assertEquals( $dates['m_end'], date( 'n' ) );
		$this->assertEquals( $dates['year_end'], date( 'Y' ) );

	}

	/**
	 * @covers ::affwp_get_report_dates()
	 */
	public function test_get_report_dates_correct_this_month_at_the_end_of_the_month_nz() {

		date_default_timezone_set( 'Pacific/Auckland' );

		$dates = affwp_get_report_dates();

		$this->assertEquals( $dates['day'], 1 );
		$this->assertEquals( $dates['m_start'], date( 'n' ) );
		$this->assertEquals( $dates['year'], date( 'Y' ) );
		$this->assertEquals( $dates['day_end'], cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) );
		$this->assertEquals( $dates['m_end'], date( 'n' ) );
		$this->assertEquals( $dates['year_end'], date( 'Y' ) );
	}

	/**
	 * @covers ::affwp_get_report_dates()
	 */
	public function test_get_report_dates_correct_this_month_at_the_beginning_of_the_month_utc() {

		date_default_timezone_set( 'UTC' );

		$dates = affwp_get_report_dates();

		$this->assertEquals( $dates['day'], 1 );
		$this->assertEquals( $dates['m_start'], date( 'n' ) );
		$this->assertEquals( $dates['year'], date( 'Y' ) );
		$this->assertEquals( $dates['day_end'], cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) );
		$this->assertEquals( $dates['m_end'], date( 'n' ) );
		$this->assertEquals( $dates['year_end'], date( 'Y' ) );
	}

	/**
	 * @covers ::affwp_get_report_dates()
	 */
	public function test_get_report_dates_correct_this_month_at_the_beginning_of_the_month_pdt() {

		date_default_timezone_set( 'America/Los_Angeles' );

		$dates = affwp_get_report_dates();

		$this->assertEquals( $dates['day'], 1 );
		$this->assertEquals( $dates['m_start'], date( 'n' ) );
		$this->assertEquals( $dates['year'], date( 'Y' ) );
		$this->assertEquals( $dates['day_end'], cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) );
		$this->assertEquals( $dates['m_end'], date( 'n' ) );
		$this->assertEquals( $dates['year_end'], date( 'Y' ) );
	}

	/**
	 * @covers ::affwp_get_report_dates()
	 */
	public function test_get_report_dates_correct_this_moment_utc() {

		date_default_timezone_set( 'UTC' );

		$current_time = current_time( 'timestamp' );
		$dates = affwp_get_report_dates();

		$this->assertEquals( $dates['day'], 1 );
		$this->assertEquals( $dates['m_start'], date( 'n', $current_time ) );
		$this->assertEquals( $dates['year'], date( 'Y', $current_time ) );
		$this->assertEquals( $dates['day_end'], cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) );
		$this->assertEquals( $dates['m_end'], date( 'n', $current_time ) );
		$this->assertEquals( $dates['year_end'], date( 'Y', $current_time ) );
	}

}
