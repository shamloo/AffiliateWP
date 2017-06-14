<?php
namespace AffWP\Referral\Database;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Affiliate_WP_DB_Affiliates class
 *
 * @covers Affiliate_WP_Referrals_DB
 * @group database
 * @group referrals
 */
class Referrals_DB_Tests extends UnitTestCase {

	protected static $referrals = array();

	protected static $affiliate_id = 0;

	protected static $visits = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$affiliate_id = parent::affwp()->affiliate->create();

		for ( $i = 0; $i <= 3; $i++ ) {
			self::$referrals[ $i ] = parent::affwp()->referral->create( array(
				'affiliate_id' => self::$affiliate_id,
				'visit_id'     => self::$visits[ $i ] = parent::affwp()->visit->create( array(
					'affiliate_id' => self::$affiliate_id
				) )
			) );
		}
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::$cache_group
	 */
	public function test_cache_group_should_be_referrals() {
		$this->assertSame( 'referrals', affiliate_wp()->referrals->cache_group );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::$query_object_type
	 */
	public function test_query_object_type_should_be_AffWP_Referral() {
		$this->assertSame( 'AffWP\Referral', affiliate_wp()->referrals->query_object_type );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::$primary_key
	 */
	public function test_primary_key_should_be_referral_id() {
		$this->assertSame( 'referral_id', affiliate_wp()->referrals->primary_key );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::$REST
	 */
	public function test_REST_should_be_AffWP_Referral_REST_v1_Endpoints() {
		$this->assertSame( 'AffWP\Referral\REST\v1\Endpoints', get_class( affiliate_wp()->referrals->REST ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::get_object()
	 */
	public function test_get_object_should_return_valid_object_when_passed_a_valid_referral_id() {
		$object = affiliate_wp()->referrals->get_object( self::$referrals[0] );
		$this->assertEquals( 'AffWP\Referral', get_class( $object ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::get_object()
	 */
	public function test_get_object_should_Return_false_when_passed_an_invalid_referral_id() {
		$this->assertFalse( affiliate_wp()->referrals->get_object( 0 ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::get_object()
	 */
	public function test_get_object_should_return_valid_object_when_passed_a_valid_referral_object() {
		$object = affiliate_wp()->referrals->get_object( affwp_get_referral( self::$referrals[0] ) );

		$this->assertSame( 'AffWP\Referral', get_class( $object ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::get_columns()
	 */
	public function test_get_columns_should_return_all_columns() {
		$columns = affiliate_wp()->referrals->get_columns();

		$expected = array(
			'referral_id' => '%d',
			'affiliate_id'=> '%d',
			'visit_id'    => '%d',
			'description' => '%s',
			'status'      => '%s',
			'amount'      => '%s',
			'currency'    => '%s',
			'custom'      => '%s',
			'context'     => '%s',
			'campaign'    => '%s',
			'reference'   => '%s',
			'products'    => '%s',
			'payout_id'   => '%d',
			'coupon_id'   => '%d',
			'date'        => '%s',
		);

		$this->assertEqualSets( $expected, $columns );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_should_return_array_of_Referral_objects_if_not_count_query() {
		$results = affiliate_wp()->referrals->get_referrals();

		// Check a random referral.
		$this->assertContainsOnlyType( 'AffWP\Referral', $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_should_return_integer_if_count_query() {
		$results = affiliate_wp()->referrals->get_referrals( array(), $count = true );

		$this->assertSame( 4, $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 * @group database-fields
	 */
	public function test_get_referrals_fields_ids_should_return_an_array_of_ids_only() {
		$results = affiliate_wp()->referrals->get_referrals( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( self::$referrals, $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 * @group database-fields
	 */
	public function test_get_referrals_invalid_fields_arg_should_return_regular_Referral_object_results() {
		$referrals = array_map( 'affwp_get_referral', self::$referrals );

		$results = affiliate_wp()->referrals->get_referrals( array(
			'fields' => 'foo'
		) );

		$this->assertEqualSets( $referrals, $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 * @group database-fields
	 */
	public function test_get_referrals_fields_ids_should_return_an_array_of_integer_ids() {
		$results = affiliate_wp()->referrals->get_referrals( array(
			'fields' => 'ids'
		) );

		$this->assertContainsOnlyType( 'integer', $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 * @group database-fields
	 */
	public function test_get_referrals_with_no_fields_should_return_an_array_of_affiliate_objects() {
		$results = affiliate_wp()->referrals->get_referrals();

		$this->assertContainsOnlyType( 'AffWP\Referral', $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 * @group database-fields
	 */
	public function test_get_referrals_with_multiple_valid_fields_should_return_an_array_of_stdClass_objects() {
		$results = affiliate_wp()->referrals->get_referrals( array(
			'fields' => array( 'referral_id', 'context' )
		) );

		$this->assertContainsOnlyType( 'stdClass', $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_with_single_payout_id_should_return_referrals_matching_that_payout() {
		$payout = $this->factory->payout->create( array(
			'referrals' => self::$referrals
		) );

		$results = affiliate_wp()->referrals->get_referrals( array(
			'payout_id' => $payout,
			'fields'    => 'ids'
		) );

		$this->assertEqualSets( self::$referrals, $results );

		// Clean up.
		affwp_delete_payout( $payout );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_with_multiple_payout_ids_should_return_referrals_matching_those_payouts() {
		$payout1 = $this->factory->payout->create( array(
			'referrals' => array( self::$referrals[0], self::$referrals[1] )
		) );

		$payout2 = $this->factory->payout->create( array(
			'referrals' => array( self::$referrals[2], self::$referrals[3] )
		) );

		$results = affiliate_wp()->referrals->get_referrals( array(
			'payout_id' => array( $payout1, $payout2 ),
			'fields'    => 'ids'
		) );

		$this->assertEqualSets( self::$referrals, $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_0_if_status_is_invalid() {
		$this->assertSame( 0, affiliate_wp()->referrals->count_by_status( 'foo', self::$affiliate_id ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_0_if_affiliate_is_invalid() {
		$this->assertSame( 0, affiliate_wp()->referrals->count_by_status( 'unpaid', 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_of_given_status() {
		$this->assertSame( 4, affiliate_wp()->referrals->count_by_status( 'pending', self::$affiliate_id ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_created_within_a_month_if_date_is_month() {
		// Set up 3 pending referrals for six months ago.
		$this->factory->referral->create_many( 3, array(
			'affiliate_id' => self::$affiliate_id,
			'date'         => date( 'Y-m-d H:i:s', time() - ( 6 * ( 2592000 ) ) ),
		) );

		// 4 referrals are created on test class set up.
		$this->assertSame( 4, affiliate_wp()->referrals->count_by_status( 'pending', self::$affiliate_id, 'month' ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_created_today_if_date_is_today() {
		// 4 referrals are created on test class set up, i.e. 'today'.
		$this->assertSame( 4, affiliate_wp()->referrals->count_by_status( 'pending', self::$affiliate_id, 'today' ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_for_all_time_if_date_is_invalid() {
		// Set up 3 pending referrals for six months ago.
		$this->factory->referral->create_many( 4, array(
			'affiliate_id' => self::$affiliate_id,
			'date'         => date( 'Y-m-d H:i:s', time() - ( 6 * ( 2592000 ) ) ),
		) );

		// 4 referrals created in setUp().
		$this->assertSame( 8, affiliate_wp()->referrals->count_by_status( 'pending', self::$affiliate_id, 'foo' ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::update_referral()
	 */
	public function test_update_referral_no_supplied_affiliate_id_should_use_the_existing_affiliate_id() {
		// Update the referral with no data.
		affiliate_wp()->referrals->update_referral( self::$referrals[0] );

		$result = affwp_get_referral( self::$referrals[0] );

		$this->assertSame( self::$affiliate_id, $result->affiliate_id );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::update_referral()
	 */
	public function test_update_referral_supplied_affiliate_id_should_update_the_affiliate_id() {
		// Update the referral with a new affiliate ID.
		affiliate_wp()->referrals->update_referral( self::$referrals[0], array(
			'affiliate_id' => $affiliate_id = $this->factory->affiliate->create()
		) );

		$result = affwp_get_referral( self::$referrals[0] );

		$this->assertSame( $affiliate_id, $result->affiliate_id );

		// Clean up.
		affiliate_wp()->referrals->update_referral( self::$referrals[0], array(
			'affiliate_id' => self::$affiliate_id
		) );

		affwp_delete_affiliate( $affiliate_id );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::update_referral()
	 */
	public function test_update_referral_no_supplied_visit_id_should_use_the_existing_visit_id() {
		// Update the referral with no new data.
		affiliate_wp()->referrals->update_referral( self::$referrals[0] );

		$result = affwp_get_referral( self::$referrals[0] );

		$this->assertSame( self::$visits[0], $result->visit_id );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::update_referral()
	 */
	public function test_update_referral_with_supplied_visit_id_should_update_the_visit_id() {
		// Update the referral with a new visit ID.
		affiliate_wp()->referrals->update_referral( self::$referrals[0], array(
			'visit_id' => $visit_id = $this->factory->visit->create( array(
				'affiliate_id' => self::$affiliate_id
			) )
		) );

		$result = affwp_get_referral( self::$referrals[0] );

		$this->assertSame( $visit_id, $result->visit_id );

		// Clean up.
		affiliate_wp()->referrals->update_referral( self::$referrals[0], array(
			'visit_id' => self::$visits[0]
		) );
		affwp_delete_visit( $visit_id );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::get_by()
	 */
	public function test_get_by_with_empty_column_should_return_false() {
		$this->assertFalse( affiliate_wp()->referrals->get_by( '', 10 ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::get_by()
	 */
	public function test_get_by_with_empty_row_id_should_return_false() {
		$this->assertFalse( affiliate_wp()->referrals->get_by( 'affiliate_id', '' ) );
	}

	/**
	 * @covers \Affiliate_WP_Referrals_DB::paid_earnings()
	 */
	public function test_paid_earnings_with_empty_date_set_affiliate_id_format_true_should_retrieve_all_time_paid_earnings() {
		$total = 0;
		foreach ( self::$referrals as $referral_id ) {
			$total += affwp_get_referral( $referral_id )->amount;
		}

		$total = affwp_currency_filter( affwp_format_amount( $total ) );

		$this->assertSame( $total, affiliate_wp()->referrals->paid_earnings( '', self::$affiliate_id ) );
	}

}
