<?php

$attachments = d4p_get_post_attachments($post_ID);
if (empty($attachments)) {
    _e("No attachments here.", "gd-bbpress-attachments");
} else {
    echo '<ul style="list-style: decimal outside; margin-left: 1.5em;">';
    foreach ($attachments as $attachment) {
        $file = get_attached_file($attachment->ID);
        $filename = pathinfo($file, PATHINFO_BASENAME);
        echo '<li>'.$filename.'</li>';
    }
    echo '</ul>';
}

?>