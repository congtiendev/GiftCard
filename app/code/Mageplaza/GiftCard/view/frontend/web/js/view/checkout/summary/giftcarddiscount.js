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
                template: 'Mageplaza_GiftCard/checkout/summary/giftcarddiscount'
            },
            isDisplayed: function () {
                return totals.getSegment('giftcard_discount').value > 0 ? true : false;
            },
            getDiscount: function () {
                return this.getFormattedPrice(
                    totals.getSegment('giftcard_discount').value
                );
            },
            getTitle: function () {
                return totals.getSegment('giftcard_discount').title;
            }
        });
    }
);