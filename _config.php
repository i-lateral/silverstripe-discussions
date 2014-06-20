<?php

// Add commenting to discussions
Commenting::add('Discussion', array(
    'require_login' => true, // boolean, whether a user needs to login
));

Object::add_extension("Member", "DiscussionsMember");
Object::add_extension("Group", "DiscussionsGroup");
Object::add_extension("Comment", "DiscussionsComment");

if(class_exists('Users_Account_Controller')) {
    Users_Account_Controller::add_extension('DiscussionsUsersController');
}
