jQuery(document).ready(function($) {
    // 每分钟更新一次名言
    setInterval(function() {
        $('.daily-quote-container').each(function() {
            var container = $(this);
            
            // 发送 AJAX 请求获取新名言
            $.ajax({
                url: dailyQuotesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_daily_quote',
                    nonce: dailyQuotesAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        container.html(response.data);
                    }
                }
            });
        });
    }, 60000); // 60000毫秒 = 1分钟
}); 