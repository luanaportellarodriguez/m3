define(['jquery'], function ($) {
    'use strict';

    return function () {
        var currentFile = null;

        $(document).ready(function () {
            // Clique no botão de upload
            $('.upload-btn').on('click', function () {
                $('#upload-input').trigger('click');
            });

            // Quando um arquivo é selecionado
            $('#upload-input').on('change', function (e) {
                var file = this.files[0];
                if (!file) return;

                // Validação de tipo
                if (!['image/png', 'image/jpeg'].includes(file.type)) {
                    alert('Apenas arquivos PNG e JPG são permitidos');
                    $(this).val('');
                    return;
                }

                // Validação de tamanho (5MB)
                var maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('O arquivo deve ter no máximo 5MB');
                    $(this).val('');
                    return;
                }

                // Armazena o arquivo atual
                currentFile = file;

                // Cria preview da imagem na miniatura
                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#thumbnail-preview').attr('src', event.target.result);
                    $('#thumbnail-container').fadeIn(300);
                };
                reader.readAsDataURL(file);
            });

            // Botão de deletar miniatura
            $(document).on('click', '.delete-thumbnail', function () {
                $('#thumbnail-container').fadeOut(0);
                $('#thumbnail-preview').attr('src', '');
                $('#upload-input').val('');
                currentFile = null;
            });

            // Botão de enviar
            $('.send-btn').on('click', function () {
                if (!currentFile) {
                    alert('Por favor, selecione uma imagem primeiro');
                    return;
                }

                // Validações finais
                if (!['image/png', 'image/jpeg'].includes(currentFile.type)) {
                    alert('Tipo de arquivo inválido');
                    return;
                }

                if (currentFile.size > 5 * 1024 * 1024) {
                    alert('Arquivo muito grande');
                    return;
                }

                // Esconde elementos
                $('.button-container').hide();
                $('.page-title-wrapper').hide();
                $('#thumbnail-container').hide();

                // Carrega a imagem no preview do popup
                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#uploaded-preview').attr('src', event.target.result);
                    // Mostra o popup
                    $('#popup-working').fadeIn(300);
                };
                reader.readAsDataURL(currentFile);

                // Aqui você faria o envio real para o servidor
                console.log('Enviando arquivo:', currentFile.name);
                
                // Exemplo de FormData para envio
                var formData = new FormData();
                formData.append('image', currentFile);
                
                // AJAX aqui
                // $.ajax({
                //     url: 'sua-url-de-upload',
                //     type: 'POST',
                //     data: formData,
                //     processData: false,
                //     contentType: false,
                //     success: function(response) {
                //         console.log('Upload concluído', response);
                //     }
                // });
            });
        });
    };
});