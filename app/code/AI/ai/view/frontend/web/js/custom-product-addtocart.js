define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data',
    'mage/cookies'
], function ($, storage, alert, customerData) {
    'use strict';

    return function (config, element) {
        var $form = $(element);
        
        // Intercepta o submit do formulário
        $form.on('submit', function(e) {
            var processedImage = $('#custom-processed-image').val();
            var originalImage = $('#custom-original-image').val();
            
            // Se temos imagens customizadas, intercepta e usa nossa API
            if (processedImage && originalImage) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                var formData = $form.serialize();
                var formDataArray = $form.serializeArray();
                var productId = null;
                
                // Pega o product ID do form
                $.each(formDataArray, function(i, field) {
                    if (field.name === 'product') {
                        productId = field.value;
                    }
                });
                
                if (!productId) {
                    alert({content: 'Erro ao identificar o produto'});
                    return false;
                }
                
                // Mapeia ID para SKU (seus produtos)
                var productSkuMap = {
                    '11': 'redraw-3d',
                    '12': 'redraw-fotorrealista', 
                    '13': 'redraw-anime',
                    '14': 'redraw-aquarela'
                };
                
                var sku = productSkuMap[productId];
                
                if (!sku) {
                    alert({content: 'Produto não encontrado: ' + productId});
                    return false;
                }
                
                // Mostra loader
                $('body').trigger('processStart');
                
                // Envia para nossa API customizada
                $.ajax({
                    url: '/ai/cart/add',
                    type: 'POST',
                    data: {
                        sku: sku,
                        image_url: processedImage,
                        original_image: originalImage,
                        style: sku.replace('redraw-', ''),
                        form_key: $.mage.cookies.get('form_key')
                    },
                    success: function(response) {
                        $('body').trigger('processStop');
                        
                        if (response.success) {
                            // Força reload do customer data (minicart)
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                            
                            // Mostra mensagem de sucesso
                            var message = $('<div class="message-success success message">' +
                                '<div>' + response.message + '</div>' +
                                '</div>');
                            
                            $('.product-info-main').prepend(message);
                            
                            // Remove mensagem após 3 segundos
                            setTimeout(function() {
                                message.fadeOut(500, function() {
                                    $(this).remove();
                                });
                            }, 3000);
                            
                        } else {
                            alert({content: 'Erro: ' + (response.error || 'Erro desconhecido')});
                        }
                    },
                    error: function(xhr) {
                        $('body').trigger('processStop');
                        alert({content: 'Erro ao adicionar ao carrinho'});
                    }
                });
                
                return false;
            }
            
            // Se não tem imagens customizadas, deixa o Magento processar normalmente
            return true;
        });
    };
});