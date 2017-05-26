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
		add_action( 'edd_post_insert_discount', 'set_coupon_template', 10, 2 );

		// Discount code tracking actions and filters
		add_action( 'edd_add_discount_form_bottom', array( $this, 'discount_edit' ) );
		add_action( 'edd_edit_discount_form_bottom', array( $this, 'discount_edit' ) );
		add_action( 'edd_post_update_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );
		add_action( 'edd_post_insert_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );
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
	public function get_coupon_template_id() {

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
	 * Shows the affiliate drop down on the discount edit / add screens
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function discount_edit( $discount_id = 0 ) {

		add_filter( 'affwp_is_admin_page', '__return_true' );
		affwp_admin_scripts();

		$user_name    = '';
		$user_id      = 0;
		$affiliate_id = get_post_meta( $discount_id, 'affwp_is_coupon_template', true );
		if( $affiliate_id ) {
			$user_id      = affwp_get_affiliate_user_id( $affiliate_id );
			$user         = get_userdata( $user_id );
			$user_name    = $user ? $user->user_login : '';
		}
?>
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="affwp_is_coupon_template"><?php _e( 'Use this discount as the Affiliate Coupon Template?', 'affiliate-wp' ); ?>
						</label>
					</th>
					<td>
						<input type="checkbox" name="_affwp_is_coupon_template" id="affwp_is_coupon_template" value="1"<?php checked( $disabled, true ); ?> />

						<p class="description"><?php _e( 'Check this option if you would like to use this discount as the template from which all EDD affiliate coupons are generated.', 'affiliate-wp' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
<?php
	}

	public function store_discount_affiliate() {

	}

	/**
	 * Gets the EDD coupon template used as a basis for generating all automatic affiliate coupons.
	 * Searches for post meta of `affwp_is_coupon_template`.
	 * @since  2.1
	 *
	 * @return mixed int|bool Returns an EDD discount ID if a coupon template is located in EDD, otherwise returns false.
	 */
	public function set_coupon_template( $meta, $discount_id ) {

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
