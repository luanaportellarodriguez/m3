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

                // Validação de tamanho (opcional - 5MB)
                var maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('O arquivo deve ter no máximo 5MB');
                    $(this).val('');
                    return;
                }

                // Armazena o arquivo atual
                currentFile = file;

                // Cria preview da imagem
                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#thumbnail-preview').attr('src', event.target.result);
                    $('#thumbnail-container').fadeIn(300);
                };
                reader.readAsDataURL(file);
            });

            // Botão de deletar miniatura
            $(document).on('click', '.delete-thumbnail', function () {
                // Remove o preview
                $('#thumbnail-container').fadeOut(0);
                $('#thumbnail-preview').attr('src', '');
                
                // Limpa o input file
                $('#upload-input').val('');
                
                // Limpa o arquivo armazenado
                currentFile = null;
            });

            // Exemplo de uso do botão enviar
            $('.send-btn').on('click', function () {
                if (!currentFile) {
                    alert('Por favor, selecione uma imagem primeiro');
                    return;
                }

                // Aqui você pode adicionar a lógica de envio
                console.log('Enviando arquivo:', currentFile.name);
                
                // Exemplo de como criar um FormData para envio
                var formData = new FormData();
                formData.append('image', currentFile);
                
                // Aqui você faria o AJAX para enviar para o servidor
                // $.ajax({ ... });
                
                alert('Pronto para enviar: ' + currentFile.name);
            });
        });
    };
});