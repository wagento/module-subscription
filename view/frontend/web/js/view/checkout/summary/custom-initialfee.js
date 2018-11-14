/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, Component, quote, totals, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Wagento_Subscription/checkout/summary/custom-initialfee'
            },
            totals: quote.getTotals(),
            isDisplayedInitialFee: function () {
                return true;
            },
            getInitialFee: function () {
                var price = totals.getSegment('initialfee').value;
                return this.getFormattedPrice(price);
            }
        });
    }
);