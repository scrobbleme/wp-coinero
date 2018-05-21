<?php

class Coinero_Coinhive_Captcha extends Coinero_Widget
{
    function __construct()
    {
        parent::__construct('coinero-captcha', __('Captcha', 'wp-coinhive'));

        add_action('login_form', [$this, 'add_captcha_to_login']);
        add_filter('authenticate', [$this, 'authenticate'], 10, 3);
    }

    function render_content($atts, $content, $script_prefix)
    {
        wp_enqueue_script($script_prefix . '-captcha');
        ?>
        <div class="coinhive-captcha"
             data-hashes="<?php esc_attr_e($atts['hashes']) ?>"
             data-key="<?php esc_attr_e($atts['key']) ?>"
             data-autostart="<?php esc_attr_e($atts['autostart']) ?>"
             data-whitelabel="<?php esc_attr_e($atts['whitelabel']) ?>"
             data-callback="<?php esc_attr_e($atts['callback']) ?>"
             data-disable-elements="<?php esc_attr_e($atts['disable-elements']) ?>"
        >
            <div class="no-script"><?php echo $content ?></div>
        </div>
        <input type="hidden" name="coinhive-sitekey" value="<?php esc_attr_e($atts['key']) ?>"/>
        <input type="hidden" name="coinhive-hashes" value="<?php esc_attr_e($atts['hashes']) ?>"/>
        <?php
    }


    function filter_render_content($content = '')
    {
        if (empty($content)) {
            return __('<em>Loading Captcha...<br /> If it doesn\'t load, please disable Adblock!</em>', 'wp-coinero');
        }
        return $content;
    }

    function filter_render_atts($atts = array())
    {
        return shortcode_atts(array(
            'classes' => '',
            'key' => $this->get_sitekey(),
            'hashes' => $this->get_option('hashes', 1024),
            'autostart' => $this->get_option('autostart', false) ? 'true' : 'false',
            'whitelabel' => $this->get_option('whitelabel', false) ? 'true' : 'false',
            'disable-elements' => $this->get_option('disable-elements', ''),
            'callback' => $this->get_option('callback', ''),
        ), $atts);
    }

    function add_captcha_to_login()
    {
        if (!$this->get_option('use-for-login', true)) {
            return;
        }
        echo $this->render(array('autostart' => "true"), '', true);
        ob_start();
        ?>
        <style>
            #login {
                min-width: 354px;
            }
        </style>
        <?php
        echo ob_get_clean();
    }

    function authenticate($user, $username, $password)
    {
        if (!$this->get_option('use-for-login', true)) {
            return null;
        }
        if ($user instanceof WP_Error) {
            return $user;
        }
        $token = $_POST['coinhive-captcha-token'];
        if (empty($token)) {
            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            remove_action('authenticate', 'wp_authenticate_email_password', 20);
            return new WP_Error('denied', __('<strong>ERROR</strong> The Captcha is missing. Please try again and disable any script and ad blockers.', 'wp-coinero'));
        }

        $verified = apply_filters('wp-coinero-validate-token', false, $token, absint($_POST['coinhive-hashes']), $_POST['coinhive-sitekey']);
        if (!$verified || is_wp_error($verified)) {
            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            remove_action('authenticate', 'wp_authenticate_email_password', 20);
            if (is_wp_error($verified)) {
                return $verified;
            }
            return new WP_Error('denied', __('<strong>ERROR</strong> The Captcha can\'t be verified. Please try again.', 'wp-coinero'));
        }
        return $user;
    }


    /**
     * @param $wp_customize WP_Customize_Manager
     * @param $section_id
     * @param $settings_prefix
     */
    function add_customizer_settings($wp_customize, $section_id, $settings_prefix)
    {
        // autostart
        $wp_customize->add_setting($settings_prefix . 'autostart', array(
            'default' => '0',
            'capability' => 'edit_theme_options',
            'type' => 'option',
            'sanitize_callback' => 'absint',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'autostart-control', [
                'label' => __('Autostart', 'wp-coinero'),
                'section' => $section_id,
                'settings' => $settings_prefix . 'autostart',
                'type' => 'checkbox'
            ]
        );

        // whitelabel
        $wp_customize->add_setting($settings_prefix . 'whitelabel', array(
            'default' => '0',
            'capability' => 'edit_theme_options',
            'type' => 'option',
            'sanitize_callback' => 'absint',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'whitelabel-control', [
                'label' => __('Whitelabel', 'wp-coinero'),
                'section' => $section_id,
                'settings' => $settings_prefix . 'whitelabel',
                'type' => 'checkbox'
            ]
        );

        // Hashes
        $wp_customize->add_setting($settings_prefix . 'hashes', array(
            'default' => '1024',
            'capability' => 'edit_theme_options',
            'type' => 'option',
            'sanitize_callback' => 'absint',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'hashes-control', [
                'label' => __('Hashes', 'wp-coinero'),
                'description' => __('Needs to be a multiplier of 256.', 'wp-coinero'),
                'section' => $section_id,
                'settings' => $settings_prefix . 'hashes',
            ]
        );

        // disable-elements' => "",
        $wp_customize->add_setting($settings_prefix . 'disable-elements', array(
            'default' => '',
            'capability' => 'edit_theme_options',
            'type' => 'option',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'disable-elements-control', [
                'label' => __('Disable Elements', 'wp-coinero'),
                'description' => 'CSS selector of disabled elements',
                'section' => $section_id,
                'settings' => $settings_prefix . 'disable-elements',
            ]
        );
        // callback
        $wp_customize->add_setting($settings_prefix . 'callback', array(
            'default' => '',
            'capability' => 'edit_theme_options',
            'type' => 'option',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'callback-control', [
                'label' => __('Callback', 'wp-coinero'),
                'description' => 'JavaScript callback, which is called, when the captcha is finished (i.e. alert).',
                'section' => $section_id,
                'settings' => $settings_prefix . 'callback',
            ]
        );
    }
}

return new Coinero_Coinhive_Captcha();