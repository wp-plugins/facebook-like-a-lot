jQuery(document).ready(function($){
    var data = {
        // here we declare the parameters to send along with the request
        // this means the following action hooks will be fired:
        // wp_ajax_nopriv_ and wp_ajax_
        action : PlulzOrcamento.action,

        // other parameters can be added along with "action"
        postID : PlulzOrcamento.postID,

        // send the nonce along with the request
        orcamentoAjaxNonce : PlulzOrcamento.orcamentoAjaxNonce
    }

    jQuery('#pazzanibrindes_orcamento_email').blur(function(){

        var $value = jQuery(this).val();

        if ( $value != '' )
        {
            jQuery('#pazzanibrindes_orcamento_nome').val( 'Aguarde...' );
            jQuery('#pazzanibrindes_orcamento_telefone').val( 'Aguarde...' );

            jQuery.post( PlulzOrcamento.ajaxurl, $value, function( response ){
                alert(response);
            })
        }
        
    });
});