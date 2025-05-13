<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 主插件类
 */
class Daily_Quotes {
    private $api_url = 'https://api.xygeng.cn/one';

    /**
     * 初始化插件
     */
    public function init() {
        // 注册简码
        add_shortcode('daily_quote', array($this, 'render_quote'));
        
        // 加载管理类
        if (is_admin()) {
            require_once DAILY_QUOTES_PLUGIN_DIR . 'admin/class-admin-settings.php';
            new Daily_Quotes_Admin_Settings();
        }

        // 添加样式
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * 注册样式
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'daily-quotes-style',
            DAILY_QUOTES_PLUGIN_URL . 'assets/css/style.css',
            array(),
            DAILY_QUOTES_VERSION
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'daily-quotes-script',
            DAILY_QUOTES_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'),
            DAILY_QUOTES_VERSION,
            true
        );

        // 传递 AJAX URL 到 JavaScript
        wp_localize_script('daily-quotes-script', 'dailyQuotesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('daily_quotes_nonce')
        ));
    }

    /**
     * 渲染名言简码
     */
    public function render_quote($atts) {
        $quote = $this->fetch_quote();
        
        if (!$quote) {
            return '无法获取一言';
        }

        $html = '<div class="daily-quote-container">';
        $html .= '<div class="daily-quote-content">';
        $html .= esc_html($quote['content']);
        if (!empty($quote['author'])) {
            $html .= '<div class="daily-quote-author">—— ' . esc_html($quote['author']) . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * 从API获取名言
     */
    private function fetch_quote() {
        $response = wp_remote_get($this->api_url);
        
        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // 调试输出
        error_log('API Response: ' . print_r($data, true));

        if (empty($data) || !isset($data['data'])) {
            return false;
        }

        // 根据实际API返回结构处理数据
        $quote_data = $data['data'];
        return array(
            'content' => isset($quote_data['content']) ? $quote_data['content'] : 
                        (isset($quote_data['text']) ? $quote_data['text'] : ''),
            'author' => isset($quote_data['author']) ? $quote_data['author'] : 
                       (isset($quote_data['from']) ? $quote_data['from'] : '')
        );
    }

    /**
     * 格式化名言输出
     */
    private function format_quote($quote, $atts) {
        $style = esc_attr($atts['style']);
        $type = esc_attr($atts['type']);
        
        $html = '<div class="daily-quote ' . $style . ' type-' . $type . '">';
        
        if ($type === 'image' && !empty($quote['image'])) {
            $html .= '<div class="quote-image">';
            $html .= '<img src="' . esc_url($quote['image']) . '" alt="' . esc_attr($quote['content']) . '">';
            $html .= '</div>';
        }
        
        $html .= '<div class="quote-content">';
        $html .= '<blockquote>' . esc_html($quote['content']) . '</blockquote>';
        
        if (!empty($quote['author'])) {
            $html .= '<cite>— ' . esc_html($quote['author']) . '</cite>';
        }
        
        $html .= '</div>'; // 结束 quote-content
        $html .= '</div>'; // 结束 daily-quote
        
        return $html;
    }
} 