<?php
/**
 * Plugin Name: Daily Quotes
 * Description: 一个可以生成和插入名言名句的WordPress插件
 * Version: 1.0.0
 * Author: DengWeather
 * Text Domain: daily-quotes
 * License: GPL v2 or later
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('DAILY_QUOTES_VERSION', '1.0.0');
define('DAILY_QUOTES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DAILY_QUOTES_PLUGIN_URL', plugin_dir_url(__FILE__));

// 加载主类文件
require_once DAILY_QUOTES_PLUGIN_DIR . 'includes/class-daily-quotes.php';

// 初始化插件
function daily_quotes_init() {
    $plugin = new Daily_Quotes();
    $plugin->init();
}
add_action('plugins_loaded', 'daily_quotes_init');

// 激活插件时的操作
register_activation_hook(__FILE__, 'daily_quotes_activate');
function daily_quotes_activate() {
    // 创建默认选项
    $default_options = array(
        'cache_hours' => 24,
        'default_style' => 'default',
        'default_type' => 'text',
        'show_author' => 1,
        'show_tags' => 1
    );
    add_option('daily_quotes_options', $default_options);
}