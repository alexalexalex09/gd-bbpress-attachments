<?php

class gdbbPressAttachments_Defaults {
    var $default_options = array(
        "version" => "1.2.0",
        "date" => "2011.10.09.",
        "status" => "Stable",
        "product_id" => "gd-bbpress-attachments",
        "edition" => "free",
        "revision" => 0,
        "build" => 602,
        "include_js" => 1,
        "include_css" => 1,
        "max_file_size" => 512,
        "max_to_upload" => 4,
        "roles_to_upload" => null,
        "attachment_icon" => 1,
        "attchment_icons" => 1,
        "log_upload_errors" => 0,
        "errors_visible_to_roles" => array("administrator"),
        "errors_visible_to_author" => 1
    );

    function __construct() { }
}

?>