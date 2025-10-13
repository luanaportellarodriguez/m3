define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data'
], function ($, alert, customerData) {
    'use strict';

    window.customerData = customerData;
    
    return function () {
        var currentFile = null;
        var selectedStyle = null;
        var uploadedImageUrl = null;
        var originalImageUrl = null;

        $(document).ready(function () {

            // Seleção do estilo
            $(document).on('click', '.option-btn', function(e) {
                if ($(e.target).hasClass('status-icon')) {
                    $(this).removeClass('select-option');
                    selectedStyle = null;
                    return;
                }

                $('.option-btn').removeClass('select-option');
                $(this).addClass('select-option');
                selectedStyle = $(this).data('role');
            });

            // Upload
            $('.upload-btn').on('click', function () {
                $('#upload-input').trigger('click');
            });

            $('#upload-input').on('change', function (e) {
                var file = this.files[0];
                if (!file) return;

                if (!['image/png', 'image/jpeg'].includes(file.type)) {
                    alert({content: 'Apenas arquivos PNG e JPG são permitidos'});
                    $(this).val('');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert({content: 'O arquivo deve ter no máximo 5MB'});
                    $(this).val('');
                    return;
                }

                currentFile = file;

                var reader = new FileReader();
                reader.onload = function (event) {
                    var base64Image = event.target.result;
                    
                    // Mostra preview imediatamente
                    $('#thumbnail-preview').attr('src', base64Image);
                    $('#thumbnail-container').fadeIn(300);
                    
                    // Faz upload em background
                    $.ajax({
                        url: '/ai/image/upload',
                        type: 'POST',
                        data: {
                            image_data: base64Image,
                            form_key: $.mage.cookies.get('form_key')
                        },
                        success: function(response) {
                            if (response.success) {
                                originalImageUrl = response.url;
                                console.log('Upload concluído:', originalImageUrl);
                            } else {
                                alert({content: 'Erro ao fazer upload: ' + response.error});
                            }
                        },
                        error: function() {
                            alert({content: 'Erro ao fazer upload da imagem'});
                        }
                    });
                };
                reader.readAsDataURL(file);
            });

            // Deletar thumbnail
            $(document).on('click', '.delete-thumbnail', function () {
                $('#thumbnail-container').fadeOut(0);
                $('#thumbnail-preview').attr('src', '');
                $('#upload-input').val('');
                currentFile = null;
                originalImageUrl = null;
            });

            // ENVIAR (mock)
            $('.send-btn').on('click', function () {
                if (!currentFile) {
                    alert({content: 'Por favor, selecione uma imagem primeiro'});
                    return;
                }

                if (!selectedStyle) {
                    alert({content: 'Por favor, selecione um estilo artístico'});
                    return;
                }

                // Define caminho base
                var baseImageUrl = '/media/customer_uploads/';
                uploadedImageUrl = baseImageUrl + 'dog-small-' + selectedStyle + '.jpg';

                // Inicia o processamento da imagem
                processImage();
            });

            // Função para processar a imagem
            function processImage() {
                // Desabilita os botões
                $('.add-cart, .refresh-draw').prop('disabled', true).css('opacity', '0.5');
                
                // Mostra o popup e o conteúdo de trabalho
                $('.button-container, .page-title-wrapper, .upload-container, #thumbnail-container').hide();
                $('#popup-working').fadeIn(300);
                $('.working-content').show();

                // Remove qualquer overlay existente
                $('.image-reveal-overlay').remove();
                $('.image-reveal-container').contents().unwrap();

                // Define a imagem imediatamente com blur
                var $preview = $('#uploaded-preview')
                    .attr('src', uploadedImageUrl)
                    .css('filter', 'blur(8px)')
                    .wrap('<div class="image-reveal-container"></div>');

                // Adiciona o overlay de revelação
                $preview.after('<div class="image-reveal-overlay"></div>');

                // Simula "upload + IA" com delay
                setTimeout(function() {
                    $preview.css('filter', 'none');
                    $('.working-content').hide();
                    $('.add-cart').fadeIn(300);
                    
                    // Habilita os botões novamente
                    $('.add-cart, .refresh-draw').prop('disabled', false).css('opacity', '1');
                    
                    // Remove o overlay após a animação terminar
                    setTimeout(function() {
                        $preview.unwrap().next('.image-reveal-overlay').remove();
                    }, 1500);
                }, 3000);
            }

            // Evento para o botão de refresh
            $(document).on('click', '.refresh-draw', function() {
                if (!uploadedImageUrl) return;
                processImage();
            });

// ADICIONAR AO CARRINHO
$(document).on('click', '.add-cart', function() {
    if (!uploadedImageUrl || !selectedStyle || !originalImageUrl) {
        alert({content: 'Dados incompletos'});
        return;
    }

    var skuMap = {
        '3d': 'redraw-3d',
        'fotorrealista': 'redraw-fotorrealista',
        'aquarela': 'redraw-aquarela',
        'anime': 'redraw-anime'
    };

    var productSku = skuMap[selectedStyle];

    console.log('=== ENVIANDO PARA CARRINHO ===');
    console.log('SKU:', productSku);

    // Mostra loader
    $('body').trigger('processStart');

    $.ajax({
        url: '/ai/cart/add',
        type: 'POST',
        data: {
            sku: productSku,
            image_url: uploadedImageUrl,
            original_image: originalImageUrl,
            style: selectedStyle,
            form_key: $.mage.cookies.get('form_key')
        },
        success: function(response) {
            $('body').trigger('processStop');
            
            console.log('=== RESPOSTA DO SERVIDOR ===');
            console.log('Resposta completa:', response);
            
            if (response.success) {
                console.log('✅ Sucesso! Forçando reload do carrinho...');
                
                // Força reload completo da página do carrinho
                // Isso garante que tudo seja atualizado corretamente
                window.location.href = '/checkout/cart?reload=' + Date.now();
            } else {
                console.error('❌ Erro retornado:', response.error);
                alert({
                    content: 'Erro: ' + (response.error || 'Erro desconhecido')
                });
            }
        },
        error: function(xhr, status, error) {
            $('body').trigger('processStop');
            
            console.error('=== ERRO AJAX ===');
            console.error('Status:', status);
            console.error('Response Text:', xhr.responseText);
            
            alert({
                content: 'Erro ao adicionar ao carrinho. Status: ' + xhr.status
            });
        }
    });
});

            function resetPage() {
                $('#popup-working').fadeOut(0);
                $('.working-content').show();
                $('.button-container, .page-title-wrapper, .upload-container, #thumbnail-container').show();

                if (selectedStyle) {
                    $('.option-btn').removeClass('select-option');
                    $('.option-btn[data-role="' + selectedStyle + '"]').addClass('select-option');
                }

                if (originalImageUrl) {
                    $('#thumbnail-preview').attr('src', originalImageUrl);
                }
            }

            // Botão deletar draw
            $(document).on('click', '.delete-draw', resetPage);

        });
    };
});