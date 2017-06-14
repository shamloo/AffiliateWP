<?php
namespace AffWP\Tests\Factory;

class Coupon extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
	}

	function create_object( $args ) {
		$affiliate = new Affiliate();

		// Only create the associated affiliate if one wasn't supplied.
		if ( empty( $args['affiliate_id'] ) ) {
			$args['affiliate_id'] = $affiliate->create();
		}

		if ( empty( $args['integration_coupon_id'] ) ) {
			$args['integration_coupon_id'] = rand( 1, 1000 );
		}

		if ( empty( $args['integration'] ) ) {
			$args['integration'] = 'tests';
		}

		return affiliate_wp()->affiliates->coupons->add( $args );
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \AffWP\Affiliate\Payout|int
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function update_object( $payout_id, $fields ) {
		return affiliate_wp()->affiliates->coupons->update( $payout_id, $fields, '', 'coupon' );
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param int $coupon_id Coupon ID.
	 * @return \AffWP\Affiliate\Coupon|false
	 */
	function get_object_by_id( $coupon_id ) {
		return affwp_get_coupon( $coupon_id );
	}
}
