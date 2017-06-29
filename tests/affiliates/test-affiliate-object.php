<?php
namespace AffWP\Affiliate\Object;

use AffWP\Tests\UnitTestCase;
use AffWP\Affiliate as Affiliate;

/**
 * Tests for AffWP\Affiliate
 *
 * @covers AffWP\Affiliate
 * @covers AffWP\Base_Object
 *
 * @group affiliates
 * @group objects
 */
class Tests extends UnitTestCase {

	/**
	 * User fixture.
	 *
	 * @access protected
	 * @var int
	 * @static
	 */
	protected static $user_id = 0;

	/**
	 * Affiliate fixture.
	 *
	 * @access protected
	 * @var int
	 * @static
	 */
	protected static $affiliate_id = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$user_id = parent::affwp()->user->create();

		self::$affiliate_id = parent::affwp()->affiliate->create( array(
			'user_id' => self::$user_id
		) );
	}

	/**
	 * @covers AffWP\Base_Object::get_instance()
	 */
	public function test_get_instance_with_invalid_affiliate_id_should_return_false() {
		$this->assertFalse( Affiliate::get_instance( 0 ) );
	}

	/**
	 * @covers AffWP\Base_Object::get_instance()
	 */
	public function test_get_instance_with_affiliate_id_should_return_Affiliate_object() {
		$affiliate = Affiliate::get_instance( self::$affiliate_id );

		$this->assertInstanceOf( 'AffWP\Affiliate', $affiliate );
	}

	/**
	 * @covers AffWP\Affiliate
	 */
	public function test_affiliate_user_object_should_be_lazy_loadable() {
		$this->assertInstanceOf( '\stdClass', affwp_get_affiliate( self::$affiliate_id )->user );
	}

	/**
	 * @covers AffWP\Affiliate
	 */
	public function test_lazy_loaded_user_object_should_contain_first_name_user_meta_in_data_object() {
		$first_name = rand_str( 10 );

		update_user_meta( self::$user_id, 'first_name', $first_name );

		$this->assertEquals( $first_name, affwp_get_affiliate( self::$affiliate_id )->user->first_name );
	}

	/**
	 * @covers AffWP\Affiliate
	 */
	public function test_lazy_loaded_user_object_should_contain_last_name_user_meta_in_data_object() {
		$last_name = rand_str( 10 );

		update_user_meta( self::$user_id, 'last_name', $last_name );

		$this->assertSame( $last_name, affwp_get_affiliate( self::$affiliate_id )->user->last_name );
	}

	/**
	 * @covers AffWP\Affiliate
	 */
	public function test_earnings_property_should_be_of_type_float() {
		affwp_increase_affiliate_earnings( self::$affiliate_id, '1.50' );

		$earnings = affwp_get_affiliate( self::$affiliate_id )->earnings;

		$this->assertSame( 'double', gettype( $earnings ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_default_format_empty_should_return_stored_date_registered() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$this->assertSame( current_time( 'mysql' ), $affiliate->date_registered() );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_true_should_return_datetime_formatted_date_registered() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$expected = date( affiliate_wp()->utils->date->datetime_format, strtotime( $affiliate->date_registered ) );

		$this->assertSame( $expected, $affiliate->date_registered( true ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_date_should_return_date_formatted_date_registered() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$expected = date( affiliate_wp()->utils->date->date_format, strtotime( $affiliate->date_registered ) );

		$this->assertSame( $expected, $affiliate->date_registered( 'date' ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_time_should_return_time_formatted_date_registered() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$expected = date( affiliate_wp()->utils->date->time_format, strtotime( $affiliate->date_registered ) );

		$this->assertSame( $expected, $affiliate->date_registered( 'time' ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_datetime_should_return_datetime_formatted_date_registered() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$expected = date( affiliate_wp()->utils->date->datetime_format, strtotime( $affiliate->date_registered ) );

		$this->assertSame( $expected, $affiliate->date_registered( 'datetime' ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_utc_should_return_datetime_formatted_date_registered() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$expected = date( affiliate_wp()->utils->date->datetime_format, strtotime( $affiliate->date_registered ) );

		$this->assertSame( $expected, $affiliate->date_registered( 'utc' ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_object_should_return_Carbon_object() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$this->assertInstanceOf( '\Carbon\Carbon', $affiliate->date_registered( 'object' ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_timestamp_should_return_timestamp() {
		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$this->assertSame( strtotime( $affiliate->date_registered ), $affiliate->date_registered( 'timestamp' ) );
	}

	/**
	 * @covers \AffWP\Affiliate::date_registered()
	 * @group dates
	 */
	public function test_date_registered_format_real_date_format_should_return_formatted_date_registered() {
		$format = 'l jS \of F Y h:i:s A';

		$affiliate = affwp_get_affiliate( self::$affiliate_id );

		$expected = date( $format, strtotime( $affiliate->date_registered ) );

		$this->assertSame( $expected, $affiliate->date_registered( $format ) );
	}
}
