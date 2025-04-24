<?php
/**
 * API Secret Settings Template
 *
 * This file is used to display the content of the API Secret Settings tab.
 *
 * @package    points-and-rewards-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h2 class="wps_rwpr_setting_title"><?php esc_html_e( 'Senha API', 'points-and-rewards-for-woocommerce' ); ?></h2>
    <p class="wps_rwpr_setting_description"><?php esc_html_e( 'Entre sua senha de API', 'points-and-rewards-for-woocommerce' ); ?></p>
    
    <div id="wps_api_messages" style="margin-bottom: 20px;"></div>
    
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="api_secret_key" class="wps_rwpr_label"><?php esc_html_e( 'Senha API', 'points-and-rewards-for-woocommerce' ); ?></label>
            </th>
            <td>
                <input type="text" name="api_secret_key" id="api_secret_key" value="" class="regular-text wps_rwpr_input">
                <p class="description"><?php esc_html_e( 'Entre sua senha de API.', 'points-and-rewards-for-woocommerce' ); ?></p>
            </td>
        </tr>
    </table>
    <div style="text-align: center; margin-top: 20px;">
        <button type="button" id="wps_save_api_secret" class="button button-primary wps_rwpr_button">
            <?php esc_html_e( 'Salvar Alterações', 'points-and-rewards-for-woocommerce' ); ?>
        </button>
        <span class="spinner" id="wps_api_spinner" style="float: none; visibility: hidden;"></span>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#wps_save_api_secret').on('click', function() {
        // Exibir spinner
        $('#wps_api_spinner').css('visibility', 'visible');
        
        // Obter o valor da chave API
        var apiKey = $('#api_secret_key').val();
        
        // Limpar o campo imediatamente após obter o valor
        $('#api_secret_key').val('');
        
        // Verificar se a chave não está vazia
        if (!apiKey) {
            $('#wps_api_messages').html('<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'A chave API não pode estar vazia.', 'points-and-rewards-for-woocommerce' ); ?></p></div>');
            $('#wps_api_spinner').css('visibility', 'hidden');
            return;
        }
        
        // Enviar a requisição AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wps_save_api_secret_ajax',
                api_secret_key: apiKey,
                security: '<?php echo wp_create_nonce('wps_api_ajax_nonce'); ?>'
            },
            success: function(response) {
                $('#wps_api_spinner').css('visibility', 'hidden');
                
                if (response.success) {
                    $('#wps_api_messages').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                } else {
                    $('#wps_api_messages').html('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>');
                }
                
                // Tornar as notificações dispensáveis
                $('.notice-dismiss').on('click', function() {
                    $(this).parent().fadeOut();
                });
            },
            error: function() {
                $('#wps_api_spinner').css('visibility', 'hidden');
                $('#wps_api_messages').html('<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Ocorreu um erro ao processar sua solicitação.', 'points-and-rewards-for-woocommerce' ); ?></p></div>');
            }
        });
    });
});
</script>