require(['jquery'], function($) {
    $(document).on('click', '.option-btn', function(e) {
        if ($(e.target).hasClass('status-icon')) {
            $(this).removeClass('select-option');
            return;
        }

        $('.option-btn').removeClass('select-option');
        $(this).addClass('select-option');
    });
});
