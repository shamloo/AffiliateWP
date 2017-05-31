<?php
namespace AffWP\REST\v1;

use AffWP\Tests\UnitTestCase;

/**
 * REST Tests
 *
 * @group rest
 */
class Tests extends UnitTestCase {

	/**
	 * Affiliate test fixtures.
	 *
	 * @access protected
	 * @var array
	 * @static
	 */
	protected static $affiliates;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$affiliates = parent::affwp()->affiliate->create_many( 3 );
	}

	/**
	 * @covers ::affwp_register_rest_field()
	 */
	public function test_register_rest_field_should_return_false_for_earlier_than_44() {
		global $wp_version;

		$original_version = $wp_version;

		$GLOBALS['wp_version'] = '4.3';

		$this->assertFalse( affwp_register_rest_field( 'affiliate', 'field' ) );

		// Clean up.
		$GLOBALS['wp_version'] = $original_version;
	}

	/**
	 * @covers ::affwp_register_rest_field()
	 */
	public function test_register_rest_field_with_affiliate_type_should_register_affiliate_field() {
		$field_name = 'foobar';

		affwp_register_rest_field( 'affiliate', $field_name );

		$fields = affiliate_wp()->affiliates->REST->get_additional_fields( 'affwp_affiliate' );

		$this->assertSame( true, isset( $fields[ $field_name ] ) );
	}

	/**
	 * @covers ::affwp_register_rest_field()
	 */
	public function test_register_rest_field_with_creative_type_should_register_creative_field() {
		$field_name = 'foobar';

		affwp_register_rest_field( 'creative', $field_name );

		$fields = affiliate_wp()->creatives->REST->get_additional_fields( 'affwp_creative' );

		$this->assertSame( true, isset( $fields[ $field_name ] ) );
	}

	/**
	 * @covers ::affwp_register_rest_field()
	 */
	public function test_register_rest_field_with_payout_type_should_register_payout_field() {
		$field_name = 'foobar';

		affwp_register_rest_field( 'payout', $field_name );

		$fields = affiliate_wp()->affiliates->payouts->REST->get_additional_fields( 'affwp_payout' );

		$this->assertSame( true, isset( $fields[ $field_name ] ) );
	}

	/**
	 * @covers ::affwp_register_rest_field()
	 */
	public function test_register_rest_field_with_referral_type_should_register_referral_field() {
		$field_name = 'foobar';

		affwp_register_rest_field( 'referral', $field_name );

		$fields = affiliate_wp()->referrals->REST->get_additional_fields( 'affwp_referral' );

		$this->assertSame( true, isset( $fields[ $field_name ] ) );
	}

	/**
	 * @covers ::affwp_register_rest_field()
	 */
	public function test_register_rest_field_with_visit_type_should_register_visit_field() {
		$field_name = 'foobar';

		affwp_register_rest_field( 'visit', $field_name );

		$fields = affiliate_wp()->visits->REST->get_additional_fields( 'affwp_visit' );

		$this->assertSame( true, isset( $fields[ $field_name ] ) );
	}

}
