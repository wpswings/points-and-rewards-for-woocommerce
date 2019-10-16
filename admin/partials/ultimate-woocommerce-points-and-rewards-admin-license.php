<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="ultimate-woocommerce-points-and-rewards-license-sec">

	<h3><?php _e('Enter your License', 'ultimate-woocommerce-points-and-rewards' ) ?></h3>

    <p>
    	<?php _e('This is the License Activation Panel. After purchasing extension from ', 'ultimate-woocommerce-points-and-rewards' ); ?>
    	<span>
            <a href="https://makewebbetter.com/" target="_blank" ><?php _e('MakeWebBetter',  'ultimate-woocommerce-points-and-rewards' ); ?></a>
        </span>&nbsp;

        <?php _e('you will get the purchase code of this extension. Please verify your purchase below so that you can use the features of this plugin.', 'ultimate-woocommerce-points-and-rewards' ); ?>
    </p>

	<form id="ultimate-woocommerce-points-and-rewards-license-form">

	    <label><b><?php _e('Purchase Code : ', 'ultimate-woocommerce-points-and-rewards' )?></b></label>

	    <input type="text" id="ultimate-woocommerce-points-and-rewards-license-key" placeholder="<?php _e('Enter your code here.', 'ultimate-woocommerce-points-and-rewards' )?>" required="">

	    <div id="ultimate-woocommerce-points-and-rewards-ajax-loading-gif"><img src="<?php echo 'images/spinner.gif'; ?>"></div>
	    
	    <p id="ultimate-woocommerce-points-and-rewards-license-activation-status"></p>

	    <button type="submit" class="button-primary"  id="ultimate-woocommerce-points-and-rewards-license-activate"><?php _e('Activate', 'ultimate-woocommerce-points-and-rewards' )?></button>
	    
	    <?php wp_nonce_field( 'ultimate-woocommerce-points-and-rewards-license-nonce-action', 'ultimate-woocommerce-points-and-rewards-license-nonce' ); ?>

	</form>

</div>