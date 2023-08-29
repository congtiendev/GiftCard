define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_Affiliate/checkout/summary/affiliate_discount'
        },
        isDisplayed: function () {
            if (totals.getSegment('affiliate_discount')) {
                return totals.getSegment('affiliate_discount').value > 0;
            }
            return false;
        },

        getTitle: function () {
            return totals.getSegment('affiliate_discount').title;
        },

        getDiscount: function () {
            let discount = 0;
            if (totals.getSegment('affiliate_discount')) {
                discount = totals.getSegment('affiliate_discount').value;
            }
            return priceUtils.formatPrice(discount, quote.getPriceFormat());
        },
    });
});
