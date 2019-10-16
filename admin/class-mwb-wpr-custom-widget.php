<?php
/*
 * Custom Widget for Public Coupons are
 */

register_widget( 'Mwb_Wpr_Custom_Widget' );
class Mwb_Wpr_Custom_Widget extends WP_Widget {
    public function __construct() {
        $widget_options = array( 'classname' => 'mwb_wpr_custom_widget', 'description' => 'My Point' );
        parent::__construct( 'mwb_wpr_custom_widget', 'Points and Rewards', $widget_options );
    }
    /*
    * Custom Widget
    */
    public function widget( $args, $instance ) { 
              
        extract( $args );
        $title      = apply_filters( 'widget_title', $instance['title'] );
        $mypoint    = isset($instance['mypoint']) && !empty($instance['mypoint'])? $instance['mypoint'] : __('Total Point:','ultimate-woocommerce-points-and-rewards');
        $mycurrentlevel    = isset($instance['mycurrentlevel']) && !empty($instance['mypoint'])? $instance['mycurrentlevel'] : __('User Level:','ultimate-woocommerce-points-and-rewards');
        $membershipexpiration    = isset($instance['membershipexpiration']) && !empty($instance['membershipexpiration'])? $instance['membershipexpiration'] : __('Expiration Date:','ultimate-woocommerce-points-and-rewards');
        $user_ID = get_current_user_ID();
        if(isset($user_ID) && !empty($user_ID))
        {
            echo $before_widget;
            if ( $title ) {
                echo $before_title . $title . $after_title;
            }
            
                $get_points = (int)get_user_meta($user_ID , 'mwb_wpr_points', true);
                echo "<p><strong>".$mypoint.' '.$get_points."</strong></p>"; 
                $user_level = get_user_meta($user_ID,'membership_level',true);
                $mwb_wpr_mem_expr = get_user_meta($user_ID,'membership_expiration',true);
                if(isset($user_level) && !empty($user_level)){
                   echo "<div><strong>".$mycurrentlevel.' '.$user_level."</strong></div>"; 
                }
                if(isset($mwb_wpr_mem_expr) && !empty($mwb_wpr_mem_expr)){
                    echo "<div><strong>".$membershipexpiration.' '.$mwb_wpr_mem_expr."</strong></div>";
                }
            echo $after_widget;
        }             
        
    }
    /*
    * Create Widget Form
    */
    public function form( $instance ) {
    $title = '';
    $mypoint = '';
    $mycurrentlevel = ''; 
    $membershipexpiration = '';
    if(isset($instance) && $instance != null)
    {
        $title = esc_attr( $instance['title'] );
        $mypoint = esc_attr( $instance['mypoint'] );
        $mycurrentlevel = esc_attr( $instance['mycurrentlevel'] );
        $membershipexpiration = esc_attr( $instance['membershipexpiration'] );
    }
    ?>
     
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','ultimate-woocommerce-points-and-rewards'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p> 
        <p>
        <label for="<?php echo $this->get_field_id('mypoint'); ?>"><?php _e('Total Point Text:','ultimate-woocommerce-points-and-rewards'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('mypoint'); ?>" name="<?php echo $this->get_field_name('mypoint'); ?>" type="text" value="<?php echo $mypoint; ?>" /></p>
        <p>
        <label for="<?php echo $this->get_field_id('mycurrentlevel'); ?>"><?php _e('User Level:','ultimate-woocommerce-points-and-rewards'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('mycurrentlevel'); ?>" name="<?php echo $this->get_field_name('mycurrentlevel'); ?>" type="text" value="<?php echo $mycurrentlevel; ?>" /></p>
        <p>
        <label for="<?php echo $this->get_field_id('membershipexpiration'); ?>"><?php _e('Expiration Text:','ultimate-woocommerce-points-and-rewards'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('membershipexpiration'); ?>" name="<?php echo $this->get_field_name('membershipexpiration'); ?>" type="text" value="<?php echo $membershipexpiration; ?>" /></p>   
    <?php 
    }
    /*
    * Update Widget
    */
    function update( $new_instance, $old_instance )
    {        
        $instance = $old_instance;   
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['mypoint'] = strip_tags( $new_instance['mypoint'] );
        $instance['mycurrentlevel'] = strip_tags( $new_instance['mycurrentlevel'] );
        $instance['membershipexpiration'] = strip_tags( $new_instance['membershipexpiration'] );
        return $instance;
    }
}
?>