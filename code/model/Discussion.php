<?php

class Discussion extends DataObject implements PermissionProvider
{
    private static $db = array(
        "Title"     => "Varchar(99)",
        "Content"   => "Text"
    );

    private static $has_one = array(
        "Author"    => "Member",
        "Parent"    => "DiscussionHolder"
    );

    private static $has_many = array(
        "Reports"   => "ReportedDiscussion"
    );

    private static $many_many = array(
        "Categories"=> "DiscussionCategory"
    );

    private static $belongs_many_many = array(
        "LikedBy"   => "Member"
    );

    private static $default_sort = "Created DESC";

    private static $casting = array(
        "Link"      => "Varchar",
        "Liked"     => "Boolean",
        "Reported"  => "Boolean"
    );
    
    private static $summary_fields = array(
        "Title",
        "Reports.Count"
    );
    
    public function providePermissions()
    {
        return array(
            'DISCUSSIONS_POSTING' => array(
                'name'      => 'Post Discussions',
                'help'      => 'Can post new discussions and reply to discussions',
                'category'  => 'Discussions',
                'sort'      => 90
            ),
            'DISCUSSIONS_MODERATION' => array(
                'name'      => 'Moderate discussions',
                'help'      => 'Moderate discussions created by users',
                'category'  => 'Discussions',
                'sort'      => 100
            ),
            'DISCUSSIONS_MANAGER' => array(
                'name'      => 'Manage discussion settings',
                'help'      => 'Manage Discussions and Categories membership',
                'category'  => 'Discussions',
                'sort'      => 110
            ),
        );
    }
    
    /**
     * Address to send notification emails from
     * 
     * @var String
     * @config
     */
    private static $send_emails_from;

    public function Link($action = "view")
    {
        return Controller::join_links(
            $this->Parent()->Link($action),
            $this->ID
        );
    }

    /**
     * Determine if this discussion has been liked by the current user
     *
     * @return boolean
     */
    public function getLiked($member = null)
    {
        if ($member && $member->ID) {
            $member_id = $member->ID;
        } else {
            $member_id = Member::currentUserID();
        }
        
        if ($this->LikedBy()->find("ID", $member_id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if this discussion has been reported by the current user
     *
     * @return boolean
     */
    public function getReported()
    {
        $member_id = Member::currentUserID();

        if ($this->Reports()->find("ReporterID", $member_id)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Can the member view this discussion?
     * 
     * @param $member Member object
     * @return Boolean
     */
    public function canView($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }
        $return = true;

        // Check if this author is on the user's block list
        if ($member && $member->BlockedMembers()->exists()) {
            if ($member->BlockedMembers()->find("ID", $this->AuthorID)) {
                $return = false;
            }
        }

        return $return;
    }
    
    /**
     * Can the member create a discussion?
     * 
     * @param $member Member object
     * @return Boolean
     */
    public function canCreate($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if (!$member) {
            return false;
        }

        // If admin, return true
        if (Permission::check(array("ADMIN", "DISCUSSIONS_POSTING"))) {
            return true;
        }

        // If member is in discussions moderator groups, return true
        if ($this->Parent()->PosterGroups()->filter("Members.ID", $member->ID)->exists()) {
            return true;
        }

        return false;
    }
    
    /**
     * Can the member like this post?
     * 
     * @param $member Member object
     * @return Boolean
     */
    public function canLike($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if (!$member) {
            return false;
        }
        
        // If the Author or already liked, disallow
        if ($member->ID == $this->AuthorID || $this->getLiked($member)) {
            return false;
        }

        // If correct permission, return true
        if (Permission::check(array("ADMIN", "DISCUSSIONS_MODERATION"))) {
            return true;
        }

        // If member is in discussions moderator groups, return true
        if ($this->Parent()->PosterGroups()->filter("Members.ID", $member->ID)->exists()) {
            return true;
        }

        return false;
    }
    
    public function canEdit($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        // If admin, return true
        if (Permission::check(array("ADMIN", "DISCUSSIONS_POSTING"))) {
            return true;
        }

        // If member is in discussions moderator groups, return true
        if ($this->Parent()->ModeratorGroups()->filter("Members.ID", $member->ID)->exists()) {
            return true;
        }

        // If member is the author
        if ($this->Author()->ID == $member->ID) {
            return true;
        }

        return false;
    }

    public function canDelete($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        // If admin, return true
        if (Permission::check("ADMIN")) {
            return true;
        }

        // If member is in discussions moderator groups, return true
        if ($this->Parent()->ModeratorGroups()->filter("Members.ID", $member->ID)->exists()) {
            return true;
        }

        // If member is the author
        if ($this->Author()->ID == $member->ID) {
            return true;
        }

        return false;
    }

    /**
     * Check if members who liked this would like a notification
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        foreach ($this->LikedBy() as $member) {
        }
    }

    /**
     * Perform database cleanup when deleting
     */
    public function onBeforeDelete()
    {
        parent::onBeforeDelete();

        foreach ($this->getComments() as $comment) {
            $comment->delete();
        }
    }
}
