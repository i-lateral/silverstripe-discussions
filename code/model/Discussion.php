<?php

class Discussion extends DataObject implements PermissionProvider {
    private static $db = array(
        "Title"     => "Varchar(99)",
        "Content"   => "Text",
        "Tags"      => "Text"
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
        "Link"          => "Varchar",
        "Liked"         => "Boolean",
        "Reported"      => "Boolean"
    );

    /**
     * Determine if this discussion has been liked by the current user
     *
     * @return boolean
     */
    public function getLiked() {
        $member_id = Member::currentUserID();

        if($this->LikedBy()->find("ID", $member_id))
            return true;
        else
            return false;
    }

    /**
     * Determine if this discussion has been reported by the current user
     *
     * @return boolean
     */
    public function getReported() {
        $member_id = Member::currentUserID();

        if($this->Reports()->find("ReporterID", $member_id))
            return true;
        else
            return false;
    }

    public function providePermissions() {
        return array(
            'DISCUSSIONS_MODERATION' => array(
                'name'      => 'Moderate discussions',
                'help'      => 'Moderate discussions created by users',
                'category'  => 'Discussions',
                'sort'      => 100
            ),
        );
    }

    /**
     * Returns the tags added to this discussion
     */
    public function TagsCollection() {
        $output = new ArrayList();

        if($this->Tags) {
            $tags = preg_split(" *, *", trim($this->Tags));

            $link = $this->Parent() ? $this->Parent()->Link('tag') : '';

            foreach($tags as $tag) {
                $output->push(new ArrayData(array(
                    'Tag' => Convert::raw2xml($tag),
                    'Link' => Controller::join_links($link, Convert::raw2url($tag)),
                    'URLTag' => Convert::raw2url($tag)
                )));
            }
        }

        return $output;
    }

    public function canView($member = null) {
        if(!$member) $member = Member::currentUser();
        $return = true;

        // Check if this author is on the user's block list
        if($member && $member->BlockedMembers()->exists()) {
            if($member->BlockedMembers()->find("ID", $this->AuthorID))
                $return = false;
        }

        return $return;
    }

    public function canEdit($member = null) {
        if(!$member) $member = Member::currentUser();

        // If admin, return true
        if(Permission::check("ADMIN"))
            return true;

        // If member is in discussions moderator groups, return true
        if($this->Parent()->ModeratorGroups()->filter("Members.ID", $member->ID)->exists())
            return true;

        // If member is the author
        if($this->Author()->ID == $member->ID)
            return true;

        return false;
    }

    public function canDelete($member = null) {
        if(!$member) $member = Member::currentUser();

        // If admin, return true
        if(Permission::check("ADMIN"))
            return true;

        // If member is in discussions moderator groups, return true
        if($this->Parent()->ModeratorGroups()->filter("Members.ID", $member->ID)->exists())
            return true;

        // If member is the author
        if($this->Author()->ID == $member->ID)
            return true;

        return false;
    }

    public function canCreate($member = null) {
        if(!$member) $member = Member::currentUser();

        return $this->Parent()->canStartDiscussions($member);
    }

    /**
     * Perform database cleanup when deleting
     */
    public function onBeforeDelete() {
        parent::onBeforeDelete();

        foreach($this->Comments() as $comment) {
            $comment->delete();
        }

        foreach($this->Files() as $file) {
            $file->delete();
        }
    }
}
