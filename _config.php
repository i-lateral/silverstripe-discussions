<?php

if (ClassInfo::exists('Commenting')) {
    // Add commenting to discussions
    Commenting::add('Discussion', array(
        "require_login" => true,
        "required_permission" => "DISCUSSIONS_REPLY",
        "order_comments_by" => "\"Created\" ASC",
    ));

    Object::add_extension("Member", "DiscussionsMember");
    Object::add_extension("Group", "DiscussionsGroup");
    Object::add_extension("Comment", "DiscussionsComment");

    if(class_exists('Users_Account_Controller')) {
        Users_Account_Controller::add_extension('DiscussionsUsersController');
    }
}
