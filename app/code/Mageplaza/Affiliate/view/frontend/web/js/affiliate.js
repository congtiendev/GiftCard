require(['jquery', 'Magento_Ui/js/modal/alert'], function ($, alert) {
    $(document).ready(function () {

        const renderAffiliateHistory = function () {
            $.ajax({
                url: 'http://magento2.loc/affiliate/history/gethistory',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.referenced_by === null || response.referenced_by === "") {
                        $('#referent-by').remove();
                    } else {
                        $('#referent-by').show();
                        $('#referent-by-name').html(response.referenced_by);
                    }
                    if (response.account) {
                        $('#create-affiliate-account').remove();
                        if (response.account_status == 1) {
                            $('#affiliate-dashboard').show();
                            $('#affiliate-balance').html(response.balance);
                            $('#refer-link').data('link', response.refer_link);
                            $('#refer-code').data('code', response.refer_code);

                            const historyRows = response.history.map(function (item) {
                                return '<tr>' +
                                    '<td>' + item.order_increment_id + '</td>' +
                                    '<td>' + item.title + '</td>' +
                                    '<td>' + item.amount + '</td>' +
                                    '<td>' + item.status + '</td>' +
                                    '<td>' + item.created_at + '</td>' +
                                    '</tr>';
                            });

                            if (historyRows.length > 0) {
                                $('#affiliate-history-tbody').append(historyRows.join(''));
                            } else {
                                $('#affiliate-history-tbody').html(
                                    '<tr>' +
                                    '<td colspan="5" style="text-align:center">' +
                                    '<div class="message info empty">' +
                                    '<span>You have not affiliate history</span>' +
                                    '</div>' +
                                    '</td>' +
                                    '</tr>'
                                );
                            }
                        } else {
                            $('#affiliate-dashboard').remove();
                            $('#not-active-account').show();
                        }
                    } else {
                        $('#affiliate-dashboard').remove();
                        $('#create-affiliate-account').show();
                        $('#affiliate-history-tbody').html('');
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        };
        renderAffiliateHistory();

        function copyToClipboard(data) {
            const tempInput = document.createElement('input');
            tempInput.style = 'position: absolute; left: -1000px; top: -1000px';
            tempInput.value = data;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
        }

        $('#refer-link').on('click', function () {
            const $this = $(this);
            const linkToCopy = $this.data('link');
            copyToClipboard(linkToCopy);
            alert({
                title: 'Success',
                content: 'Link has been copied to clipboard: ' + '<a href="' + linkToCopy + '" style="color: blue;">' + linkToCopy + '</a>',
            });
        });

        $('#refer-code').on('click', function () {
            const $this = $(this);
            const codeToCopy = $this.data('code');
            copyToClipboard(codeToCopy);
            alert({
                title: 'Success',
                content: 'Code has been copied to clipboard: ' + codeToCopy,
            });
        });

    });
});