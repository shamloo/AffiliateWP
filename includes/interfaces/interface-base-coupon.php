<?php
namespace AffWP\Affiliate\Coupon;

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
	 * Defines the integration property of this class, such as `edd` or `woocommerce`.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @return string Integration string.
	 */
	public function init();

	/**
	 * Get all coupons for the relevant integration.
	 * The desired return format is specified below.
	 *
	 * @since  2.1
	 *
	 * @return array|bool An array of coupon objects, otherwise false.
	 */
	public function get_integration_coupons();

}