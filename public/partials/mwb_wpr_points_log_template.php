<?php

$user_id = $user_ID;
if(isset($user_id) && $user_id!='' && is_numeric($user_id)){
	$point_log = get_user_meta( $user_id, 'points_details', true);
	$total_points = get_user_meta( $user_id, 'mwb_wpr_points', true );
	if(isset($point_log) && is_array($point_log) && $point_log != null){
	?>
		<h2><?php _e(' Point Log Table', MWB_RWPR_Domain); ?></h2>
		<table class="woocommerce-MyAccount-points shop_table my_account_points account-points-table mwb_wpr_table_view_log">
				<thead>
					<tr>
						<th class="view-log-Date">
							<span class="nobr"><?php echo __( 'Date', MWB_RWPR_Domain ); ?></span>
						</th>
						<th class="view-log-Status">
							<span class="nobr"><?php echo __( 'Point Status', MWB_RWPR_Domain ); ?></span>
						</th>
						<th class="view-log-Activity">
							<span class="nobr"><?php echo __( 'Activity', MWB_RWPR_Domain ); ?></span>
						</th>
					</tr>
				</thead>
      </table>
      <div class="mwb_wpr_slide_toggle">
        <table class="mwb_wpr_common_table">
          <tr>
              <?php 
              if(array_key_exists('registration', $point_log)){
              ?>  
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e('Signup Event',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
                  <td><?php echo mwb_wpr_set_the_wordpress_date_format($point_log['registration']['0']['date']);?></td>
                  <td><?php echo "+".($point_log['registration']['0']['registration']);?></td>
                  <td><?php _e('Registration Points', MWB_RWPR_Domain);?></td>
              <?php    
              }
              ?>
          </tr>
          <tr>
              <?php 
              if(array_key_exists('import_points', $point_log)){
              ?>   
                  <td><?php echo mwb_wpr_set_the_wordpress_date_format($point_log['import_points']['0']['date']);?></td>
                  <td><?php echo "+".($point_log['import_points']['0']['import_points']);?></td>
                  <td><?php _e('Registration Points', MWB_RWPR_Domain);?></td>
              <?php    
              }
              ?>
          </tr>
        </table>
      </div> 
	           <?php 
              if(array_key_exists('Coupon_details', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Coupon Creation',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['Coupon_details'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['Coupon_details'];?></td>
                    <td><?php _e('Coupon Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
              </table></div>
              <?php   
              }
              if(array_key_exists('product_details', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points Earned via Particular Product',MWB_RWPR_Domain);?> <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
             
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['product_details'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['product_details'];?></td>
                    <td><?php _e('Product purchase Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
              ?>
              </table>
              </div>
              <?php   
              }
              if(array_key_exists('pro_conversion_points', $point_log)){ ?>
               <div class="mwb_wpr_slide_toggle">
                <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points earned on Order Total',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
                  <table class="mwb_wpr_common_table">
                  <?php foreach ($point_log['pro_conversion_points'] as $key => $value){ ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['pro_conversion_points'];?></td>
                    <td><?php _e('Product Conversion Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
                 </table>
                 </div>
                 <?php  
              }
              if(array_key_exists('points_on_order', $point_log)){ ?>
               <div class="mwb_wpr_slide_toggle">
                <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points on Order',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
                  <table class="mwb_wpr_common_table">
                 	<?php foreach ($point_log['points_on_order'] as $key => $value){ ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['points_on_order'];?></td>
                    <td><?php _e('Points earned on Order Total', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
                 </table>
                 </div>
                 <?php  
              }
              if(array_key_exists('comment', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points earned via giving review',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
              <table class="mwb_wpr_common_table"> 
              <?php
              foreach ($point_log['comment'] as $key => $value){
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['comment'];?></td>
                    <td><?php _e('Comment Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table>
                 </div>
                 <?php  
              }
              if(array_key_exists('reference_details', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points earned via referring someone',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['reference_details'] as $key => $value) {
                   $user_name = '';
                    if(isset($value['refered_user']) && !empty($value['refered_user'])){
                        $user = get_user_by('ID',$value['refered_user']);
                        $user_name = $user->user_nicename;
                    }
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['reference_details'];?></td>
                    <td><?php _e('Reference Points by ', MWB_RWPR_Domain); echo $user_name;?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table> </div>
                 <?php  
              }
              if(array_key_exists('ref_product_detail', $point_log)){?>
              <div class="mwb_wpr_slide_toggle">
               <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points earned by the puchase has been made by referrals',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['ref_product_detail'] as $key => $value) {
                  $user_name = '';
                   if(isset($value['refered_user']) && !empty($value['refered_user'])){
                        $user = get_user_by('ID',$value['refered_user']);
                        $user_name = $user->user_nicename;
                    }
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['ref_product_detail'];?></td>
	                  <td><?php _e('Product purchase by Reffered User Points ', MWB_RWPR_Domain);echo $user_name;?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table>
                </div> 
                 <?php 
              }
              if(array_key_exists('membership', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points deducted becuase you have entered to the membership level and may use more benefit',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
              <table class="mwb_wpr_common_table">
              <?php   	
                 foreach ($point_log['membership'] as $key => $value){ 
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['membership'];?></td>
                    <td><?php _e('Membership Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table>
                 </div>
                 <?php
              }
              if(array_key_exists('admin_points', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
               <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Free rewards you earned becuase of loyality',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['admin_points'] as $key => $value) {
                  $value['sign'] = isset($value['sign'])?$value['sign']: '+/-';
                  $value['reason'] = isset($value['reason'])?$value['reason']: __('Updated By Admin', MWB_RWPR_Domain);
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo $value['sign'].$value['admin_points'];?></td>
                    <td><?php echo $value['reason'];?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table></div>
                 <?php 
              }
              if(array_key_exists('pur_by_points', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
               <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Deduction of points as you has purchased your product',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
               
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['pur_by_points'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['pur_by_points'];?></td>
                    <td><?php _e('Purchased through Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table></div>
               <?php   
              }
              if(array_key_exists('deduction_of_points', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Deduction of points for your return request',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['deduction_of_points'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['deduction_of_points'];?></td>
                    <td><?php _e('Deduct Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
                 </table> </div>
                 <?php  
              }
              if(array_key_exists('return_pur_points', $point_log)){?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points returned successfully on your return request',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['return_pur_points'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?> </td>
                    <td><?php echo "+".$value['return_pur_points'];?></td>
                    <td><?php _e('Return Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
                </table>
                </div>
                <?php  
              }
              if(array_key_exists('deduction_currency_spent', $point_log)){?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points deducted successfully on your return request',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['deduction_currency_spent'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['deduction_currency_spent'];?></td>
                    <td><?php _e('Deduct Per Currency Spent Point', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table>
                 </div>
                 <?php
              }
              if(array_key_exists('Sender_point_details', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points deducted successfully as you have shared your points',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['Sender_point_details'] as $key => $value) {
                  $user_name = '';
                   if(isset($value['give_to']) && !empty($value['give_to'])){
                        $user = get_user_by('ID',$value['give_to']);
                        $user_name = $user->user_nicename;
                    }
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?> </td>
                    <td><?php echo "-".$value['Sender_point_details'];?></td>
                    <td><?php _e('Shared to ', MWB_RWPR_Domain); echo $user_name; ?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table></div>   
              <?php }
              if(array_key_exists('Receiver_point_details', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points received successfully as someone has shared',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['Receiver_point_details'] as $key => $value) {
                  $user_name = '';
                   if(isset($value['received_by']) && !empty($value['received_by'])){
                        $user = get_user_by('ID',$value['received_by']);
                        $user_name = $user->user_nicename;
                    }
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['Receiver_point_details'];?></td>
                    <td><?php _e('Received Points via ', MWB_RWPR_Domain);echo $user_name; ?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table></div>
                <?php   
              }
              if(array_key_exists('cart_subtotal_point', $point_log)){ ?>
              <div class="mwb_wpr_slide_toggle">
              <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points Applied on Cart',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
              
              <table class="mwb_wpr_common_table">
              <?php
                 foreach ($point_log['cart_subtotal_point'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['cart_subtotal_point'];?></td>
                    <td><?php _e('Points Used on Cart Subtotal', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 } ?>
                 </table></div>
                <?php   
              }
       		if(array_key_exists('expired_details', $point_log)){ ?>
          <div class="mwb_wpr_slide_toggle">
          <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Oops!! Points are expired!',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
          
          <table class="mwb_wpr_common_table">
          <?php
                 foreach ($point_log['expired_details'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['expired_details'];?></td>
                    <td><?php _e('Get Expired', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
          </table>
          </div> 
          <?php
          }
          if(array_key_exists('deduct_currency_pnt_cancel', $point_log)){ ?>
          <div class="mwb_wpr_slide_toggle">
          <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Order Points Deducted due to Cancelation of Order',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
          
          <table class="mwb_wpr_common_table">
          <?php
                 foreach ($point_log['deduct_currency_pnt_cancel'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['deduct_currency_pnt_cancel'];?></td>
                    <td><?php _e('Deducted Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
          </table>
          </div> 
          <?php
          }
          if(array_key_exists('deduct_bcz_cancel', $point_log)){ ?>
          <div class="mwb_wpr_slide_toggle">
          <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Assigned Points Deducted due Cancelation of Order',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
          
          <table class="mwb_wpr_common_table">
          <?php
                 foreach ($point_log['deduct_bcz_cancel'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['deduct_bcz_cancel'];?></td>
                    <td><?php _e('Deducted Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
          </table>
          </div> 
          <?php
          }
          if(array_key_exists('pur_points_cancel', $point_log)){ ?>
          <div class="mwb_wpr_slide_toggle">
          <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points Returned due to Cancelation of Order',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
          
          <table class="mwb_wpr_common_table">
          <?php
                 foreach ($point_log['pur_points_cancel'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "+".$value['pur_points_cancel'];?></td>
                    <td><?php _e('Returned Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
          </table>
          </div> 
          <?php
          }
          //MWB CUSTOM CODE
          if(array_key_exists('pur_pro_pnt_only', $point_log)){ ?>
          <div class="mwb_wpr_slide_toggle">
          <p class="mwb_wpr_view_log_notice mwb_wpr_common_slider"><?php _e('Points deducted for purchasing the product',MWB_RWPR_Domain);?><a class ="mwb_wpr_open_toggle"  href="javascript:;"></a></p>
          
          <table class="mwb_wpr_common_table">
          <?php
                 foreach ($point_log['pur_pro_pnt_only'] as $key => $value) {
                  ?>
                  <tr>
                    <td><?php echo mwb_wpr_set_the_wordpress_date_format($value['date']);?></td>
                    <td><?php echo "-".$value['pur_pro_pnt_only'];?></td>
                    <td><?php _e('Product Purchased by Points', MWB_RWPR_Domain);?></td>
                  </tr>
                 <?php 
                 }
                 ?>
          </table>
          </div> 
          <?php
          }
          //END OF MWB CUSTOM CODE
          ?>
        <div class="mwb_wpr_slide_toggle">
        <table class="mwb_wpr_total_points">
          <tr>
              <td><h6><?php _e('Total Points', MWB_RWPR_Domain);?></h6></td>
              <td><h6><?php echo $total_points;?></h6></td>
              <td></td>
          </tr>        
		    </table>
        </div>
	<?php	
	}
	else{
		 echo "<h3>".__('No Points Generated Yet.',MWB_RWPR_Domain)."<h3>";
	}
}
?>