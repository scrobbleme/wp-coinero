<?php

class Coinero_Settings
{

    function __construct()
    {
        add_action('customize_register', [$this, 'customize_register']);
        add_action('admin_menu', [$this, 'register_settings_menu']);


    }

    function register_settings_menu()
    {
        add_submenu_page('edit.php?post_type=coinero_download', __('Settings & Help', 'wp-coinero'), __('Settings & Help', 'wp-coinero'), 'manage_options', 'wp-coinero-settings', [$this, 'redirect_to_customizer']);
    }

    function redirect_to_customizer()
    {
        $url = 'customize.php?autofocus[panel]=wp-coinero&return=' . urlencode(admin_url('edit.php?post_type=coinero_download&page=wp-coinero-settings'));
        wp_redirect(admin_url($url), 301);
        ?>
        <h1><?php _e('Settings & Help', 'wp-coinero') ?></h1>
        <h2><?php _e('Settings', 'wp-coinero') ?></h2>

        <p>
            <?php _e('Please click the button to open the customizer:', 'wp-coinero') ?>
        </p>
        <a href="<?php echo $url ?>"
           class="button button-primary button-large"><?php _e('Open customizer', 'wp-coinero') ?></a>

        <h2><?php _e('Help', 'wp-coinero') ?></h2>
        <?php
        do_action('wp-coinero-render-help-section');
    }

    /**
     * @param $wp_customize  WP_Customize_Manager
     */
    function customize_register($wp_customize)
    {
        $wp_customize->add_panel('wp-coinero', [
            'capability' => 'edit_theme_options',
            'title' => __('WP Coinero', 'wp-coinero'),
            'priority' => 500
        ]);


        $wp_customize->add_section('wp-coinero-general-settings', array(
            'capability' => 'edit_theme_options',
            'title' => __('General settings', 'wp-coinero'),
            'description' => __('You don\'t need to add your own keys. This way you will 100% support plugin development. If you add your custom keys, about 3% will still be used for plugin development.', 'wp-coinero'),
            'panel' => 'wp-coinero',
            'priority' => 0
        ));

        // Site key
        $wp_customize->add_setting('wp-coinero-general-site-key', array(
            'default' => '',
            'capability' => 'edit_theme_options',
            'type' => 'option',
        ));

        $wp_customize->add_control(
            'wp-coinero-general-site-key-callback-control', [
                'label' => __('Site key', 'wp-coinero'),
                'section' => 'wp-coinero-general-settings',
                'settings' => 'wp-coinero-general-site-key',
            ]
        );

        // Private key
        $wp_customize->add_setting('wp-coinero-general-private-key', array(
            'default' => '',
            'capability' => 'edit_theme_options',
            'type' => 'option',
        ));

        $wp_customize->add_control(
            'wp-coinero-general-private-key-callback-control', [
                'label' => __('Private key', 'wp-coinero'),
                'section' => 'wp-coinero-general-settings',
                'settings' => 'wp-coinero-general-private-key',
                'type' => 'password'
            ]
        );
    }
}

return new Coinero_Settings();