<?php
namespace AffWP\Coupon\Database;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Affiliate_WP_Coupons_DB class
 *
 * @covers Affiliate_WP_Coupons_DB
 * @group database
 * @group coupons
 */
class Tests extends UnitTestCase {

	/**
	 * Affiliate fixture.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $affiliate_id = 0;

	/**
	 * Referrals fixture.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $referrals = array();

	/**
	 * Users fixtures.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $users = array();

	/**
	 * Coupon fixture.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $coupons = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$affiliate_id = parent::affwp()->affiliate->create();

		self::$coupons = parent::affwp()->coupon->create_many( 4, array(
			'affiliate_id' => self::$affiliate_id
		) );

		self::$users = parent::affwp()->user->create_many( 2 );

		self::$referrals = parent::affwp()->referral->create_many( 4, array(
			'affiliate_id' => self::$affiliate_id
		) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::add()
	 */
	public function test_add_should_return_false_if_affiliate_id_undefined() {
		$this->assertFalse( affiliate_wp()->affiliates->coupons->add() );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::add()
	 */
	public function test_add_should_return_false_if_invalid_affiliate_id() {
		$this->assertFalse( affiliate_wp()->affiliates->coupons->add( array(
			'affiliate_id' => rand( 500, 5000 )
		) ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::add()
	 */
	public function test_add_should_return_false_if_no_integration_supplied() {
		$coupon_id = affiliate_wp()->affiliates->coupons->add( array(
			'affiliate_id'          => self::$affiliate_id,
			'integration_coupon_id' => 1
		) );

		$this->assertFalse( $coupon_id );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::add()
	 */
	public function test_add_should_return_false_if_no_integration_coupon_id_supplied() {
		$coupon_id = affiliate_wp()->affiliates->coupons->add( array(
			'affiliate_id' => self::$affiliate_id,
			'integration'  => 'tests'
		) );

		$this->assertFalse( $coupon_id );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::add()
	 */
	public function test_add_should_convert_array_of_referral_ids_to_comma_separated_string() {
		$referrals_string = implode( ',', self::$referrals );

		$coupon_id = affiliate_wp()->affiliates->coupons->add( array(
			'affiliate_id'          => self::$affiliate_id,
			'integration'           => 'tests',
			'integration_coupon_id' => 1,
			'referrals'             => self::$referrals,
		) );

		$this->assertSame( $referrals_string, affiliate_wp()->affiliates->coupons->get_column( 'referrals', $coupon_id ) );

		// Clean up.
		affwp_delete_coupon( $coupon_id );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::coupon_exists()
	 */
	public function test_coupon_exists_should_return_false_if_coupon_does_not_exist() {
		$this->assertFalse( affiliate_wp()->affiliates->coupons->coupon_exists( 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::coupon_exists()
	 */
	public function test_coupon_exists_should_return_true_if_coupon_exists() {
		$this->assertTrue( affiliate_wp()->affiliates->coupons->coupon_exists( self::$coupons[0] ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::coupon_exists()
	 */
	public function test_column_defaults_should_return_zero_for_coupon_id() {
		$defaults = affiliate_wp()->affiliates->coupons->get_column_defaults();

		$this->assertSame( 0, $defaults['coupon_id'] );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::coupon_exists()
	 */
	public function test_column_defaults_should_return_active_status() {
		$defaults = affiliate_wp()->affiliates->coupons->get_column_defaults();

		$this->assertSame( 'active', $defaults['status'] );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::coupon_exists()
	 */
	public function test_column_defaults_should_return_the_current_date_for_date() {
		$defaults = affiliate_wp()->affiliates->coupons->get_column_defaults();

		$this->assertSame( date( 'Y-m-d H:i:s' ), $defaults['expiration_date'] );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_columns()
	 */
	public function test_get_columns_should_return_all_columns() {
		$columns = affiliate_wp()->affiliates->coupons->get_columns();

		$expected = array(
			'integration_coupon_id' => '%d',
			'coupon_code'           => '%d',
			'coupon_id'             => '%d',
			'affiliate_id'          => '%d',
			'referrals'             => '%s',
			'integration'           => '%s',
			'owner'                 => '%d',
			'status'                => '%s',
			'expiration_date'       => '%s'
		);

		$this->assertEqualSets( $expected, $columns );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_object()
	 */
	public function test_get_object_should_return_false_if_invalid_coupon_id() {
		$this->assertFalse( affiliate_wp()->affiliates->coupons->get_object( 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_object()
	 */
	public function test_get_object_should_return_coupon_object_if_valid_coupon_id() {
		$this->assertInstanceOf( 'AffWP\Affiliate\Coupon', affiliate_wp()->affiliates->coupons->get_object( self::$coupons[0] ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_referral_ids()
	 */
	public function test_get_referral_ids_should_return_empty_array_if_invalid_coupon_id() {
		$this->assertSame( array(), affiliate_wp()->affiliates->coupons->get_referral_ids( 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_referral_ids()
	 */
	public function test_get_referral_ids_should_return_empty_array_if_invalid_coupon_object() {
		$this->assertSame( array(), affiliate_wp()->affiliates->coupons->get_referral_ids( new \stdClass() ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_referral_ids()
	 */
	public function test_get_referral_ids_should_return_an_array_of_referral_ids() {
		$coupon_id = $this->factory->coupon->create( array(
			'affiliate_id' => self::$affiliate_id,
			'referrals'    => self::$referrals
		) );

		$this->assertEqualSets( self::$referrals, affiliate_wp()->affiliates->coupons->get_referral_ids( $coupon_id ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_number_should_return_number_if_available() {
		$coupons = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'number' => 3
		) );

		$this->assertSame( 3, count( $coupons ) );
		$this->assertTrue( count( $coupons ) <= 3 );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_offset_should_offset_number_given() {
		$coupons = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'number' => 3,
			'offset' => 1,
			'fields' => 'ids',
		) );

		$this->assertEqualSets( $coupons, array_slice( self::$coupons, 0, 3 ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_single_coupon_id_should_return_that_coupon() {
		$coupons = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'coupon_id' => self::$coupons[3],
			'fields'    => 'ids',
		) );

		$this->assertCount( 1, $coupons );
		$this->assertSame( self::$coupons[3], $coupons[0] );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_multiple_coupon_ids_should_return_those_coupons() {
		$to_query = array( self::$coupons[0], self::$coupons[2] );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'coupon_id' => $to_query,
			'order'     => 'ASC', // Default descending.
			'fields'    => 'ids',
		) );

		$this->assertCount( 2, $results );
		$this->assertEqualSets( $to_query, $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_single_affiliate_id_should_return_coupons_for_that_affiliate_only() {
		// Total of 5 coupons, two different affiliates.
		$coupon = $this->factory->coupon->create( array(
			'affiliate_id' => $affiliate_id = $this->factory->affiliate->create()
		) );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'affiliate_id' => $affiliate_id,
			'fields'       => 'ids',
		) );

		$this->assertSame( 1, count( $results ) );
		$this->assertSame( $coupon, $results[0] );

		// Clean up.
		affwp_delete_coupon( $coupon );
		affwp_delete_affiliate( $affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_multiple_affiliate_ids_should_return_coupons_for_multiple_affiliates() {
		// Total of 6 coupons, two different affiliates.
		$coupons = $this->factory->coupon->create_many( 2, array(
			'affiliate_id' => $affiliate_id = $this->factory->affiliate->create(),
			'referrals'    => $referrals = $this->factory->referral->create( array(
				'affiliate_id' => $affiliate_id
			) )
		) );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'affiliate_id' => array( $affiliate_id, self::$affiliate_id ),
		) );

		$affiliates = array_unique( wp_list_pluck( $results, 'affiliate_id' ) );

		$this->assertTrue(
			in_array( $affiliate_id, $affiliates, true )
			&& in_array( self::$affiliate_id, $affiliates, true )
		);

		// Clean up.
		affwp_delete_coupon( $coupons[0] );
		affwp_delete_coupon( $coupons[1] );
		affwp_delete_affiliate( $affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_single_integration_coupon_id_should_return_the_coupon_for_that_id() {
		$integration_coupon_id = rand( 1, 100 );

		$coupon = $this->factory->coupon->create( array(
			'affiliate_id'          => self::$affiliate_id,
			'integration_coupon_id' => $integration_coupon_id
		) );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'integration_coupon_id' => $integration_coupon_id,
			'fields'                => 'ids',
		) );

		$this->assertSame( array( $coupon ), $results );

		// Clean up.
		affwp_delete_coupon( $coupon );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_should_default_to_all_statuses() {
		$coupon_ids = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( self::$coupons, $coupon_ids );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_paid_status_should_return_only_paid_status_coupons() {
		$failed_coupons = $this->factory->coupon->create_many( 2, array(
			'status' => 'failed'
		) );

		$coupon_ids = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'status' => 'paid',
			'fields' => 'ids',
		) );

		$this->assertEqualSets( self::$coupons, $coupon_ids );

		// Clean up.
		affwp_delete_coupon( $failed_coupons[0] );
		affwp_delete_coupon( $failed_coupons[1] );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_inactive_status_should_return_only_inactive_status_coupons() {
		$inactive_coupons = $this->factory->coupon->create_many( 2, array(
			'status' => 'inactive'
		) );

		$coupon_ids = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'status' => 'inactive',
			'fields' => 'ids',
		) );

		$this->assertEqualSets( $inactive_coupons, $coupon_ids );

		// Clean up.
		foreach ( $inactive_coupons as $coupon ) {
			affwp_delete_coupon( $coupon );
		}
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_invalid_status_should_default_to_paid_status() {
		$failed = $this->factory->coupon->create_many( 2, array( 'status' => 'failed' ) );

		$coupon_ids = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'status' => 'foo',
			'fields' => 'ids',
		) );

		$this->assertEqualSets( self::$coupons, $coupon_ids );

		// Clean up.
		affwp_delete_coupon( $failed[0] );
		affwp_delete_coupon( $failed[1] );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_owner_with_single_owner_should_return_coupons_only_for_that_owner() {
		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$coupons = $this->factory->coupon->create_many( 2, array(
			'affiliate_id' => self::$affiliate_id,
			'referrals'    => self::$referrals
		) );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'owner'  => $user_id,
			'fields' => 'ids',
		) );

		$this->assertEqualSets( $coupons, $results );

		// Clean up.
		foreach ( $coupons as $coupon ) {
			affwp_delete_coupon( $coupon );
		}
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_owner_with_multiple_owners_should_return_coupons_only_for_those_owners() {
		wp_set_current_user( self::$users[0] );

		$coupons1 = $this->factory->coupon->create_many( 2, array(
			'affiliate_id' => self::$affiliate_id,
			'referrals'    => self::$referrals
		) );

		wp_set_current_user( self::$users[1] );

		$coupons2 = $this->factory->coupon->create_many( 2, array(
			'affiliate_id' => self::$affiliate_id,
			'referrals'    => self::$referrals
		) );

		$combined_coupons = array_merge( $coupons1, $coupons2 );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'owner'  => self::$users,
			'fields' => 'ids',
		) );

		$this->assertEqualSets( $combined_coupons, $results );

		// Clean up.
		foreach ( $combined_coupons as $coupon ) {
			affwp_delete_coupon( $coupon );
		}
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_with_count_true_should_return_a_count_only() {
		$this->assertSame( 4, affiliate_wp()->affiliates->coupons->get_coupons( array(), true ) );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 */
	public function test_get_coupons_should_return_array_of_Coupon_objects_if_not_count_query() {
		$results = affiliate_wp()->affiliates->coupons->get_coupons();

		// Check a random referral.
		$this->assertContainsOnlyType( 'AffWP\Affiliate\Coupon', $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 * @group database-fields
	 */
	public function test_get_coupons_fields_ids_should_return_an_array_of_ids_only() {
		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'fields' => 'ids',
			'order'  => 'ASC', // Default 'DESC'
		) );

		$this->assertEqualSets( self::$coupons, $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 * @group database-fields
	 */
	public function test_get_coupons_invalid_fields_arg_should_return_regular_Coupon_object_results() {
		$coupons = array_map( 'affwp_get_coupon', self::$coupons );

		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'fields' => 'foo'
		) );

		$this->assertEqualSets( $coupons, $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 * @group database-fields
	 */
	public function test_get_coupons_fields_ids_should_return_an_array_of_integer_ids() {
		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'fields' => 'ids'
		) );

		$this->assertContainsOnlyType( 'integer', $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 * @group database-fields
	 */
	public function test_get_coupons_with_no_fields_should_return_an_array_of_affiliate_objects() {
		$results = affiliate_wp()->affiliates->coupons->get_coupons();

		$this->assertContainsOnlyType( 'AffWP\Affiliate\Coupon', $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 * @group database-fields
	 */
	public function test_get_coupons_with_multiple_valid_fields_should_return_an_array_of_stdClass_objects() {
		$results = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'fields' => array( 'coupon_id', 'expiration_date' )
		) );

		$this->assertContainsOnlyType( 'stdClass', $results );
	}

	/**
	 * @covers Affiliate_WP_Coupons_DB::get_coupons()
	 * @group database-fields
	 */
	public function test_get_coupons_fields_array_with_multiple_valid_fields_should_return_objects_with_those_fields_only() {
		$fields = array( 'coupon_id', 'referrals' );

		$result = affiliate_wp()->affiliates->coupons->get_coupons( array(
			'fields' => $fields
		) );

		$object_vars = get_object_vars( $result[0] );

		$this->assertEqualSets( $fields, array_keys( $object_vars ) );

	}

}
