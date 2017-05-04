<?php
namespace AffWP\Utils\Batch_Process;

use AffWP\Utils\Batch_Process as Batch;

/**
 * Implements a batch processor for importing referrals from a CSV file.
 *
 * @since 2.0
 *
 * @see \AffWP\Utils\Batch_Process\Import\CSV
 * @see \AffWP\Utils\Batch_Process\With_PreFetch
 */
class Import_Referrals extends Batch\Import\CSV implements Batch\With_PreFetch {

	/**
	 * Instantiates the batch process.
	 *
	 * @param string $_file
	 * @param int    $_step
	 */
	public function __construct( $_file = '', $_step = 1 ) {

		$fields = affiliate_wp()->referrals->get_columns();

		unset( $fields['referral_id'] );

		$fields   = array_keys( $fields );
		$fields   = array_fill_keys( $fields, '' );

		$this->map_fields( $fields );

		parent::__construct( $_file, $_step );
	}

	/**
	 * Initializes the batch process.
	 *
	 * This is the point where any relevant data should be initialized for use by the processor methods.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function init( $data = null ) {
		if ( null !== $data ) {
			if ( ! empty( $data['affwp-import-field'] ) ) {
				$this->data = $data['affwp-import-field'];
			}
		}
	}

	/**
	 * Pre-fetches data to speed up processing.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function pre_fetch() {
		$total_to_import = $this->get_total_count();

		if ( false === $total_to_import  ) {
			$this->set_total_count( absint( $this->total ) );
		}
	}

	/**
	 * Processes a single step of importing referrals.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @return int|string
	 */
	public function process_step() {
		if ( ! $this->can_import() ) {
			wp_die( __( 'You do not have permission to import data.', 'affiliate-wp' ), __( 'Error', 'affiliate-wp' ), array( 'response' => 403 ) );
		}

		$running_count = 0;
		$current_count = $this->get_current_count();
		$offset        = $this->get_offset();

		if ( $current_count >= $this->get_total_count() ) {
			return 'done';
		}

		$data = $this->get_data();

		if ( $data ) {

			$data = array_slice( $data, $offset, $this->per_step, true );

			foreach ( $data as $key => $row ) {
				$args = $this->map_row( $row );

				// Required fields.
//				if ( empty( $args['email'] ) ) {
//					continue;
//				}

				// Match with affiliate (maybe instantiate Import_Affiliates to leverage user-creation logic?
//				$user_id = $this->create_user( $args );
//
//				if ( $user_id ) {
//					// Check for an existing affiliate for this user.
//					if ( $affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_id ) ) {
//						continue;
//					} else {
//						$args['user_id'] = $user_id;
//					}
//				} else {
//					continue;
//				}
//
//				$args['user_id'] = $user_id;

				if ( false !== affwp_add_referral( $args ) ) {
					$running_count++;
				}
			}
		}

		$this->set_current_count( $current_count + $this->per_step );
		$this->set_running_count( $this->get_running_count() + $running_count );

		return ++$this->step;
	}

	/**
	 * Retrieves a message for the given code.
	 *
	 * @access public
	 * @since  2.0
	 *
	 * @param string $code Message code.
	 * @return string Message.
	 */
	public function get_message( $code ) {

		switch( $code ) {

			case 'done':
				$final_count = $this->get_running_count();
				$total_count = $this->get_total_count();
				$skipped     = $final_count < $total_count ? $total_count - $final_count : 0;

				if ( 0 == $final_count ) {

					$message = __( 'No new referrals were imported.', 'affiliate-wp' );

				} else {

					$message = sprintf(
						_n(
							'%s referral was successfully imported.',
							'%s referrals were successfully imported.',
							$final_count,
							'affiliate-wp'
						), number_format_i18n( $final_count )
					);

				}


				if ( $skipped > 0 ) {
					$message .= sprintf( ' ' .
	                     _n(
		                     '%s other existing referral or invalid row was skipped.',
		                     '%s other existing referrals or invalid rows were skipped.',
		                     $skipped,
		                     'affiliate-wp'
	                     ), number_format_i18n( $skipped )
					);
				}

				// Add a link to manage affiliates in the success message.
				$message .= ' ' . affwp_admin_link( 'referrals', __( 'Manage your referrals.', 'affiliate-wp' ) );

				break;

			default:
				$message = '';
				break;
		}

		return $message;
	}

	/**
	 * Defines logic to execute once batch processing is complete.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function finish() {
		// Invalidate the affiliates cache.
		wp_cache_set( 'last_changed', microtime(), 'referrals' );

		parent::finish();
	}

}
