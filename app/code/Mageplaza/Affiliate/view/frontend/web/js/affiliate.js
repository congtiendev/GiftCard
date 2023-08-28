require(['jquery', 'Magento_Ui/js/modal/alert'], function ($, alert) {
    $(document).ready(function () {

        // Render affiliate history with ajax
        $(document).ready(function () {
            $.ajax({
                url: 'http://magento2.loc/affiliate/history/gethistory',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.account) {
                        $('#create-affiliate-account').remove();
                        $('#affiliate-dashboard').show();
                        $('#affiliate-balance').html(response.balance);
                        $('#refer-link').html(response.refer_link);

                        if (response.history.length > 0) {
                            const historyRows = response.history.map(function (item) {
                                return '<tr>' +
                                    '<td>' + item.order_id + '</td>' +
                                    '<td>' + item.order_increment_id + '</td>' +
                                    '<td>' + item.title + '</td>' +
                                    '<td>' + item.amount + '</td>' +
                                    '<td>' + item.status + '</td>' +
                                    '<td>' + item.created_at + '</td>' +
                                    '</tr>';
                            });
                            $('#affiliate-history-tbody').append(historyRows.join(''));
                        } else {
                            $('#affiliate-history-tbody').append(
                                '<tr>' +
                                '<td colspan="6" style="text-align:center">' +
                                '<div class="message info empty">' +
                                '<span>You have not affiliate history</span>' +
                                '</div>' +
                                '</td>' +
                                '</tr>'
                            );
                        }
                    } else {
                        $('#affiliate-dashboard').remove();
                        $('#create-affiliate-account').show();
                        $('#static-block').html(response.static_block);
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });


        // Copy refer link
        $('#refer-link').on('click', function () {
            const $this = $(this);
            const range = document.createRange();
            range.selectNode($this[0]);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            alert({
                title: 'Success',
                content: 'Link has been copied to clipboard: ' + '<a href=' + $this.text() + ' style="color: blue;">' + $this.text() + '</a>',
            });
        });

    });
});
