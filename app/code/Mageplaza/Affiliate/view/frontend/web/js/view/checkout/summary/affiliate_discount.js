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
                template: 'Mageplaza_Affiliate/checkout/summary/affiliate_discount'
            },
            isDisplayed: function () {
                return totals.getSegment('affiliate_discount').value > 0 ? true : false;
            },
            getDiscount: function () {
                return this.getFormattedPrice(
                    totals.getSegment('affiliate_discount').value
                );
            },
            getTitle: function () {
                return totals.getSegment('affiliate_discount').title;
            }
        });
    }
);