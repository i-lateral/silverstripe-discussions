<?php

/**
 * A discussion category is a flag for filtering discussions (similar to
 * tags, but selected by the creator)
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package Discussions
 */
class DiscussionCategory extends DataObject
{
    private static $db = array(
        "Title"         => "Varchar",
        "URLSegment"    => "Varchar"
    );

    private static $belongs_many_many = array(
        "Discussions"   => "Discussion"
    );

    private static $summary_fields = array(
        "Title",
        "URLSegment"
    );

    private static $default_sort = "Title ASC";

    public function Link($action = "category")
    {
        if (Discussion::useCMS()) {
            return Controller::join_links(
                $this->Parent()->Link($action),
                $this->URLSegment
            );
        } else {
            return Controller::join_links(
                Discussion_Controller::create()->Link($action),
                $this->URLSegment
            );
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName("ParentID");

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->URLSegment = ($this->URLSegment) ? $this->URLSegment : Convert::raw2url($this->Title);
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        if(Discussion::useCMS()) {
            return $this->Parent()->canEdit($member);
        } else {
            if (Permission::check(array("ADMIN", "DISCUSSIONS_MANAGER"))) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function canDelete($member = null)
    {
        if(Discussion::useCMS()) {
            return $this->Parent()->canDelete($member);
        } else {
            if (Permission::check(array("ADMIN", "DISCUSSIONS_MANAGER"))) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function canCreate($member = null)
    {
        if(Discussion::useCMS()) {
            return $this->Parent()->canCreate($member);
        } else {
            if (Permission::check(array("ADMIN", "DISCUSSIONS_MANAGER"))) {
                return true;
            } else {
                return false;
            }
        }
    }
}
