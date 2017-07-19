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
 * Adds a coupon object. This function calls the `add()` method in `Affiliate_WP_Coupons_DB`,
 * and does not itself generate a coupon for an integration.
 *
 * For methods which generate coupons for integrations,
 * see `affwp_add_integration_coupon`.
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
 * @return int|false $coupon The newly-added coupon object, otherwise false.
 */
function affwp_add_coupon( $args = array() ) {

	if ( $coupon = affiliate_wp()->affiliates->coupons->add( $args ) ) {
		/**
		 * Fires immediately after a coupon has been added.
		 *
		 * @since 2.1
		 *
		 * @param int $coupon ID of the newly-added coupon.
		 */
		do_action( 'affwp_add_coupon', $coupon );

	}

	return $coupon;
}

/**
 * Deletes a coupon.
 *
 * @since 2.1
 *
 * @param int|\AffWP\Affiliate\Coupon $coupon_id  AffiliateWP coupon ID or object.
 * @return bool True if the coupon was successfully deleted, otherwise false.
 */
function affwp_delete_coupon( $coupon ) {
	if ( ! $coupon = affwp_get_coupon( $coupon ) ) {
		return false;
	}

	if ( affiliate_wp()->affiliates->coupons->delete( $coupon->ID, 'coupon' ) ) {
		/**
		 * Fires immediately after a coupon has been deleted.
		 *
		 * @since 2.1
		 *
		 * @param int $coupon_id Core coupon ID.
		 */
		do_action( 'affwp_delete_coupon', $coupon->ID );

		return true;
	}

	return false;
}

/**
 * Retrieves all coupons associated with a specified affiliate.
 *
 * @since 2.1
 *
 * @param int $affiliate_id Affiliate ID.
 * @return array An array of coupon objects associated with the affiliate.
 */
function affwp_get_affiliate_coupons( $affiliate_id ) {

	$args = array(
		'affiliate_id' => $affiliate_id,
		'number'       => -1
	);

	$coupons = affiliate_wp()->affiliates->coupons->get_coupons( $args );

	/**
	 * Returns coupon objects filtered by a provided affiliate ID.
	 *
	 * @since 2.1
	 *
	 * @param array $coupons      Affiliate coupons.
	 * @param int   $affiliate_id Affiliate ID.
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

	/**
	 * Filters the coupon status label.
	 *
	 * @since 2.1
	 *
	 * @param string                 $label  A localized version of the coupon status label.
	 * @param AffWP\Affiliate\Coupon $coupon Coupon object.
	 */
	return apply_filters( 'affwp_coupon_status_label', $label, $coupon );
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
 * Retrieves an array of coupon IDs based on the specified AffiliateWP integration and affiliate ID.
 *
 * @since 2.1
 *
 * @param array $args {
 *     Arguments for retrieving coupons by integration.
 *
 *     @type int    $affiliate_id Affiliate ID
 *     @type string $integration  Integration.
 * }
 * @return array Array of coupons based on the specified AffiliateWP integration, otherwise empty array.
 */
function affwp_get_coupons_by_integration( $args ) {

	$coupons   = array();
	$coupon_id = 0;

	if ( isset( $args[ 'coupon_id' ] ) ) {

		if ( affwp_get_coupon( $args[ 'coupon_id' ] ) ) {
			$coupon_id = is_int( $args[ 'coupon_id' ] ) ? absint( $args[ 'coupon_id' ] ) : 0;
		}
	}

	if ( ! isset( $args[ 'integration' ] ) ) {
		affiliate_wp()->utils->log( 'affwp_get_coupons_by_integration: Unable to determine integration when querying coupons.' );
		return $coupons;
	}

	if ( ! isset( $args[ 'affiliate_id' ] ) ) {
		affiliate_wp()->utils->log( 'affwp_get_coupons_by_integration: Unable to determine affiliate ID when querying coupons.' );
		return $coupons;
	}

	if ( affwp_has_coupon_support( $args['integration'] ) ) {
		// Cycle through active integrations, and gets all coupons for the given affiliate ID.
		switch ( $args[ 'integration' ] ) {
			case 'edd':
				// Only retrieve active EDD discounts.
				$discount_args = array(
					'post_status'              => 'active',
					'affwp_discount_affiliate' => $args[ 'affiliate_id' ]
				);

				// Returns an array of WP Post objects.
				$discounts = edd_get_discounts( $discount_args );

				if ( $discounts ) {
					foreach( $discounts as $discount ) {

						$referrals = affwp_get_coupon_referrals( $discount->ID, 'edd' );
						$referrals = implode( ', ', wp_list_pluck( $referrals, 'referral_id' ) );

						$coupons[ $discount->ID ] = array(
							'integration_coupon_id' => $discount->ID,
							'coupon_id'             => $coupon_id,
							'integration'           => 'edd',
							'coupon_code'           => get_post_meta( $discount->ID, '_edd_discount_code', true ),
							'referrals'             => $referrals

						);
					}
				}

				break;

			default:
				affiliate_wp()->utils->log( 'Unable to determine integration when querying coupons in affwp_get_coupons_by_integration.' );
				break;
		}
	}

	if ( empty( $coupons ) ) {
		affiliate_wp()->utils->log( 'Unable to locate coupons for this integration.' );
	}

	return $coupons;
}

/**
 * Returns an array of inegrations which support coupons.
 *
 * @since  2.1
 *
 * @return array Array of integrations.
 */
function affwp_has_coupon_support_list() {

	/**
	 * An array of integration which support coupons.
	 *
	 * @param array $list Array of integrations which support coupons.
	 * @since 2.1
	 */
	return apply_filters( 'affwp_has_coupon_support_list', array(
			'edd'          => 'Easy Digital Downloads',
			'gravityforms' => 'Gravity Forms' ,
			'exchange'     => 'iThemes Exchange',
			'jigoshop'     => 'Jigoshop',
			'lifterlms'    => 'LifterLMS',
			'memberpress'  => 'MemberPress',
			'pmp'          => 'Paid Memberships Pro',
			'pms'          => 'Paid Member Subscriptions',
			'rcp'          => 'Restrict Content Pro',
			'woocommerce'  => 'WooCommerce'

		)
	);
}

/**
 * Checks whether the specified integration has support for coupons in AffiliateWP.
 *
 * @param  string  $integration The integration to check.
 * @return bool                 Returns true if the integration is supported, otherwise false.
 * @since  2.1
 */
function affwp_has_coupon_support( $integration ) {

	if ( empty( $integration ) ) {
		affiliate_wp()->utils->log( 'An integration must be provided when querying via affwp_has_coupon_support.' );
		return false;
	}

	$integrations = affiliate_wp()->integrations->get_enabled_integrations();
	$supported    = affwp_has_coupon_support_list();
	$has_support  = array_key_exists( $integration, $integrations );

	/**
	 * Filters whether the given coupon integration is supported.
	 *
	 * To add support for an integration or additional third-party plugin,
	 * provide a unique name for your integration as the `$integration.`
	 *
	 * An array of coupon-supporting integrations in AffiliateWP core are provided by
	 * `$supported` for reference.
	 *
	 * @since 2.1
	 *
	 * @param bool   $has_support True if the given integration has support, otherwise false.
	 * @param string $integration Integration being checked.
	 * @param array  $supported   Supported integrations.
	 */
	return apply_filters( 'affwp_has_coupon_support', $integration, $supported );
}

/**
 * Retrieves the coupon template ID, if set.
 *
 * @param  string $integration The integration.
 * @return int    The coupon template ID if set, otherwise returns 0.
 * @since  2.1
 */
function affwp_get_coupon_template_id( $integration ) {
	return affiliate_wp()->affiliates->coupons->get_coupon_template_id( $integration );
}

/**
 * Retrieves the coupon template URL for the given integration coupon ID and integration.
 *
 * @since 2.1
 *
 * @param int    $integration_coupon_id The integration coupon ID.
 * @param string $integration           Integration.
 * @return string The template edit URL for the integration coupon ID, otherwise empty string.
 */
function affwp_get_coupon_edit_url( $integration_coupon_id, $integration_id ) {
	return affiliate_wp()->affiliates->coupons->get_coupon_edit_url( $integration_coupon_id, $integration_id );
}

/**
 * Retrieves a list of active integrations with both coupon support and a selected coupon template.
 *
 * @since  2.1
 *
 * @return string $output Formatted list of integration coupon templates, otherwise an error message.
 */
function affwp_get_coupon_templates() {

	$integrations       = affiliate_wp()->integrations->get_enabled_integrations();
	$integration_output = array();
	$output             = '';

	if ( ! empty( $integrations ) ) {

		foreach ( $integrations as $integration_id => $integration_term ) {

			// Ensure that this integration has both coupon support,
			// and a coupon template has also been selected.
			if ( affwp_has_coupon_support( $integration_id ) ) {

				$template_id = affiliate_wp()->affiliates->coupons->get_coupon_template_id( $integration_id );

				if ( ! $template_id ) {
					continue;
				} else {
					$template_url = affiliate_wp()->affiliates->coupons->get_coupon_edit_url( $template_id, $integration_id );

					$integration_output[] = sprintf( '<li data-integration="%1$s">%2$s: %3$s</li>',
						esc_html( $integration_id ),
						esc_html( $integration_term ),
						sprintf( '<a href="%1$s">View coupon (ID %2$s)</a>',
							esc_url( $template_url ),
							esc_html( $template_id )
						)
					);
				}
			}
		}
	}

	if ( ! empty( $integration_output ) ) {
		$output = '<ul class="affwp-coupon-template-list">';

		foreach ( $integration_output as $item_output ) {
			$output .= $item_output;
		}

		$output .= '</ul>';
	}

	return $output ? $output : __( 'No coupon templates have been selected for any active AffiliateWP integrations.', 'affiliate-wp' );

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

	if ( empty( $integration ) ) {
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
		affiliate_wp()->utils->log( sprintf( 'affwp_get_coupon_create_url: The %s integration does not presently have AffiliateWP coupon support.', $integration ) );
		return false;
	}

	if ( $html ) {
		return '<a class="affwp-inline-link" href="' . esc_url( $url ) . '">' . esc_html__( 'Create Coupon', 'affiliate-wp' ) . '</a>';
	}

	return $url;
}

/**
 * Integration-specific coupon functions.
 *
 * @since 2.1
 */

/**
 * Generates a unique coupon code string, used when generating an integration coupon.
 *
 * @param  integer            $affiliate_id  Affiliate ID.
 * @param  string             $integration  Integration.
 * @param  bool               $auto         Whether or not this is an auto-generated process.
 *                                          Default is false.
 *
 * @return mixed array|false  $coupon       Coupon code string if successful, otherwise returns false.
 * @since  2.1
 */
function affwp_generate_coupon_code( $affiliate_id = 0, $integration = '', $auto = false ) {

	$coupon_code = false;

	if ( ! $affiliate_id || empty( $integration ) ) {
		affiliate_wp()->utils->log( 'affwp_generate_coupon_code: Both the integration and the Affiliate ID  must be provided.' );
		return false;
	}

	$template_id = affwp_get_coupon_template_id( $integration );
	$user_login  = affwp_get_affiliate_login( $affiliate_id ) ? affwp_get_affiliate_login( $affiliate_id ) : '';

	/**
	 * Uses the following data to build a coupon code:
	 * - `affiliate_id`
	 * - `user_login`
	 * - Integration name, eg `edd` or `woocommerce`
	 * - The integration coupon template ID
	 */
	if ( $template_id && $user_login ) {
		// Given the following data:
		// affiliate_id = 1
		// user_login = `alf`
		// integration = woocommerce
		// template ID = 123
		//
		// The generated coupon code would be:
		//
		// 1-893f53c159eab9178ab181bad8da4262-woocommerce-123
		$coupon_code = $affiliate_id . '-' . md5( $user_login ) . '-' . $integration . '-' . $template_id;
	} elseif ( $template_id ) {
		// Get the affiliate user name, since the user_login is not available.
		$name = affwp_get_affiliate_name( $affiliate_id );
		$coupon_code = $affiliate_id . '-' . md5( $name ) . '-' . $integration . '-' . $template_id;
	} else {
		// Bail, since the coupon template is not available for this integration.
		affiliate_wp()->utils->log( 'affwp_generate_coupon_code: Unable to determine coupon template ID when generating coupon code for ' . $integration . '. Make sure to set the coupon template for this integration.' );
	}

	/**
	 * Sets the coupon code when generating a coupon for a supported integration.
	 *
	 * Specify a string to use for the coupon code,
	 * ensuring that the strings formatting is supported by the integration's coupon code sanitization.
	 *
	 * @param mixed string|false $coupon_code The generated coupon code string, otherwise returns false.
	 * @since 2.1
	 */
	return apply_filters( 'affwp_generate_coupon_code', $coupon_code );
}

/**
 * Generates a coupon within the specified integration.
 *
 * Each integration should return either the ID of the generated coupon on success, or an array containing
 * the ID as `integration_coupon_id` or `id`.
 *
 * @param  array         $args   Integration coupon arguments.
 * @return object|false  $coupon Coupon object on success, otherwise false.
 * @since  2.2
 */
function affwp_generate_integration_coupon( $args = array() ) {

	if ( ! isset( $args[ 'integration' ] ) || empty( $args[ 'integration'] ) ) {
		affiliate_wp()->utils->log( 'affwp_generate_integration_coupon: The integration must be specified when attempting to generate a coupon for an integration.' );
		return false;
	}

	if ( ! affwp_has_coupon_support( $args[ 'integration' ] ) ) {
		affiliate_wp()->utils->log( 'affwp_generate_integration_coupon: The provided integration does not have coupon support in AffiliateWP at this time. Please see affwp_has_coupon_support_list for a list of compatible integrations.' );
		return false;
	}


	$args[ 'affiliate_id' ] = is_int( $args[ 'affiliate_id' ] ) ? $args[ 'affiliate_id' ] : false;
	$args[ 'template_id' ]  = is_int( $args[ 'template_id' ] )  ? $args[ 'template_id' ] : affwp_get_coupon_template_id( $args[ 'integration' ] );

	// Bail if no affiliate ID or coupon template is provided.
	if ( ! $args[ 'affiliate_id' ] ) {
		affiliate_wp()->utils->log( 'The affiliate ID must be set when generating a coupon for an integration.' );
		return false;
	}

	if ( ! $args[ 'template_id' ] ) {
		affiliate_wp()->utils->log( 'affwp_generate_integration_coupon_edd: The ID of the EDD coupon template must be specified.' );
		return false;
	}

	$integration_data = call_user_func( 'affwp_generate_integration_coupon_' . $args[ 'integration' ], $args );

	if ( ! $integration_data || empty( $integration_data ) ) {
		affiliate_wp()->utils->log( 'affwp_generate_integration_coupon: Could not generate integration coupon via dynamic caller.' );
		return false;
	}

	$integration_coupon_id = false;

	// intval will return a 1 for non-empty arrays.
	if ( ! is_array( $integration_data ) && intval( $integration_data ) ) {
		$integration_coupon_id = $integration_data;
	} elseif ( isset( $integration_data[ 'integration_coupon_id' ] ) ) {
		$integration_coupon_id = absint( $integration_data[ 'integration_coupon_id' ] );
	} else {
		$integration_coupon_id = absint( $integration_data[ 'id' ] );
	}

	// The integration coupon ID is required to generate an internal AffiliateWP coupon object.
	if ( ! is_int( $integration_coupon_id ) ) {
		affiliate_wp()->utils->log( 'affwp_generate_integration_coupon: Could not determine the ID of the integration coupon.' );
		return false;
	}

	$affwp_coupon_args = array(
		'affiliate_id'          => $args[ 'affiliate_id' ],
		'coupon_code'           => $args[ 'coupon_code' ],
		'integration_coupon_id' => $integration_coupon_id,
		'referrals'             => array(),
		'integration'           => $args[ 'integration' ],
		'owner'                 => get_current_user_id(),
		'status'                => 'active',
	);

	return affwp_add_coupon( $affwp_coupon_args );

}

/**
 * Generates an EDD coupon.
 *
 * @param  array              $args    Coupon arguments. The array should contain:
 *     `affiliate_id`
 *     `template_id`
 *     `name`
 *     `coupon_code`
 *     `amount`
 *     `type`
 *     `affwp_discount_affiliate`
 *
 * @return mixed array|false  $coupon  Array of coupon data if successful, otherwise returns false.
 * @see    affwp_generate_integration_coupon
 * @since  2.1
 */
function affwp_generate_integration_coupon_edd( $args = array() ) {

	$discount_args = array();
	$coupon_args   = array();
	$template      = false;

	// Ensure that a coupon template exists before proceeding.
	if ( is_int( $args[ 'template_id' ] ) && edd_get_discount( $args[ 'template_id' ] ) ) {
		$template = edd_get_discount( $args[ 'template_id' ] );
	} else {
		affiliate_wp()->utils->log( 'affwp_generate_integration_coupon_edd: Unable to retrieve the EDD discount tmeplate.' );
		return false;
	}

	/**
	 * If a coupon code is provided, use it.
	 * Otherwise, generate the coupon code string by using the following data:
	 * - Affiliate ID
	 * - Coupon template data
	 * - The date
	 */
	$args[ 'coupon_code' ]  = isset( $args[ 'coupon_code' ] ) && ! empty( $args[ 'coupon_code' ] ) ? sanitize_text_field( $args[ 'coupon_code' ] ) : affwp_generate_coupon_code( $affiliate_id, $args[ 'integration' ] );

	$args[ 'name' ]         = ! empty( $args[ 'name' ] ) ? $args[ 'name' ] : $args[ 'coupon_code' ];
	$args[ 'amount' ]       = $args[ 'amount' ] ? $args[ 'amount' ] : template[ 'coupon_code' ];


	$discount_args = array(
		'name'                     => $template[ 'name' ],
		'amount'                   => $template[ 'amount' ],
		'coupon_code'              => $args[ 'coupon_code' ],
		'type'                     => $template[ 'type' ],
		'expiration'               => $template[ 'expiration' ],
		'affwp_discount_affiliate' => $args[ 'affiliate_id' ],
	);

	return edd_store_discount( $discount_args );
}

