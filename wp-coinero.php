<?php
/*
Plugin Name:  WP Coinero
Plugin URI:   https://moewe.io/
Description:  Use Coinhive to generate Moneros and avoid spam ...
Version:      1.1
Author:       MOEWE
Author URI:   https://www.moewe.io/
Contributors: adrian2k7
Text Domain:  wp-coinero
*/

define('COINERO_BASE_PATH', realpath(dirname(__FILE__)));

define('COINERO_DEFAULT_SITEKEY', 'U25ZGhn9E4fAM2V8R03TYMMVv0VVCLRn');
define('COINERO_PROXY_BASE_URL', 'https://coinhive-proxy.moewe.io');

class Coinero
{
    public $simple_ui_miner;
    public $captcha;
    public $javascript_miner;

    private $token_validator;
    private $settings;
    private $downloads;

    function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 1000);
        add_action('login_enqueue_scripts', [$this, 'enqueue_scripts'], 1000);

        include "includes/CustomizerSupport.php";
        include "includes/coinhive/widgets/Coinero_Widget.php";
        $this->captcha = include "includes/coinhive/widgets/Captcha.php";
        $this->simple_ui_miner = include "includes/coinhive/widgets/SimpleMinerUI.php";
        $this->javascript_miner = include "includes/coinhive/widgets/JavaScriptMiner.php";
        $this->token_validator = include "includes/coinhive/Token_Validator.php";

        $this->downloads = include "includes/Downloads.php";
        $this->settings = include "includes/Settings.php";
    }

    function enqueue_scripts()
    {
        // Nice trick from https://developer.wordpress.org/reference/functions/wp_enqueue_script/#comment-1558
        $my_css_ver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/custom.css'));
        $my_js_ver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/custom.js'));

        wp_enqueue_style('wp-coinero', plugins_url('assets/custom.css', __FILE__), [], $my_css_ver);

        wp_register_script('wp-coinero', plugins_url('assets/custom.js', __FILE__), [], $my_js_ver, true);
        wp_localize_script('wp-coinero', 'wp_coinero_ajax_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'loading_message' => __('<div class="loading-wrapper"><span class="loading">0%</span>' . __('Loading your download. Please wait.', 'wp-coinero') . '</div>', 'wp-coinero')
        ]);

        // Basic version
        wp_register_script('coinhive-simple-ui', 'https://coinhive.com/lib/miner.min.js', [], '', true);
        wp_register_script('coinhive-captcha', 'https://coinhive.com/lib/captcha.min.js', [], '', true);
        wp_register_script('coinhive-javascript-miner', 'https://coinhive.com/lib/coinhive.min.js', [], '', true);

        // Authedmine version
        wp_register_script('authedmine-simple-ui', 'https://authedmine.com/lib/simple-ui.min.js', [], '', true);
        wp_register_script('authedmine-captcha', 'https://authedmine.com/lib/captcha.min.js', [], '', true);
        wp_register_script('authedmine-javascript-miner', 'https://authedmine.com/lib/authedmine.min.js', [], '', true);


        // Libs
        wp_register_style('jquery-modal', plugins_url('assets/libs/jquery-modal-0.9.1/jquery.modal.css', __FILE__), [], '0.9.1');
        wp_register_script('jquery-modal', plugins_url('assets/libs/jquery-modal-0.9.1/jquery.modal.js', __FILE__), ['jquery'], '0.9.1');

        // Datatables
        if (apply_filters('wp-coinero-need-datatables', true)) {
            wp_enqueue_style('datatables', plugins_url('assets/libs/datatables-1.10.18/datatables.css', __FILE__), [], '1.10.18');
            wp_enqueue_style('datatables-responsive', plugins_url('assets/libs/datatables-1.10.18/Responsive-2.2.2/css/responsive.dataTables.css', __FILE__), ['datatables'], '2.2.2');

            wp_register_script('datatables', plugins_url('assets/libs/datatables-1.10.18/datatables.js', __FILE__), ['jquery'], '1.10.18', true);
            wp_register_script('datatables-responsive', plugins_url('assets/libs/datatables-1.10.18/Responsive-2.2.2/js/dataTables.responsive.js', __FILE__), ['datatables'], '2.2.2', true);
        }
    }
}

$GLOBALS['coinero'] = new Coinero();
