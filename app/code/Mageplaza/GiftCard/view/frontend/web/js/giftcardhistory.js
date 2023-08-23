define(['jquery'],
    function ($) {
        'use strict';
        return function (config) {
            $(document).ready(function () {

                $.ajax({
                    url: config.url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        $('#balance').html(response.giftCardBalance);
                        const historyTable = $('#history');
                        if (response.giftCardHistory.length > 0) {
                            $.each(response.giftCardHistory, function (index, item) {
                                historyTable.append(
                                    '<tr>' +
                                    '<td>' + item.action_time + '</td>' +
                                    '<td>' + item.code + '</td>' +
                                    '<td>' + item.amount + '</td>' +
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
