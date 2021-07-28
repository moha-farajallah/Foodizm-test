/*jshint browser:true, jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    "jquery/ui",
    "mage/translate"
], function($, confirm){
    "use strict";

    $.widget('gmo.deleteCard', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this card?')
        },

        /**
         * Bind event handlers for deleting cards.
         * @private
         */
        _create: function() {
            var options         = this.options,
                deleteCard   = options.deleteCard;

            if( deleteCard ){
                $(document).on('click', deleteCard, this._deleteCard.bind(this));
            }
        },

        /**
         * Delete the card whose id is specified in a data attribute after confirmation from the user.
         * @private
         * @param {Event}
         * @return {Boolean}
         */
        _deleteCard: function(e) {
            var self = this;

            confirm({
                content: this.options.deleteConfirmMessage,
                actions: {
                    confirm: function() {
                        if (typeof $(e.target).parent().data('card') !== 'undefined') {
                            window.location = self.options.deleteUrlPrefix + $(e.target).parent().data('card')
                                + '/form_key/' + $.mage.cookies.get('form_key');
                        }
                        else {
                            window.location = self.options.deleteUrlPrefix + $(e.target).data('card')
                                + '/form_key/' + $.mage.cookies.get('form_key');
                        }
                    }
                }
            });

            return false;
        }
    });

    return $.mage.deleteCard;
});