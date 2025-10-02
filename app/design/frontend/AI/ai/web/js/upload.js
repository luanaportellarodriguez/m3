define(['jquery'], function ($) {
    'use strict';

    return function () {
        $(document).ready(function () {
            $('.upload-btn').on('click', function () {
                $('#upload-input').trigger('click');
            });

            $('#upload-input').on('change', function () {
                var file = this.files[0];
                if (!file) return;

                if (!['image/png','image/jpeg'].includes(file.type)) {
                    alert('Apenas PNG e JPG');
                    $(this).val('');
                    return;
                }
                alert('Arquivo válido: ' + file.name);
            });
        });
    };
});
