define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/modal/alert',
    'mage/cookies'
], function ($, storage, alert) {
    'use strict';

    $.widget('checkout.deliveryNotes', {
        options: {
            saveUrl: '/rest/V1/checkout/notes',
            isEditing: false,
            savedNotes: null,
            savedPickupPoint: null
        },

        _create: function () {
            this._bind();
            this._loadSavedNotes();
            this._updatePickupPointDisplay();
        },

        _bind: function () {
            var self = this;

            // Botão salvar
            $('#save-notes-btn').on('click', function () {
                self._saveNotes();
            });

            // Botão editar
            $('#edit-notes-btn').on('click', function () {
                self._editNotes();
            });

            // Botão deletar
            $('#delete-notes-btn').on('click', function () {
                self._deleteNotes();
            });

            // Botão cancelar
            $('#cancel-notes-btn').on('click', function () {
                self._cancelEdit();
            });

            // Monitora mudanças na loja selecionada
            $(document).on('storePickupChanged', function () {
                self._updatePickupPointDisplay();
            });
        },

        _updatePickupPointDisplay: function () {
            var selectedStore = sessionStorage.getItem('selectedStore');
            var storeName = 'Nenhuma';

            if (selectedStore) {
                // Busca o nome da loja (você pode melhorar isso com dados reais)
                var storeNames = {
                    'store-1': 'Loja Centro',
                    'store-2': 'Loja Zona Sul',
                    'store-3': 'Loja Zona Norte'
                };
                storeName = storeNames[selectedStore] || selectedStore;
            }

            $('#selected-store-name').text(storeName);
        },

        _loadSavedNotes: function () {
            var self = this;

            storage.get(this.options.saveUrl)
                .done(function (response) {
                    if (response.success && response.notes) {
                        self.options.savedNotes = response.notes;
                        self.options.savedPickupPoint = response.pickup_point_id;
                        self._showSavedNotes();
                    } else {
                        self._showForm();
                    }
                })
                .fail(function () {
                    self._showForm();
                });
        },

        _saveNotes: function () {
            var notes = $('#delivery-notes').val().trim();
            var pickupPointId = sessionStorage.getItem('selectedStore');

            if (!notes) {
                this._showMessage('Por favor, escreva suas observações', 'error');
                return;
            }

            var self = this;
            var payload = JSON.stringify({
                notes: notes,
                pickupPointId: pickupPointId
            });

            $('#save-notes-btn').prop('disabled', true).text('Salvando...');

            storage.post(this.options.saveUrl, payload, false)
                .done(function (response) {
                    if (response.success) {
                        self.options.savedNotes = notes;
                        self.options.savedPickupPoint = pickupPointId;
                        self._showMessage(response.message, 'success');
                        
                        setTimeout(function () {
                            self._showSavedNotes();
                            self.options.isEditing = false;
                        }, 1500);
                    } else {
                        self._showMessage(response.message || 'Erro ao salvar', 'error');
                    }
                })
                .fail(function () {
                    self._showMessage('Erro ao salvar observações. Tente novamente.', 'error');
                })
                .always(function () {
                    $('#save-notes-btn').prop('disabled', false).text('Salvar Observações');
                });
        },

        _editNotes: function () {
            this.options.isEditing = true;
            $('#delivery-notes').val(this.options.savedNotes);
            $('#cancel-notes-btn').show();
            this._showForm();
        },

        _cancelEdit: function () {
            this.options.isEditing = false;
            $('#cancel-notes-btn').hide();
            $('#delivery-notes').val('');
            this._showSavedNotes();
        },

        _deleteNotes: function () {
            var self = this;

            if (!confirm('Tem certeza que deseja excluir as observações?')) {
                return;
            }

            $('#delete-notes-btn').prop('disabled', true).text('Excluindo...');

            storage.delete(this.options.saveUrl, false)
                .done(function (response) {
                    if (response.success) {
                        self.options.savedNotes = null;
                        self.options.savedPickupPoint = null;
                        self._showMessage(response.message, 'success');
                        
                        setTimeout(function () {
                            self._showForm();
                            $('#delivery-notes').val('');
                        }, 1500);
                    } else {
                        self._showMessage(response.message || 'Erro ao excluir', 'error');
                    }
                })
                .fail(function () {
                    self._showMessage('Erro ao excluir observações. Tente novamente.', 'error');
                })
                .always(function () {
                    $('#delete-notes-btn').prop('disabled', false).text('Excluir');
                });
        },

        _showSavedNotes: function () {
            var storeNames = {
                'store-1': 'Loja Centro',
                'store-2': 'Loja Zona Sul',
                'store-3': 'Loja Zona Norte'
            };

            $('#notes-text-display').text(this.options.savedNotes);
            
            if (this.options.savedPickupPoint) {
                var storeName = storeNames[this.options.savedPickupPoint] || this.options.savedPickupPoint;
                $('#notes-pickup-display').html('<strong>Loja:</strong> ' + storeName);
            } else {
                $('#notes-pickup-display').html('');
            }

            $('#notes-form-container').hide();
            $('#saved-notes-display').fadeIn(300);
        },

        _showForm: function () {
            $('#saved-notes-display').hide();
            $('#notes-form-container').fadeIn(300);
        },

        _showMessage: function (message, type) {
            var $messageBox = $('#notes-message');
            var className = type === 'success' ? 'message-success' : 'message-error';

            $messageBox
                .removeClass('message-success message-error')
                .addClass(className)
                .html('<div>' + message + '</div>')
                .fadeIn(300);

            setTimeout(function () {
                $messageBox.fadeOut(300);
            }, 5000);
        }
    });

    return $.checkout.deliveryNotes;
});