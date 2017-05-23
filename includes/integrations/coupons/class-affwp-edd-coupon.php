<?php
/**
 * EDD_Coupon class.
 *
 * Generates EDD Discounts.
 *
 * @package AffiliateWP
 * @category Core
 *
 * @since 2.1
 */

namespace AffWP\Affiliate;

/**
 * Implements a coupon object.
 *
 * @see   AffWP\Base_Object
 * @see   affwp_get_coupons()
 * @since 2.1
 *
 * @property-read int $ID Alias for `$coupon_id`.
 */
class EDD_Coupon extends \AffWP\Affiliate\Coupon {

	/**
	 * Defines the integration.
	 *
	 * @since  2.1
	 *
	 * @return string Integration string.
	 */
	public function init() {
		$this->integration = 'edd';

		add_action( 'affwp_edd_coupon_store_discount_affiliate', array( $this, 'create_affwp_coupon' ), 10, 2 );

		// edd_add_discount
	}

	/**
	 * Gets coupon data from the active integration in which it was generated.
	 *
	 * Specify either the AffiliateWP `affwp_coupon_id`, or the coupon ID from the integration, `coupon_id`.
	 *
	 * @param  $coupon_id int  The coupon ID provided by the integration.
	 * @return mixed bool|array  $data Coupon data. Returns false if the integration is not set.
	 * @since  2.1
	 */
	public function data( $coupon_id = 0 ) {

		if ( 'edd' !== $this->integration ) {
			return false;
		}

		$this->coupon_id = $coupon_id;

		// Bail if coupon ID is not set.
		if ( ! $coupon_id ) {
			return false;
		}

		$data = array();

		// Get EDD discount meta
		$discount                  = edd_get_discount( $coupon_id );
		$data[ 'type' ]            = $discount->type;
		$data[ 'code' ]            = $discount->code;
		$data[ 'uses' ]            = $discount->uses;
		$data[ 'status' ]          = edd_is_discount_expired( $coupon_id ) ? 'inactive' : 'active';
		$data[ 'expiration_date' ] = $discount->expires;
		$data[ 'integration' ]     = $this->integration;
		$data[ 'affiliate_id' ]    = $this->affiliate_id;

		return $data;
	}

	/**
	 * Creates an EDD discount.
	 *
	 * Requires a coupon object or array from the integration in which
	 * the coupon is generated.
	 *
	 * @param  array  $args  An array of coupon arguments.
	 * @return bool          Returns true if an EDD discount was created, otherwise false.
	 * @since  2.1
	 */
	public function create_coupon( $args ) {

		$args = $this->get_coupon_template();

		$details = array(
			'code'              => $args[ 'code' ],
			'name'              => $args[ 'name' ],
			'status'            => $args[ 'status' ],
			'uses'              => $args[ 'uses' ],
			'max_uses'          => $args[ 'max_uses' ],
			'amount'            => $args[ 'amount' ],
			'start'             => $args[ 'start' ],
			'expiration'        => $args[ 'expiration' ],
			'type'              => $args[ 'type' ],
			'min_price'         => $args[ 'min_price' ],
			'product_reqs'      => $args[ 'product_reqs' ],
			'product_condition' => $args[ 'product_condition' ],
			'excluded_products' => $args[ 'excluded_products' ],
			'is_not_global'     => $args[ 'is_not_global' ],
			'is_single_use'     => $args[ 'is_single_use' ]
		);

		return edd_store_discount( $details );
	}

	/**
	 * Creates an AffiliateWP coupon object when a coupon is created in the integration.
	 * Requires an EDD discount post ID.
	 *
	 * @param  array  $args  An array of coupon arguments.
	 * @return bool          Returns true if a coupon object was created, otherwise false.
	 * @since  2.1
	 */
	public function create_affwp_coupon( $discount_id = 0, $args = array() ) {

		if ( 0 === $discount_id ) {
			return false;
		}

		$args = $this->data( $discount_id );

		return affiliate_wp()->coupons->add( $args );
	}

	public function get_integration_coupons() {
		$discounts = edd_get_discounts(
			array(
				'meta_key'       => 'affwp_is_coupon_template',
				'meta_value'     => 1,
				'post_status'    => 'active'
			)
		);

		return $discounts;
	}

	/**
	 * Gets the EDD coupon template used as a basis for generating all automatic affiliate coupons.
	 * Searches for post meta of `affwp_is_coupon_template`.
	 * @since  2.1
	 *
	 * @return mixed int|bool Returns an EDD discount ID if a coupon template is located in EDD, otherwise returns false.
	 */
	public function get_coupon_template() {

		if ( ! affiliate_wp()->settings->get( 'auto_generate_coupons_enabled' ) ) {
			return false;
		}

		$discount = edd_get_discount(
			array(
				'meta_key'       => 'affwp_is_coupon_template',
				'meta_value'     => 1,
				'post_status'    => 'active'
			)
		);


		return $discount->id;
	}

	/**
	 * Gets the EDD coupon template used as a basis for generating all automatic affiliate coupons.
	 * Searches for post meta of `affwp_is_coupon_template`.
	 * @since  2.1
	 *
	 * @return mixed int|bool Returns an EDD discount ID if a coupon template is located in EDD, otherwise returns false.
	 */
	public function set_coupon_template( $details, $discount_id = 0 ) {

		if ( ! $discount_id || ! affiliate_wp()->settings->get( 'auto_generate_coupons_enabled' ) ) {
			return false;
		}

		$discount = edd_get_discount(
			array(
				'meta_key'       => 'affwp_is_coupon_template',
				'meta_value'     => 1,
				'post_status'    => 'active'
			)
		);


	}
}
