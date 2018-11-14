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
        'Wagento_Subscription/js/model/subscription-popup'
    ],
    function (
        ko,
        $,
        storage,
        $t,
        subscription
    ) {
        'use strict';
        return function (subscriptionData) {
            var parseData = ko.toJSON(subscriptionData);
            var sentData = JSON.stringify(parseData);
            return storage.post(
                'subscription/ajax/subscribecart',
                sentData,
                false
            ).done(
                function (response) {
                    if (response.status == 'success') {
                        alert(response.message);
                        subscription.closeModal();
                        window.location.reload(true);
                    } else {
                        alert(response.message);
                        subscription.closeModal();
                        window.location.reload(true);
                    }
                }
            ).fail(
                function (error) {
                }
            );
        };
    }
);
