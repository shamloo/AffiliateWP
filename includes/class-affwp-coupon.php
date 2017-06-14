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
	 * Object constructor.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function __construct() {
		// foreach ( get_object_vars( $object ) as $key => $value ) {
		// 	$this->$key = $value;
		// }

		// Load coupon-specific classes for each active integration.
		$integrations = affiliate_wp()->integrations->get_enabled_integrations();

		if ( ! empty( $integrations ) ) {

			foreach ( $integrations as $key => $value ) {

				$path = AFFILIATEWP_PLUGIN_DIR . 'includes/integrations/coupons/class-' . $key . '-coupon.php';
				if ( affwp_has_coupon_support( $key ) && file_exists( $path ) ) {
					include $path;
				}
			}
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
	 * Get all coupons for the relevant integration.
	 * The desired return format is specified below.
	 *
	 * @since  2.1
	 *
	 * @return array|bool An array of coupon objects, otherwise false.
	 */
	abstract public function get_integration_coupons();

	/**
	 * Creates an AffiliateWP coupon object when a coupon is created in the integration.
	 *
	 * This method is required in extensions of this class,
	 * and should be hooked onto the action which fires in the integration at the time of coupon creation.
	 *
	 * @param  array  $args  An array of coupon arguments.
	 * @return bool          Returns true if a coupon object was created, otherwise false.
	 * @since  2.1
	 */
	public function create_affwp_coupon( $args ) {

		if ( ! $args ) {
			return false;
		}

		$discount_id = $details->id;

		return affiliate_wp()->affiliates->coupons->add( $details );
	}

	/**
	 * Sets the coupon template used as a template when generating all automatic affiliate coupons.
	 *
	 * For auto-generated coupons, there can be only one AffiliateWP coupon template per integration.
	 *
	 * For each relevant integration, this is set via post meta in the coupon itself.
	 * If `affwp_is_coupon_template` meta is true,
	 * this template is used as the coupon template for this integration.
	 *
	 * The manner by which meta is set in the coupon will vary for each integration.
	 *
	 * For example, in EDD, `affwp_is_coupon_template` post meta is stored
	 * in the post meta of an edd_discount post type post.
	 *
	 * @since  2.1
	 *
	 * @return int|bool Returns a coupon ID if a coupon template is located, otherwise returns false.
	 */
	public function __set_coupon_template() {

		if ( ! method_exists( array( $this, 'set_coupon_template' ) ) ) {
			affiliate_wp()->utils->log( 'A set_coupon_template method must be defined when extending the AffWP\Affiliate\Coupon class for an integration.' );
			return false;
		}

		return $this->set_coupon_template();
	}

	/**
	 * Sanitizes an affiliate object field.
	 *
	 * @param  string $field  Object field.
	 * @param  mixed  $value  Field value.
	 * @return mixed          Sanitized field value.
	 * @since  2.1
	 * @static
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
