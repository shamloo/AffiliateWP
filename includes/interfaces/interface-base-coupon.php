<?php
namespace AffWP\Integration;

/**
 * Base coupon interface.
 *
 * Enforces the contract that an abstract coupon object would, except that we need
 * the \AffWP\Affiliate\Coupon object not to be abstract so it can be instantiated.
 *
 * @since 2.1
 */
interface Base_Coupon {

	/**
	 * Retrieves all coupons for the relevant integration.
	 *
	 * The desired return format is specified below.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @return array|false An array of coupon objects, otherwise false.
	 */
	public function get_integration_coupons();

	/**
	 * Sets the coupon template for the integration.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @return int|false A discount ID if a coupon template is located for the integration, otherwise false.
	 */
	public function set_coupon_template( $meta, $discount_id );

}