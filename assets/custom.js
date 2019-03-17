function wp_coinero_prepare_download_captcha(container_id) {
    if (typeof CoinHive === 'undefined' || typeof CoinHive.Captcha === 'undefined') {
        jQuery('#' + container_id + ' .loading-wrapper').hide();
        jQuery('#' + container_id + ' .script-blocker-modal').show();
        return;
    }

    let captcha_div = document.getElementById(container_id + '-captcha');
    let callback_id = 'coinero-download-callback-' + container_id;
    window[callback_id] = function (token) {
        wp_coinero_download_captcha_callback(token, container_id);
    };

    captcha_div.dataset.callback = callback_id;
    let captcha = new CoinHive.Captcha(captcha_div);
}

function wp_coinero_download_captcha_callback(token, container_id) {
    let container = jQuery('#' + container_id);
    container.find('.loading-wrapper').css('display', 'none');
    container.find('.loading-finished-wrapper').css('display', 'block');

    let downloadLink = container.find('.start-download');
    let link = downloadLink.attr('href');
    link = link.replace('{{token}}', token);
    downloadLink.attr('href', link);
}

jQuery(function(jQuery){
    otrkeyTable = jQuery('.coinero-downloads-list table').dataTable({
        "ordering": true,
        "order": [[0, 'asc']],
        "responsive": true,
        "autoWidth": true,
        "info": false,
        "jQueryUI": false,
        "pageLength": -1,
        "paging": false,
        "language": {
            "search": "Filter:",
            "zeroRecords": 'Missing a country or region? <a href="/missing-a-country-or-region/">Contact Me</a>'
        },
        "search": {
            "search": getUrlParameter('q')
        },
        "processing": false,
    });

    // Thanks: https://stackoverflow.com/a/21903119/1165132
    function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
        return '';
    }
});