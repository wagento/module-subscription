/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'mage/storage',
        'mage/translate',
        'Wagento_Subscription/js/model/subscription-popup',
        'Magento_Customer/js/customer-data'
    ],
    function (
        ko,
        $,
        storage,
        $t,
        subscription,
        customerData
    ) {
        'use strict';
        return function (subscriptionData) {
            var parseData = ko.toJSON(subscriptionData);
            var sentData = JSON.stringify(parseData);
            $('body').trigger('processStart');
            return storage.post(
                'subscription/ajax/subscribe',
                sentData,
                false
            ).done(
                function (response) {
                    alert(response.message);
                    subscription.closeModal();
                    $('body').trigger('processStop');
                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                    window.location.reload(true);
                }
            ).fail(
                function (error) {
                }
            );
        };
    }
);
