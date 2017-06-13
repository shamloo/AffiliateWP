<?php
/**
 * Coupon functions
 *
 * @since 2.1
 * @package Affiliate_WP
 */

/**
 * Retrieves a coupon object.
 *
 * @since 2.1
 *
 * @param int|AffWP\Affiliate\Coupon $coupon Coupon ID or object.
 * @return AffWP\Affiliate\Coupon|false Coupon object if found, otherwise false.
 */
function affwp_get_coupon( $coupon = 0 ) {

	if ( is_object( $coupon ) && isset( $coupon->coupon_id ) ) {
		$coupon_id = $coupon->coupon_id;
	} elseif ( is_numeric( $coupon ) ) {
		$coupon_id = absint( $coupon );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->coupons->get_object( $coupon_id );
}

/**
 * Adds a coupon record.
 *
 * @since 2.1
 *
 * @param array $args {
 *     Arguments for adding a new coupon record. Default empty array.
 *
 *     @type int          $affiliate_id    Affiliate ID.
 *     @type int|array    $referrals       Referral ID or array of IDs.
 *     @type string       $integration     Coupon integration.
 *     @type string       $status          Coupon status. Default 'active'.
 *     @type string|array $expiration_date Coupon expiration date.
 * }
 * @return int|false The ID for the newly-added coupon, otherwise false.
 */
function affwp_add_coupon( $args = array() ) {

	if ( empty( $args['integration'] ) || empty( $args['affiliate_id'] ) || empty( $args['integration_coupon_id'] ) ) {
		affiliate_wp()->utils->log( 'Unable to add new coupon object. Please ensure that the integration name, the affiliate ID, and the coupon ID from the integration are specified.' );
		return false;
	}

	if ( $coupon = affiliate_wp()->affiliates->coupons->add( $args ) ) {
		/**
		 * Fires immediately after a coupon has been added.
		 *
		 * @since 2.1
		 *
		 * @param int $coupon ID of the newly-added coupon.
		 */
		do_action( 'affwp_add_coupon', $coupon );

		return $coupon;
	}

	return false;
}

/**
 * Deletes a coupon.
 *
 * @param  int|\AffWP\Affiliate\Coupon $coupon_id  AffiliateWP coupon ID or object.
 * @return bool True if the coupon was successfully deleted, otherwise false.
 * @since  2.1
 */
function affwp_delete_coupon( $coupon ) {
	if ( ! $coupon = affwp_get_coupon( $coupon ) ) {
		return false;
	}

	if ( affiliate_wp()->affiliates->coupons->delete( $coupon->coupon_id, 'coupon' ) ) {
		/**
		 * Fires immediately after a coupon has been deleted.
		 *
		 * @since 2.1
		 *
		 * @param int    $coupon_id  AffiliateWP coupon ID.
	     * @param object $coupon           AffiliateWP coupon object.
		 */
		do_action( 'affwp_delete_coupon', $coupon->coupon_id, $coupon );

		return true;
	}

	return false;
}

/**
 * Retrieves all coupons associated with a specified affiliate.
 *
 * @since  2.1
 *
 * @param  integer $affiliate_id Affiliate ID.
 *
 * @return array   $coupons      An array of coupon objects associated with the affiliate.
 */
function affwp_get_affiliate_coupons( $affiliate_id = 0 ) {

	if ( 0 === $affiliate_id ) {
		affiliate_wp()->utils->log( 'affwp_get_affiliate_coupons: No valid affiliate ID specified.' );
		return false;
	}

	$args = array(
		'affiliate_id' => $affiliate_id
		);

	$coupons = affiliate_wp()->affiliates->coupons->get_coupons( $args );

	/**
	 * Returns coupon objects filtered by a provided affiliate ID.
	 *
	 * @param int    $affiliate_id  Affiliate ID.
	 * @param object $coupons       Coupon objects
	 *
	 * @since 2.1
	 */
	return apply_filters( 'affwp_get_affiliate_coupons', $coupons, $affiliate_id );
}


/**
 * Retrieves the status label for a coupon.
 *
 * @param int|AffWP\Affiliate\Coupon $coupon Coupon ID or object.
 * @return string|false The localized version of the coupon status label, otherwise false.
 * @since 2.1
 */
function affwp_get_coupon_status_label( $coupon ) {

	if ( ! $coupon = affwp_get_coupon( $coupon ) ) {
		return false;
	}

	$statuses = array(
		'active'   => _x( 'Active', 'coupon', 'affiliate-wp' ),
		'inactive' => __( 'Inactive', 'affiliate-wp' ),
	);

	$label = array_key_exists( $coupon->status, $statuses ) ? $statuses[ $coupon->status ] : _x( 'Active', 'coupon', 'affiliate-wp' );
}

/**
 * Retrieves the referrals associated with a coupon.
 *
 * @param  int         $integration_coupon_id  Integration coupon ID.
 * @return array|false                         List of referral objects associated with the coupon,
 *                                             otherwise false.
 * @since  2.1
 */
function affwp_get_coupon_referrals( $integration_coupon_id = 0, $integration = '' ) {

	$referrals = array();

	if ( empty( $integration ) || ! is_int( $integration_coupon_id ) ) {
		return false;
	}

	$referrals = affiliate_wp()->affiliates->coupons->get_referral_ids( $integration_coupon_id );

	return array_map( 'affwp_get_referral', $referrals );
}

/**
 * Returns an array of coupon IDs based on the specified AffiliateWP integration.
 *
 * @param  array              $args     Arguments.
 * @return mixed  bool|array  $coupons  Array of coupons based on the specified AffiliateWP integration.
 * @since  2.1
 */
function affwp_get_coupons_by_integration( $args ) {

	if ( ! isset( $args[ 'integration' ] ) ) {
		affiliate_wp()->utils->log( 'affwp_get_coupons_by_integration: Unable to determine integration when querying coupons.' );
		return false;
	}

	if ( ! isset( $args[ 'affiliate_id' ] ) ) {
		affiliate_wp()->utils->log( 'affwp_get_coupons_by_integration: Unable to determine affiliate ID when querying coupons.' );
		return false;
	}

	$coupons = false;

	// Cycles through active integrations, and gets all coupons for the given affiliate ID.
	switch ( $args[ 'integration' ] ) {
		case 'edd':
			// Only retrieve active EDD discounts.
			$discount_args = array(
				'post_status'              => 'active',
				'affwp_discount_affiliate' => $args[ 'affiliate_id' ]
			);

			// Returns an array of WP Post objects.
			$discounts = edd_get_discounts( $discount_args );

			foreach ($discounts as $discount) {
				$coupons[ $discount->ID ] = array(
					'integration_coupon_id' => $discount->ID,
					'integration'           => 'edd',
					'coupon_code'           => get_post_meta( $discount->ID, '_edd_discount_code', true ),
					'referrals'             => affwp_get_coupon_referrals( $discount->ID, 'edd' )

				);
			}

			break;

		default:
			affiliate_wp()->utils->log( 'Unable to determine integration when querying coupons in affwp_get_coupons_by_integration.' );
			return false;
			break;
	}

	if ( $coupons ) {
		return (array) $coupons;
	} else {
		affiliate_wp()->utils->log( 'Unable to locate coupons for this integration.' );
		return false;
	}
}

/**
 * Checks whether the specified integration has support for coupons in AffiliateWP.
 *
 * @param  string  $integration The integration to check.
 * @return bool                 Returns true if the integration is supported, otherwise false.
 * @since  2.1
 */
function affwp_has_coupon_support( $integration ) {

	// $integrations = affiliate_wp()->integrations->get_enabled_integrations();
	// $is_enabled   = in_array( $integration, $integrations );

	if ( empty( $integration ) ) {
		affiliate_wp()->utils->log( 'An integration must be provided when querying via affwp_has_coupon_support.' );
		return false;
	}

	/**
	 * Integrations with AffiliateWP coupon support.
	 *
	 * @since 2.1
	 *
	 * @var array $supported
	 */
	$supported = array(
		'woocommerce',
		'edd',
		'exchange',
		'rcp',
		'pmp',
		'pms',
		'memberpress',
		'jigoshop',
		'lifterlms',
		'gravityforms'
	);

	return in_array( $integration, $supported );
}

/**
 * Get coupon template ID
 *
 * @param  string $integration The integration.
 * @since  2.1
 *
 * @return int|false Returns the coupon template ID if set. If not, returns false.
 */
function affwp_get_coupon_template_id( $integration ) {
	return affiliate_wp()->affiliates->coupons->get_coupon_template_id( $integration );
}

/**
 * Gets the coupon template url.
 *
 * @param  int $coupon_id          The coupon ID.
 * @param  string $integration_id  The integration ID.
 * @since  2.1
 *
 * @return string|false            Returns the coupon template ID if set. If not, returns false.
 */
function affwp_get_coupon_edit_url( $integration_coupon_id, $integration_id ) {
	return affiliate_wp()->affiliates->coupons->get_coupon_edit_url( $integration_coupon_id, $integration_id );
}

/**
 * Returns a list of active integrations with both coupon support and a selected coupon template.
 *
 * @return string $output  List of integration coupon templates.
 * @since  2.1
 */
function affwp_get_coupon_templates() {

	$templates    = array();
	$has_template = false;
	$integrations = affiliate_wp()->integrations->get_enabled_integrations();

	if ( ! empty( $integrations ) ) {

		$output = '<ul class="affwp-coupon-template-list">';

		foreach ( $integrations as $integration_id => $integration_term ) {

			// Ensure that this integration has both coupon support,
			// and a coupon template has also been selected.
			if ( affwp_has_coupon_support( $integration_id ) ) {

				$template_id  = affiliate_wp()->affiliates->coupons->get_coupon_template_id( $integration_id );

				if ( $template_id ) {

					$has_template = true;
					$template_url = affiliate_wp()->affiliates->coupons->get_coupon_edit_url( $template_id, $integration_id );

					$output .= '<li>' . $integration_id . ': ' . $integration_term . ' : <a href="' . $template_url . '">(' . $template_id . ')</a></li>';
				} else {
					return false;
				}
			}
		}

		$output .= '</ul>';

		return $has_template ? $output : __( 'No coupon templates have been selected for any active AffiliateWP integrations.', 'affiliate-wp' );
	}
}

/**
 * Gets the coupon-creation admin url for the specified integration.
 * Can output wither a raw admin url, or a formatted html anchor containing the link.
 *
 * The affiliate ID is used optionally in cases where data may be passed to the integration.
 *
 * @since  2.1
 *
 * @param  string  $integration   The integration.
 * @param  int     $affiliate_id  Affiliate ID.
 * @param  bool    $html          Whether or not to provide an html anchor tag in the return.
 *                                Specify true to output an anchor tag. Default is false.
 *
 * @return string|false         The coupon creation admin url, otherwise false.
 */
function affwp_get_coupon_create_url( $integration, $affiliate_id = 0, $html = false ) {

	$url = false;

	if ( empty( $integration ) || ! $integration ) {
		return false;
	}

	if ( affwp_has_coupon_support( $integration ) ) {

		$user_name = affwp_get_affiliate_username( $affiliate_id );

		switch ( $integration ) {
			case 'edd':
				$url = admin_url( 'edit.php?post_type=download&page=edd-discounts&edd-action=add_discount&user_name=' . $user_name);
				break;

			default:
				break;
		}

	} else {
		affiliate_wp()->utils->log( 'affwp_get_coupon_create_url: This integration does not presently have AffiliateWP coupon support.' );
		return false;
	}

	if ( $html ) {
		return '<a class="affwp-inline-link" href="' . $url . '">Create Coupon</a>';
	}

	return $url;
}
