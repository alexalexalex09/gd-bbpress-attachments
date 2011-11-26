<?php

/**
 * Check if the current page is forum, topic or other bbPress page.
 *
 * @return bool true if the current page is the forum related
 */
function d4p_is_bbpress() {
    $is = false;

    if (function_exists("bbp_get_forum_id")) {
        $is = bbp_get_forum_id() > 0;
        if (!$is) {
            global $template;
            $templates = array("single-reply-edit.php", "single-topic-edit.php");
            $file = pathinfo($template, PATHINFO_BASENAME);
            $is = in_array($file, $templates);
        }
    }

    return $is;
}

/**
 * Checks to see if the currently logged user is admin.
 *
 * @return bool is user admin or not
 */
function d4p_is_user_admin() {
    global $current_user;
    if (is_array($current_user->roles)) {
        return in_array("administrator", $current_user->roles);
    } else {
        return false;
    }
}

/**
 * Get the list of attachments for a post.
 *
 * @param int $post_id topic or reply ID to get attachments for
 * @return array list of attachments objects
 */
function d4p_get_post_attachments($post_id) {
    $args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post_id); 
    return get_posts($args);
}

/**
 * Count attachments for the forum topic. It can include topic replies in the count.
 *
 * @param int $topic_id id of the topic to count attachments for
 * @param bool $include_replies true, to include reply attachments
 * @return int number of attachments
 */
function d4p_topic_attachments_count($topic_id, $include_replies = false) {
    global $wpdb;

    $sql = "select ID from ".$wpdb->posts." where post_parent = ".$topic_id." and post_type = 'attachment'";

    if ($include_replies) {
        $sql = "(".$sql.") union (select ID from ".$wpdb->posts." where post_parent in (select ID from ".$wpdb->posts." where post_parent = ".$topic_id." and post_type = 'reply') and post_type = 'attachment')";
    }

    $attachments = $wpdb->get_results($sql);
    return count($attachments);
}

function d4p_bbattachment_handle_upload_error(&$file, $message) {
    return new WP_Error("wp_upload_error", $message);
}

?>