define(['jquery', 'Magento_Ui/js/modal/alert'], function ($, alert) {
    'use strict';

    return function () {
        var currentFile = null;
        var selectedStyle = null;
        var uploadedImageUrl = null;

        $(document).ready(function () {
            
            // Captura o estilo selecionado
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

                var maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert({content: 'O arquivo deve ter no máximo 5MB'});
                    $(this).val('');
                    return;
                }

                currentFile = file;

                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#thumbnail-preview').attr('src', event.target.result);
                    $('#thumbnail-container').fadeIn(300);
                };
                reader.readAsDataURL(file);
            });

            $(document).on('click', '.delete-thumbnail', function () {
                $('#thumbnail-container').fadeOut(0);
                $('#thumbnail-preview').attr('src', '');
                $('#upload-input').val('');
                currentFile = null;
            });

            // ENVIAR
            $('.send-btn').on('click', function () {
                if (!currentFile) {
                    alert({content: 'Por favor, selecione uma imagem primeiro'});
                    return;
                }

                if (!selectedStyle) {
                    alert({content: 'Por favor, selecione um estilo artístico'});
                    return;
                }

                if (!['image/png', 'image/jpeg'].includes(currentFile.type)) {
                    alert({content: 'Tipo de arquivo inválido'});
                    return;
                }

                if (currentFile.size > 5 * 1024 * 1024) {
                    alert({content: 'Arquivo muito grande'});
                    return;
                }

                // Esconde elementos
                $('.button-container').hide();
                $('.page-title-wrapper').hide();
                $('#thumbnail-container').hide();
                $('.upload-container').hide();

                // Mostra loading
                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#uploaded-preview').attr('src', event.target.result);
                    $('#popup-working').fadeIn(300);
                };
                reader.readAsDataURL(currentFile);

                // AJAX Upload
                var formData = new FormData();
                formData.append('image', currentFile);
                formData.append('style', selectedStyle);

                $.ajax({
                    url: '/ai/index/upload',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    showLoader: false,
                    success: function(response) {
                        if (response.success) {
                            uploadedImageUrl = response.url;
                            
                            // Simula processamento da IA (substitua pela chamada real)
                            setTimeout(function() {
                                $('.working-content').hide();
                                $('#uploaded-preview').attr('src', uploadedImageUrl);
                                $('.add-cart').show();
                            }, 3000);
                            
                        } else {
                            alert({content: 'Erro ao enviar imagem: ' + response.error});
                            resetPage();
                        }
                    },
                    error: function(xhr, status, error) {
                        alert({content: 'Erro na requisição: ' + error});
                        resetPage();
                    }
                });
            });

            // ADICIONAR AO CARRINHO
            $(document).on('click', '.add-cart', function() {
                if (!uploadedImageUrl || !selectedStyle) {
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

                // Adiciona ao carrinho via AJAX
                $.ajax({
                    url: '/ai/cart/add',
                    type: 'POST',
                    data: {
                        sku: productSku,
                        image_url: uploadedImageUrl,
                        style: selectedStyle
                    },
                    showLoader: true,
                    success: function(response) {
                        if (response.success) {
                            alert({
                                content: response.message,
                                actions: {
                                    always: function() {
                                        // Recarrega a página ou redireciona para o carrinho
                                        window.location.href = '/checkout/cart';
                                    }
                                }
                            });
                        } else {
                            alert({content: 'Erro: ' + response.error});
                        }
                    },
                    error: function() {
                        alert({content: 'Erro ao adicionar ao carrinho'});
                    }
                });
            });

            
$(document).on('click', '.delete-draw', function () {
    resetPage();
});

$(document).on('click', '.refresh-draw', function () {
    if (!currentFile || !selectedStyle) {
        alert({content: 'Não há imagem para reenviar. Selecione uma nova arte.'});
        return;
    }

    $('.working-content').show();

    var reader = new FileReader();
    reader.onload = function (event) {
        $('#uploaded-preview').attr('src', event.target.result);
        $('#popup-working').fadeIn(300);
    };
    reader.readAsDataURL(currentFile);

    var formData = new FormData();
    formData.append('image', currentFile);
    formData.append('style', selectedStyle);

    $.ajax({
        url: '/ai/index/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        showLoader: false,
        success: function (response) {
            if (response.success) {
                uploadedImageUrl = response.url;
                setTimeout(function () {
                    $('.working-content').hide();
                    $('#uploaded-preview').attr('src', uploadedImageUrl);
                    $('.add-cart').show();
                }, 3000);
            } else {
                alert({content: 'Erro ao enviar imagem: ' + response.error});
                resetPage();
            }
        },
        error: function (xhr, status, error) {
            alert({content: 'Erro na requisição: ' + error});
            resetPage();
        }
    });
});
        });

        function resetPage() {
            $('#popup-working').fadeOut(0);
            $('.working-content').show();
            $('.button-container').show();
            $('.page-title-wrapper').show();
            $('.upload-container').show();
            $('#thumbnail-container').show();

            if (selectedStyle) {
                $('.option-btn').removeClass('select-option');
                $('.option-btn[data-role="' + selectedStyle + '"]').addClass('select-option');
            }
        
            if (currentFile && !$('#thumbnail-preview').attr('src')) {
                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#thumbnail-preview').attr('src', event.target.result);
                };
                reader.readAsDataURL(currentFile);
            }
        }
    };

    
});