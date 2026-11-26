<?php
/**
 * This is the setting template
 *
 * Achievement / bonus rules settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

$wps_wpr_ach_deactivated = 0;
if ( isset( $_POST['wps_wpr_save_achievement_rules'], $_POST['wps_wpr_achievement_rules_nonce'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_achievement_rules_nonce'] ) ), 'wps-wpr-achievement-rules' ) ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized field by field in wps_wpr_sanitize_rules().
		$wps_wpr_ach_raw    = isset( $_POST['wps_wpr_achievement_rules'] ) ? wp_unslash( $_POST['wps_wpr_achievement_rules'] ) : array();
		$wps_wpr_ach_result = WPS_WPR_Achievement_Rules::wps_wpr_sanitize_rules( $wps_wpr_ach_raw );
		update_option( WPS_WPR_Achievement_Rules::OPTION, $wps_wpr_ach_result['rules'] );
		$wps_wpr_ach_deactivated = $wps_wpr_ach_result['deactivated'];
		$settings_obj->wps_wpr_settings_saved();
	}
}

wp_enqueue_script( 'wps-wpr-achievement-rules-admin', WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-achievement-rules-admin.js', array(), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION, true );

$wps_wpr_ach_rules      = WPS_WPR_Achievement_Rules::wps_wpr_get_rules();
$wps_wpr_ach_limit      = WPS_WPR_Achievement_Rules::wps_wpr_rules_limit();
$wps_wpr_ach_goal_types = WPS_WPR_Achievement_Rules::wps_wpr_goal_types();
$wps_wpr_ach_cond_types = WPS_WPR_Condition_Engine::wps_wpr_get_condition_types();
$wps_wpr_ach_list_ops   = WPS_WPR_Condition_Engine::wps_wpr_list_operators();
$wps_wpr_ach_num_ops    = WPS_WPR_Condition_Engine::wps_wpr_number_operators();
$wps_wpr_ach_limited    = WPS_WPR_Achievement_Rules::wps_wpr_get_limited_rule_ids();

/**
 * Print one condition row.
 *
 * @param string $rule_key  rule array key.
 * @param string $cond_key  condition array key.
 * @param array  $condition condition values.
 * @param array  $types     condition types.
 * @param array  $list_ops  list operators.
 * @param array  $num_ops   number operators.
 * @return void
 */
$wps_wpr_ach_condition_row = function ( $rule_key, $cond_key, $condition, $types, $list_ops, $num_ops ) {
	$name     = 'wps_wpr_achievement_rules[' . $rule_key . '][conditions][' . $cond_key . ']';
	$type     = isset( $condition['type'] ) ? $condition['type'] : 'user_role';
	$operator = isset( $condition['operator'] ) ? $condition['operator'] : '';
	$value    = isset( $condition['value'] ) ? $condition['value'] : '';
	?>
	<div class="wps_wpr_ach_condition">
		<select name="<?php echo esc_attr( $name . '[type]' ); ?>" class="wps_wpr_ach_cond_type">
			<?php foreach ( $types as $type_key => $type_def ) : ?>
				<option value="<?php echo esc_attr( $type_key ); ?>" data-kind="<?php echo esc_attr( $type_def['kind'] ); ?>" data-hint="<?php echo esc_attr( isset( $type_def['hint'] ) ? $type_def['hint'] : '' ); ?>" <?php selected( $type, $type_key ); ?>><?php echo esc_html( $type_def['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="<?php echo esc_attr( $name . '[operator]' ); ?>" class="wps_wpr_ach_cond_operator">
			<?php foreach ( $list_ops as $op_key => $op_label ) : ?>
				<option value="<?php echo esc_attr( $op_key ); ?>" data-kind="list" <?php selected( $operator, $op_key ); ?>><?php echo esc_html( $op_label ); ?></option>
			<?php endforeach; ?>
			<?php foreach ( $num_ops as $op_key => $op_label ) : ?>
				<option value="<?php echo esc_attr( $op_key ); ?>" data-kind="number" <?php selected( $operator, $op_key ); ?>><?php echo esc_html( $op_label ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="<?php echo esc_attr( $name . '[value]' ); ?>" class="wps_wpr_ach_cond_value" value="<?php echo esc_attr( $value ); ?>">
		<button type="button" class="button wps_wpr_ach_remove_condition" aria-label="<?php esc_attr_e( 'Remove condition', 'points-and-rewards-for-woocommerce' ); ?>">&times;</button>
	</div>
	<?php
};

/**
 * Print one rule block.
 *
 * @param string $rule_key rule array key.
 * @param array  $rule     rule values.
 * @return void
 */
$wps_wpr_ach_rule_block = function ( $rule_key, $rule ) use ( $wps_wpr_ach_condition_row, $wps_wpr_ach_goal_types, $wps_wpr_ach_cond_types, $wps_wpr_ach_list_ops, $wps_wpr_ach_num_ops, $wps_wpr_ach_limited ) {
	$rule    = wp_parse_args( $rule, WPS_WPR_Achievement_Rules::wps_wpr_rule_defaults() );
	$name    = 'wps_wpr_achievement_rules[' . $rule_key . ']';
	$limited = '' !== $rule['id'] && in_array( $rule['id'], $wps_wpr_ach_limited, true );
	?>
	<section class="wps_wpr_general_row_wrap wps_wpr_ach_rule" data-rule-key="<?php echo esc_attr( $rule_key ); ?>">
		<div class="wps_wpr_general_sign_title"><?php echo '' !== $rule['name'] ? esc_html( $rule['name'] ) : esc_html__( 'New rule', 'points-and-rewards-for-woocommerce' ); ?></div>
		<div class="wps_wpr_section_content">
			<input type="hidden" name="<?php echo esc_attr( $name . '[id]' ); ?>" value="<?php echo esc_attr( $rule['id'] ); ?>">
			<article class="wps_wpr_general_row">
				<label class="wps_wpr_general_label"><?php esc_html_e( 'Rule name', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<input type="text" class="wps_wpr_ach_name" name="<?php echo esc_attr( $name . '[name]' ); ?>" value="<?php echo esc_attr( $rule['name'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Big spender bonus', 'points-and-rewards-for-woocommerce' ); ?>">
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<label class="wps_wpr_general_label"><?php esc_html_e( 'Active', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<input type="checkbox" class="wps_wpr_ach_active" name="<?php echo esc_attr( $name . '[active]' ); ?>" value="yes" <?php checked( $rule['active'], 'yes' ); ?>>
					<?php if ( $limited ) : ?>
						<span class="wps_wpr_ach_limited_badge" style="color:#b32d2e;font-weight:600;margin-left:6px;"><?php esc_html_e( 'Inactive (limit)', 'points-and-rewards-for-woocommerce' ); ?></span>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'This rule is not running because the active rule limit was reached. Saving will mark it inactive.', 'points-and-rewards-for-woocommerce' ); ?></span>
					<?php endif; ?>
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<label class="wps_wpr_general_label"><?php esc_html_e( 'Goal', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<select class="wps_wpr_ach_goal_type" name="<?php echo esc_attr( $name . '[goal_type]' ); ?>">
						<?php foreach ( $wps_wpr_ach_goal_types as $goal_key => $goal_label ) : ?>
							<option value="<?php echo esc_attr( $goal_key ); ?>" <?php selected( $rule['goal_type'], $goal_key ); ?>><?php echo esc_html( $goal_label ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="number" min="0" step="0.01" class="wps_wpr_ach_goal_value" name="<?php echo esc_attr( $name . '[goal_value]' ); ?>" value="<?php echo esc_attr( $rule['goal_value'] ); ?>">
					<?php esc_html_e( 'within', 'points-and-rewards-for-woocommerce' ); ?>
					<input type="number" min="1" class="wps_wpr_ach_window" name="<?php echo esc_attr( $name . '[window_days]' ); ?>" value="<?php echo esc_attr( $rule['window_days'] ); ?>">
					<?php esc_html_e( 'days', 'points-and-rewards-for-woocommerce' ); ?>
					<span class="wps_wpr_label_notice"><?php esc_html_e( 'Orders count once they reach the status that awards points.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<label class="wps_wpr_general_label"><?php esc_html_e( 'Bonus points', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<input type="number" min="1" class="wps_wpr_ach_points" name="<?php echo esc_attr( $name . '[points]' ); ?>" value="<?php echo esc_attr( $rule['points'] ); ?>">
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<label class="wps_wpr_general_label"><?php esc_html_e( 'Award', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<select name="<?php echo esc_attr( $name . '[repeat]' ); ?>" class="wps_wpr_ach_repeat">
						<option value="once" <?php selected( $rule['repeat'], 'once' ); ?>><?php esc_html_e( 'Once per customer', 'points-and-rewards-for-woocommerce' ); ?></option>
						<option value="repeat" <?php selected( $rule['repeat'], 'repeat' ); ?>><?php esc_html_e( 'Repeatable, at most once per period', 'points-and-rewards-for-woocommerce' ); ?></option>
					</select>
					<span class="wps_wpr_label_notice"><?php esc_html_e( 'Repeatable rules award at most once per period (the number of days above). After an award, progress starts again: only orders that reach the award status after the award count toward the next one, and the next award can only happen once the period has passed.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<label class="wps_wpr_general_label"><?php esc_html_e( 'Conditions', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<p>
						<?php esc_html_e( 'Customer must match', 'points-and-rewards-for-woocommerce' ); ?>
						<select name="<?php echo esc_attr( $name . '[match]' ); ?>">
							<option value="all" <?php selected( $rule['match'], 'all' ); ?>><?php esc_html_e( 'all conditions', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="any" <?php selected( $rule['match'], 'any' ); ?>><?php esc_html_e( 'any condition', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
					</p>
					<div class="wps_wpr_ach_conditions">
						<?php
						foreach ( array_values( (array) $rule['conditions'] ) as $cond_index => $condition ) {
							$wps_wpr_ach_condition_row( $rule_key, 'c' . $cond_index, $condition, $wps_wpr_ach_cond_types, $wps_wpr_ach_list_ops, $wps_wpr_ach_num_ops );
						}
						?>
					</div>
					<button type="button" class="button wps_wpr_ach_add_condition"><?php esc_html_e( 'Add condition', 'points-and-rewards-for-woocommerce' ); ?></button>
					<span class="wps_wpr_label_notice"><?php esc_html_e( 'Leave empty to apply the rule to every customer. List values are comma separated.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>
			</article>
			<p><button type="button" class="button-link-delete wps_wpr_ach_remove_rule"><?php esc_html_e( 'Delete rule', 'points-and-rewards-for-woocommerce' ); ?></button></p>
		</div>
	</section>
	<?php
};
?>

<div class="wps_wpr_achievement_rules_wrapper">
	<input type="hidden" name="wps_wpr_achievement_rules_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wps-wpr-achievement-rules' ) ); ?>">
	<p class="wps_wpr_ach_intro">
		<?php esc_html_e( 'Reward customers with bonus points when they reach a goal, for example spending 500 within 30 days. Target rules with conditions such as role, country, order count, lifetime spend or language.', 'points-and-rewards-for-woocommerce' ); ?>
	</p>
	<?php if ( $wps_wpr_ach_limit > 0 ) : ?>
		<div class="notice notice-info inline wps_wpr_ach_limit_notice"><p>
			<?php
			/* translators: %s: number of active rules */
			echo esc_html( sprintf( _n( 'You can have %s active rule. Upgrade to Pro for unlimited active rules.', 'You can have %s active rules. Upgrade to Pro for unlimited active rules.', $wps_wpr_ach_limit, 'points-and-rewards-for-woocommerce' ), number_format_i18n( $wps_wpr_ach_limit ) ) );
			?>
		</p></div>
	<?php endif; ?>
	<?php if ( ! empty( $wps_wpr_ach_limited ) ) : ?>
		<div class="notice notice-warning inline wps_wpr_ach_limited_notice"><p>
			<?php
			/* translators: %s: number of rules */
			echo esc_html( sprintf( _n( '%s rule marked active is not running because the active rule limit was reached. It is shown as "Inactive (limit)".', '%s rules marked active are not running because the active rule limit was reached. They are shown as "Inactive (limit)".', count( $wps_wpr_ach_limited ), 'points-and-rewards-for-woocommerce' ), number_format_i18n( count( $wps_wpr_ach_limited ) ) ) );
			?>
		</p></div>
	<?php endif; ?>
	<?php if ( $wps_wpr_ach_deactivated > 0 ) : ?>
		<div class="notice notice-warning inline wps_wpr_ach_deactivated_notice"><p>
			<?php
			/* translators: %s: number of rules */
			echo esc_html( sprintf( _n( '%s rule was saved as inactive because the active rule limit was reached.', '%s rules were saved as inactive because the active rule limit was reached.', $wps_wpr_ach_deactivated, 'points-and-rewards-for-woocommerce' ), number_format_i18n( $wps_wpr_ach_deactivated ) ) );
			?>
		</p></div>
	<?php endif; ?>

	<main class="wps_wpr_ach_rules">
		<?php
		foreach ( $wps_wpr_ach_rules as $wps_wpr_ach_index => $wps_wpr_ach_rule ) {
			$wps_wpr_ach_rule_block( 'r' . $wps_wpr_ach_index, $wps_wpr_ach_rule );
		}
		?>
	</main>

	<p><button type="button" class="button wps_wpr_ach_add_rule"><?php esc_html_e( 'Add rule', 'points-and-rewards-for-woocommerce' ); ?></button></p>

	<script type="text/template" id="wps_wpr_ach_rule_template">
		<?php $wps_wpr_ach_rule_block( '__RULE__', array() ); ?>
	</script>
	<script type="text/template" id="wps_wpr_ach_condition_template">
		<?php $wps_wpr_ach_condition_row( '__RULE__', '__COND__', array(), $wps_wpr_ach_cond_types, $wps_wpr_ach_list_ops, $wps_wpr_ach_num_ops ); ?>
	</script>

	<input type="submit" name="wps_wpr_save_achievement_rules" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_achievement_rules" value="<?php esc_attr_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
