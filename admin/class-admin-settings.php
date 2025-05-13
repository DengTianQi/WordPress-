<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 管理设置类
 */
class Daily_Quotes_Admin_Settings {
    /**
     * 构造函数
     */
    public function __construct() {
        // 移除原来的菜单注册
        // add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 添加到设置菜单
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_settings_page() {
        // 添加到设置菜单下
        add_options_page(
            '名言设置', // 页面标题
            '名言设置', // 菜单标题
            'manage_options', // 权限
            'daily-quotes-settings', // 菜单slug
            array($this, 'render_settings_page') // 回调函数
        );
    }

    /**
     * 注册设置
     */
    public function register_settings() {
        // 注册设置
        register_setting(
            'daily_quotes_settings', // 选项组
            'daily_quotes_options', // 选项名
            array($this, 'sanitize_options') // 清理函数
        );

        // 添加设置部分
        add_settings_section(
            'daily_quotes_general', // 部分ID
            '基本设置', // 部分标题
            array($this, 'render_section_general'), // 回调函数
            'daily-quotes-settings' // 页面slug
        );

        // 添加设置字段
        add_settings_field(
            'cache_hours', // 字段ID
            '缓存时间（小时）', // 字段标题
            array($this, 'render_cache_field'), // 回调函数
            'daily-quotes-settings', // 页面slug
            'daily_quotes_general' // 部分ID
        );

        add_settings_field(
            'default_type',
            '默认显示模式',
            array($this, 'render_type_field'),
            'daily-quotes-settings',
            'daily_quotes_general'
        );

        add_settings_field(
            'default_style',
            '默认样式',
            array($this, 'render_style_field'),
            'daily-quotes-settings',
            'daily_quotes_general'
        );
    }

    /**
     * 渲染设置页面
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>名言设置</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('daily_quotes_settings');
                do_settings_sections('daily-quotes-settings');
                submit_button('保存设置');
                ?>
            </form>

            <div class="daily-quotes-preview" style="margin-top: 30px;">
                <h2>预览</h2>
                <?php echo do_shortcode('[daily_quote]'); ?>
            </div>

            <div class="daily-quotes-shortcode-help" style="margin-top: 30px;">
                <h2>使用说明</h2>
                <p>在文章或页面中使用以下简码：</p>
                <code>[daily_quote]</code>
                <p>可选参数：</p>
                <ul>
                    <li><code>type="image"</code> - 图片模式</li>
                    <li><code>style="modern"</code> - 现代样式</li>
                    <li><code>style="classic"</code> - 经典样式</li>
                    <li><code>style="dark"</code> - 暗色样式</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * 渲染各个设置部分
     */
    public function render_section_general() {
        echo '<p>配置名言插件的基本设置。</p>';
    }

    /**
     * 渲染各个设置字段
     */
    public function render_cache_field() {
        $options = get_option('daily_quotes_options', array());
        $cache_hours = isset($options['cache_hours']) ? $options['cache_hours'] : 24;
        ?>
        <input type="number" 
               name="daily_quotes_options[cache_hours]" 
               value="<?php echo esc_attr($cache_hours); ?>" 
               min="1" 
               class="small-text" />
        <p class="description">设置名言缓存的时间（小时）</p>
        <?php
    }

    public function render_type_field() {
        $options = get_option('daily_quotes_options', array());
        $default_type = isset($options['default_type']) ? $options['default_type'] : 'text';
        ?>
        <select name="daily_quotes_options[default_type]">
            <option value="text" <?php selected($default_type, 'text'); ?>>文本模式</option>
            <option value="image" <?php selected($default_type, 'image'); ?>>图片模式</option>
        </select>
        <p class="description">选择默认的显示模式</p>
        <?php
    }

    public function render_style_field() {
        $options = get_option('daily_quotes_options', array());
        $default_style = isset($options['default_style']) ? $options['default_style'] : 'default';
        ?>
        <select name="daily_quotes_options[default_style]">
            <option value="default" <?php selected($default_style, 'default'); ?>>默认样式</option>
            <option value="modern" <?php selected($default_style, 'modern'); ?>>现代样式</option>
            <option value="classic" <?php selected($default_style, 'classic'); ?>>经典样式</option>
            <option value="dark" <?php selected($default_style, 'dark'); ?>>暗色样式</option>
        </select>
        <p class="description">选择名言的默认显示样式</p>
        <?php
    }

    /**
     * 加载管理界面资源
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'daily-quotes') === false) {
            return;
        }

        wp_enqueue_style(
            'daily-quotes-admin',
            DAILY_QUOTES_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            DAILY_QUOTES_VERSION
        );
    }

    // 添加选项清理函数
    public function sanitize_options($input) {
        $sanitized = array();
        
        if (isset($input['cache_hours'])) {
            $sanitized['cache_hours'] = absint($input['cache_hours']);
        }
        
        if (isset($input['default_type'])) {
            $sanitized['default_type'] = sanitize_text_field($input['default_type']);
        }
        
        if (isset($input['default_style'])) {
            $sanitized['default_style'] = sanitize_text_field($input['default_style']);
        }
        
        return $sanitized;
    }
} 