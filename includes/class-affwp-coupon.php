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
class Coupon extends \AffWP\Base_Object {

	/**
	 * Coupon ID. This is the primary key for this object.
	 * Stored as an internal coupon ID.
	 *
	 * @since  2.1
	 * @var    int
	 */
	public $coupon_id = 0;

	/**
	 * The coupon ID as it exists within the integration. Stored as an internal coupon ID.
	 *
	 * @since  2.1
	 * @var    int
	 */
	public $integration_coupon_id = 0;

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
	 * This data should be specified by each integrations
	 * AffWP_{integration}_Coupon class which extends `AFFWP_Coupon`,
	 * and should be hooked onto an action within the integration which fires
	 * at the time of coupon creation.
	 *
	 * If an object is provided by the integration, it should be casted to an array.
	 *
	 * This method does not directly provide data.
	 *
	 * @since  2.1
	 * @param  int  $coupon_id  The coupon ID.
	 * @return bool $data       Returns false in the base class.
	 *                          Returns an aray in each integration coupon class, otherwise returns false.
	 */
	public function data( $integration_coupon_id ) {

		$coupon_id             = $this->coupon_id;
		$integration_coupon_id = $this->integration_coupon_id;

		if ( ! isset( $coupon_id ) || false === $coupon_id ) {

			// Attempt to determine the coupon ID from the array.
			if ( isset( $coupon['id'] ) && is_int( $coupon['id'] ) ) {

				// Cast to an array if needed.
				if ( is_object( $coupon ) ) {
					$coupon = (array) $coupon;
				}

				$coupon_id = $coupon['id'];

			} else {
				// Bail if the integration's coupon ID is still not set.
				return false;
			}
		}

		$data = false;

		return $data;
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
	 * Sanitizes a coupon object field.
	 *
	 * @access public
	 * @since  2.1
	 * @static
	 *
	 * @param string $field  Object field.
	 * @param mixed  $value  Field value.
	 * @return mixed Sanitized field value.
	 */
	public static function sanitize_field( $field, $value ) {
		if ( in_array( $field, array( 'integration_coupon_id', 'coupon_id', 'affiliate_id', 'ID', 'owner' ) ) ) {
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
