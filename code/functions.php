<?php

function d4p_bbattachment_handle_upload_error(&$file, $message) {
    return new WP_Error("file_upload_failed", $message);
}

function d4p_get_post_attachments($post_id) {
    $args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post_id); 
    return get_posts($args);
}

?>