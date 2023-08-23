define(['jquery', 'Magento_Catalog/js/price-utils'],
    function ($, priceUtils) {
        'use strict';
        return function (config) {
            $(document).ready(function () {
                $.ajax({
                    url: config.url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        const balance = $('#balance');
                        balance.html(priceUtils.formatPrice(response.giftCardBalance));
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
        };
    });