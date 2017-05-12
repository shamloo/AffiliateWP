<?php
namespace AffWP\Integrations;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for the Integrations API.
 *
 * @group integrations
 */
class Tests extends UnitTestCase {

	/**
	 * Mock Integrations base class test fixture.
	 *
	 * @access protected
	 * @var    \Affiliate_WP_Base
	 */
	protected $mockBase;

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->mockBase = $this->getMockForAbstractClass( '\Affiliate_WP_Base' );
	}

	/**
	 * @covers \Affiliate_WP_Base::debug
	 */
	public function test_debug_is_set() {
		$this->assertNotNull( $this->mockBase->debug );
	}

	/**
	 * @covers \Affiliate_WP_Base::debug
	 */
	public function test_debug_reflects_debug_mode_setting() {
		$debug_mode = affiliate_wp()->settings->get( 'debug_mode', false );

		$this->assertSame( $debug_mode, $this->mockBase->debug );
	}
}
