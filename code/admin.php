<?php

class gdbbPA_Admin {
    private $page_ids = array();
    private $admin_plugin = false;

    function __construct() {
        add_action('after_setup_theme', array($this, 'load'), 10);
    }

    public function load() {
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('admin_menu', array(&$this, 'admin_meta'));
        add_action('admin_head', array(&$this, 'admin_head'));
        add_action('save_post', array(&$this, 'save_edit_forum'));

        add_filter('plugin_action_links', array(&$this, 'plugin_actions'), 10, 2);
        add_action('manage_topic_posts_columns', array(&$this, 'admin_post_columns'), 1000);
        add_action('manage_reply_posts_columns', array(&$this, 'admin_post_columns'), 1000);
        add_action('manage_topic_posts_custom_column', array(&$this, 'admin_columns_data'), 1000, 2);
        add_action('manage_reply_posts_custom_column', array(&$this, 'admin_columns_data'), 1000, 2);
    }

    public function admin_init() {
        if (isset($_POST['gdbb-attach-submit'])) {
            global $gdbbpress_attachments;
            check_admin_referer('gd-bbpress-attachments');

            $gdbbpress_attachments->o['max_file_size'] = absint(intval($_POST['max_file_size']));
            $gdbbpress_attachments->o['max_to_upload'] = absint(intval($_POST['max_to_upload']));
            $gdbbpress_attachments->o['roles_to_upload'] = (array)$_POST['roles_to_upload'];
            $gdbbpress_attachments->o['attachment_icon'] = isset($_POST['attachment_icon']) ? 1 : 0;
            $gdbbpress_attachments->o['attchment_icons'] = isset($_POST['attchment_icons']) ? 1 : 0;
            $gdbbpress_attachments->o['hide_from_visitors'] = isset($_POST['hide_from_visitors']) ? 1 : 0;
            $gdbbpress_attachments->o['include_js'] = isset($_POST['include_js']) ? 1 : 0;
            $gdbbpress_attachments->o['include_css'] = isset($_POST['include_css']) ? 1 : 0;
            $gdbbpress_attachments->o['delete_attachments'] = strip_tags($_POST['delete_attachments']);
            $gdbbpress_attachments->o['image_thumbnail_active'] = isset($_POST['image_thumbnail_active']) ? 1 : 0;
            $gdbbpress_attachments->o['image_thumbnail_caption'] = isset($_POST['image_thumbnail_caption']) ? 1 : 0;
            $gdbbpress_attachments->o['image_thumbnail_rel'] = strip_tags($_POST['image_thumbnail_rel']);
            $gdbbpress_attachments->o['image_thumbnail_css'] = strip_tags($_POST['image_thumbnail_css']);
            $gdbbpress_attachments->o['image_thumbnail_size_x'] = absint(intval($_POST['image_thumbnail_size_x']));
            $gdbbpress_attachments->o['image_thumbnail_size_y'] = absint(intval($_POST['image_thumbnail_size_y']));
            $gdbbpress_attachments->o['log_upload_errors'] = isset($_POST['log_upload_errors']) ? 1 : 0;
            $gdbbpress_attachments->o['errors_visible_to_admins'] = isset($_POST['errors_visible_to_admins']) ? 1 : 0;
            $gdbbpress_attachments->o['errors_visible_to_author'] = isset($_POST['errors_visible_to_author']) ? 1 : 0;

            update_option('gd-bbpress-attachments', $gdbbpress_attachments->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }

        if (isset($_GET['page'])) {
            $this->admin_plugin = $_GET['page'] == 'gdbbpress_attachments';
        }

        if ($this->admin_plugin) {
            wp_enqueue_style('gd-bbpress-attachments', GDBBPRESSATTACHMENTS_URL."css/gd-bbpress-attachments_admin.css", array(), GDBBPRESSATTACHMENTS_VERSION);
        }
    }

    public function admin_menu() {
        $this->page_ids[] = add_submenu_page('edit.php?post_type=forum', 'GD bbPress Attachments', __("Attachments", "gd-bbpress-attachments"), GDBBPRESSATTACHMENTS_CAP, 'gdbbpress_attachments', array(&$this, 'menu_attachments'));

        $this->admin_load_hooks();
    }

    public function admin_load_hooks() {
        if (GDBBPRESSATTACHMENTS_WPV < 33) return;

        foreach ($this->page_ids as $id) {
            add_action('load-'.$id, array(&$this, 'load_admin_page'));
        }
    }

    public function admin_meta() {
        if (current_user_can(GDBBPRESSATTACHMENTS_CAP)) {
            add_meta_box("gdbbattach-meta-forum", __("Attachments Settings", "gd-bbpress-attachments"), array(&$this, "metabox_forum"), "forum", "side", "high");
            add_meta_box("gdbbattach-meta-files", __("Attachments List", "gd-bbpress-attachments"), array(&$this, "metabox_files"), "topic", "side", "high");
            add_meta_box("gdbbattach-meta-files", __("Attachments List", "gd-bbpress-attachments"), array(&$this, "metabox_files"), "reply", "side", "high");
        }
    }

    public function admin_head() { ?>
        <style type="text/css">
            /*<![CDATA[*/
            th.column-gdbbatt_count, td.column-gdbbatt_count { width: 3%; text-align: center; }
            /*]]>*/
        </style><?php
    }

    public function save_edit_forum($post_id) {
        if (isset($_POST['post_ID']) && $_POST['post_ID'] > 0) {
            $post_id = $_POST['post_ID'];
        }

        if (isset($_POST['gdbbatt_forum_meta']) && $_POST['gdbbatt_forum_meta'] == 'edit') {
            $data = (array)$_POST['gdbbatt'];
            $meta = array(
                'disable' => isset($data['disable']) ? 1 : 0,
                'to_override' => isset($data['to_override']) ? 1 : 0,
                'hide_from_visitors' => isset($data['hide_from_visitors']) ? 1 : 0,
                'max_file_size' => absint(intval($data['max_file_size'])),
                'max_to_upload' => absint(intval($data['max_to_upload']))
            );
            update_post_meta($post_id, '_gdbbatt_settings', $meta);
        }
    }

    public function plugin_actions($links, $file) {
        if ($file == 'gd-bbpress-attachments/gd-bbpress-attachments.php' ){
            $settings_link = '<a href="edit.php?post_type=forum&page=gdbbpress_attachments">'.__("Settings", "gd-bbpress-attachments").'</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    public function admin_post_columns($columns) {
        $columns['gdbbatt_count'] = '<img src="'.GDBBPRESSATTACHMENTS_URL.'gfx/attachment.png" width="16" height="12" alt="'.__("Attachments", "gd-bbpress-attachments").'" title="'.__("Attachments", "gd-bbpress-attachments").'" />';
        return $columns;
    }

    public function admin_columns_data($column, $id) {
        if ($column == 'gdbbatt_count') {
            $attachments = d4p_get_post_attachments($id);
            echo count($attachments);
        }
    }

    public function load_admin_page() {
        $screen = get_current_screen();

        $screen->set_help_sidebar('
            <p><strong>Dev4Press:</strong></p>
            <p><a target="_blank" href="http://www.dev4press.com/">'.__("Website", "gd-bbpress-attachments").'</a></p>
            <p><a target="_blank" href="http://twitter.com/milangd">'.__("On Twitter", "gd-bbpress-attachments").'</a></p>
            <p><a target="_blank" href="http://facebook.com/dev4press">'.__("On Facebook", "gd-bbpress-attachments").'</a></p>');

        $screen->add_help_tab(array(
            "id" => "gdpt-screenhelp-help",
            "title" => __("Get Help", "gd-bbpress-attachments"),
            "content" => '<h5>'.__("General Plugin Information", "gd-bbpress-attachments").'</h5>
                <p><a href="http://www.dev4press.com/plugins/gd-bbpress-attachments/" target="_blank">'.__("Home Page on Dev4Press.com", "gd-bbpress-attachments").'</a> | 
                <a href="http://wordpress.org/extend/plugins/gd-bbpress-attachments/" target="_blank">'.__("Home Page on WordPress.org", "gd-bbpress-attachments").'</a></p> 
                <h5>'.__("Getting Plugin Support", "gd-bbpress-attachments").'</h5>
                <p><a href="http://www.dev4press.com/forums/forum/free-plugins/gd-bbpress-attachments/" target="_blank">'.__("Support Forum on Dev4Press.com", "gd-bbpress-attachments").'</a> | 
                <a href="http://wordpress.org/tags/gd-bbpress-attachments?forum_id=10" target="_blank">'.__("Support Forum on WordPress.org", "gd-bbpress-attachments").'</a> | 
                <a href="http://www.dev4press.com/plugins/gd-bbpress-attachments/support/" target="_blank">'.__("Plugin Support Sources", "gd-bbpress-attachments").'</a></p>'));

        $screen->add_help_tab(array(
            "id" => "gdpt-screenhelp-website",
            "title" => "Dev4Press", "sfc",
            "content" => '<p>'.__("On Dev4Press website you can find many useful plugins, themes and tutorials, all for WordPress. Please, take a few minutes to browse some of these resources, you might find some of them very useful.", "gd-bbpress-attachments").'</p>
                <p><a href="http://www.dev4press.com/plugins/" target="_blank"><strong>'.__("Plugins", "gd-bbpress-attachments").'</strong></a> - '.__("We have more than 10 plugins available, some of them are commercial and some are available for free.", "gd-bbpress-attachments").'</p>
                <p><a href="http://www.dev4press.com/themes/" target="_blank"><strong>'.__("Themes", "gd-bbpress-attachments").'</strong></a> - '.__("All our themes are based on our own xScape Theme Framework, and only available as premium.", "gd-bbpress-attachments").'</p>
                <p><a href="http://www.dev4press.com/category/tutorials/" target="_blank"><strong>'.__("Tutorials", "gd-bbpress-attachments").'</strong></a> - '.__("Premium and free tutorials for our plugins themes, and many general and practical WordPress tutorials.", "gd-bbpress-attachments").'</p>
                <p><a href="http://www.dev4press.com/documentation/" target="_blank"><strong>'.__("Central Documentation", "gd-bbpress-attachments").'</strong></a> - '.__("Growing collection of functions, classes, hooks, constants with examples for our plugins and themes.", "gd-bbpress-attachments").'</p>
                <p><a href="http://www.dev4press.com/forums/" target="_blank"><strong>'.__("Support Forums", "gd-bbpress-attachments").'</strong></a> - '.__("Premium support forum for all with valid licenses to get help. Also, report bugs and leave suggestions.", "gd-bbpress-attachments").'</p>'));
    }

    public function metabox_forum() {
        global $post_ID;

        $meta = get_post_meta($post_ID, '_gdbbatt_settings', true);
        if (!is_array($meta)) {
            global $gdbbpress_attachments;
            $meta = array(
                'disable' => 0, 
                'to_override' => 0, 
                'hide_from_visitors' => 1, 
                'max_file_size' => $gdbbpress_attachments->get_file_size(true), 
                'max_to_upload' => $gdbbpress_attachments->get_max_files(true)
            );
        }

        include(GDBBPRESSATTACHMENTS_PATH.'forms/meta_forum.php');
    }

    public function metabox_files() {
        global $post_ID, $user_ID;
        $post = get_post($post_ID);
        $author_id = $post->post_author;

        include(GDBBPRESSATTACHMENTS_PATH.'forms/meta_files.php');
    }

    public function menu_attachments() {
        global $wp_roles, $gdbbpress_attachments;
        $options = $gdbbpress_attachments->o;

        include(GDBBPRESSATTACHMENTS_PATH.'forms/panels.php');
    }
}

global $gdbbpress_attachments_admin;
$gdbbpress_attachments_admin = new gdbbPA_Admin();

?>