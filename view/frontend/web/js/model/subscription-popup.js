/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',

], function ($, modal) {
    'use strict';

    return {
        modalWindow: null,

        /**
         * Create popUp window for provided element
         *
         * @param {HTMLElement} element
         */
        createPopUp: function (element) {
            var options = {
                'type': 'popup',
                'responsive': true,
                'innerScroll': true,
                'trigger': '.subscriptionPopup',
                'modalClass': 'subscription-popup',
                'size': 'sm',
                buttons: []
            };
            this.modalWindow = element;
            modal(options, $(this.modalWindow));

            jQuery(this.modalWindow).on('modalclosed', function() {
                jQuery('#subscriptionPopup').attr('checked', false);
            });

            $('.subscriptionPopup').on('change', function () {
                var links = jQuery('#product-options-wrapper').length;
                if (links == 1) {
                    $('#product-options-wrapper').appendTo('#product-options-wrapper-popup');
                }
            });

            $('.action-close').on('click', function () {
                var links = jQuery('#product-options-wrapper').length;
                if (links == 1) {
                    $('#product-options-wrapper').appendTo('.product-add-form');
                }
            });
        },

        /** Close subscription popup window*/
        closeModal: function () {
            $(this.modalWindow).modal('closeModal');
        }

    };
});

