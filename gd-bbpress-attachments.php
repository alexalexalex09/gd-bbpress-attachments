<?php

/*
Plugin Name: GD bbPress Attachments
Plugin URI: http://www.dev4press.com/plugin/gd-bbpress-attachments/
Description: Implements attachments upload to the topics and replies in bbPress plugin through media library and adds additional forum based controls.
Version: 1.6
Author: Milan Petrovic
Author URI: http://www.dev4press.com/

== Copyright ==
Copyright 2008 - 2011 Milan Petrovic (email: milan@gdragon.info)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(dirname(__FILE__)."/code/defaults.php");
require_once(dirname(__FILE__)."/code/functions.php");

if (!defined("GDBBPRESSATTACHMENTS_CAP")) {
    define("GDBBPRESSATTACHMENTS_CAP", "edit_dashboard");
}

class gdbbPressAttachments {
    private $page_ids = array();

    private $l;
    private $o;

    private $wp_version;
    private $plugin_path;
    private $plugin_url;
    private $admin_plugin = false;

    private $icons = array(
        "code" => "c|cc|h|js|class", 
        "xml" => "xml", 
        "excel" => "xla|xls|xlsx|xlt|xlw|xlam|xlsb|xlsm|xltm", 
        "word" => "docx|dotx|docm|dotm", 
        "image" => "png|gif|jpg|jpeg|jpe|jp|bmp|tif|tiff", 
        "psd" => "psd", 
        "ai" => "ai", 
        "archive" => "zip|rar|gz|gzip|tar",
        "text" => "txt|asc|nfo", 
        "powerpoint" => "pot|pps|ppt|pptx|ppam|pptm|sldm|ppsm|potm", 
        "pdf" => "pdf", 
        "html" => "htm|html|css", 
        "video" => "avi|asf|asx|wax|wmv|wmx|divx|flv|mov|qt|mpeg|mpg|mpe|mp4|m4v|ogv|mkv", 
        "documents" => "odt|odp|ods|odg|odc|odb|odf|wp|wpd|rtf",
        "audio" => "mp3|m4a|m4b|mp4|m4v|wav|ra|ram|ogg|oga|mid|midi|wma|mka",
        "icon" => "ico"
    );

    function __construct() {
        $this->_init();
    }

    private function _upgrade($old, $new) {
        foreach ($new as $key => $value) {
            if (!isset($old[$key])) $old[$key] = $value;
        }

        $unset = Array();
        foreach ($old as $key => $value) {
            if (!isset($new[$key])) $unset[] = $key;
        }

        foreach ($unset as $key) {
            unset($old[$key]);
        }

        return $old;
    }

    private function _init() {
        global $wp_version;
        $this->wp_version = substr(str_replace(".", "", $wp_version), 0, 2);
        define("GDBBPRESSATTACHMENTS_WPV", intval($this->wp_version));

        $gdd = new gdbbPressAttachments_Defaults();

        $this->o = get_option("gd-bbpress-attachments");
        if (!is_array($this->o)) {
            $this->o = $gdd->default_options;
            update_option("gd-bbpress-attachments", $this->o);
        }

        if ($this->o["build"] != $gdd->default_options["build"]) {
            $this->o = $this->_upgrade($this->o, $gdd->default_options);

            $this->o["version"] = $gdd->default_options["version"];
            $this->o["date"] = $gdd->default_options["date"];
            $this->o["status"] = $gdd->default_options["status"];
            $this->o["build"] = $gdd->default_options["build"];
            $this->o["revision"] = $gdd->default_options["revision"];
            $this->o["edition"] = $gdd->default_options["edition"];

            update_option("gd-bbpress-attachments", $this->o);
        }

        define("GDBBPRESSATTACHMENTS_INSTALLED", $gdd->default_options["version"]." Free");
        define("GDBBPRESSATTACHMENTS_VERSION", $gdd->default_options["version"]."_b".($gdd->default_options["build"]."_free"));

        $this->plugin_path = dirname(__FILE__)."/";
        $this->plugin_url = plugins_url("/gd-bbpress-attachments/");

        define("GDBBPRESSATTACHMENTS_URL", $this->plugin_url);
        define("GDBBPRESSATTACHMENTS_PATH", $this->plugin_path);

        add_action("after_setup_theme", array($this, "load"));
    }

    private function _icon($ext) {
        foreach ($this->icons as $icon => $list) {
            $list = explode("|", $list);
            if (in_array($ext, $list)) return $icon;
        }
        return "generic";
    }

    public function load() {
        add_action("init", array(&$this, "load_translation"));
        add_action("init", array(&$this, "init_thumbnail_size"), 1);

        add_action("before_delete_post", array(&$this, "delete_post"));

        if (is_admin()) {
            add_action("admin_init", array(&$this, "admin_init"));
            add_action("admin_menu", array(&$this, "admin_menu"));
            add_action("admin_menu", array(&$this, "admin_meta"));
            add_action("admin_head", array(&$this, "admin_head"));
            add_action("save_post", array(&$this, "save_edit_forum"));

            add_filter("plugin_action_links", array(&$this, "plugin_actions"), 10, 2);
            add_action("manage_topic_posts_columns", array(&$this, "admin_post_columns"), 1000);
            add_action("manage_reply_posts_columns", array(&$this, "admin_post_columns"), 1000);
            add_action("manage_topic_posts_custom_column", array(&$this, "admin_columns_data"), 1000, 2);
            add_action("manage_reply_posts_custom_column", array(&$this, "admin_columns_data"), 1000, 2);
        } else {
            add_action("bbp_head", array(&$this, "bbp_head"));

            add_action("bbp_theme_after_reply_form_tags", array(&$this, "embed_form"));
            add_action("bbp_theme_after_topic_form_tags", array(&$this, "embed_form"));
            add_action("bbp_edit_reply", array(&$this, "save_reply"), 10, 5);
            add_action("bbp_edit_topic", array(&$this, "save_topic"), 10, 4);
            add_action("bbp_new_reply", array(&$this, "save_reply"), 10, 5);
            add_action("bbp_new_topic", array(&$this, "save_topic"), 10, 4);
            add_action("bbp_get_reply_content", array(&$this, "embed_attachments"), 10, 2);
            add_action("bbp_get_topic_content", array(&$this, "embed_attachments"), 10, 2);
            add_action("bbp_get_reply_content", array(&$this, "embed_attachments"), 10, 2);

            if ($this->o["attachment_icon"] == 1) {
                add_action("bbp_theme_before_topic_title", array(&$this, "show_attachments_icon"));
            }
        }
    }

    public function init_thumbnail_size() {
        add_image_size("d4p-bbp-thumb", $this->o["image_thumbnail_size_x"], $this->o["image_thumbnail_size_y"], true);
    }

    public function show_attachments_icon() {
        $topic_id = bbp_get_topic_id();
        $count = d4p_topic_attachments_count($topic_id, true);
        if ($count > 0) {
            echo '<span class="bbp-attachments-count" title="'.$count.' '._n("attachment", "attachments", $count, "gd-bbpress-attachments").'"></span>';
        }
    }

    public function enabled_for_forum($id = 0) {
        $meta = get_post_meta(bbp_get_forum_id($id), "_gdbbatt_settings", true);
        return !isset($meta["disable"]) || (isset($meta["disable"]) && $meta["disable"] == 0);
    }

    public function get_file_size($global_only = false) {
        $value = $this->o["max_file_size"];
        if (!$global_only) {
            $meta = get_post_meta(bbp_get_forum_id(), "_gdbbatt_settings", true);

            if (is_array($meta) && $meta["to_override"] == 1) {
                $value = $meta["max_file_size"];
            }
        }
        return $value;
    }

    public function get_max_files($global_only = false) {
        $value = $this->o["max_to_upload"];
        if (!$global_only) {
            $meta = get_post_meta(bbp_get_forum_id(), "_gdbbatt_settings", true);
            if (is_array($meta) && $meta["to_override"] == 1) {
                $value = $meta["max_to_upload"];
            }
        }
        return $value;
    }

    public function is_right_size($file) {
        $file_size = apply_filters("d4p_bbpressattchment_max_file_size", $this->get_file_size(), bbp_get_forum_id());
        return $file["size"] < $file_size * 1024;
    }

    public function is_user_allowed() {
        if (is_user_logged_in()) {
            if (!isset($this->o["roles_to_upload"])) {
                return true;
            }

            $value = $this->o["roles_to_upload"];
            if (!is_array($value)) {
                return true;
            }

            global $current_user;
            if (is_array($current_user->roles)) {
                $matched = array_intersect($current_user->roles, $value);
                return !empty($matched);
            }
        }

        return false;
    }

    public function is_hidden_from_visitors() {
        $value = $this->o["hide_from_visitors"];
        $meta = get_post_meta(bbp_get_forum_id(), "_gdbbatt_settings", true);

        if (is_array($meta) && $meta["to_override"] == 1) {
            $value = $meta["hide_from_visitors"];
        }

        return $value == 1;
    }

    public function admin_post_columns($columns) {
        $columns["gdbbatt_count"] = '<img src="'.GDBBPRESSATTACHMENTS_URL.'gfx/attachment.png" width="16" height="12" alt="'.__("Attachments", "gd-bbpress-attachments").'" title="'.__("Attachments", "gd-bbpress-attachments").'" />';
        return $columns;
    }

    public function admin_columns_data($column, $id) {
        if ($column == "gdbbatt_count") {
            $attachments = d4p_get_post_attachments($id);
            echo count($attachments);
        }
    }

    public function admin_meta() {
        if (current_user_can(GDBBPRESSATTACHMENTS_CAP)) {
            add_meta_box("gdbbattach-meta-forum", __("Attachments Settings", "gd-bbpress-attachments"), array(&$this, "metabox_forum"), "forum", "side", "high");
            add_meta_box("gdbbattach-meta-files", __("Attachments List", "gd-bbpress-attachments"), array(&$this, "metabox_files"), "topic", "side", "high");
            add_meta_box("gdbbattach-meta-files", __("Attachments List", "gd-bbpress-attachments"), array(&$this, "metabox_files"), "reply", "side", "high");
        }
    }

    public function save_edit_forum($post_id) {
        if (isset($_POST["post_ID"]) && $_POST["post_ID"] > 0) {
            $post_id = $_POST["post_ID"];
        }

        if (isset($_POST["gdbbatt_forum_meta"]) && $_POST["gdbbatt_forum_meta"] == "edit") {
            $data = (array)$_POST["gdbbatt"];
            $meta = array(
                "disable" => isset($data["disable"]) ? 1 : 0,
                "to_override" => isset($data["to_override"]) ? 1 : 0,
                "hide_from_visitors" => isset($data["hide_from_visitors"]) ? 1 : 0,
                "max_file_size" => absint(intval($data["max_file_size"])),
                "max_to_upload" => absint(intval($data["max_to_upload"]))
            );
            update_post_meta($post_id, "_gdbbatt_settings", $meta);
        }
    }

    public function metabox_forum() {
        global $post_ID;

        $meta = get_post_meta($post_ID, "_gdbbatt_settings", true);
        if (!is_array($meta)) {
            $meta = array(
                "disable" => 0, 
                "to_override" => 0, 
                "hide_from_visitors" => 1, 
                "max_file_size" => $this->get_file_size(true), 
                "max_to_upload" => $this->get_max_files(true)
            );
        }

        include(GDBBPRESSATTACHMENTS_PATH."forms/meta_forum.php");
    }

    public function metabox_files() {
        global $post_ID, $user_ID;
        $post = get_post($post_ID);
        $author_id = $post->post_author;

        include(GDBBPRESSATTACHMENTS_PATH."forms/meta_files.php");
    }

    public function plugin_actions($links, $file) {
        static $this_plugin;
        if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

        if ($file == $this_plugin ){
            $settings_link = '<a href="edit.php?post_type=forum&page=gdbbpress_attachments">'.__("Settings", "gd-bbpress-attachments").'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    public function admin_menu() {
        $this->page_ids[] = add_submenu_page("edit.php?post_type=forum", "GD bbPress Attachments", __("Attachments", "gd-bbpress-attachments"), GDBBPRESSATTACHMENTS_CAP, "gdbbpress_attachments", array(&$this, "menu_attachments"));

        $this->admin_load_hooks();
    }

    public function admin_load_hooks() {
        if (GDBBPRESSATTACHMENTS_WPV < 33) return;

        foreach ($this->page_ids as $id) {
            add_action("load-".$id, array(&$this, "load_admin_page"));
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

    public function menu_attachments() {
        global $wp_roles;
        $options = $this->o;
        include(GDBBPRESSATTACHMENTS_PATH."forms/panels.php");
    }

    public function load_translation() {
        $this->l = get_locale();
        if(!empty($this->l)) {
            $moFile = GDBBPRESSATTACHMENTS_PATH."languages/gd-bbpress-attachments-".$this->l.".mo";
            if (@file_exists($moFile) && is_readable($moFile)) load_textdomain('gd-bbpress-attachments', $moFile);
        }
    }

    public function admin_init() {
        if (isset($_POST["gdbb-attach-submit"])) {
            check_admin_referer("gd-bbpress-attachments");

            $this->o["max_file_size"] = absint(intval($_POST["max_file_size"]));
            $this->o["max_to_upload"] = absint(intval($_POST["max_to_upload"]));
            $this->o["roles_to_upload"] = (array)$_POST["roles_to_upload"];
            $this->o["attachment_icon"] = isset($_POST["attachment_icon"]) ? 1 : 0;
            $this->o["attchment_icons"] = isset($_POST["attchment_icons"]) ? 1 : 0;
            $this->o["hide_from_visitors"] = isset($_POST["hide_from_visitors"]) ? 1 : 0;
            $this->o["include_js"] = isset($_POST["include_js"]) ? 1 : 0;
            $this->o["include_css"] = isset($_POST["include_css"]) ? 1 : 0;
            $this->o["delete_attachments"] = strip_tags($_POST["delete_attachments"]);
            $this->o["image_thumbnail_active"] = isset($_POST["image_thumbnail_active"]) ? 1 : 0;
            $this->o["image_thumbnail_caption"] = isset($_POST["image_thumbnail_caption"]) ? 1 : 0;
            $this->o["image_thumbnail_rel"] = strip_tags($_POST["image_thumbnail_rel"]);
            $this->o["image_thumbnail_css"] = strip_tags($_POST["image_thumbnail_css"]);
            $this->o["image_thumbnail_size_x"] = absint(intval($_POST["image_thumbnail_size_x"]));
            $this->o["image_thumbnail_size_y"] = absint(intval($_POST["image_thumbnail_size_y"]));
            $this->o["log_upload_errors"] = isset($_POST["log_upload_errors"]) ? 1 : 0;
            $this->o["errors_visible_to_admins"] = isset($_POST["errors_visible_to_admins"]) ? 1 : 0;
            $this->o["errors_visible_to_author"] = isset($_POST["errors_visible_to_author"]) ? 1 : 0;

            update_option("gd-bbpress-attachments", $this->o);
            wp_redirect(add_query_arg("settings-updated", "true"));
            exit();
        }

        if (isset($_GET["page"])) {
            $this->admin_plugin = $_GET["page"] == "gdbbpress_attachments";
        }

        if ($this->admin_plugin) {
            wp_enqueue_style("gd-bbpress-attachments", GDBBPRESSATTACHMENTS_URL."css/gd-bbpress-attachments_admin.css", array(), GDBBPRESSATTACHMENTS_VERSION);
        }
    }

    public function admin_head() { ?>
        <style type="text/css">
            /*<![CDATA[*/
            th.column-gdbbatt_count, td.column-gdbbatt_count { width: 3%; text-align: center; }
            /*]]>*/
        </style><?php
    }

    public function bbp_head() { 
        if (d4p_is_bbpress()) {
            wp_enqueue_script("jquery");
            if ($this->o["include_css"] == 1) { ?>
                <style type="text/css">
                    /*<![CDATA[*/
                    .bbp-attachments, .bbp-attachments-errors { border-top: 1px solid #dddddd; margin-top: 15px; padding: 5px 0; }
                    .bbp-attachments h6 { margin: 0 0 5px; }
                    .bbp-attachments ol { margin: 0; list-style: decimal inside none; }
                    .bbp-attachments ol.with-icons { list-style: none; }
                    .bbp-attachments li { line-height: 16px; height: 16px; margin: 0 0 4px; }
                    .bbp-attachments ol.with-icons li { padding: 0 0 0 18px; }
                    .bbp-attachments ol.with-icons li.bbp-atthumb { padding: 0; height: auto; }
                    .bbp-attachments ol.with-icons li.bbp-atthumb .wp-caption { padding: 5px 5px 0; margin: 0; height: auto; }
                    .bbp-attachments-count { background: transparent url(<?php echo GDBBPRESSATTACHMENTS_URL; ?>gfx/icons.png); display: inline-block; width: 16px; height: 16px; float: left; margin-right: 4px; }
                    .bbp-atticon { background: transparent url(<?php echo GDBBPRESSATTACHMENTS_URL; ?>gfx/icons.png) no-repeat; }
                    .bbp-atticon-generic { background-position: 0 -16px; }
                    .bbp-atticon-code { background-position: 0 -32px; }
                    .bbp-atticon-xml { background-position: 0 -48px; }
                    .bbp-atticon-excel { background-position: 0 -64px; }
                    .bbp-atticon-word { background-position: 0 -80px; }
                    .bbp-atticon-image { background-position: 0 -96px; }
                    .bbp-atticon-psd { background-position: 0 -112px; }
                    .bbp-atticon-ai { background-position: 0 -128px; }
                    .bbp-atticon-archive { background-position: 0 -144px; }
                    .bbp-atticon-text { background-position: 0 -160px; }
                    .bbp-atticon-powerpoint { background-position: 0 -176px; }
                    .bbp-atticon-pdf { background-position: 0 -192px; }
                    .bbp-atticon-html { background-position: 0 -208px; }
                    .bbp-atticon-video { background-position: 0 -224px; }
                    .bbp-atticon-documents { background-position: 0 -240px; }
                    .bbp-atticon-audio { background-position: 0 -256px; }
                    .bbp-atticon-icon { background-position: 0 -272px; }
                    /*]]>*/
                </style>
            <?php } ?>
            <script type="text/javascript">
                /* <![CDATA[ */
                var gdbbPressAttachmentsInit = {
                    max_files: <?php echo apply_filters("d4p_bbpressattchment_allow_upload", $this->get_max_files(), bbp_get_forum_id()); ?>
                };

                <?php if ($this->o["include_js"] == 1) { ?>
                    var gdbbPressAttachments={storage:{files_counter:1},init:function(){jQuery("form#new-post").attr("enctype","multipart/form-data");jQuery("form#new-post").attr("encoding","multipart/form-data");jQuery(".d4p-attachment-addfile").live("click",function(e){e.preventDefault();if(gdbbPressAttachments.storage.files_counter<gdbbPressAttachmentsInit.max_files){jQuery(this).before('<input type="file" size="40" name="d4p_attachment[]"><br/>');gdbbPressAttachments.storage.files_counter++}if(gdbbPressAttachments.storage.files_counter==gdbbPressAttachmentsInit.max_files){jQuery(this).remove()}})}};jQuery(document).ready(function(){gdbbPressAttachments.init()});
                <?php } ?>
                /* ]]> */
            </script>
        <?php }
    }

    public function delete_post($id) {
        if (!function_exists("bbp_is_reply")) exit;

        if (bbp_is_reply($id) || bbp_is_topic($id)) {
            if ($this->o["delete_attachments"] == "delete") {
                $files = d4p_get_post_attachments($id);

                if (is_array($files) && !empty($files)) {
                    foreach ($files as $file) {
                        wp_delete_attachment($file->ID);
                    }
                }
            } else if ($this->o["delete_attachments"] == "detach") {
                global $wpdb;

                $wpdb->update($wpdb->posts, array('post_parent' => 0), array('post_parent' => $id, 'post_type' => 'attachment'));
            }
        }
    }

    public function save_topic($topic_id, $forum_id, $anonymous_data, $topic_author) {
        $this->save_reply(0, $topic_id, $forum_id, $anonymous_data, $topic_author);
    }

    public function save_reply($reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author) {
        $uploads = array();

        if (!empty($_FILES) && !empty($_FILES["d4p_attachment"])) {
            require_once(ABSPATH."wp-admin/includes/file.php");

            $errors = new WP_Error();
            $overrides = array("test_form" => false, "upload_error_handler" => "d4p_bbattachment_handle_upload_error");
            foreach ($_FILES["d4p_attachment"]["error"] as $key => $error) {
                $file_name = $_FILES["d4p_attachment"]["name"][$key];
                if ($error == UPLOAD_ERR_OK) {
                    $file = array("name" => $file_name,
                        "type" => $_FILES["d4p_attachment"]["type"][$key],
                        "size" => $_FILES["d4p_attachment"]["size"][$key],
                        "tmp_name" => $_FILES["d4p_attachment"]["tmp_name"][$key],
                        "error" => $_FILES["d4p_attachment"]["error"][$key]);
                    if ($this->is_right_size($file)) {
                        $upload = wp_handle_upload($file, $overrides);
                        if (!is_wp_error($upload)) {
                            $uploads[] = $upload;
                        } else {
                            $errors->add("wp_upload_error", $upload->errors["wp_upload_error"][0], $file_name);
                        }
                    } else {
                        $errors->add("d4p_upload_error", "File exceeds allowed file size.", $file_name);
                    }
                } else {
                    switch ($error) {
                        default:
                        case "UPLOAD_ERR_NO_FILE":
                            $errors->add("php_upload_error", "File not uploaded.", $file_name);
                            break;
                        case "UPLOAD_ERR_INI_SIZE":
                            $errors->add("php_upload_error", "Upload file size exceeds PHP maximum file size allowed.", $file_name);
                            break;
                        case "UPLOAD_ERR_FORM_SIZE":
                            $errors->add("php_upload_error", "Upload file size exceeds FORM specified file size.", $file_name);
                            break;
                        case "UPLOAD_ERR_PARTIAL":
                            $errors->add("php_upload_error", "Upload file only partially uploaded.", $file_name);
                            break;
                        case "UPLOAD_ERR_CANT_WRITE":
                            $errors->add("php_upload_error", "Can't write file to the disk.", $file_name);
                            break;
                        case "UPLOAD_ERR_NO_TMP_DIR":
                            $errors->add("php_upload_error", "Temporary folder for upload is missing.", $file_name);
                            break;
                        case "UPLOAD_ERR_EXTENSION":
                            $errors->add("php_upload_error", "Server extension restriction stopped upload.", $file_name);
                            break;
                    }
                }
            }
        }

        $post_id = $reply_id == 0 ? $topic_id : $reply_id;

        if (!empty($errors->errors) && $this->o["log_upload_errors"] == 1) {
            foreach ($errors->errors as $code => $messages) {
                if ($errors->error_data[$code] != "") {
                    add_post_meta($post_id, "_bbp_attachment_upload_error", array(
                        "file" => $errors->error_data[$code], "message" => $messages[0]));
                }
            }
        }

        if (!empty($uploads)) {
            require_once(ABSPATH."wp-admin/includes/image.php");

            foreach ($uploads as $upload) {
                $wp_filetype = wp_check_filetype(basename($upload["file"]), null );
                $attachment = array("post_mime_type" => $wp_filetype["type"],
                    "post_title" => preg_replace('/\.[^.]+$/', '', basename($upload["file"])),
                    "post_content" => "","post_status" => "inherit");
                $attach_id = wp_insert_attachment($attachment, $upload["file"], $post_id);
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload["file"]);
                wp_update_attachment_metadata($attach_id, $attach_data);
            }
        }
    }

    public function embed_attachments($content, $id) {
        $attachments = d4p_get_post_attachments($id);
        $content = '';

        if (!empty($attachments)) {
            $content.= '<div class="bbp-attachments">';
            $content.= '<h6>'.__("Attachments", "gd-bbpress-attachments").':</h6>';

            if (!is_user_logged_in() && $this->is_hidden_from_visitors()) {
                $content.= sprintf(__("You must be <a href='%s'>logged in</a> to view attched files.", "gd-bbpress-attachments"), wp_login_url(get_permalink()));
            } else {
                global $user_ID;

                if (!empty($attachments)) {
                    $content.= '<ol';
                    if ($this->o["attchment_icons"] == 1) {
                        $content.= ' class="with-icons"';
                    }
                    $content.= '>';

                    foreach ($attachments as $attachment) {
                        $file = get_attached_file($attachment->ID);
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        $filename = pathinfo($file, PATHINFO_BASENAME);
                        $url = wp_get_attachment_url($attachment->ID);

                        $html = $class_li = $class_a = $rel_a = "";
                        $a_title = $filename;
                        $caption = false;
                        if ($this->o["image_thumbnail_active"] == 1) {
                            $html = wp_get_attachment_image($attachment->ID, "d4p-bbp-thumb");

                            if ($html != "") {
                                $class_li = "bbp-atthumb";
                                $class_a = $this->o["image_thumbnail_css"];
                                $caption = $this->o["image_thumbnail_caption"] == 1;
                                $rel_a = ' rel="'.$this->o["image_thumbnail_rel"].'"';
                                $rel_a = str_replace("%ID%", $id, $rel_a);
                                $rel_a = str_replace("%TOPIC%", bbp_get_topic_id(), $rel_a);
                            }
                        }
                        if ($html == "") {
                            $html = $filename;

                            if ($this->o["attchment_icons"] == 1) {
                                $class_li = "bbp-atticon bbp-atticon-".$this->_icon($ext);
                            }
                        }

                        $content.= '<li id="d4p-bbp-attachment_'.$attachment->ID.'" class="d4p-bbp-attachment d4p-bbp-attachment-'.$ext.' '.$class_li.'">';
                        if ($caption) {
                            $content.= '<div style="width: '.$this->o["image_thumbnail_size_x"].'px" class="wp-caption">';
                        }

                        $content.= '<a class="'.$class_a.'"'.$rel_a.' href="'.$url.'" title="'.$a_title.'">'.$html.'</a>';
                        if ($caption) {
                            $content.= '<p class="wp-caption-text">'.$a_title.'</p></div>';
                        }

                        $content.= '</li>';
                    }

                    $content.= '</ol></div>';
                }

                $post = get_post($id);
                $author_id = $post->post_author;

                if (($this->o["errors_visible_to_author"] == 1 && $author_id == $user_ID) || ($this->o["errors_visible_to_admins"] == 1 && d4p_is_user_admin())) {
                    $errors = get_post_meta($id, "_bbp_attachment_upload_error");

                    if (!empty($errors)) {
                        $content.= '<div class="bbp-attachments-errors">';
                        $content.= '<h6>'.__("Upload Errors", "gd-bbpress-attachments").':</h6>';
                        $content.= '<ol>';

                        foreach ($errors as $error) {
                            $content.= '<li><strong>'.$error["file"].'</strong>: '.__($error["message"], "gd-bbpress-attachments").'</li>';
                        }

                        $content.= '</ol></div>';
                    }
                }
            }
        }

        return $content;
    }

    public function embed_form() {
        $can_upload = apply_filters("d4p_bbpressattchment_allow_upload", $this->is_user_allowed(), bbp_get_forum_id());
        if (!$can_upload) return;

        $is_enabled = apply_filters("d4p_bbpressattchment_forum_enabled", $this->enabled_for_forum(), bbp_get_forum_id());
        if (!$is_enabled) return;

        $file_size = apply_filters("d4p_bbpressattchment_max_file_size", $this->get_file_size(), bbp_get_forum_id());
        include(GDBBPRESSATTACHMENTS_PATH."forms/uploader.php");
    }
}

$gdbbpress_attachments = new gdbbPressAttachments();

?>