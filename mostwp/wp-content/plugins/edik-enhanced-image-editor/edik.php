<?php
/**
 * Plugin Name: Edik Advanced Image Editor
 * Plugin URI: http://www.sitedevel.com/products/edik
 * Description: Edik Advanced Image Editor for Wordpress is Wordpress plugin which allows to edit site's images using own image editor instead of standard one.
 * Version: 0.1.2
 * Author: Site.Devel
 * Author URI: http://www.sitedevel.com
 * License: GPL2
 */

class Edik {

    private static $_options = array(
        'edik_enable_buildin_editor' => '0',
    );

    public function __construct() {
        $this->plugin_url = plugins_url().'/edik-enhanced-image-editor';
        $this->plugin_dir_path = plugin_dir_path( __FILE__ );
        plugin_dir_path( __FILE__ );

        // Frontend
        add_filter('wp_print_scripts', array(&$this, 'init_javascripts'), 10);
        add_filter('media_row_actions', array(&$this, 'media_row_action_add'), 10);
        add_action( 'admin_footer-post-new.php', array(&$this, 'edik_script_injection') ); // Hook to inject into attachment details area
        add_action( 'admin_footer-post.php', array(&$this, 'edik_script_injection') ); // Hook to inject into attachment details area

        // Admin hooks
        add_action('admin_menu', array(&$this, 'edik_settings_menu'));

        $this->ajax_init();
    }

    function edik_settings_menu() {
      add_submenu_page('options-general.php', 'Edik Settings', 'Edik Settings', 'manage_options', 'edik-settings', array(&$this, 'settings_page'));
      add_action( 'admin_init', array(&$this, 'register_mysettings' ));
    }

    function register_mysettings() {
        foreach(self::$_options as $option=>$value) {
            register_setting( 'edik-settings-group', $option );
        }
    }

    function settings_page() {
        foreach(self::$_options as $option_name=>$default_value)
            $options[$option_name] = get_option($option_name, $default_value);

        require($this->plugin_dir_path.'/tpl/edik-settings.php');
    }

    private function ajax_init() {
        add_filter('wp_ajax_edik_get_editor_content', array(&$this, 'ajax_edik_get_editor_content'), 10);
        add_filter('wp_ajax_edik_update_attachment', array(&$this, 'ajax_edik_update_attachment'), 10);
    }

    public function media_row_action_add($row_actions) {
        // Extract current attachement ID
        preg_match('/post=([0-9]*)/', $row_actions['edit'], $out);
        $current_att_id = $out[1];

        $action['edik_edit'] = '<a href="'.get_site_url().'/wp-admin/admin-ajax.php?action=edik_get_editor_content&image='.$current_att_id.'" class="edik-wp-extended-edit" title="Edit using Edik extended editor">Extended Image Editor</a>';

        $updated_row_actions = array_merge($action, array_slice($row_actions, 1));
        if (get_option('edik_enable_buildin_editor', 0)):
            $updated_row_actions = array_merge(array_slice($row_actions, 0, 1), $updated_row_actions);
        endif;

        return $updated_row_actions;
    }

    public function init_javascripts() {
        if ( is_admin() ) {
            wp_register_script('edik_admin_script', ( $this->plugin_url . '/js/edik.js'), false);
            wp_enqueue_script('edik_admin_script');

            wp_localize_script('edik_admin_script', 'edik_script_vars', array( 'edik_plugin_url' => $this->plugin_url, 'ajax_nonce' => wp_create_nonce('edik-attachment')));
        }
    }

    public function ajax_edik_get_editor_content() {
        $image = wp_get_attachment_image_src($_GET["image"], 'full');
        echo '<div id="for_edik" data-attach-id="'.$_GET["image"].'" data-src="'.$image[0].'"></div>';
        die();
    }

    public function ajax_edik_update_attachment() {
        check_ajax_referer('edik-attachment', 'nonce');

        $att_image_path = get_attached_file($_POST['att_id']);
        $image = $_POST["image"];

        list($type, $data) = explode(';', $image);
        list(, $data)      = explode(',', $data);

        file_put_contents($att_image_path, base64_decode($data));
        
        $old_meta = wp_get_attachment_metadata($_POST['att_id']);

        $path_parts = pathinfo($old_meta["file"]);

        $upload_dir = wp_upload_dir();
        $files_path =  $upload_dir["basedir"]."/".$path_parts["dirname"];

        // Deleting all old files, before creating new
        foreach ($old_meta["sizes"] as $val) {
            @unlink($files_path.'/'.$val['file']);
        }
        
        // Thumbnails regenerating
        $data = wp_generate_attachment_metadata( $_POST['att_id'], $att_image_path );
        wp_update_attachment_metadata( $_POST['att_id'], $data );

        $data['full_path'] = $upload_dir['baseurl'].'/'.$path_parts['dirname'];
        echo json_encode($data);

        die();
    }

    public function edik_script_injection()
    {
        ?>
        <script>
            jQuery(function($) {
                $('#wpcontent').ajaxStop(function() {

                    var add_link = function() {
                        var details = $('.attachment-details .edit-attachment');
                        $.each(details, function(i, detail) {
                            parent = $(detail).parent();
                            if (parent.find('.edik-wp-extended-edit').length<=0) {
                                // Getting of attachment ID
                                var mask = /post=([0-9]*)/;
                                var found = $(detail).attr('href').match(mask);
                                var att_id = found[1];

                                $(detail).before($('<a class="edik-wp-extended-edit" href="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=edik_get_editor_content&image='+att_id+'">Extended image edit</a>'));
                            }
                            <?php if (!get_option('edik_enable_buildin_editor', 0)):?>
                            $(detail).css('display', 'none');
                            <?php endif; ?>
                        });
                    };
                    add_link();

                    $(document).on('click', '.attachment-preview .thumbnail', function() {
                        add_link();
                    });

                    // WOO Commerce: Appending Extended edit button to products list (Product Gallery)
                    var imgs = $('#woocommerce-product-images .product_images .image');

                    $.each(imgs, function(img) {
                        if ($(this).find('.actions a.edik-wp-extended-edit').length<=0)
                            $(this).find('.actions').append('<li><a href="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=edik_get_editor_content&image='+$(this).data('attachment_id')+'" class="edik-wp-extended-edit" title="Extended image editor"></a></li>')
                    });

                    $('.hide-if-no-js #remove-post-thumbnail').parent().append('<a id="edik-featured-image" href="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=edik_get_editor_content&image=<?php echo get_post_thumbnail_id(); ?>" class="edik-wp-extended-edit" title="Extended image editor">Image editor</a>');
                });

                // Adding edik editor to attachment page
                var standard_btn = $('input[id*="imgedit-open-btn-"]');

                $('<input type="button" value="Extended image edit" class="button edik-wp-extended-edit" data-src="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=edik_get_editor_content&image=<?php echo $_GET["post"]; ?>">').insertAfter(standard_btn);

                <?php if (!get_option('edik_enable_buildin_editor', 0)):?>
                    standard_btn.remove();

                <?php endif; ?>
            });
        </script>
    <?php
    }

}



new Edik();