<?php
/**
 * Configurable order statuses for earning and reversing order points.
 *
 * Adds an "Order Status Settings" section to the General settings tab:
 * - "Award Order Points On Status": the status at which order points are awarded
 *   (stored as `wps_wpr_points_award_order_status`, the same key the Pro plugin used,
 *   so existing Pro settings keep working).
 * - "Also Deduct Points On Status": extra statuses (on top of Cancelled and Refunded)
 *   at which awarded order points are taken back.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPS_WPR_Order_Status_Settings.
 */
class WPS_WPR_Order_Status_Settings {

	/**
	 * Settings option (the General tab option).
	 */
	const OPTION = 'wps_wpr_settings_gallery';

	/**
	 * Award status key.
	 */
	const AWARD_KEY = 'wps_wpr_points_award_order_status';

	/**
	 * Extra reversal statuses key.
	 */
	const REVERSAL_KEY = 'wps_wpr_points_reversal_order_statuses';

	/**
	 * Statuses that can never award points.
	 *
	 * @var array
	 */
	private static $never_award = array( 'pending', 'failed', 'cancelled', 'refunded', 'on-hold', 'checkout-draft' );

	/**
	 * Register hooks.
	 */
	public function __construct() {
		// Before the Pro plugin's filters (priority 10 / 20), so a saved Pro value is read the same way.
		add_filter( 'wps_wpr_points_award_order_status', array( $this, 'wps_wpr_saved_award_status' ), 5, 1 );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_add_settings' ), 15, 1 );
		// After every other plugin: an older Pro (3.7.5 and below) adds its own award status select at priority 20.
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_defer_award_field_to_old_pro' ), PHP_INT_MAX, 1 );
		add_filter( 'wps_wpr_general_settings_save_option', array( $this, 'wps_wpr_save_settings' ), 15, 1 );
		add_action( 'wps_wpr_additional_general_settings', array( $this, 'wps_wpr_render_field' ), 10, 2 );
	}

	/**
	 * Statuses that can be chosen as the award status (slug => label), "completed" first.
	 *
	 * @return array
	 */
	public static function wps_wpr_award_status_options() {
		$options = array();
		foreach ( wc_get_order_statuses() as $key => $label ) {
			$slug = preg_replace( '/^wc-/', '', $key );
			if ( in_array( $slug, self::$never_award, true ) ) {
				continue;
			}
			$options[ $slug ] = $label;
		}
		if ( isset( $options['completed'] ) ) {
			$options = array( 'completed' => $options['completed'] ) + $options;
		}
		return $options;
	}

	/**
	 * Statuses that can be added as extra reversal statuses (slug => label).
	 *
	 * Cancelled and Refunded always reverse points and are not listed. Statuses that mean
	 * the order is still live or unpaid (completed, processing, pending, draft) are excluded.
	 *
	 * @return array
	 */
	public static function wps_wpr_reversal_status_options() {
		$options = array();
		foreach ( wc_get_order_statuses() as $key => $label ) {
			$slug = preg_replace( '/^wc-/', '', $key );
			if ( in_array( $slug, array( 'completed', 'processing', 'pending', 'checkout-draft', 'cancelled', 'refunded' ), true ) ) {
				continue;
			}
			$options[ $slug ] = $label;
		}
		return $options;
	}

	/**
	 * Saved award status, used as the base value of the award status filter.
	 *
	 * @param string $status default status.
	 * @return string
	 */
	public function wps_wpr_saved_award_status( $status ) {
		$settings = get_option( self::OPTION, array() );
		if ( is_array( $settings ) && ! empty( $settings[ self::AWARD_KEY ] ) ) {
			$status = (string) $settings[ self::AWARD_KEY ];
		}
		return $status;
	}

	/**
	 * Add the section to the General settings tab.
	 *
	 * @param array $settings settings fields.
	 * @return array
	 */
	public function wps_wpr_add_settings( $settings ) {
		$settings[] = array(
			'title' => __( 'Order Status Settings', 'points-and-rewards-for-woocommerce' ),
			'type'  => 'title',
		);
		$settings[] = array(
			'title'    => __( 'Award Order Points On Status', 'points-and-rewards-for-woocommerce' ),
			'id'       => self::AWARD_KEY,
			'type'     => 'wps_wpr_award_status_select',
			'desc_tip' => __( 'Order points (per-currency, product, order total and order rewards points) are awarded when an order first reaches this status. Choose Processing to award as soon as the order is paid, or any custom status. Moving the order to another status afterwards never awards points twice.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'    => __( 'Also Deduct Points On Status', 'points-and-rewards-for-woocommerce' ),
			'id'       => self::REVERSAL_KEY,
			'type'     => 'wps_wpr_reversal_statuses',
			'desc_tip' => __( 'Awarded order points are always taken back when an order is Cancelled or Refunded. Select any other statuses, such as Failed or a custom "Returned" status, that should also take the points back. Moving the order back to the award status awards the points again.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
		);
		return $settings;
	}

	/**
	 * Older Pro versions (3.7.5 and below) render their own "Award Order Points On Status" select with the
	 * same field name. Two selects with one name would let Pro's value silently win on save, so keep Pro's
	 * select and replace ours with a short note. The extra deduct statuses field stays.
	 *
	 * @param array $settings settings fields.
	 * @return array
	 */
	public function wps_wpr_defer_award_field_to_old_pro( $settings ) {
		if ( ! is_array( $settings ) || ! self::wps_wpr_other_award_field_exists( $settings ) ) {
			return $settings;
		}
		foreach ( $settings as $index => $field ) {
			if ( is_array( $field ) && isset( $field['id'], $field['type'] ) && self::AWARD_KEY === $field['id'] && 'wps_wpr_award_status_select' === $field['type'] ) {
				$settings[ $index ] = array(
					'title' => __( 'Award Order Points On Status', 'points-and-rewards-for-woocommerce' ),
					'id'    => self::AWARD_KEY . '_note',
					'type'  => 'wps_wpr_award_status_note',
				);
			}
		}
		return $settings;
	}

	/**
	 * Whether a field other than ours (e.g. an older Pro's select) saves the award status.
	 *
	 * @param array $settings settings fields.
	 * @return bool
	 */
	public static function wps_wpr_other_award_field_exists( $settings ) {
		foreach ( $settings as $field ) {
			if ( is_array( $field ) && isset( $field['id'] ) && self::AWARD_KEY === $field['id'] && ( ! isset( $field['type'] ) || 'wps_wpr_award_status_select' !== $field['type'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Save the section. The General tab verifies the nonce before this filter runs.
	 *
	 * @param array $general_settings_array settings to save.
	 * @return array
	 */
	public function wps_wpr_save_settings( $general_settings_array ) {
		if ( ! is_array( $general_settings_array ) ) {
			return $general_settings_array;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$award = ! empty( $_POST[ self::AWARD_KEY ] ) ? sanitize_key( wp_unslash( $_POST[ self::AWARD_KEY ] ) ) : 'completed';
		if ( ! array_key_exists( $award, self::wps_wpr_award_status_options() ) ) {
			$award = 'completed';
		}

		$posted   = ! empty( $_POST[ self::REVERSAL_KEY ] ) && is_array( $_POST[ self::REVERSAL_KEY ] ) ? array_map( 'sanitize_key', wp_unslash( $_POST[ self::REVERSAL_KEY ] ) ) : array();
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$allowed  = self::wps_wpr_reversal_status_options();
		$reversal = array();
		foreach ( $posted as $status ) {
			if ( isset( $allowed[ $status ] ) && $status !== $award && ! in_array( $status, $reversal, true ) ) {
				$reversal[] = $status;
			}
		}

		$general_settings_array[ self::AWARD_KEY ]    = $award;
		$general_settings_array[ self::REVERSAL_KEY ] = $reversal;
		return $general_settings_array;
	}

	/**
	 * Render the custom field types of this section.
	 *
	 * @param array $value            field.
	 * @param array $general_settings saved settings.
	 * @return void
	 */
	public function wps_wpr_render_field( $value, $general_settings ) {
		if ( empty( $value['type'] ) || empty( $value['id'] ) ) {
			return;
		}
		$general_settings = is_array( $general_settings ) ? $general_settings : array();

		if ( 'wps_wpr_award_status_select' === $value['type'] ) {
			$saved = ! empty( $general_settings[ self::AWARD_KEY ] ) ? (string) $general_settings[ self::AWARD_KEY ] : 'completed';
			?>
			<select name="<?php echo esc_attr( self::AWARD_KEY ); ?>" id="<?php echo esc_attr( self::AWARD_KEY ); ?>" class="wps_wgm_new_woo_ver_style_select">
				<?php foreach ( self::wps_wpr_award_status_options() as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $saved, $slug ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
			return;
		}

		if ( 'wps_wpr_award_status_note' === $value['type'] ) {
			?>
			<p class="description wps_wpr_award_status_note"><?php esc_html_e( 'Set in the "Order Points Award Trigger" section of Points and Rewards Pro. Update Pro to manage it here.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<?php
			return;
		}

		if ( 'wps_wpr_reversal_statuses' === $value['type'] ) {
			$saved = ! empty( $general_settings[ self::REVERSAL_KEY ] ) && is_array( $general_settings[ self::REVERSAL_KEY ] ) ? $general_settings[ self::REVERSAL_KEY ] : array();
			?>
			<fieldset id="<?php echo esc_attr( self::REVERSAL_KEY ); ?>" class="wps_wpr_reversal_statuses">
				<label><input type="checkbox" checked disabled> <?php echo esc_html( wc_get_order_status_name( 'cancelled' ) ); ?></label><br>
				<label><input type="checkbox" checked disabled> <?php echo esc_html( wc_get_order_status_name( 'refunded' ) ); ?></label><br>
				<?php foreach ( self::wps_wpr_reversal_status_options() as $slug => $label ) : ?>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::REVERSAL_KEY ); ?>[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $saved, true ) ); ?>>
						<?php echo esc_html( $label ); ?>
					</label><br>
				<?php endforeach; ?>
			</fieldset>
			<?php
		}
	}
}
