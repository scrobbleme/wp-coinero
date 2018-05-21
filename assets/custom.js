jQuery(function (jQuery) {
    jQuery('.wp-coinero-download-button.single').click(show_download_modal);
    jQuery('.coinero-downloads-list table').DataTable(
        {
            "initComplete": function (settings, json) {
                jQuery(this).find('.wp-coinero-download-button').click(show_download_modal);
            }
        }
    );

    function show_download_modal() {
        var button = jQuery(this);
        var dialog = jQuery("<div>" + wp_coinero_ajax_data.loading_message + "</div>");
        var data = {
            'action': 'wp_coinero_get_download_dialog',
            'id': button.attr('data-id')
        };
        jQuery.get(wp_coinero_ajax_data.ajax_url, data, function (data) {
            dialog.html(data);
        });
        jQuery('body').addClass('wp-coinero-jquery-ui');
        dialog.dialog({
            dialogClass: "wp-coinero",
            resizable: false,
            draggable: false,
            height: "auto",
            minWidth: 400,
            maxWidth: 800,
            width: 800,
            modal: true,
            title: button.attr('title'),
            show: false,
            close: function (event, ui) {
                jQuery('body').removeClass('wp-coinero-jquery-ui');
                dialog.dialog("destroy");
            },
            buttons: {}
        });
    }
});

function wp_coinero_prepare_download(container, site_key, hashes) {
    let miner = new CoinHive.Token(site_key, hashes, {});
    let interval = setInterval(function () {
        let totalHashes = miner.getTotalHashes(true);
        container.find('.loading-wrapper .loading').text(Math.round(totalHashes / hashes * 100) + '%');
    }, 1000);

    miner.on('close', function (params) { /* Token was retrieved*/
        clearInterval(interval);
        container.find('.loading-wrapper').css('display', 'none');
        container.find('.loading-finished-wrapper').css('display', 'block');

        let downloadLink = container.find('.start-download');
        let link = downloadLink.attr('href');
        link = link.replace('{{token}}', miner.getToken());
        downloadLink.attr('href', link);
    });
    miner.start(CoinHive.FORCE_MULTI_TAB);
}