<?php
namespace AffWP\Coupon\Functions;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Coupon functions in coupon-functions.php.
 *
 * @group coupons
 * @group functions
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
	 * Coupons fixture.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $coupon_id = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$affiliate_id = parent::affwp()->affiliate->create();

		self::$coupon_id = parent::affwp()->coupon->create( array(
			'affiliate_id' => self::$affiliate_id
		) );
	}

	/**
	 * @covers ::affwp_get_coupon()
	 */
	public function test_get_coupon_with_an_invalid_coupon_id_should_return_false() {
		$this->assertFalse( affwp_get_coupon( 0 ) );
	}

	/**
	 * @covers ::affwp_get_coupon()
	 */
	public function test_get_coupon_with_a_valid_coupon_id_should_return_a_coupon_object() {
		$this->assertInstanceOf( 'AffWP\Affiliate\Coupon', affwp_get_coupon( self::$coupon_id ) );
	}

	/**
	 * @covers ::affwp_get_coupon()
	 */
	public function test_get_coupon_with_an_invalid_coupon_object_should_return_false() {
		$this->assertFalse( affwp_get_coupon( new \stdClass() ) );
	}

	/**
	 * @covers ::affwp_get_coupon()
	 */
	public function test_get_coupon_with_a_valid_coupon_object_should_return_a_coupon_object() {
		$coupon = affwp_get_coupon( self::$coupon_id );

		$this->assertInstanceOf( 'AffWP\Affiliate\Coupon', affwp_get_coupon( $coupon ) );
	}

	/**
	 * @covers ::affwp_add_coupon()
	 */
	public function test_add_coupon_without_affiliate_id_should_return_false() {
		$this->assertFalse( affwp_add_coupon() );
	}

	/**
	 * @covers ::affwp_add_coupon()
	 */
	public function test_add_coupon_with_empty_integration_should_return_false() {
		$this->assertFalse( affwp_add_payout( array(
			'affiliate_id' => 1
		) ) );
	}

	/**
	 * @covers ::affwp_add_coupon()
	 */
	public function test_add_coupon_should_return_coupon_id_on_success() {
		$coupon = affwp_add_coupon( array(
			'affiliate_id'          => $affiliate_id = $this->factory->affiliate->create(),
			'integration'           => 'tests',
			'integration_coupon_id' => 1
		) );

		$this->assertTrue( is_numeric( $coupon ) );

		// Clean up.
		affwp_delete_coupon( $coupon );
	}

	/**
	 * @covers ::affwp_delete_coupon()
	 */
	public function test_delete_coupon_should_return_false_if_invalid_coupon_id() {
		$this->assertFalse( affwp_delete_coupon( 0 ) );
	}

	/**
	 * @covers ::affwp_delete_coupon()
	 */
	public function test_delete_coupon_should_return_false_if_invalid_coupon_object() {
		$this->assertFalse( affwp_delete_coupon( new \stdClass() ) );
	}

	/**
	 * @covers ::affwp_delete_coupon()
	 */
	public function test_delete_coupon_should_return_true_if_coupon_deleted_successfully() {
		$coupon = $this->factory->coupon->create();

		$this->assertTrue( affwp_delete_coupon( $coupon ) );
	}

	/**
	 * @covers ::affwp_get_coupon_status_label()
	 */
	public function test_get_coupon_status_label_should_return_false_if_invalid_coupon() {
		$this->assertFalse( affwp_get_coupon_status_label( 0 ) );
		$this->assertFalse( affwp_get_coupon_status_label( new \stdClass() ) );
	}

	/**
	 * @covers ::affwp_get_coupon_status_label()
	 */
	public function test_get_coupon_status_label_should_return_active_status_by_default() {
		$this->assertSame( 'Active', affwp_get_coupon_status_label( self::$coupon_id ) );
	}

	/**
	 * @covers ::affwp_get_coupon_status_label()
	 */
	public function test_get_coupon_status_label_should_return_coupon_status_label() {
		$coupon_id = $this->factory->coupon->create();

		$this->assertSame( 'Active', affwp_get_coupon_status_label( $coupon_id ) );

		// Clean up.
		affwp_delete_coupon( $coupon_id );
	}

	/**
	 * @covers ::affwp_get_coupon_status_label()
	 */
	public function test_get_coupon_status_label_should_return_active_if_invalid_status() {
		$coupon_id = $this->factory->coupon->create( array(
			'status' => 'foo'
		) );

		$this->assertSame( 'Active', affwp_get_coupon_status_label( $coupon_id ) );

		// Clean up.
		affwp_delete_coupon( $coupon_id );
	}
}
