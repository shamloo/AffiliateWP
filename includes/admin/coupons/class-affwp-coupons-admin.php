<?php
/**
 * 'Coupons' Admin Table
 *
 * @package   AffiliateWP\Admin\Coupons
 * @copyright Copyright (c) 2017, AffiliateWP, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AffWP_Coupons_Admin class.
 *
 * Renders the Coupons table on Affiliate-edit and screens.
 *
 * @since 2.1
 */
class AffWP_Coupons_Admin {

	/**
	 * Coupons table constructor.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @see WP_List_Table::__construct()
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through
	 *                    the list table. Default empty array.
	 */
	public function __construct( $args = array() ) {
	}

	/**
	 * Renders create coupons UI on affiliate edit and new screens.
	 *
	 * @since  2.1
	 *
	 * @param  integer $affiliate_id Affiliate ID.
	 *
	 * @return void
	 */
	public function create_coupons( $affiliate_id = 0 ) {

	$all = false; ?>

	<p>
		<strong>
			<?php echo __( 'Create affiliate coupons:', 'affiliate-wp' ); ?>
		</strong>
	</p>
	<form method="post" enctype="multipart/form-data" class="affwp-batch-form" data-batch_id="generate-coupons" data-nonce="<?php echo esc_attr( wp_create_nonce( 'generate-coupons_step_nonce' ) ); ?>">

		<select name="coupon_integration" id="coupon_integration">

			<option value="<?php echo $all; ?>" <?php selected( $all, $all ); ?>><?php _e( 'Create a coupon for all integrations listed', 'affiliate-wp' ); ?></option>
		<?php
		$integrations = affiliate_wp()->integrations->get_enabled_integrations();

		foreach ( $integrations as $integration_id => $integration_term ) {

			if ( affwp_has_coupon_support( $integration_id ) ) { ?>

				<option value="<?php echo $integration_id; ?>" <?php selected( $integration_id, $integration_id ); ?>><?php echo $integration_term; ?></option>

			<?php }

		}
	?>
		</select>

		<input type="text" id="coupon_code" name="coupon_code" size="24" value="" placeholder="<?php _e( 'Coupon code (optional)', 'affiliate-wp' ); ?>" />

		<?php

		$submit_text = __( 'Create Coupon(s)', 'A submit button which will trigger the creation of one or more affiliate coupons. This element is shown on the affiliate edit and new screens, ', 'affiliate-wp' );

		submit_button( $submit_text, 'secondary', 'generate-coupons-submit', false ); ?>

	</form>

	<p class="description">
		<?php _e( 'AffiliateWP integrations which are active and currently have coupon support will be shown in the dropdown select above. To create a coupon for a specific integration for this affiliate, select the desired integration and click Create Coupon. You can also optionally set the desired coupon code, or create coupons for this affiliate for every integration listed at once, by selecting "Create a coupon for all integrations listed" in the dropdown select above.', 'affiliate-wp' ); ?>
	</p>
<?php
	}

	/**
	 * Renders the coupons table on affiliate edit and new screens.
	 *
	 * @since  2.1
	 *
	 * @param  integer $affiliate_id Affiliate ID.
	 *
	 * @return void
	 */
	public function coupons_table( $affiliate_id = 0 ) {

		if ( ! $affiliate_id ) {

			if ( ! isset( $affiliate ) ) {
				$affiliate  = affwp_get_affiliate( absint( $_GET['affiliate_id'] ) );
			}

			$affiliate_id = $affiliate->affiliate_id;

			if ( ! $affiliate_id ) {
				affiliate_wp()->utils->log( 'Unable to determine affiliate ID in coupons_table method.' );
				return false;
			}

		}

		/**
		 * Fires at the top of coupons admin table views.
		 *
		 * @since 2.1
		 */
		do_action( 'affwp_affiliate_coupons_table_top' );

		?>

		<hr />

		<p>
			<style type="text/css">
				#affiliatewp-coupons th {
					padding-left: 10px;
				}
			</style>
			<strong>
				<?php _e( 'Coupons for this affiliate:', 'affiliate-wp' ); ?>
			</strong>
		</p>

		<table id="affiliatewp-coupons" class="form-table wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th><?php _e( 'Integration', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Coupon Code', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'ID',          'affiliate-wp' ); ?></th>
					<th><?php _e( 'Referrals',   'affiliate-wp' ); ?></th>
					<th><?php _e( 'View/Edit',        'affiliate-wp' ); ?></th>
					<th style="width:5%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php

				$integrations = affiliate_wp()->integrations->get_enabled_integrations();

				foreach ( $integrations as $integration_id => $integration_term ) {

					if ( affwp_has_coupon_support( $integration_id ) ) {

						$args = array(
							'affiliate_id' => $affiliate_id,
							'integration'  => $integration_id
						);

						// This should be replaced by a call to affwp coupons, instead of directly
						// querying the integrations.
						$coupons = affwp_get_coupons_by_integration( $args );

						if ( ! empty( $coupons ) ) {

							foreach ( $coupons as $coupon ) {

								$coupon_referrals = affiliate_wp()->referrals->get_referrals( array(
										'number'       => -1,
										'affiliate_id' => $affiliate_id,
										'coupon_id'    => $coupon['coupon_id']

									)
								);

								$referrals_url = affwp_admin_url( 'referrals' );
								$referrals_url = $coupon['coupon_id'] ? add_query_arg( 'coupon_id', $coupon['coupon_id'], $referrals_url ) : $referrals_url;

								?>
								<tr>
									<td>
										<?php echo $coupon['integration']; ?>
									</td>
									<td>
										<?php echo $coupon['coupon_code']; ?>
									</td>
									<td>
										<?php echo $coupon['integration_coupon_id']; ?>
									</td>
									<td>
										<?php echo $referrals_url; ?>
									</td>
									<td>
										<?php
										$coupon_edit_url = affwp_get_coupon_edit_url( $coupon['integration_coupon_id'], $coupon['integration'] );
										if ( $coupon_edit_url ) {
											echo '<a href="' . esc_url( $coupon_edit_url ) . '">' . __( 'View/Edit', 'affiliate-wp' ) . '</a>';
										} else {
											affiliate_wp()->utils->log( sprintf( 'Unable to get coupon edit URL for the %s integration.', $coupon['integration'] ) );
										} ?>

									</td>
								</tr>
					<?php   }

						}
					}
				}
?>

			</tbody>
			<tfoot>
			</tfoot>
		</table>

		<p class="description">
			<?php echo __( 'The current coupons for this affiliate.', 'affiliate-wp' ); ?>
		</p>

	<?php

		/**
		 * Fires at the bottom of coupons admin table views.
		 *
		 * @since 2.1
		 */
		do_action( 'affwp_affiliate_coupons_table_bottom' );
	}

	public function coupons_form() {
		$to_generate = array();

		$to_generate = affiliate_wp()->settings->get( 'coupon_integrations' );

		foreach ( $integrations as $integration ) {
			// $args = array(
			// 	'affiliate_id' => $affiliate_id,
			// 	'integration'  => $integration
			// );

			// affiliate_wp()->affiliates->coupons->add( $args );
			echo $integration;
		}

	}

}
