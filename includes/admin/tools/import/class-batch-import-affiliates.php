<?php
namespace AffWP\Utils\Batch_Process;

use AffWP\Utils\Batch_Process as Batch;

/**
 * Implements a batch processor for importing affiliate accounts from a CSV file.
 *
 * @since 2.0
 *
 * @see \AffWP\Utils\Batch_Process\Import\CSV
 * @see \AffWP\Utils\Batch_Process\With_PreFetch
 */
class Import_Affiliates extends Batch\Import\CSV implements Batch\With_PreFetch {

	/**
	 * Whether to use 'strict' mode when sanitizing generated usernames.
	 *
	 * See {@see 'affwp_batch_import_affiliates_strict_usernames'}.
	 *
	 * @access public
	 * @since  2.1
	 * @var    bool
	 */
	public $use_strict;

	/**
	 * Instantiates the batch process.
	 *
	 * @param string $_file
	 * @param int    $_step
	 */
	public function __construct( $_file = '', $_step = 1 ) {

		/**
		 * Filters whether to generate new affiliate usernames using 'strict' mode,
		 * i.e. reduce generated usernames to ASCII-only.
		 *
		 * Notes: Some platform scenarios such as multisite will apply further sanitization
		 * to usernames regardless of whether `$use_strict` is enabled or not.
		 *
		 * @since 2.1
		 *
		 * @param bool $use_strict Whether to use 'strict' mode. Default true.
		 */
		$this->use_strict = apply_filters( 'affwp_batch_import_affiliates_strict_usernames', true );

		$fields = affiliate_wp()->affiliates->get_columns();

		unset( $fields['affiliate_id'] );
		unset( $fields['user_id'] );

		$fields   = array_keys( $fields );
		$fields[] = 'user_name';
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
	 * Processes a single step of importing affiliates.
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

		$current_count = $this->get_current_count();
		$running_count = $this->get_running_count();
		$offset        = $this->get_offset();

		if ( $current_count >= $this->get_total_count() ) {
			return 'done';
		}

		if ( $this->csv->data ) {
			$data = $this->csv->data;

			$data = array_slice( $data, $offset, $this->per_step, true );

			foreach ( $data as $key => $row ) {
				$args = $this->map_row( $row );

				if ( empty( $args['email'] ) ) {
					continue;
				}

				$user_id = $this->create_user( $args );

				if ( $user_id ) {
					$args['user_id'] = $user_id;
				} else {
					continue;
				}

				$args['user_id'] = $user_id;

				if ( false !== affwp_add_affiliate( $args ) ) {
					$running_count++;
				}
			}
		}

		$this->set_current_count( $current_count + $this->per_step );
		$this->set_running_count( $running_count + $running_count );

		return ++$this->step;
	}

	/**
	 * Sets the running count in the temporary data store.
	 *
	 * The "running" count differs from the "current" count because the current count is used
	 * to calculate the percentage and keep the progress bar moving, whereas the running count
	 * is the actual number of items processed.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @param int $count Running count.
	 */
	public function set_running_count( $count ) {
		affiliate_wp()->utils->data->write( "{$this->batch_id}_running_count", $count );
	}

	/**
	 * Retrieves the running count from the temporary data store.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @see set_running_count()
	 *
	 * @return int Running count of processed affiliates.
	 */
	public function get_running_count() {
		return affiliate_wp()->utils->data->get( "{$this->batch_id}_running_count", 0 );
	}

	/**
	 * Maps affiliate fields for pairing with the imported CSV.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @param array $import_fields Fields to import.
	 */
	public function map_fields( $import_fields = array() ) {
		$this->field_mapping = $import_fields;
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

				$message = sprintf(
					_n(
						'%s affiliate was successfully imported.',
						'%s affiliates were successfully imported.',
						$final_count,
						'affiliate-wp'
					), number_format_i18n( $final_count )
				);

				if ( $skipped > 0 ) {
					$message .= sprintf( ' ' .
						_n(
							'%s existing affiliate or invalid row was skipped.',
							'%s existing affiliates or invalid rows were skipped.',
							$skipped,
							'affiliate-wp'
						), number_format_i18n( $skipped )
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
	 * Helper that attempts to create a user account for the new affiliate.
	 *
	 * If a user account is found matching the given payment email, that user ID is returned instead.
	 *
	 * @access protected
	 * @since  2.1
	 *
	 * @param array $args Arguments for adding a new affiliate.
	 * @return int|false User ID if a user was found or derived, otherwise false.
	 */
	protected function create_user( $args ) {
		$defaults = array_fill_keys( array( 'user_login', 'email' ), '' );
		$args     = wp_parse_args( $args, $defaults );

		$user_id = $this->get_user_from_args( $args );

		if ( $user_id ) {
			return $user_id;
		}

		if ( ! empty( $args['user_login'] ) ) {
			$user_login = $args['user_login'];
		} else {
			$user_login = $this->generate_login_from_email( $args['email'] );
		}

		$user_id = wp_insert_user( array(
			'user_login' => sanitize_user( $user_login, $this->use_strict ),
			'user_email' => sanitize_text_field( $args['email'] ),
			'user_pass'  => wp_generate_password( 20, false ),
			'first_name' => ! empty( $args['first_name'] ) ? sanitize_text_field( $args['first_name'] ) : '',
			'last_name'  => ! empty( $args['last_name'] ) ? sanitize_text_field( $args['last_name'] ) : '',
		) );

		if ( ! is_wp_error( $user_id ) ) {
			return $user_id;
		} else {
			return false;
		}
	}

	/**
	 * Gets a user ID from a set of mapped affiliate arguments.
	 *
	 * @access protected
	 * @since  2.1
	 *
	 * @param array $args Affiliate arguments.
	 * @return int|false A derived user ID, otherwise false.
	 */
	protected function get_user_from_args( $args ) {

		if ( $user = get_user_by( 'login', $args['user_login'] ) ) {
			$user_id = $user->ID;
		} elseif ( $user = get_user_by( 'email', $args['email'] ) ) {
			$user_id = $user->ID;
		} else {
			$user_id = false;
		}

		return $user_id;
	}

	/**
	 * Generates a username from a given email address.
	 *
	 * @access protected
	 * @since  2.1
	 *
	 * @param string $email Email to use for generating a unique username.
	 * @return string Generated username.
	 */
	protected function generate_login_from_email( $email ) {

		$number = rand( 321, 123456 );

		preg_match( '/[^@]*/', $email, $matches );

		if ( isset( $matches[0] ) ) {
			$user_login = "{$matches[0]}{$number}";
		} else {
			$user_login = "affiliate{$number}";
		}

		return $user_login;
	}

	/**
	 * Defines logic to execute once batch processing is complete.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function finish() {
		// Invalidate the affiliates cache.
		wp_cache_set( 'last_changed', microtime(), 'affiliates' );

		// Running count (the "real" count).
		affiliate_wp()->utils->data->delete( "{$this->batch_id}_running_count" );

		parent::finish();
	}

}
