<?php
namespace AffWP\Coupon\Object;

use AffWP\Tests\UnitTestCase;
use AffWP\Affiliate\Coupon;

/**
 * Tests for AffWP\Affiliate\Coupon
 *
 * @covers AffWP\Affiliate\Coupon
 * @covers AffWP\Base_Object
 *
 * @group coupons
 * @group objects
 */
class Tests extends UnitTestCase {

	/**
	 * @covers AffWP\Base_Object::get_instance()
	 */
	public function test_get_instance_with_invalid_coupon_id_should_return_false() {
		$this->assertFalse( Coupon::get_instance( 0 ) );
	}

	/**
	 * @covers AffWP\Base_Object::get_instance()
	 */
	public function test_get_instance_with_coupon_id_should_return_Coupon_object() {
		$coupon_id = $this->factory->coupon->create();

		$coupon = Coupon::get_instance( $coupon_id );

		$this->assertInstanceOf( 'AffWP\Affiliate\Coupon', $coupon );

		// Clean up.
		affwp_delete_coupon( $coupon_id );
	}
}
