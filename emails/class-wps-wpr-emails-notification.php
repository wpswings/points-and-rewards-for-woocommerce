<?php
/**
 * This file is used to include email template.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.8
 *
 * @package    points-and-rewards-for-wooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wps_Wpr_Emails_Notification' ) ) {
		/**
		 * Wps_Wpr_Emails_Notification function
		 *
		 * @param [int] $email_content
		 * @param [mixed] $wps_wpr_email_subject
		 * @return void
		 */
	class Wps_Wpr_Emails_Notification extends WC_Email {
		/**
		 * Email_content variable
		 *
		 * @var [int]
		 */
		public $email_content;
		/**
		 * Mwb_wpr_email_subject variable
		 *
		 * @var [int]
		 */
		public $wps_wpr_email_subject;
		/**
		 * __construct function
		 */
		public function __construct() {
			$this->id             = 'wps_wpr_email_notification';
			$this->title          = __( 'Points and rewards email', 'points-and-rewards-for-woocommerce' );
			$this->customer_email = true;
			$this->description    = __( 'This email is sent to the customer at every event.', 'points-and-rewards-for-woocommerce' );
			$this->template_html  = 'wps-wpr-email-notification-template.php';
			$this->template_plain = 'plain/wps-wpr-email-notification-template.php';
			$this->template_base  = WPS_RWPR_DIR_PATH . 'emails/templates/';
			$this->placeholders   = array(
				'{site_title}'       => $this->get_blogname(),
				'{email_content}' => '',
			);

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since      1.0.8
		 * @return string
		 */
		public function get_default_subject() {

			return $this->wps_wpr_email_subject;
		}

		/**
		 * Get email heading.
		 *
		 * @since      1.0.8
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Points and rewards notification', 'points-and-rewards-for-woocommerce' );
		}
		/**
		 * Trigger function
		 *
		 * @param  [int]    $user_id Used for id.
		 * @param [String] $email_content used for email content.
		 * @param [String] $wps_wpr_email_subject used for email subject.
		 * @return void
		 */
		public function trigger( $user_id, $email_content, $wps_wpr_email_subject ) {
			if ( $user_id ) {
				$this->setup_locale();

				$user      = new WP_User( $user_id );
				$user_info = get_userdata( $user_id );
				if ( is_a( $user, 'WP_User' ) ) {
					$this->object                          = $user;
					$this->email_content                   = $email_content;
					$this->wps_wpr_email_subject           = $wps_wpr_email_subject;
					$this->recipient                       = $user_info->user_email;
					$this->placeholders['{email_content}'] = $email_content;

					if ( $this->is_enabled() && $this->get_recipient() ) {
						$this->send( $this->get_recipient(), $wps_wpr_email_subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );
					}
				}
				$this->restore_locale();
			}

		}
		/**
		 * Trigger_test function
		 *
		 * @param [int]    $user_id for userid.
		 * @param [string] $email_content used for email content .
		 * @param [String] $wps_wpr_email_subject used for email subject.
		 * @param [String] $wps_reciever_email used for email.
		 * @return void
		 */
		public function trigger_test( $user_id, $email_content, $wps_wpr_email_subject, $wps_reciever_email ) {
			if ( $user_id ) {
				$this->setup_locale();

				$user      = new WP_User( $user_id );
				$user_info = get_userdata( $user_id );
				if ( is_a( $user, 'WP_User' ) ) {
					$this->object                          = $user;
					$this->email_content                   = $email_content;
					$this->wps_wpr_email_subject           = $wps_wpr_email_subject;
					$this->recipient                       = $wps_reciever_email;
					$this->placeholders['{email_content}'] = $email_content;

					if ( $this->is_enabled() && $this->get_recipient() ) {
						$this->send( $this->get_recipient(), $wps_wpr_email_subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );
					}
				}
				$this->restore_locale();
			}

		}

		/**
		 * Get content html.
		 *
		 * @since      1.0.8
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'user'          => $this->object,
					'email_content' => $this->email_content,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @since      1.0.8
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'user'          => $this->object,
					'email_content' => $this->email_content,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => true,
					'email'         => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Initialise settings form fields.
		 *
		 * @since      1.0.8
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'points-and-rewards-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'points-and-rewards-for-woocommerce' ),
					'default' => 'yes',
				),
				'heading' => array(
					'title'       => __( 'Email heading', 'points-and-rewards-for-woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'points-and-rewards-for-woocommerce' ), '<code>{site_title}</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'points-and-rewards-for-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'points-and-rewards-for-woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}

	}

}
return new Wps_Wpr_Emails_Notification();
