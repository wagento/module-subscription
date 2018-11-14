/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'uiComponent',
    'Wagento_Subscription/js/action/subscribe-cart',
    'Wagento_Subscription/js/action/unsubscribe-cart',
    'Wagento_Subscription/js/model/subscription-popup-cart',
    'mage/validation'
], function (ko, $, Component, subscribeAction, unsubscribeAction, subscriptionPopup) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Wagento_Subscription/subscription-popup-cart',
            subscriptionName: '',
            subscriptionFrequency: '',
            subscriptionDiscount: '',
            subscriptionFee: '',
            subscriptionId: '',
            subscriptionProductId: '',
            subcostWithoutCurrency: '',
            subdiscountWithoutCurrency: '',
            subItemId: '',
            subqty: '',
            subHowMany: '',
            subIsEnableHowMany: '',
            howMany: '',
            howManyUnit: '',
            linksPurchasedSeparately: '',
            isSaleable: '',
            hasLinks: '',
            linksTitle: '',
            isRequired: '',
            links: '',
        },
        initialize: function () {
            this._super();
            this.subscriptionName = ko.observable(this.subsname);
            this.subscriptionFrequency = ko.observable(this.frequency);
            this.subscriptionDiscount = ko.observable(this.discount);
            this.subscriptionFee = ko.observable(this.cost);
            this.subscriptionId = ko.observable(this.subscriptionId);
            this.subscriptionProductId = ko.observable(this.productId);
            this.subcostWithoutCurrency = ko.observable(this.costWithoutCurrency);
            this.subdiscountWithoutCurrency = ko.observable(this.discountWithoutCurrency);
            this.subItemId = ko.observable(this.itemId);
            this.subqty = ko.observable('');
            this.subHowMany = ko.observable('');
            this.subIsEnableHowMany = ko.observable(this.isEnableHowMany);
            this.subhowMany = ko.observable(this.howMany);
            this.subhowManyUnit = ko.observable(this.howManyUnit);
            this.sublinksPurchasedSeparately = ko.observable(this.linksPurchasedSeparately);
            this.subIsSalable = ko.observable(this.isSalable);
            this.subHaslinks = ko.observable(this.hasLinks);
            this.subLinkTitle = ko.observable(this.linksTitle);
            this.subLinkRequired = ko.observable(this.isRequired);
            this.sublinks = ko.observable(this.links);
        },

        /**
         *
         *
         * @param {Object} element
         */
        showContent: function () {
            $(this.modalWindow).modal('openModal');
        },

        /**
         * Init modal window for rendered element
         *
         * @param {Object} element
         */
        setModalElement: function (element) {
            var subItemId = this.subItemId._latestValue;
            subscriptionPopup.createPopUp(element, subItemId);
        },


        validateForm: function (subscribeForm) {
            return $(subscribeForm).validation() && $(subscribeForm).validation('isValid');
        },

        /**
         * Provide subscribe action
         *
         * @param formUiElement
         * @param event
         * @returns {boolean}
         */
        subscribe: function (subscribeForm, event) {
            var id = subscribeForm.itemId;
            if (!this.validateForm('#subscription-form-' + id)) {
                return;
            }
            var self = this;
            var susbribeData = {}, SubformDataArray = $('#subscription-form-' + id).serializeArray();
            event.stopPropagation;
            var array_values = [];

            $.each($("input[name='links[]']:checked"), function () {
                array_values.push($(this).val());
            });

            event.stopPropagation;
            SubformDataArray.forEach(function (entry) {
                if (entry.name === 'links[]') {
                    return;
                }
                susbribeData[entry.name] = entry.value;
            });
            susbribeData['links'] = array_values;
            subscribeAction(susbribeData).always(function () {
            });
        },

        unsubscribe: function (subscribeForm, event) {
            var id = subscribeForm.itemId;

            if (!this.validateForm('#unsubscription-form-' + id)) {
                return;
            }
            var self = this;
            var susbribeData = {}, SubformDataArray = $('#unsubscription-form-' + id).serializeArray();
            event.stopPropagation;
            unsubscribeAction(subscribeForm).always(function () {
            });
        },
    });
});

