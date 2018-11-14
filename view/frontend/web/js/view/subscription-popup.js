/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'uiComponent',
    'Wagento_Subscription/js/action/subscribe',
    'Wagento_Subscription/js/action/unsubscribe',
    'Wagento_Subscription/js/model/subscription-popup',
    'mage/validation'
], function (ko, $, Component, subscribeAction, unsubscribeAction, subscriptionPopup) {
    'use strict';

    return Component.extend({
        defaults: {
            //template: 'Wagento_Subscription/subscription-popup',
            subscriptionName: '',
            subscriptionFrequency: '',
            subscriptionDiscount: '',
            subscriptionFee: '',
            subscriptionId: '',
            subscriptionProductId: '',
            subcostWithoutCurrency: '',
            subdiscountWithoutCurrency: '',
            subqty: '',
            subHowMany: '',
            subSubscription: false,
            subIsEnableHowMany: '',
            howMany: '',
            howManyUnit: '',
            links: ''
        },
        initialize: function () {
            this._super();
            this.subscriptionName = ko.observable(this.subsname);
            this.subscriptionFrequency = ko.observable(this.frequency);
            this.subscriptionDiscount = ko.observable(this.discount);
            this.subscriptionFee = ko.observable(this.cost);
            this.ubscriptionId = ko.observable(this.subscriptionId);
            this.subscriptionProductId = ko.observable(this.productId);
            this.subcostWithoutCurrency = ko.observable(this.costWithoutCurrency);
            this.subdiscountWithoutCurrency = ko.observable(this.discountWithoutCurrency);
            this.subqty = ko.observable('');
            this.subHowMany = ko.observable('');
            this.subSubscription = ko.observable(this.subSubscription);
            this.subIsEnableHowMany = ko.observable(this.isEnableHowMany);
            this.subhowMany = ko.observable(this.howMany);
            this.subhowManyUnit = ko.observable(this.howManyUnit);
            this.links = ko.observable(this.links);
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
            subscriptionPopup.createPopUp(element);
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
            if (!this.validateForm('#subscription-form')) {
                return;
            }
            var self = this;
            var array_values = [];
            var susbribeData = {},
                SubformDataArray = $('#subscription-form').serializeArray();
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
            if (!this.validateForm('#subscription-form')) {
                return;
            }
            var self = this;
            var susbribeData = {};
            event.stopPropagation;
            unsubscribeAction(subscribeForm).always(function () {
            });
        },

        setSub: function () {
            $('#subscriptionPopup').change(function () {
                return this.checked;
            });
        }
    });
});

