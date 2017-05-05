<?php
/**
 * Objects: Coupon
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
final class Coupon extends \AffWP\Base_Object {

	/**
	 * Coupon ID. This is the primary key for this object.
	 * Stored as an internal coupon ID.
	 *
	 * @since  2.1
	 * @var    int
	 */
	public $affwp_coupon_id = 0;

	/**
	 * The coupon ID as it exists within the integration. Stored as an internal coupon ID.
	 *
	 * @since  2.1
	 * @var    int
	 */
	public $coupon_id = 0;

	/**
	 * Affiliate ID.
	 *
	 * @since  2.1
	 * @var    int
	 */
	public $affiliate_id = 0;

	/**
	 * IDs for referrals associated with the coupon.
	 *
	 * @since  2.1
	 * @var    array
	 */
	public $referrals = array();

	/**
	 * Coupon integration.
	 *
	 * @since  2.1
	 * @var    string
	 */
	public $integration;

	/**
	 * Coupon status. Either active or inactive.
	 *
	 * @since  2.1
	 * @var    string
	 */
	public $status;

	/**
	 * Coupon expiration date.
	 *
	 * @since  2.1
	 * @var    string
	 */
	public $expiration_date;

	/**
	 * ID of the WordPress user who generated the coupon.
	 *
	 * @since  2.1
	 * @var    int
	 */
	public $owner = 0;

	/**
	 * Gets coupon data from the active integration in which it was generated.
	 *
	 * Specify either the AffiliateWP `affwp_coupon_id`, or the coupon ID from the integration, `coupon_id`.
	 *
	 * @since  2.1
	 *
	 * @return array $data Coupon data.
	 */
	public function data( $coupon_id = 0, $affwp_coupon_id = 0 ) {

		$affwp_coupon_id = $this->affwp_coupon_id;
		$coupon_id       = $this->coupon_id;

		// Bail if either coupon ID is not set.
		if ( ! $affwp_coupon_id || ! $coupon_id ) {
			return false;
		}

		$data = array();

		switch ( $this->integration ) {
			case 'edd':
				// Get EDD-specific coupon meta
				$discount = edd_get_discount( $coupon_id );
				$data[ 'type' ]   = $discount->type;
				$data[ 'code' ]   = $discount->code;
				$data[ 'uses' ]   = $discount->uses;
				$data[ 'status' ] = edd_is_discount_expired( $coupon_id ) ? 'inactive' : 'active';
				$data[ 'expiration_date' ] = $discount->expires;
				$data[ 'integration' ] = $this->integration;
				$data[ 'affiliate_id' ] = $this->affiliate_id;

				break;

			// case 'woocommerce':
			// 	// Get WooCommerce-specific coupon meta
			// 	break;

			// case 'rcp':
			// 	// Get RCP-specific coupon meta
			// 	break;

			default:
				$data = false;
				break;
		}
	}

	/**
	 * Token to use for generating cache keys.
	 *
	 * @var    string
	 * @since  2.1
	 * @static
	 *
	 * @see AffWP\Base_Object::get_cache_key()
	 */
	public static $cache_token = 'affwp_coupons';

	/**
	 * Database group.
	 *
	 * Used in \AffWP\Base_Object for accessing the affiliates DB class methods.
	 *
	 * @since 2.1
	 * @var string
	 */
	public static $db_group = 'affiliates:coupons';

	/**
	 * Object type.
	 *
	 * Used as the cache group and for accessing object DB classes in the parent.
	 *
	 * @since  2.1
	 * @var    string
	 * @static
	 */
	public static $object_type = 'coupons';

	/**
	 * Sanitizes an affiliate object field.
	 *
	 *
	 * @param  string $field  Object field.
	 * @param  mixed  $value  Field value.
	 * @return mixed          Sanitized field value.
	 * @since  2.1
	 * @static
	 */
	public static function sanitize_field( $field, $value ) {
		if ( in_array( $field, array( 'coupon_id', 'affiliate_id', 'ID', 'owner' ) ) ) {
			$value = (int) $value;
		}

		if ( 'referrals' === $field ) {
			$value = implode( ',', wp_parse_id_list( $value ) );
		}

		if ( 'expiration_date' === $field ) {
			$value = strtotime( $value );
		}

		return $value;
	}

}
