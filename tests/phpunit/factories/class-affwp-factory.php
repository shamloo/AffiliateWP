<?php
namespace AffWP\Tests;

/**
 * A factory for making WordPress data with a cross-object type API.
 *
 * Tests should use this factory to generate test fixtures.
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var \AffWP\Tests\Factory\Affiliate
	 */
	public $affiliate;

	/**
	 * @var \AffWP\Tests\Factory\Coupon
	 */
	public $coupon;

	/**
	 * @var \AffWP\Tests\Factory\Creative
	 */
	public $creative;

	/**
	 * @var \AffWP\Tests\Factory\Payout
	 */
	public $payout;

	/**
	 * @var \AffWP\Tests\Factory\Referral
	 */
	public $referral;

	/**
	 * @var \AffWP\Tests\Factory\Visit
	 */
	public $visit;

	function __construct() {
		parent::__construct();

		$this->affiliate = new Factory\Affiliate( $this );
		$this->coupon    = new Factory\Coupon( $this );
		$this->creative  = new Factory\Creative( $this );
		$this->payout    = new Factory\Payout( $this );
		$this->referral  = new Factory\Referral( $this );
		$this->visit     = new Factory\Visit( $this );
	}
}
