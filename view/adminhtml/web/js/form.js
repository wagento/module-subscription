/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery'], function ($) {
    var subscriptionForm = {
        update: function () {
            var action = $("select[name='product[subscription_configurate]']").val();

            switch (action) {
                case 'subscription_only':
                    this.showFields('div[data-index="subscription_attribute_product"]');
                    break;
                case 'optional':
                    this.showFields('div[data-index="subscription_attribute_product"]');
                    break;
                case 'no':
                    this.hideFields('div[data-index="subscription_attribute_product"]');
                    break;
            }
        },

        hideFields: function (names) {
            console.log($(names));
            $(names).toggle(false);
        },

        showFields: function (names) {
            console.log($(names));
            $(names).toggle(true);
        }
    };

    return subscriptionForm;
});
