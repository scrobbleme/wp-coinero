<?php

abstract class Coinero_Widget extends Coinero_CustomizerSupport
{
    function __construct($slug, $name)
    {
        parent::__construct($slug, $name);

        add_filter('wp-coinero-render-attributes-' . $this->slug, [$this, 'filter_render_atts'], 10, 1);
        add_filter('wp-coinero-render-content-' . $this->slug, [$this, 'filter_render_content'], 10, 1);

        add_action('wp-coinero-add-customizer-settings-' . $this->slug, [$this, 'add_widget_customizer_settings'], 0, 3);

        add_shortcode($slug, [$this, 'render_shortcode']);
    }

    function render_shortcode($atts = array(), $content = '')
    {
        return $this->render($atts, $content);
    }

    function render($atts = array(), $content = '', $force_render = false)
    {
        if (!$force_render && !$this->get_option('enabled')) {
            return '';
        }

        $atts = apply_filters('wp-coinero-render-attributes-' . $this->slug, $atts);
        $content = do_shortcode($content);
        $content = apply_filters('wp-coinero-render-content-' . $this->slug, $content);

        $classes = isset($atts['classes']) ? esc_attr($atts['classes']) : '';
        if (isset($atts['debug']) && $atts['debug']) {
            $classes = $classes . ' debug';
        }

        $script_prefix = $this->get_option('use-authedmine', true) ? 'authedmine' : 'coinhive';

        ob_start();
        echo '<div class="coinero-widget ' . $this->slug . ' ' . $classes . '">';
        $this->render_content($atts, $content, $script_prefix);
        echo '</div>';
        return ob_get_clean();
    }

    function filter_render_content($content = '')
    {
        return $content;
    }

    function filter_render_atts($atts = array())
    {
        return $atts;
    }

    /**
     * @param $wp_customize WP_Customize_Manager
     */
    function add_widget_customizer_settings($wp_customize, $section_id, $settings_prefix)
    {
        // Enabled control
        $wp_customize->add_setting($this->settings_prefix . 'enabled', array(
            'default' => '1',
            'capability' => 'edit_theme_options',
            'type' => 'option',
            'sanitize_callback' => 'absint',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'enabled-control', [
                'label' => __('Enable shortcode', 'wp-coinero'),
                'section' => $section_id,
                'settings' => $settings_prefix . 'enabled',
                'type' => 'checkbox'
            ]
        );
    }

    abstract function render_content($atts, $content, $script_prefix);
}