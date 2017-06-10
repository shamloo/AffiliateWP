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

		// Create an affiliate coupon when an EDD coupon is generated
		add_action( 'affwp_add_edd_discount', array( $this, 'create_affwp_coupon' ) );
		add_action( 'edd_post_insert_discount', array( $this, 'set_coupon_template' ), 10, 2 );
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

		if ( ! affwp_has_coupon_support( $this->integration ) ) {
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
	 * @param  array $args          An array of coupon template data, used to populate the new coupon.
	 * @param  int   $affiliate_id  Affiliate ID.
	 * @return bool                 Returns true if an EDD discount was created, otherwise false.
	 * @since  2.1
	 */
	public function create_coupon( $affiliate_id, $args ) {

		if ( ! $affiliate_id ) {

			$suffix = false;

			if ( edd_get_discount( $args->id ) ) {
				$suffix = ' from coupon template' . $args->id . '.';
			}

			$suffix = $suffix ? $suffix : '.';

			affiliate_wp()->utils->log( 'Missing affiliate ID when creating affiliate coupon' . $suffix );

			return false;
		}

		// Get coupon
		$args = $this->get_coupon_template();

		$details = array(
			'code'              => $args[ 'code' ] . '-' . date( 'U' ) . '-' . $affiliate_id,
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

		if ( edd_store_discount( $details ) ) {
			/**
			 * Fires when an EDD discount is created via AffiliateWP.
			 *
			 * @param $details EDD disount properties.
			 * @since 2.1
			 */
			do_action( 'affwp_add_edd_discount', $details );
		}


		return edd_store_discount( $details );
	}

	/**
	 * Gets the active coupons for this integration.
	 *
	 * @return array $discounts Array of EDD discount objects.
	 * @since  2.1
	 */
	public function get_integration_coupons() {
		$discounts = edd_get_discounts(
			array(
				'meta_key'       => 'affwp_is_coupon_template',
				'meta_value'     => 1,
				'post_status'    => 'active',
				'paged'          => true,
			)
		);

		return $discounts;
	}

	/**
	 * Sets the EDD coupon template.
	 * Searches for post meta of `affwp_is_coupon_template`.
	 *
	 * @see AffWP\Affiliate\Coupon::set_coupon_template()
	 * @since  2.1
	 * @return mixed int|bool Returns an EDD discount ID if a coupon template is located in EDD, otherwise returns false.
	 */
	public function set_coupon_template( $meta, $discount_id ) {

		if ( ! $discount_id || ! affiliate_wp()->settings->get( 'auto_generate_coupons_enabled' ) ) {
			affiliate_wp()->utils->log( 'Unable to set coupon template for discount.' );
			return false;
		}

		if ( edd_get_discount( $discount_id ) ) {
			update_post_meta( $discount_id, 'affwp_is_coupon_template', true );
		} else {
			affiliate_wp()->utils->log( 'Could not locate EDD discount by $discount_id when attempting to set it as the AffiliateWP coupon template.' );
			return false;
		}
	}
}
