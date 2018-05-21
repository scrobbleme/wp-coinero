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