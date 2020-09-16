<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.7
 *
 * @package    Makewebbetter_Onboarding_Helper
 * @subpackage Makewebbetter_Onboarding_Helper/includes/
 */

/**
 * The Onboarding-specific functionality of the plugin admin side.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Makewebbetter_Onboarding_Helper
 * @subpackage Makewebbetter_Onboarding_Helper/admin
 * @author     Make Web Better <dev@mwb.com>
 */
if ( class_exists( 'Makewebbetter_Onboarding_Helper' ) ) {
	return;
}

/**
 * Helper module for MakeWebBetter plugins.
 */
class Makewebbetter_Onboarding_Helper {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.7
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'add_onboarding_popup_screen' ) );
		add_action( 'admin_footer', array( $this, 'add_deactivation_popup_screen' ) );
		add_filter( 'mwb_on_boarding_form_fields', array( $this, 'add_on_boarding_form_fields' ) );
		add_filter( 'mwb_deactivation_form_fields', array( $this, 'add_deactivation_form_fields' ) );

		// Ajax to send data.
		add_action( 'wp_ajax_send_onboarding_data', array( $this, 'send_onboarding_data' ) );
		add_action( 'wp_ajax_nopriv_send_onboarding_data', array( $this, 'send_onboarding_data' ) );

		// Ajax to Skip popup.
		add_action( 'wp_ajax_skip_onboarding_popup', array( $this, 'skip_onboarding_popup' ) );
		add_action( 'wp_ajax_nopriv_skip_onboarding_popup', array( $this, 'skip_onboarding_popup' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.7
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Makewebbetter_Onboarding_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Makewebbetter_Onboarding_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( $this->is_valid_page_screen() ) {

			wp_enqueue_style( 'makewebbetter-onboarding-style', MWB_RWPR_DIR_URL . 'admin/css/makewebbetter-onboarding-admin.css', array(), '1.0.0', 'all' );
			wp_enqueue_style( 'select2' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.7
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Makewebbetter_Onboarding_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Makewebbetter_Onboarding_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( $this->is_valid_page_screen() ) {

			wp_enqueue_script( 'makewebbetter-onboarding-scripts', MWB_RWPR_DIR_URL . 'admin/js/makewebbetter-onboarding-admin.js', array( 'jquery', 'select2' ), '1.0.0', true );

			global $pagenow;
			$current_slug = ! empty( explode( '/', plugin_basename( __FILE__ ) ) ) ? explode( '/', plugin_basename( __FILE__ ) )[0] : '';
			wp_localize_script(
				'makewebbetter-onboarding-scripts',
				'mwb',
				array(
					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
					'auth_nonce'    => wp_create_nonce( 'mwb_onboarding_nonce' ),
					'current_screen'    => $pagenow,
					'current_supported_slug'    => apply_filters( 'mwb_deactivation_supported_slug', array() ),
				)
			);
		}
	}

	/**
	 * Get all valid screens to add scripts and templates.
	 *
	 * @since    1.0.7
	 */
	public function add_onboarding_popup_screen() {

		if ( $this->is_valid_page_screen() && $this->can_show_onboarding_popup() ) {
			require_once MWB_RWPR_DIR_PATH . 'includes/extra-templates/makewebbetter-onboarding-template-display.php';
		}
	}


	/**
	 * Get all valid screens to add scripts and templates.
	 *
	 * @since    1.0.7
	 */
	public function add_deactivation_popup_screen() {

		global $pagenow;
		if ( ! empty( $pagenow ) && 'plugins.php' == $pagenow ) {
			require_once MWB_RWPR_DIR_PATH . 'includes/extra-templates/makewebbetter-deactivation-template-display.php';
		}
	}


	/**
	 * Validate current screen.
	 *
	 * @since    1.0.7
	 */
	public function is_valid_page_screen() {

		global $pagenow;
		$screen = get_current_screen();

		$is_valid = false;

		if ( ! empty( $screen->id ) ) {

			$is_valid = in_array( $screen->id, apply_filters( 'mwb_helper_valid_frontend_screens', array() ) ) && $this->add_mwb_additional_validation();
		}

		if ( empty( $is_valid ) && 'plugins.php' == $pagenow ) {
			$is_valid = true;
		}

		return $is_valid;
	}

	/**
	 * Validate the popup to be shown on screen.
	 *
	 * @since    1.0.7
	 */
	public function can_show_onboarding_popup() {

		$is_already_sent = get_option( 'onboarding-data-sent', false );

		// Already submitted the data.
		if ( ! empty( $is_already_sent ) && 'sent' == $is_already_sent ) {
			return false;
		}

		$get_skipped_timstamp = get_option( 'onboarding-data-skipped', false );
		if ( ! empty( $get_skipped_timstamp ) ) {

			$next_show = strtotime( '+2 days', $get_skipped_timstamp );

			$current_time = time();

			$time_diff = $next_show - $current_time;

			if ( 0 < $time_diff ) {
				return false;
			}
		}

		// By default Show.
		return true;
	}

	/**
	 * Add your onboarding form fields.
	 *
	 * @since    1.0.7
	 */
	public function add_on_boarding_form_fields() {

		$current_user = wp_get_current_user();
		if ( ! empty( $current_user ) ) {
			$current_user_email = $current_user->user_email ? $current_user->user_email : '';
		}

		$currency_symbol = get_woocommerce_currency_symbol();
		$store_name = get_the_title( wc_get_page_id( 'shop' ) );
		$store_url = get_home_url();

		/**
		 * Do not repeat id index.
		 */

		$fields = array(

			/**
			 * Input field with label.
			 * Radio field with label ( select only one ).
			 * Radio field with label ( select multiple one ).
			 * Checkbox radio with label ( select only one ).
			 * Checkbox field with label ( select multiple one ).
			 * Only Label ( select multiple one ).
			 * Select field with label ( select only one ).
			 * Select2 field with label ( select multiple one ).
			 * Email field with label. ( auto filled with admin email )
			 */

			rand() => array(
				'id' => 'monthly-revenue',
				'label' => esc_html__( 'What is your monthly revenue?', 'points-and-rewards-for-woocommerce' ),
				'type' => 'radio',
				'name' => 'monthly-revenue',
				'value' => '',
				'multiple' => 'no',
				'required' => 'yes',
				'extra-class' => '',
				'options' => array(
					'0-500'         => $currency_symbol . '0-' . $currency_symbol . '500',
					'501-5000'          => $currency_symbol . '501-' . $currency_symbol . '5000',
					'5001-10000'        => $currency_symbol . '5001-' . $currency_symbol . '10000',
					'10000+'        => $currency_symbol . '10000+',
				),
			),

			rand() => array(
				'id' => 'industry_type',
				'label' => esc_html__( 'What industry defines your business?', 'points-and-rewards-for-woocommerce' ),
				'type' => 'select',
				'name' => 'industry_type',
				'value' => '',
				'multiple' => 'yes',
				'required' => 'yes',
				'extra-class' => '',
				'options' => array(
					'agency'                => __( 'Agency', 'points-and-rewards-for-woocommerce' ),
					'consumer-services'     => __( 'Consumer Services', 'points-and-rewards-for-woocommerce' ),
					'ecommerce'             => __( 'Ecommerce', 'points-and-rewards-for-woocommerce' ),
					'financial-services'    => __( 'Financial Services', 'points-and-rewards-for-woocommerce' ),
					'healthcare'            => __( 'Healthcare', 'points-and-rewards-for-woocommerce' ),
					'manufacturing'         => __( 'Manufacturing', 'points-and-rewards-for-woocommerce' ),
					'nonprofit-and-education' => __( 'Nonprofit and Education', 'points-and-rewards-for-woocommerce' ),
					'professional-services' => __( 'Professional Services', 'points-and-rewards-for-woocommerce' ),
					'real-estate'           => __( 'Real Estate', 'points-and-rewards-for-woocommerce' ),
					'software'              => __( 'Software', 'points-and-rewards-for-woocommerce' ),
					'startups'              => __( 'Startups', 'points-and-rewards-for-woocommerce' ),
					'restaurant'            => __( 'Restaurant', 'points-and-rewards-for-woocommerce' ),
					'fitness'               => __( 'Fitness', 'points-and-rewards-for-woocommerce' ),
					'jewelry'               => __( 'Jewelry', 'points-and-rewards-for-woocommerce' ),
					'beauty'                => __( 'Beauty', 'points-and-rewards-for-woocommerce' ),
					'celebrity'             => __( 'Celebrity', 'points-and-rewards-for-woocommerce' ),
					'gaming'                => __( 'Gaming', 'points-and-rewards-for-woocommerce' ),
					'government'            => __( 'Government', 'points-and-rewards-for-woocommerce' ),
					'sports'                => __( 'Sports', 'points-and-rewards-for-woocommerce' ),
					'retail-store'          => __( 'Retail Store', 'points-and-rewards-for-woocommerce' ),
					'travel'                => __( 'Travel', 'points-and-rewards-for-woocommerce' ),
					'political-campaign'    => __( 'Political Campaign', 'points-and-rewards-for-woocommerce' ),
				),
			),

			rand() => array(
				'id' => 'onboard-email',
				'label' => esc_html__( 'What is the best email address to contact you?', 'points-and-rewards-for-woocommerce' ),
				'type' => 'email',
				'name' => 'onboard-email',
				'value' => $current_user_email,
				'required' => 'yes',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'onboard-number',
				'label' => esc_html__( 'What is your contact number?', 'points-and-rewards-for-woocommerce' ),
				'type' => 'text',
				'name' => 'onboard-number',
				'value' => '',
				'required' => 'yes',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'store-name',
				'label' => '',
				'type' => 'hidden',
				'name' => 'store-name',
				'value' => $store_name,
				'required' => '',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'store-url',
				'label' => '',
				'type' => 'hidden',
				'name' => 'store-url',
				'value' => $store_url,
				'required' => '',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'show-counter',
				'label' => '',
				'type' => 'hidden',
				'name' => 'show-counter',
				'value' => get_option( 'onboarding-data-sent', 'not-sent' ),
				'required' => '',
				'extra-class' => '',
			),
		);

		return $fields;
	}


	/**
	 * Add your deactivation form fields.
	 *
	 * @since    1.0.7
	 */
	public function add_deactivation_form_fields() {

		$current_user = wp_get_current_user();
		if ( ! empty( $current_user ) ) {
			$current_user_email = $current_user->user_email ? $current_user->user_email : '';
		}

		$store_name = get_the_title( wc_get_page_id( 'shop' ) );
		$store_url = get_home_url();

		/**
		 * Do not repeat id index.
		 */

		$fields = array(

			/**
			 * Input field with label.
			 * Radio field with label ( select only one ).
			 * Radio field with label ( select multiple one ).
			 * Checkbox radio with label ( select only one ).
			 * Checkbox field with label ( select multiple one ).
			 * Only Label ( select multiple one ).
			 * Select field with label ( select only one ).
			 * Select2 field with label ( select multiple one ).
			 * Email field with label. ( auto filled with admin email )
			 */

			rand() => array(
				'id' => 'deactivation-reason',
				'label' => '',
				'type' => 'radio',
				'name' => 'deactivation-reason',
				'value' => '',
				'multiple' => 'no',
				'required' => 'yes',
				'extra-class' => '',
				'options' => array(
					'temporary-deactivation-for-debug'      => __( 'It is a temporary deactivation. I am just debugging an issue.', 'points-and-rewards-for-woocommerce' ),
					'site-layout-broke'         => __( 'The plugin broke my layout or some functionality.', 'points-and-rewards-for-woocommerce' ),
					'complicated-configuration'         => __( 'The plugin is too complicated to configure.', 'points-and-rewards-for-woocommerce' ),
					'no-longer-need'        => __( 'I no longer need the plugin', 'points-and-rewards-for-woocommerce' ),
					'found-better-plugin'       => __( 'I found a better plugin', 'points-and-rewards-for-woocommerce' ),
					'other'         => __( 'Other', 'points-and-rewards-for-woocommerce' ),
				),
			),

			rand() => array(
				'id' => 'deactivation-reason-text',
				'label' => 'Let us know why you are deactivating {plugin-name} so we can improve the plugin',
				'type' => 'textarea',
				'name' => 'deactivation-reason-text',
				'value' => '',
				'required' => '',
				'extra-class' => 'keep_hidden',
			),

			rand() => array(
				'id' => 'admin-email',
				'label' => '',
				'type' => 'hidden',
				'name' => 'admin-email',
				'value' => $current_user_email,
				'required' => '',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'store-name',
				'label' => '',
				'type' => 'hidden',
				'name' => 'store-name',
				'value' => $store_name,
				'required' => '',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'store-url',
				'label' => '',
				'type' => 'hidden',
				'name' => 'store-url',
				'value' => $store_url,
				'required' => '',
				'extra-class' => '',
			),

			rand() => array(
				'id' => 'plugin-name',
				'label' => '',
				'type' => 'hidden',
				'name' => 'plugin-name',
				'value' => '',
				'required' => '',
				'extra-class' => '',
			),
		);

		return $fields;
	}

	/**
	 * Returns form fields html.
	 *
	 * @since       1.0.7
	 * @param       array  $attr               The attributes of this field.
	 * @param       string $base_class         The basic class for the label.
	 */
	public function render_field_html( $attr = array(), $base_class = 'on-boarding' ) {
		$id     = ! empty( $attr['id'] ) ? $attr['id'] : '';
		$name   = ! empty( $attr['name'] ) ? $attr['name'] : '';
		$label  = ! empty( $attr['label'] ) ? $attr['label'] : '';
		$type   = ! empty( $attr['type'] ) ? $attr['type'] : '';
		$class  = ! empty( $attr['extra-class'] ) ? $attr['extra-class'] : '';
		$value  = ! empty( $attr['value'] ) ? $attr['value'] : '';
		$options    = ! empty( $attr['options'] ) ? $attr['options'] : array();
		$multiple   = ! empty( $attr['multiple'] ) && 'yes' == $attr['multiple'] ? 'yes' : 'no';
		$required   = ! empty( $attr['required'] ) ? 'required="required"' : '';

		$html = '';

		if ( 'hidden' != $type ) : ?>
			<div class ="mwb-form-single-field">
			<?php
		endif;

		switch ( $type ) {

			case 'radio':
				// If field requires multiple answers.
				if ( ! empty( $options ) && is_array( $options ) ) :
					?>
					<label class="on-boarding-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_attr( $label ); ?></label>

					<?php
					$is_multiple = ! empty( $multiple ) && 'yes' != $multiple ? 'name = "' . $name . '"' : '';

					foreach ( $options as $option_value => $option_label ) :
						?>
						<div class="mwb-<?php echo esc_html( $base_class ); ?>-radio-wrapper">
							<input type="<?php echo esc_attr( $type ); ?>" class="on-boarding-<?php echo esc_attr( $type ); ?>-field <?php echo esc_attr( $class ); ?>" value="<?php echo esc_attr( $option_value ); ?>" id="<?php echo esc_attr( $option_value ); ?>" <?php echo esc_html( $required ); ?> <?php echo esc_attr( $is_multiple ); ?>>
							<label class="on-boarding-field-label" for="<?php echo esc_html( $option_value ); ?>"><?php echo esc_html( $option_label ); ?></label>
						</div>
					<?php endforeach; ?>

					<?php
				 endif;

				break;

			case 'checkbox':
				// If field requires multiple answers.
				if ( ! empty( $options ) && is_array( $options ) ) :
					?>

					<label class="on-boarding-label" for="<?php echo esc_attr( $id ); ?>'"><?php echo esc_attr( $label ); ?></label>
					
					<?php foreach ( $options as $option_id => $option_label ) : ?>
						
						   <div class="mwb-<?php echo esc_html( $base_class ); ?>-checkbox-wrapper">
						<input type="<?php echo esc_html( $type ); ?>" class="on-boarding-<?php echo esc_html( $type ); ?>-field <?php echo esc_html( $class ); ?>" value="<?php echo esc_html( $value ); ?>" id="<?php echo esc_html( $option_id ); ?>">
						<label class="on-boarding-field-label" for="<?php echo esc_html( $option_id ); ?>"><?php echo esc_html( $option_label ); ?></label>
						</div>

					<?php endforeach; ?>
					<?php
				endif;

				break;

			case 'select':
			case 'select2':
				// If field requires multiple answers.
				if ( ! empty( $options ) && is_array( $options ) ) {

					$is_multiple = 'yes' == $multiple ? 'multiple' : '';
					$select2 = ( 'yes' == $multiple && 'select' == $type ) || 'select2' == $type ? 'on-boarding-select2 ' : '';
					?>

					<label class="on-boarding-label"  for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<select class="on-boarding-select-field <?php echo esc_html( $select2 ); ?> <?php echo esc_html( $class ); ?>" id="<?php echo esc_html( $id ); ?>" name="<?php echo esc_html( $name ); ?>[]" <?php echo esc_html( $required ); ?> <?php echo esc_html( $is_multiple ); ?>>

						<?php if ( 'select' == $type ) : ?>	
							<option class="on-boarding-options" value=""><?php esc_html_e( 'Select Any One Option...', 'points-and-rewards-for-woocommerce' ); ?></option>
						<?php endif; ?>

						<?php foreach ( $options as $option_value => $option_label ) : ?>	
						
							<option class="on-boarding-options" value="<?php echo esc_attr( $option_value ); ?>"><?php echo esc_html( $option_label ); ?></option>

						<?php endforeach; ?>
					</select>

					<?php
				}

				break;

			case 'label':
				/**
				 * Only a text in label.
				 */
				?>
				<label class="on-boarding-label <?php echo( esc_html( $class ) ); ?>" for="<?php echo( esc_attr( $id ) ); ?>"><?php echo( esc_html( $label ) ); ?></label>
				<?php
				break;

			case 'textarea':
				/**
				 * Text Area Field.
				 */
				?>
				<textarea rows="3" cols="50" class="<?php echo( esc_html( $base_class ) ); ?>-textarea-field <?php echo( esc_html( $class ) ); ?>" placeholder="<?php echo( esc_attr( $label ) ); ?>" id="<?php echo( esc_attr( $id ) ); ?>" name="<?php echo( esc_attr( $name ) ); ?>"><?php echo( esc_attr( $value ) ); ?></textarea>

				<?php
				break;

			default:
				/**
				 * Text/ Password/ Email.
				 */
				?>
				<label class="on-boarding-label" for="<?php echo( esc_attr( $id ) ); ?>"><?php echo( esc_html( $label ) ); ?></label>
				<input type="<?php echo( esc_attr( $type ) ); ?>" class="on-boarding-<?php echo( esc_attr( $type ) ); ?>-field <?php echo( esc_attr( $class ) ); ?>" value="<?php echo( esc_attr( $value ) ); ?>"  name="<?php echo( esc_attr( $name ) ); ?>" id="<?php echo( esc_attr( $id ) ); ?>" <?php echo( esc_html( $required ) ); ?>>

				<?php
		}

		if ( 'hidden' != $type ) :
			?>
			</div>
			<?php
		endif;
	}


	/**
	 * Send the data to MWB server.
	 *
	 * @since    1.0.7
	 */
	public function send_onboarding_data() {

		check_ajax_referer( 'mwb_onboarding_nonce', 'nonce' );

		$form_data = ! empty( $_POST['form_data'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['form_data'] ) ) ) : '';

		$formatted_data = array();
		$formatted_data['currency'] = get_woocommerce_currency();

		if ( ! empty( $form_data ) && is_array( $form_data ) ) {

			foreach ( $form_data as $key => $input ) {

				if ( false !== strrpos( $input->name, '[]' ) ) {

					$new_key = str_replace( '[]', '', $input->name );
					$new_key = str_replace( '"', '', $new_key );
					if ( empty( $formatted_data[ $new_key ] ) ) {
						$formatted_data[ $new_key ] = array();
					}

					array_push( $formatted_data[ $new_key ], $input->value );
				} else {
					$input->name = str_replace( '"', '', $input->name );
					$formatted_data[ $input->name ] = $input->value;
				}
			}
		}

		try {

			if ( ! empty( $formatted_data ) && is_array( $formatted_data ) ) {

				unset( $formatted_data['show-counter'] );
				$email_body = $this->render_form_data_into_table( $formatted_data );
			}
			$action_type = ! empty( $formatted_data['deactivation-reason'] ) ? 'deactivation' : 'onboarding';

			/**
			 * Set the email body type temporarily.
			 *
			 * @since    1.0.0
			 */
			function set_temp_content_type() {
				return 'text/html';
			}
			add_filter( 'wp_mail_content_type', 'set_temp_content_type' );

			$email_to = 'plugins@makewebbetter.com';
			$email_subject = ! empty( $action_type ) && 'deactivation' == $action_type ? 'Deactivation Attempt' : 'New Onboarding Data';

			$send_mail = wp_mail( $email_to, $email_subject, $email_body );

			remove_filter( 'wp_mail_content_type', 'set_temp_content_type' );

		} catch ( Exception $e ) {

			echo json_encode( $e->getMessage() );
			wp_die();
		}

		if ( ! empty( $action_type ) && 'onboarding' == $action_type ) {
			$get_skipped_timstamp = update_option( 'onboarding-data-sent', 'sent' );
		}

		echo json_encode( $formatted_data );
		wp_die();
	}


	/**
	 * Covert array to html.
	 *
	 * @param      array $formatted_data       The parsed data submitted vai form.
	 * @since      1.0.7
	 */
	public function render_form_data_into_table( $formatted_data = array() ) {

		$email_body = '<table border="1" style="border-collapse: collapse;text-align:center;"><tr><th>Data</th><th>Value</th></tr>';
		foreach ( $formatted_data as $key => $value ) {

			$key = ucwords( str_replace( '_', ' ', $key ) );
			$key = ucwords( str_replace( '-', ' ', $key ) );

			if ( is_array( $value ) ) {

				$email_body .= '<tr><td>' . $key . '</td><td>';

				foreach ( $value as $k => $v ) {
					$email_body .= ucwords( $v ) . '<br>';
				}

				$email_body .= '</td></tr>';
			} else {

				$email_body .= '  <tr><td>' . $key . '</td><td>' . ucwords( $value ) . '</td></tr>';
			}
		}

		$email_body .= '</table>';

		return $email_body;
	}

	/**
	 * Skip the popup for some days.
	 *
	 * @since    1.0.7
	 */
	public function skip_onboarding_popup() {

		$get_skipped_timstamp = update_option( 'onboarding-data-skipped', time() );
		echo json_encode( 'true' );
		wp_die();
	}

	/**
	 * Add additional validations to onboard screen.
	 *
	 * @param      string $result       The result of this validation.
	 * @since    1.0.7
	 */
	public function add_mwb_additional_validation( $result = true ) {

		if ( ! empty( $_GET['tab'] ) && 'general-setting' !== $_GET['tab'] ) {
			$result = false;
		}

		return $result;
	}

	// End of Class.
}
