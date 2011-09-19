<?php

function d4p_bbattachment_handle_upload_error(&$file, $message) {
    return new WP_Error("file_upload_failed", $message);
}

function d4p_get_post_attachments($post_id) {
    $args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post_id); 
    return get_posts($args);
}

function d4p_topic_attachments_count($topic_id, $include_replies = false) {
    global $wpdb;

    $sql = "select ID from ".$wpdb->posts." where post_parent = ".$topic_id." and post_type = 'attachment'";

    if ($include_replies) {
        $sql = "(".$sql.") union (select ID from ".$wpdb->posts." where post_parent in (select ID from ".$wpdb->posts." where post_parent = ".$topic_id." and post_type = 'reply') and post_type = 'attachment')";
    }

    $attachments = $wpdb->get_results($sql);
    return count($attachments);
}

?>