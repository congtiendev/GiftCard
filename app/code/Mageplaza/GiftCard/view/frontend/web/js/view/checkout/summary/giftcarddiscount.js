// File: Mageplaza/GiftCard/view/frontend/web/js/view/checkout/summary/giftcarddiscount.js
define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_GiftCard/checkout/summary/giftcarddiscount'
        },
        isDisplayedGiftCardDiscount: function () {
            if (totals.getSegment('giftcard_discount')) {
                return totals.getSegment('giftcard_discount').value > 0;
            }
            return false;
        },

        getTitle: function () {
            return totals.getSegment('giftcard_discount').title;
        },

        getDiscount: function () {
            let discount = 0;
            if (totals.getSegment('giftcard_discount')) {
                discount = totals.getSegment('giftcard_discount').value;
            }
            return priceUtils.formatPrice(discount, quote.getPriceFormat());
        },
    });
});
