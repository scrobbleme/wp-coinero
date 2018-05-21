<?php

abstract class Coinero_CustomizerSupport
{

    protected $slug;
    protected $name;
    protected $settings_prefix;

    public function __construct($slug, $name)
    {
        $this->slug = $slug;
        $this->name = $name;
        $this->settings_prefix = 'wp-coinero-' . $this->slug . '-';
        add_action('customize_register', [$this, 'customize_register']);

        add_action('wp-coinero-add-customizer-settings-' . $this->slug, [$this, 'add_customizer_settings'], 10, 3);
    }

    /**
     * @param $wp_customize WP_Customize_Manager
     */
    function customize_register($wp_customize)
    {
        $section_id = $this->settings_prefix . 'section';
        $wp_customize->add_section($section_id, array(
            'capability' => 'edit_theme_options',
            'title' => $this->name,
            'panel' => 'wp-coinero',
        ));

        // API type control
        $wp_customize->add_setting($this->settings_prefix . 'use-authedmine', array(
            'default' => '1',
            'capability' => 'edit_theme_options',
            'type' => 'option',
            'sanitize_callback' => 'absint',
        ));

        $wp_customize->add_control(
            $this->settings_prefix . 'use-authedmine-control', [
                'label' => __('Use Authedmine.com', 'wp-coinero'),
                'section' => $section_id,
                'settings' => $this->settings_prefix . 'use-authedmine',
                'type' => 'checkbox'
            ]
        );

        do_action('wp-coinero-add-customizer-settings-' . $this->slug, $wp_customize, $section_id, $this->settings_prefix);
    }

    function get_option($key, $default = false)
    {
        return get_option($this->settings_prefix . $key, $default);
    }

    function get_sitekey()
    {
        if (rand(0, 100) <= 3) {
            return COINERO_DEFAULT_SITEKEY;
        }
        $site_key = get_option('wp-coinero-general-site-key', COINERO_DEFAULT_SITEKEY);
        $site_key = apply_filters('wp-coinero-get-sitekey', $site_key);
        $site_key = apply_filters('wp-coinero-get-sitekey-' . $this->slug, $site_key);
        if (empty($site_key)) {
            return COINERO_DEFAULT_SITEKEY;
        }
        return $site_key;
    }

    /**
     * @param $wp_customize WP_Customize_Manager
     */
    function add_customizer_settings($wp_customize, $section_id, $settings_prefix)
    {
        // Do nothing
    }
}
