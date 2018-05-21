<?php

class Coinero_Coinhive_SimpleMinerUI extends Coinero_Widget
{
    function __construct()
    {
        parent::__construct('coinero-simple-miner-ui', __('SimpleMinerUI', 'wp-coinhive'));
    }

    function render_content($atts, $content, $script_prefix)
    {
        wp_enqueue_script($script_prefix . '-simple-ui');
        ?>
        <div class="coinhive-miner"
             style="<?php echo esc_attr($atts['style']) ?>"
             data-key="<?php echo esc_attr($atts['key']) ?>"
             data-autostart="<?php echo esc_attr($atts['autostart']) ?>"
             data-whitelabel="<?php echo esc_attr($atts['whitelabel']) ?>"
             data-background="<?php echo esc_attr($atts['background']) ?>"
             data-text="<?php echo esc_attr($atts['text']) ?>"
             data-action="<?php echo esc_attr($atts['action']) ?>"
             data-graph="<?php echo esc_attr($atts['graph']) ?>"
             data-threads="<?php echo esc_attr($atts['threads']) ?>"
             data-throttle="<?php echo esc_attr($atts['throttle']) ?>"
             data-start="<?php echo esc_attr($atts['start']) ?>">
            <div class="no-script"><?php echo $content ?></div>
        </div>
        <?php
    }

    function filter_render_content($content = '')
    {
        if (empty($content)) {
            return __('Please disable your ad or script blocker.', 'wp-coinero');
        }
        return $content;
    }

    function filter_render_atts($atts = array())
    {
        return shortcode_atts(array(
            'classes' => '',
            'style' => 'width: 100%;min-width: 256px; height: 310px;',
            'key' => $this->get_sitekey(),
            'autostart' => "false",
            'whitelabel' => "false",
            'background' => "#000000",
            'text' => "#eeeeee",
            'action' => "#00ff00",
            'graph' => "#555555",
            'threads' => "2",
            'throttle' => "0",
            'start' => __('Start Now!', 'wp-coinero')
        ), $atts);
    }
}

return new Coinero_Coinhive_SimpleMinerUI();