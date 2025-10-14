define([
    'jquery',
    'jquery/ui',
    'mage/cookies'
], function ($) {
    'use strict';

    $.widget('store.pickup', {
        _create: function () {
            var self = this;
            
            // Load saved store from sessionStorage
            var savedStore = sessionStorage.getItem('selectedStore');
            if (savedStore) {
                $('.store-radio[value="' + savedStore + '"]').prop('checked', true);
            }

            // Handle store selection
            $('.store-radio').on('change', function() {
                var storeId = $(this).val();
                var storeData = $(this).data('store');
                
                // Save to sessionStorage
                sessionStorage.setItem('selectedStore', storeId);
                
                // You can also save to cookie if needed for server-side use
                $.cookie('selected_store', storeId, { path: '/' });
                
                // Optional: Show feedback to user
                self.showStoreSelected(storeData.name);
            });
        },
        
        showStoreSelected: function(storeName) {
            // Optional: Add visual feedback when a store is selected
            var message = $('<div class="message success">' + 
                           '<div>Loja selecionada: ' + storeName + '</div>' +
                           '</div>');
            
            $('.store-pickup-widget').prepend(message);
            $(document).trigger('storePickupChanged');
            
            // Remove message after 3 seconds
            setTimeout(function() {
                message.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });

    return $.store.pickup;
});
