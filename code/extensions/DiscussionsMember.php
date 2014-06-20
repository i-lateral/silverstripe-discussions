<?php

class DiscussionsMember extends DataExtension {
    private static $db = array(
        "RecieveCommentEmails"  => "Boolean",
        "ReveiveNewDiscussionEmails" => "Boolean",
        "ReveiveLikedEmails"    => "Boolean",
        "Nickname"              => "Varchar",
        "URL"                   => "Varchar(200)"
    );

    private static $has_one = array(
        "Avatar"                => "Image"
    );

    private static $many_many = array(
        "LikedDiscussions"      => "Discussion",
        "BlockedMembers"        => "Member",
    );

    private static $belongs_many_many = array(
        "BlockedBy"             => "Member"
    );

    private static $defaults = array(
        "RecieveCommentEmails"  => 1,
        "ReveiveNewDiscussionEmails" => 0,
        "RecieveLikedEmails"    => 1
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->removeByName("LikedDiscussions");
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        $this->owner->Nickname = ($this->owner->Nickname) ? $this->owner->Nickname : $this->owner->FirstName;
    }
}
