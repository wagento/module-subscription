/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'Wagento_Subscription/js/model/subscription-popup'
    ],
    function (
        $,
        modal,
        subscription
    ) {
        'use strict';


        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: $.mage.__('Continue'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        $("#product_subscription").on('click', function () {
            subscription.showModal();
        });

        $('.catalog-product-view').on('click', function (event) {
            var checked = $('#subscriptionPopup').is(':checked');
            if ($('.subscription-popup').hasClass('_show')) {
                $('#subscriptionPopup').prop('checked', checked);
            } else {
                var target = $(event.target);
                if (target.is("input#subscriptionPopup")
                    || target.is("button.action-close")) {
                    checked = (checked) ? false : true;
                    $('#subscriptionPopup').prop('checked', checked);
                }
            }
        });
        $(".checkout-cart-index").on('click', function (event) {
            var target = $(event.target);
            if (target.is("button.action-close")) {
                var t = target.closest(".modal-inner-wrap").find("#subscription_item");
                var itemId = t.val();
                $("#subscriptionPopup" + itemId).prop('checked', false);
            }
        });
    }
);