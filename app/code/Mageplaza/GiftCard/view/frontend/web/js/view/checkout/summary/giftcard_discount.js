define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals'
    ],
    function ($, Component, totals) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Giftcard/checkout/summary/giftcard_discount'
            },
            isDisplayedCustomdiscount: function () {
                return totals.getSegment('giftcard_discount').value > 0 ? true : false;
                // return false;
            },
            getCustomDiscount: function () {
                return '-' + this.getFormattedPrice(
                    totals.getSegment('giftcard_discount').value
                );
            },
            getTitle: function () {
                return totals.getSegment('giftcard_discount').title;
            }
        });
    }
);