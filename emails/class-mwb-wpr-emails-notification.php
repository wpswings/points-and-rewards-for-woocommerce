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

if ( ! class_exists( 'Mwb_Wpr_Emails_Notification' ) ) {

	class Mwb_Wpr_Emails_Notification extends WC_Email {
		public $email_content;
		public $mwb_wpr_email_subject;
		public function __construct() {
			$this->id             = 'mwb_wpr_email_notification';
			$this->title          = __( 'Points and rewards email', 'points-and-rewards-for-woocommerce' );
			$this->customer_email = true;
			$this->description    = __( 'This emal send to the customer on every event.', 'points-and-rewards-for-woocommerce' );
			$this->template_html  = 'mwb-wpr-email-notification-template.php';
			$this->template_plain = 'plain/mwb-wpr-email-notification-template.php';
			$this->template_base  = MWB_RWPR_DIR_PATH . 'emails/templates/';
			$this->placeholders   = array(
				'{site_title}'       => $this->get_blogname(),
				'{email_content}' => '',
			);

			// Call parent constructor
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since      1.0.8
		 * @return string
		 */
		public function get_default_subject() {

			return $this->mwb_wpr_email_subject;
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
		 * Trigger the sending of this email.
		 *
		 * @since      1.0.8
		 * @param int $transaction_id.
		 */
		public function trigger( $user_id, $email_content, $mwb_wpr_email_subject ) {
			if ( $user_id ) {
				$this->setup_locale();

				$user = new WP_User( $user_id );
				$user_info = get_userdata( $user_id );
				if ( is_a( $user, 'WP_User' ) ) {
					$this->object = $user;
					$this->email_content = $email_content;
					$this->mwb_wpr_email_subject = $mwb_wpr_email_subject;
					$this->recipient = $user_info->user_email;
					$this->placeholders['{email_content}'] = $email_content;

					if ( $this->is_enabled() && $this->get_recipient() ) {
						$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
					'email_content'   => $this->email_content,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $this,
				),
				'points-and-rewards-for-woocommerce',
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
					'email_content'   => $this->email_content,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => true,
					'email' => $this,
				),
				'points-and-rewards-for-woocommerce',
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
return new Mwb_Wpr_Emails_Notification();
