<?php
namespace AffWP\Utils\Batch_Process;

use AffWP\Utils;
use AffWP\Utils\Batch_Process as Batch;

$generate_coupons = affiliate_wp()->utils->batch->get( 'generate-coupons' );

if ( $generate_coupons && ! empty( $generate_coupons['file'] ) ) {
	if ( file_exists( $generate_coupons['file'] ) ) {
		require_once( $generate_coupons['file'] );
	}
}

/**
 * Implements a next-generation batch processor for regenerating coupons
 * for affiliates missed in the previous generation.
 *
 * @since 2.1
 *
 * @see \AffWP\Utils\Batch_Process
 * @see \AffWP\Utils\Batch_Process\With_PreFetch
 * @see \AffWP\Utils\Batch_Process\Generate_Coupons
 */
class Regenerate_Coupons extends Generate_Coupons implements Batch\With_PreFetch {

	/**
	 * Batch process ID.
	 *
	 * @access public
	 * @since  2.1
	 * @var    string
	 */
	public $batch_id = 'regenerate-coupons';

	/**
	 * Pre-fetches data to speed up processing.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function pre_fetch() {
		$affiliates_to_process = affiliate_wp()->utils->data->get( "{$this->batch_id}_affiliate_ids" );

		$to_process = 0;

		if ( false === $affiliates_to_process ) {
			$all_affilites = affiliate_wp()->affiliates->get_affiliates( array(
				'status' => 'active',
				'fields' => 'ids'
			) );

			$affiliates_with_coupons = affiliate_wp()->affiliates->coupons->get_coupons( array(
				'fields'                => 'affiliate_id',
				'integration'           => $this->integration,
				'integration_coupon_id' => $this->integration_coupon_id
			) );

			$outstanding = array_diff( $all_affilites, $affiliates_with_coupons );

			affiliate_wp()->utils->data->write( "{$this->batch_id}_affiliate_ids", $outstanding );

			$to_process = count( $outstanding );
		}


		$this->set_total_count( $to_process );
	}

	/**
	 * Processes a single step for generating affiliate coupons.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function process_step() {
		if ( ! $this->integration || ! $this->integration_coupon_id ) {
			return new \WP_Error(
				'missing_integration_data',
				__( 'The integration and coupon template must be defined to regenerate affiliate coupons.', 'affiliate-wp' )
			);
		}

		$current_count = $this->get_current_count();

		$affiliate_ids = affiliate_wp()->utils->data->get( "{$this->batch_id}_affiliate_ids", array() );
		$affiliate_ids = array_slice( $affiliate_ids, $this->get_offset(), $this->per_step, true );

		// If there are no more affiliates to generate coupons for, we're done.
		if ( empty( $affiliate_ids ) ) {
			return 'done';
		}

		$generated = array();

		foreach ( $affiliate_ids as $affiliate_id ) {
			$args = array(
				'affiliate_id'          => $affiliate_id,
				'integration'           => $this->integration,
				'integration_coupon_id' => $this->integration_coupon_id
			);

			$added = affwp_add_coupon( $args );

			if ( $added ) {
				$generated[] = $added;
			}
		}

		$this->set_current_count( $current_count + count( $generated ) );

		return ++$this->step;
	}

	/**
	 * Retrieves a message for the given code.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @param string $code Message code.
	 * @return string Message.
	 */
	public function get_message( $code ) {

		switch( $code ) {

			case 'done':
				$final_count = $this->get_current_count();

				if ( ! $final_count ) {
					$message = __( 'No affiliate coupons were regenerated.', 'affiliate-wp' );
				} else {
					$message = sprintf(
						_n(
							'%1$s affiliate coupon was successfully regenerated for the selected %2$s coupon template.',
							'%1$s affiliate coupons were successfully regenerated for the selected %2$s coupon template.',
							$final_count,
							'affiliate-wp'
						), number_format_i18n( $final_count ), $this->integration
					);
				}
				break;

			default:
				$message = '';
				break;
		}

		return $message;
	}

	/**
	 * Defines logic to execute after the batch processing is complete.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function finish() {
		// Clean up.
		affiliate_wp()->utils->data->delete( "{$this->batch_id}_affiliate_ids" );

		$this->delete_counts();
	}
}
