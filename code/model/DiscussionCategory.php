<?php

/**
 * A discussion category is a flag for filtering discussions (similar to
 * tags, but selected by the creator)
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package Discussions
 */
class DiscussionCategory extends DataObject {
    private static $db = array(
        "Title"         => "Varchar",
        "URLSegment"    => "Varchar"
    );

    private static $has_one = array(
        "Parent" => "DiscussionHolder"
    );

    private static $belongs_many_many = array(
        "Discussions"   => "Discussion"
    );

    private static $summary_fields = array(
        "Title",
        "URLSegment"
    );

    private static $default_sort = "Title ASC";

    public function Link() {
        return Controller::join_links(
            $this->Parent()->Link("category"),
            $this->URLSegment
        );
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName("ParentID");

        return $fields;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        $this->URLSegment = ($this->URLSegment) ? $this->URLSegment : Convert::raw2url($this->Title);
    }

    public function canView($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return $this->Parent()->canEdit($member);
    }

    public function canDelete($member = null) {
        return $this->Parent()->canDelete($member);
    }

    public function canCreate($member = null) {
        return $this->Parent()->canCreate($member);
    }
}
