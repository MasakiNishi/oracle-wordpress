jQuery(document).ready(function($) {
    // リンク挿入ダイアログが開かれるたびに実行
    $(document).on('wplink-open', function() {
        // 新しいタブで開くオプションをチェック
        setTimeout(function() {
            $('#wp-link-target').prop('checked', true).change();
        }, 50);
    });
});
