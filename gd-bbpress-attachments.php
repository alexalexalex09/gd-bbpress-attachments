<?php

/*
Plugin Name: GD bbPress Attachments
Plugin URI: http://www.dev4press.com/plugin/gd-bbpress-attachments/
Description: Implements attachments upload to the topics and replies in bbPress plugin through media library and adds additional forum based controls.
Version: 1.0.4
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

class gdbbPressAttachments {
    private $l;
    private $o;

    private $plugin_path;
    private $plugin_url;
    private $admin_plugin = false;

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

    public function load() {
        add_action("init", array($this, "load_translation"));

        if (is_admin()) {
            add_action("admin_init", array(&$this, "admin_init"));
            add_action("admin_menu", array(&$this, "admin_menu"));
            add_action("admin_menu", array(&$this, "admin_meta"));
            add_action("save_post", array(&$this, "save_edit_forum"));

            add_filter("plugin_action_links", array(&$this, "plugin_actions"), 10, 2);
            add_action("manage_topic_posts_columns", array(&$this, "admin_post_columns"), 1000);
            add_action("manage_reply_posts_columns", array(&$this, "admin_post_columns"), 1000);
            add_action("manage_topic_posts_custom_column", array(&$this, "admin_columns_data"), 1000, 2);
            add_action("manage_reply_posts_custom_column", array(&$this, "admin_columns_data"), 1000, 2);
        } else {
            add_action("bbp_init", array(&$this, "bbp_init"));
            add_action("bbp_head", array(&$this, "bbp_head"));

            add_action("bbp_theme_after_reply_form_tags", array(&$this, "embed_form"));
            add_action("bbp_theme_after_topic_form_tags", array(&$this, "embed_form"));
            add_action("bbp_new_reply", array(&$this, "save_reply"), 10, 5);
            add_action("bbp_new_topic", array(&$this, "save_topic"), 10, 4);
            add_action("bbp_get_reply_content", array(&$this, "embed_attachments"), 10, 2);
            add_action("bbp_get_topic_content", array(&$this, "embed_attachments"), 10, 2);

            if ($this->o["attachment_icon"] == 1) {
                add_action("bbp_theme_before_topic_title", array(&$this, "show_attachments_icon"));
            }
        }
    }

    public function show_attachments_icon() {
        $topic_id = bbp_get_topic_id();
        $count = d4p_topic_attachments_count($topic_id, true);
        if ($count > 0) {
            echo '<span class="bbp-attachments-count" title="'.$count.' '._n("attachment", "attachments", $count, "gd-bbpress-attachments").'"></span>';
        }
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
            $value = $this->o["roles_to_upload"];
            if (!is_array($value)) return true;

            global $current_user;
            if (is_array($current_user->roles)) {
                $matched = array_intersect($current_user->roles, $value);
                return !empty($matched);
            }
        }

        return fals;
    }

    public function admin_post_columns($columns) {
        $columns["gdbbatt_count"] = __("Attachments", "gd-bbpress-attachments");
        return $columns;
    }

    public function admin_columns_data($column, $id) {
        if ($column == "gdbbatt_count") {
            $attachments = d4p_get_post_attachments($id);
            echo count($attachments);
        }
    }

    public function admin_meta() {
        add_meta_box("gdbbattach-meta-forum", __("Attachments Settings", "gd-bbpress-attachments"), array(&$this, "metabox_forum"), "forum", "side", "high");
        add_meta_box("gdbbattach-meta-files", __("Attachments List", "gd-bbpress-attachments"), array(&$this, "metabox_files"), "topic", "side", "high");
        add_meta_box("gdbbattach-meta-files", __("Attachments List", "gd-bbpress-attachments"), array(&$this, "metabox_files"), "reply", "side", "high");
    }

    public function save_edit_forum($post_id) {
        if (isset($_POST["post_ID"]) && $_POST["post_ID"] > 0) {
            $post_id = $_POST["post_ID"];
        }

        if (isset($_POST["gdbbatt_forum_meta"]) && $_POST["gdbbatt_forum_meta"] == "edit") {
            $data = (array)$_POST["gdbbatt"];
            $meta = array(
                "to_override" => isset($data["to_override"]) ? 1 : 0,
                "max_file_size" => absint(intval($data["max_file_size"])),
                "max_to_upload" => absint(intval($data["max_to_upload"])),
            );
            update_post_meta($post_id, "_gdbbatt_settings", $meta);
        }
    }

    public function metabox_forum() {
        global $post_ID;

        $meta = get_post_meta($post_ID, "_gdbbatt_settings", true);
        if (!is_array($meta)) {
            $meta = array("to_override" => 0, "max_file_size" => $this->get_file_size(true), "max_to_upload" => $this->get_max_files(true));
        }

        include(GDBBPRESSATTACHMENTS_PATH."forms/meta_forum.php");
    }

    public function metabox_files() {
        global $post_ID;

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
        add_submenu_page("edit.php?post_type=forum", "GD bbPress Attachments", __("Attachments", "gd-bbpress-attachments"), "edit_posts", "gdbbpress_attachments", array(&$this, "menu_attachments"));
    }

    public function menu_attachments() {
        global $wp_roles;
        $options = $this->o;
        include(GDBBPRESSATTACHMENTS_PATH."forms/attachments.php");
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
            $this->o["include_js"] = isset($_POST["include_js"]) ? 1 : 0;
            $this->o["include_css"] = isset($_POST["include_css"]) ? 1 : 0;

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

    public function bbp_head() { 
        if ($this->o["include_css"] == 1) { ?>
            <style type="text/css">
                /*<![CDATA[*/
                .bbp-attachments { border-top: 1px solid #dddddd; margin-top: 20px; padding: 5px; }
                .bbp-attachments h6 { margin: 0 0 5px; }
                .bbp-attachments-count { background: transparent url(<?php echo GDBBPRESSATTACHMENTS_URL; ?>gfx/icons.png); display: inline-block; width: 16px; height: 16px; float: left; margin-right: 4px; }
                /*]]>*/
            </style>
        <?php } ?>
            <script type='text/javascript'>
                /* <![CDATA[ */
                var gdbbPressAttachmentsInit = {
                    max_files: <?php echo apply_filters("d4p_bbpressattchment_allow_upload", $this->get_max_files(), bbp_get_forum_id()); ?>
                };

                <?php if ($this->o["include_js"] == 1) { ?>
                    var gdbbPressAttachments={storage:{files_counter:1},init:function(){jQuery("form#new-post").attr("enctype","multipart/form-data");jQuery("form#new-post").attr("encoding","multipart/form-data");jQuery(".d4p-attachment-addfile").live("click",function(e){e.preventDefault();if(gdbbPressAttachments.storage.files_counter<gdbbPressAttachmentsInit.max_files){jQuery(this).before('<input type="file" size="40" name="d4p_attachment[]"><br/>');gdbbPressAttachments.storage.files_counter++}if(gdbbPressAttachments.storage.files_counter==gdbbPressAttachmentsInit.max_files){jQuery(this).remove()}})}};jQuery(document).ready(function(){gdbbPressAttachments.init()});
                <?php } ?>
                /* ]]> */
            </script>
        <?php
    }

    public function bbp_init() {
        wp_enqueue_script("jquery");
    }

    public function save_topic($topic_id, $forum_id, $anonymous_data, $topic_author) {
        $this->save_reply(0, $topic_id, $forum_id, $anonymous_data, $topic_author);
    }

    public function save_reply($reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author) {
        $uploads = array();

        if (!empty($_FILES) && !empty($_FILES["d4p_attachment"])) {
            require_once(ABSPATH.'wp-admin/includes/file.php');

            $overrides = array("test_form" => false, "upload_error_handler" => "d4p_bbattachment_handle_upload_error");
            foreach ($_FILES["d4p_attachment"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $file = array("name" => $_FILES["d4p_attachment"]["name"][$key],
                        "type" => $_FILES["d4p_attachment"]["type"][$key],
                        "size" => $_FILES["d4p_attachment"]["size"][$key],
                        "tmp_name" => $_FILES["d4p_attachment"]["tmp_name"][$key],
                        "error" => $_FILES["d4p_attachment"]["error"][$key]);
                    if ($this->is_right_size($file)) {
                        $upload = wp_handle_upload($file, $overrides);
                        if (!is_wp_error($upload)) {
                            $uploads[] = $upload;
                        }
                    }
                }
            }
        }

        if (!empty($uploads)) {
            require_once(ABSPATH.'wp-admin/includes/image.php');

            $post_id = $reply_id == 0 ? $topic_id : $reply_id;
            foreach ($uploads as $upload) {
                $wp_filetype = wp_check_filetype(basename($upload["file"]), null );
                $attachment = array('post_mime_type' => $wp_filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload["file"])),
                    'post_content' => '','post_status' => 'inherit');
                $attach_id = wp_insert_attachment($attachment, $upload["file"], $post_id);
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload["file"]);
                wp_update_attachment_metadata($attach_id, $attach_data);
            }
        }
    }

    public function embed_attachments($content, $id) {
        $attachments = d4p_get_post_attachments($id);
        if (!empty($attachments)) {
            $content.= '<div class="bbp-attachments">';
            $content.= '<h6>'.__("Attachments", "gd-bbpress-attachments").':</h6>';
            $content.= '<ol>';
            foreach ($attachments as $attachment) {
                $file = get_attached_file($attachment->ID);
                $url = wp_get_attachment_url($attachment->ID);
                $filename = pathinfo($file, PATHINFO_BASENAME);
                $content.= '<li><a href="'.$url.'">'.$filename.'</a></li>';
            }
            $content.= '</ol></div>';
        }
        return $content;
    }

    public function embed_form() {
        $can_upload = apply_filters("d4p_bbpressattchment_allow_upload", $this->is_user_allowed(), bbp_get_forum_id());
        if (!$can_upload) return;

        $file_size = apply_filters("d4p_bbpressattchment_max_file_size", $this->get_file_size(), bbp_get_forum_id());
        include(GDBBPRESSATTACHMENTS_PATH."forms/uploader.php");
    }
}

$gdbbpress_attachments = new gdbbPressAttachments();

?>