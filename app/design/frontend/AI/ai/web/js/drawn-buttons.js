require(['jquery'], function($) {
    $(document).on('click', '.option-btn', function() {
        let resposta = $(this).data('role');
        console.log('Resposta escolhida:', resposta);
        // aqui você faz o que precisar (enviar pra API, salvar, etc.)
    });
});
