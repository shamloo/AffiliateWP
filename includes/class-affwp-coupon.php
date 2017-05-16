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
abstract class Coupon extends \AffWP\Base_Object {

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
	 * Object constructor.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @param mixed $object Object for which to populate members.
	 */
	public function __construct( $object ) {
		foreach ( get_object_vars( $object ) as $key => $value ) {
			$this->$key = $value;
		}

		$this->init();
	}

	/**
	 * Defines the integration property of this class, such as `edd` or `woocommerce`.
	 * Must be set by extending classes.
	 *
	 * @since  2.1
	 *
	 * @return string Integration string.
	 */
	abstract public function init();

	/**
	 * Gets coupon data from the active integration in which it was generated.
	 *
	 * This data should be specified by each integration's
	 * AffWP_{integration}_Coupon class which extends `AFFWP_Coupon`,
	 * and be hooked onto an action within the integration which fires
	 * at the time of coupon creation.
	 *
	 * If an object is provided by the integration, it should be casted to an array.
	 *
	 * @since  2.1
	 * @param  array|object $coupon     An array or object of coupon data provided by the integration.
	 *                                  Objects will be casted to arrays.
	 * @param  int          $coupon_id  The coupon ID.
	 * @return array        $data       Coupon data.
	 */
	public function data( $coupon, $coupon_id ) {

		$affwp_coupon_id = $this->affwp_coupon_id;
		$coupon_id       = $this->coupon_id;

		if ( ! isset( $coupon_id ) || false === $coupon_id ) {

			// Attempt to determine the coupon ID from the array.
			if ( isset( $coupon ) && isset( $coupon['id'] ) && is_int( $coupon['id'] ) ) {

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

		$data = array();
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
