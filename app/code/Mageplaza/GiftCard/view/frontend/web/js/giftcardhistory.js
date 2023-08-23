define(['jquery', 'Magento_Catalog/js/price-utils', 'moment'],
    function ($, priceUtils, moment) {
        'use strict';
        return function (config) {
            $(document).ready(function () {
                function formatPrice(price) {
                    return priceUtils.formatPrice(price, config.priceFormat);
                }

                $.ajax({
                    url: config.url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        $('#balance').html(config.currencySymbol + response.giftCardBalance);
                        const historyTable = $('#history');
                        if (response.giftCardHistory.length > 0) {
                            $.each(response.giftCardHistory, function (index, item) {
                                historyTable.append(
                                    '<tr>' +
                                    '<td>' + moment(item.action_time).format('DD/MM/YY') + '</td>' +
                                    '<td>' + item.code + '</td>' +
                                    '<td>' + config.currencySymbol + item.amount + '</td>' +
                                    '<td>' + item.action + '</td>' +
                                    '<tr>'
                                );
                            });
                        } else {
                            historyTable.append(
                                '<tr>' +
                                '<td colspan="4" style="text-align:center">' +
                                '<div class="message info empty">' +
                                '<span>You have not gift card</span>' +
                                '</div>' +
                                '</td>' +
                                '<tr>'
                            );
                        }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
        };
    });
