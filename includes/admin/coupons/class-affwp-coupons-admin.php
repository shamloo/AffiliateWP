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
		add_action( 'affwp_affiliate_coupons_table_top',    array( $this, 'coupons_table'       ) );
		add_action( 'affwp_affiliate_coupons_table_bottom', array( $this, 'create_coupons'      ) );
	}

	/**
	 * Interface to render coupons table on affiliate edit and new screens.
	 *
	 * @since  2.1
	 *
	 * @param  integer $affiliate_id Affiliate ID.
	 *
	 * @return void
	 */
	public function coupons_table( $affiliate_id = 0 ) {

		$coupons      = array();
		$integrations = affiliate_wp()->integrations->get_enabled_integrations();

		foreach ( $integrations as $integration_id => $integration_term ) {

			if ( affwp_has_coupon_support( $integration_id ) ) {

				$args = array(
					'affiliate_id' => $affiliate_id,
					'integration'  => $integration_id
				);

				$affiliate_coupons = affwp_get_coupons_by_integration( $args );

				if ( $affiliate_coupons ) {

					foreach ( $affiliate_coupons as $coupon_id ) {
						$output .= '<li>(' . $integration_term . ') <a href="' . affwp_get_coupon_edit_url( $coupon_id, $integration_id, true ) . '">' . __( 'Edit coupon', 'affiliate-wp' ) . '</a></li>';
					}

				}

			} elseif ( affwp_has_coupon_support( $args[ 'integration' ] ) ) {
				$output .= '<li>' . $integration_term . ' <a class="affwp-inline-link" href="' . affwp_get_coupon_create_url( $integration_id ) . '">' . __( 'Create coupon', 'affiliate-wp' ) . '</a>';
			} else {
				// Otherwise, coupon support should not be available.
				$output .= __( 'No currently-active AffiliateWP integrations support coupons at this time.', 'affiliate-wp' );
			}
		}

		echo $output;





		?>

		<table id="affiliatewp-rates" class="form-table wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th><?php _e( 'Integration', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Coupon Code', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Referrals', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'View', 'affiliate-wp' ); ?></th>
					<th style="width:5%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php if( $coupons ) : ?>
					<?php foreach( $coupons as $key => $rate ) :
						$type = ! empty( $rate['type'] ) ? $rate['type'] : 'referrals';
						$disabled = isset( $rate['disabled'] );

						if ( $disabled ) :
							$aria_label = __( 'This rate tier is disabled', 'affiliate-wp' );
						else :
							$aria_label = __( 'This rate tier is enabled', 'affiliate-wp' );
						endif;
						?>
						<tr>
							<td>
								<select name="affwp_settings[rates][<?php echo $key; ?>][type]">
									<option value="referrals"<?php selected( 'referrals', $type ); ?>><?php _e( 'Number of Referrals', 'affiliate-wp' ); ?></option>
									<option value="earnings"<?php selected( 'earnings', $type ); ?>><?php _e( 'Total Earnings', 'affiliate-wp' ); ?></option>
								</select>
							</td>
							<td>
								<input name="affwp_settings[rates][<?php echo $key; ?>][threshold]" type="text" value="<?php echo esc_attr( $rate['threshold'] ); ?>"/>
							</td>
							<td>
								<input name="affwp_settings[rates][<?php echo $key; ?>][rate]" type="text" value="<?php echo esc_attr( $rate['rate'] ); ?>"/>
							</td>
							<td>
								<input name="affwp_settings[rates][<?php echo $key; ?>][disabled]" id="affwp_settings[disabled]" type="checkbox" <?php checked( $disabled, true ); ?> aria-label="<?php echo esc_attr( $aria_label ); ?>"/>
							</td>
							<td>
								<a href="#" class="affwp_remove_rate" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="3"><?php _e( 'No tiered rates created yet', 'affiliate-wp' ); ?></td>
					</tr>
				<?php endif; ?>
                			<?php if( empty( $coupons ) ) : ?>
					<tr>
						<td>
							<select name="affwp_settings[rates][<?php echo $count; ?>][type]">
								<option value="referrals"><?php _e( 'Number of Referrals', 'affiliate-wp' ); ?></option>
								<option value="earnings"><?php _e( 'Total Earnings', 'affiliate-wp' ); ?></option>
							</select>
						</td>
						<td>
							<input name="affwp_settings[rates][<?php echo $count; ?>][threshold]" type="text" value=""/>
						</td>
						<td>
							<input name="affwp_settings[rates][<?php echo $count; ?>][rate]" type="text" value=""/>
						</td>
						<td>
							<a href="#" class="affwp_remove_rate" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
						</td>
					</tr>
                			<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="1">
						<button id="affwp_new_rate" name="affwp_new_rate" class="button"><?php _e( 'Add New Rate', 'affiliate-wp' ); ?></button>
					</th>
					<th colspan="3">
						<?php _e( 'Add rates from low to high', 'affiliate-wp' ); ?>
					</th>
				</tr>
			</tfoot>
		</table>

	<?php }

	/**
	 * Interface to create coupons on affiliate edit and new screens.
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
	<select name="create-coupons" id="create-coupons">

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

	<input type="text" id="code" name="code" size="24" value="" placeholder="<?php _e( 'Coupon code (optional)', 'affiliate-wp' ); ?>" />

	<?php

	$submit_text = __( 'Create Coupon(s)', 'A submit button which will trigger the creation of one or more affiliate coupons. This element is shown on the affiliate edit and new screens, ', 'affiliate-wp' );

	submit_button( $submit_text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>

	<p class="description"><?php _e( 'AffiliateWP integrations which are active and currently have coupon support will be shown in the dropdown select above. To create a coupon for a specific integration for this affiliate, select the desired integration and click Create Coupon. You can also optionally set the desired coupon code, or create coupons for this affiliate for every integration listed at once, by selecting "Create a coupon for all integrations listed" in the dropdown select above.', 'affiliate-wp' ); ?></p>
<?php
}

}
