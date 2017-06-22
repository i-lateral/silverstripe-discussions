<?php

if (ClassInfo::exists('Commenting')) {
    
    // Add commenting to discussions
    Commenting::add('Discussion', array(
        "require_login" => true,
        "required_permission" => "DISCUSSIONS_POSTING",
        "order_comments_by" => "\"Created\" ASC",
    ));
}
