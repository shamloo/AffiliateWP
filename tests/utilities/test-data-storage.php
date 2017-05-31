<?php
namespace AffWP\Utils\Data_Storage;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for the Data Storage class.
 *
 * @covers \AffWP\Utils\Data_Storage
 *
 * @group utils
 * @group data
 */
class Tests extends UnitTestCase {

	/**
	 * Utilities object.
	 *
	 * @access protected
	 * @var    \Affiliate_WP_Utilities
	 */
	protected static $utils;

	/**
	 * Test storage key.
	 *
	 * @access protected
	 * @var    string
	 */
	protected static $storage_key = 'affwp-test';

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$utils = new \Affiliate_WP_Utilities;
	}

	/**
	 * Runs during tear down after each test.
	 *
	 * @access public
	 */
	public function tearDown() {
		self::$utils->data->delete( self::$storage_key );

		parent::tearDown();
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get()
	 */
	public function test_get_should_return_false_given_an_invalid_key() {
		$this->assertFalse( self::$utils->data->get( 'foobar' ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get()
	 */
	public function test_get_with_empty_value_and_default_should_return_default() {
		$this->assertSame( 'foo', self::$utils->data->get( self::$storage_key, 'foo' ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get()
	 */
	public function test_get_with_stored_value_and_default_should_return_stored_value() {
		self::$utils->data->write( self::$storage_key, 'affwp' );

		$this->assertSame( 'affwp', self::$utils->data->get( self::$storage_key, 'foo' ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get()
	 */
	public function test_get_with_stored_serialized_value_should_return_unserialized_value() {
		$data = array( 'foo' => 'bar' );

		self::$utils->data->write( self::$storage_key, $data );

		$result = self::$utils->data->get( self::$storage_key );

		$this->assertEqualSets( $data, $result );
		$this->assertFalse( is_serialized( $result ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::write()
	 */
	public function test_write_with_already_stored_value_should_overwrite() {
		self::$utils->data->write( self::$storage_key, 'affiliates' );

		$this->assertSame( 'affiliates', self::$utils->data->get( self::$storage_key ) );

		self::$utils->data->write( self::$storage_key, 'referrals' );

		$this->assertSame( 'referrals', self::$utils->data->get( self::$storage_key ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get_data_formats()
	 */
	public function test_get_data_formats_should_return_string_decimal_string_for_an_integer_value() {
		$this->assertEqualSets( array( '%s', '%d', '%s' ), self::$utils->data->get_data_formats( 1 ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get_data_formats()
	 */
	public function test_get_data_formats_should_return_string_float_string_for_a_float_value() {
		$this->assertEqualSets( array( '%s', '%f', '%s' ), self::$utils->data->get_data_formats( 1.11 ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::get_data_formats()
	 */
	public function test_get_data_formats_should_return_string_string_string_by_default_or_if_string_value() {
		$this->assertEqualSets( array( '%s', '%s', '%s' ), self::$utils->data->get_data_formats( 'affwp' ) );
	}

	/**
	 * @covers \AffWP\Utils\Data_Storage::delete()
	 */
	public function test_delete_should_delete_the_stored_value() {
		$data = array( 'bar' => 'baz' );

		self::$utils->data->write( self::$storage_key, $data );

		self::$utils->data->delete( self::$storage_key );

		$this->assertFalse( self::$utils->data->get( self::$storage_key ) );
	}

}
