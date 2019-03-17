<?php

class Coinero_Downloads extends Coinero_CustomizerSupport
{

    private $fields;

    function __construct()
    {
        parent::__construct('coinero-downloads', __('Downloads', 'wp-coinero'));

        add_action('init', [$this, 'init'], 0);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_post']);

        add_action('wp_ajax_wp_coinero_get_download_dialog', [$this, 'get_download_dialog']);
        add_action('wp_ajax_nopriv_wp_coinero_get_download_dialog', [$this, 'get_download_dialog']);

        add_action('customize_register', [$this, 'customize_register']);
        add_action('wp-coinero-render-download-modal-text', [$this, 'render_custom_text']);

        add_filter('the_content', [$this, 'add_download_button_to_content']);

        add_shortcode('coinhive_downloads', [$this, 'render_downloads_shortcode']);

        $this->fields = array(
            array(
                'id' => 'coinero_download_url',
                'label' => __('URL to file', 'wp-coinero'),
                'type' => 'url',
            ),

            array(
                'id' => 'coinero_redirect_download',
                'label' => __('External file', 'wp-coinero'),
                'type' => 'checkbox',
                'description' => __('If selected, the link will redirect to the target file, instead of streamed through a custom proxy.', 'wp-coinero')
            ),

            array(
                'id' => 'coinero_direct_download',
                'label' => __('Link to download', 'wp-coinero'),
                'type' => 'checkbox',
                'description' => __('If selected, the action within the download list will start the download instead linking to the details page.', 'wp-coinero')
            )
        );
    }

    function init()
    {
        $labels = array(
            'name' => _x('Downloads', 'Post Type General Name', 'wp-coinero'),
            'singular_name' => _x('Download', 'Post Type Singular Name', 'wp-coinero'),
            'menu_name' => __('Coinero', 'wp-coinero'),
            'name_admin_bar' => __('Coinero Download', 'wp-coinero'),
            'archives' => __('Downloads', 'wp-coinero'),
            'attributes' => __('Item Attributes', 'wp-coinero'),
            'parent_item_colon' => __('Parent File:', 'wp-coinero'),
            'all_items' => __('Files', 'wp-coinero'),
            'add_new_item' => __('Add File', 'wp-coinero'),
            'add_new' => __('Add File', 'wp-coinero'),
            'new_item' => __('New File', 'wp-coinero'),
            'edit_item' => __('Edit File', 'wp-coinero'),
            'update_item' => __('Update File', 'wp-coinero'),
            'view_item' => __('View File', 'wp-coinero'),
            'view_items' => __('View Files', 'wp-coinero'),
            'search_items' => __('Search File', 'wp-coinero'),
            'not_found' => __('Not found', 'wp-coinero'),
            'not_found_in_trash' => __('Not found in Trash', 'wp-coinero'),
            'featured_image' => __('Featured Image', 'wp-coinero'),
            'set_featured_image' => __('Set featured image', 'wp-coinero'),
            'remove_featured_image' => __('Remove featured image', 'wp-coinero'),
            'use_featured_image' => __('Use as featured image', 'wp-coinero'),
            'insert_into_item' => __('Insert into file', 'wp-coinero'),
            'uploaded_to_this_item' => __('Uploaded to this file', 'wp-coinero'),
            'items_list' => __('Items list', 'wp-coinero'),
            'items_list_navigation' => __('Items list navigation', 'wp-coinero'),
            'filter_items_list' => __('Filter files list', 'wp-coinero'),
        );
        $rewrite = array(
            'slug' => 'coinloads',
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        );
        $args = array(
            'label' => __('Download', 'wp-coinero'),
            'description' => __('Downloads using Coinhive', 'wp-coinero'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields',),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-download',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => false,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => $rewrite,
            'capability_type' => 'page',
        );
        register_post_type('coinero_download', $args);
    }

    /**
     * Hooks into WordPress' add_meta_boxes function.
     * Goes through screens (post types) and adds the meta box.
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'options',
            __('Download options', 'wp-coinero'),
            array($this, 'add_meta_box_callback'),
            'coinero_download',
            'normal',
            'default'
        );
    }

    /**
     * Generates the HTML for the meta box
     *
     * @param object $post WordPress post object
     */
    public function add_meta_box_callback($post)
    {
        wp_nonce_field('options_data', 'options_nonce');
        $this->generate_fields($post);
    }

    /**
     * @param $wp_customize WP_Customize_Manager
     */
    public function add_customizer_settings($wp_customize, $section_id, $settings_prefix)
    {

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

        $wp_customize->add_setting($settings_prefix . 'custom-text', array(
            'default' => '',
            'capability' => 'edit_theme_options',
            'type' => 'option',
        ));

        $wp_customize->add_control(
            $settings_prefix . 'custom-text-control', [
                'label' => __('Custom text', 'wp-coinero'),
                'description' => __('Text which will be shown in the download modal, between title and button. Can contain any HTML.', 'wp-coinero'),
                'section' => $section_id,
                'settings' => $settings_prefix . 'custom-text',
                'type' => 'textarea'
            ]
        );
    }

    /**
     * Generates the field's HTML for the meta box.
     */
    public function generate_fields($post)
    {
        foreach ($this->fields as $field) {
            echo '<p>';
            $label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
            $db_value = get_post_meta($post->ID, 'options_' . $field['id'], true);
            switch ($field['type']) {
                case 'checkbox':
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="checkbox" value="1" />',
                        $db_value === '1' ? 'checked' : '',
                        $field['id'],
                        $field['id']
                    );
                    echo $input . $label;
                    break;
                default:
                    $input = sprintf(
                        '<input id="%s" name="%s" type="%s" value="%s" style="width: 100%%" />',
                        $field['id'],
                        $field['id'],
                        $field['type'],
                        $db_value
                    );
                    echo $label . '<br />' . $input;
            }
            if (!empty($field['description'])) {
                echo ' <span class="dashicons dashicons-editor-help" title="' . esc_attr($field['description']) . '"></span>';
            }
            echo '</p>';
        }
    }

    /**
     * Hooks into WordPress' save_post function
     */
    public function save_post($post_id)
    {
        if (!isset($_POST['options_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST['options_nonce'];
        if (!wp_verify_nonce($nonce, 'options_data')) {
            return $post_id;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        foreach ($this->fields as $field) {
            if (isset($_POST[$field['id']])) {
                switch ($field['type']) {
                    case 'email':
                        $_POST[$field['id']] = sanitize_email($_POST[$field['id']]);
                        break;
                    case 'text':
                        $_POST[$field['id']] = sanitize_text_field($_POST[$field['id']]);
                        break;
                }
                update_post_meta($post_id, 'options_' . $field['id'], $_POST[$field['id']]);
            } else if ($field['type'] === 'checkbox') {
                update_post_meta($post_id, 'options_' . $field['id'], '0');
            }
        }
        return $post_id;
    }

    function render_custom_text()
    {
        $custom_text = $this->get_option('custom-text');
        if ($custom_text && !empty($custom_text)) {
            echo '<div class="custom-text">' . do_shortcode($custom_text) . '</div>';
        }
    }

    function render_downloads_shortcode($atts = array(), $content)
    {
        $script_prefix = $this->get_option('use-authedmine', true) ? 'authedmine' : 'coinhive';
        wp_enqueue_style('jquery-modal');

        wp_enqueue_script($script_prefix . '-javascript-miner');
        wp_enqueue_script($script_prefix . '-captcha');
        wp_enqueue_script('datatables');
        wp_enqueue_script('jquery-modal');
        wp_enqueue_script('wp-coinero');
        ob_start();
        ?>
        <div class="coinero-downloads-list">
            <table class="display responsive">
                <thead>
                <tr>
                    <th><?php _e('Name', 'wp-coinero') ?></th>
                    <th class="not-mobile"><?php _e('Updated', 'wp-coinero') ?></th>
                    <th><?php _e('Actions', 'wp-coinero') ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th><?php _e('Name', 'wp-coinero') ?></th>
                    <th><?php _e('Updated', 'wp-coinero') ?></th>
                    <th><?php _e('Actions', 'wp-coinero') ?></th>
                </tr>
                </tfoot>
                <tbody>
                <?php
                // WP_Query arguments
                $args = array(
                    'post_type' => array('coinero_download'),
                    'post_status' => array('publish'),
                    'nopaging' => true,
                    'posts_per_page' => '-1',
                    'order' => 'ASC',
                    'orderby' => 'title',
                );

                $args = apply_filters('wp-coinero-downloads-list-query-arguments', $args);
                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        echo '<tr>';
                        echo '<td data-filter="' . esc_attr(get_the_title()) . '"><a href="' . get_permalink() . '" title="' . esc_attr(get_the_title()) . '">' . get_the_title() . '</a></td>';
                        echo '<td class="download-date" data-order="' . get_the_modified_time('U') . '">' . get_the_modified_date(get_option('date_format')) . '</td>';
                        echo '<td>';
                        $this->render_download_button(get_the_ID());
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3">' . __('No files found', 'wp-coinero') . '</td></tr>';
                }
                wp_reset_postdata();
                ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    function add_download_button_to_content($content)
    {
        if (is_singular('coinero_download')) {
            wp_enqueue_style('jquery-modal');

            $script_prefix = $this->get_option('use-authedmine', true) ? 'authedmine' : 'coinhive';
            wp_enqueue_script($script_prefix . '-javascript-miner');
            wp_enqueue_script($script_prefix . '-captcha');
            wp_enqueue_script('jquery-modal');
            wp_enqueue_script('wp-coinero');
            ob_start();
            $this->render_download_button(get_the_ID());
            return ob_get_clean() . '<br /><br />' . $content;
        }
        return $content;
    }


    function render_download_button($download_id)
    {
        $direct = get_post_meta($download_id, 'options_coinero_direct_download', true);
        if ($direct) {
            $href = add_query_arg(array(
                'action' => 'wp_coinero_get_download_dialog',
                'id' => $download_id,
            ), admin_url('admin-ajax.php'));

            echo '<a href="' . $href . '" rel="modal:open"><button class="button" title="' . esc_attr__('Download: ', 'wp-coinero') . esc_attr(get_the_title($download_id)) . '" data-id="' . $download_id . '">' . __('Download', 'wp-coinero') . '</button></a>';
        } else {
            echo '<a class="button" href="' . get_the_permalink($download_id) . '">' . __('View details', 'wp-coinero') . '</a>';
        }
    }

    function get_download_dialog()
    {
        $id = 'coinero-' . esc_attr(uniqid());
        $file = isset($_GET['id']) ? $_GET['id'] : -1;
        $hashes = absint($this->get_option('hashes', 1024));
        $sitekey = $this->get_sitekey();

        if (get_post_meta($file, 'options_coinero_redirect_download', true)) {
            $href = get_post_meta($file, 'options_coinero_download_url', true);
        } else {
            $href = add_query_arg(array(
                'action' => 'wp-coinero-download-file',
                'file' => $file,
            ), admin_url('admin-ajax.php'));
        }

        $href = add_query_arg(array(
            'token' => '{{token}}',
            'hashes' => $hashes,
            'sitekey' => $sitekey
        ), $href);

        ?>
        <div id="<?php echo $id ?>" class="coinero-download-container">
            <div class="loading-wrapper">
                <p><?php _e('Please press "Verify me" to prepare your download.', 'wp-coinero') ?></p>
                <div id="<?php echo $id ?>-captcha" class="captcha-container" data-hashes="<?php echo $hashes ?>"
                     data-key="<?php esc_attr_e($sitekey) ?>" data-whitelabel="false" data-autostart="true">
                    <noscript><?php _e('You have to disable your script blocker to download the file.', 'wp-coinero') ?></noscript>
                    <span class="loading slow" style="color: transparent;">0%</span>
                </div>
            </div>
            <div class="script-blocker-modal"
                 style="display: none;">
                <p><?php _e('You have to disable your script blocker or allow authedmine.com/coinhive.com to download the file.', 'wp-coinero') ?></p>
            </div>

            <div class="loading-finished-wrapper">
                <p><?php _e('Your download is ready.', 'wp-coinero') ?></p>
                <div class="actions">
                    <a class="start-download" target="_blank" href="<?php esc_attr_e($href) ?>">
                        <button class="button"><?php _e('Start download', 'wp-coinero'); ?></button>
                    </a>
                </div>
            </div>
            <?php do_action('wp-coinero-render-download-modal-text'); ?>
            <script>
                wp_coinero_prepare_download_captcha('<?php echo $id ?>');
            </script>
        </div>
        <?php
        wp_die();
    }
}

return new Coinero_Downloads();
