<?php

class Discussion extends DataObject implements PermissionProvider
{
    private static $db = array(
        "Title"     => "Varchar(99)",
        "Content"   => "Text",
        "Pinned"    => "Boolean"
    );

    private static $has_one = array(
        "Author"    => "Member",
        "Parent"    => "DiscussionHolder"
    );

    private static $many_many = array(
        "Categories"=> "DiscussionCategory"
    );

    private static $belongs_many_many = array(
        "LikedBy"   => "Member"
    );

    private static $default_sort = array(
        "Pinned" => "DESC",
        "Created" => "DESC"
    );

    private static $casting = array(
        "Link"      => "Varchar",
        "Liked"     => "Boolean"
    );
    
    private static $summary_fields = array(
        "Title"
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
     * Customised comments interface for discussions, this allows us
     * to customise commenting explicitly for discussions without
     * altering commenting functionality for the rest of the site.
     * 
     * Includes the CommentAddForm and the composition of the comments
     * display.
     *
     * To customize the html see templates/CommentInterface.ss or
     * extend this function with your own extension.
     *
     */
    public function CommentsForm()
    {
        // Check if enabled
        $enabled = $this->getCommentsEnabled();

        if ($enabled && $this->owner->getCommentsOption('include_js')) {
            Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
            Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
            Requirements::javascript(THIRDPARTY_DIR . '/jquery-validate/lib/jquery.form.js');
            Requirements::javascript(COMMENTS_THIRDPARTY . '/jquery-validate/jquery.validate.min.js');
            Requirements::add_i18n_javascript('comments/javascript/lang');
            Requirements::javascript('comments/javascript/CommentsInterface.js');
        }

        $controller = CommentingController::create();
        $controller->setOwnerRecord($this);
        $controller->setBaseClass($this->ClassName);
        $controller->setOwnerController(Controller::curr());

        $moderatedSubmitted = Session::get('CommentsModerated');
        Session::clear('CommentsModerated');

        $form = ($enabled) ? $controller->CommentsForm() : false;

        // Customise the comments form to be more suitable for
        // discussions. We remove the URL field and set the name
        // To use the member's nickname (and be hidden)
        if ($form) {
            $member = Member::currentUser();
            $fields = $form->Fields();
            
            $fields->removeByName("URL");
            $fields->removeByName("NameView");

            $name_field = HiddenField::create("Name");

            if ($member->Nickname) {
                $name_field->setValue($member->Nickname);
            } else {
                $name_field->setValue($member->getName());                
            }

            $fields->push($name_field);

            $this->extend("updateDiscussionCommentsForm", $form);
        }

        // a little bit all over the show but to ensure a slightly easier upgrade for users
        // return back the same variables as previously done in comments
        return $this
            ->owner
            ->customise(array(
                'AddCommentForm' => $form,
                'ModeratedSubmitted' => $moderatedSubmitted,
            ))
            ->renderWith('DiscussionsCommentsInterface');
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
        
        // If the Author disallow
        if ($member->ID == $this->AuthorID) {
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

        if (!$member) {
            return false;
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

        if (!$member) {
            return false;
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
     * Can the member pin this post? By default posts can only be pinned
     * by moderators, managers and admins
     * 
     * @param $member Member object
     * @return Boolean
     */
    public function canPin($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if (!$member) {
            return false;
        }

        // If correct permission, return true
        if (Permission::checkMember($member, array("ADMIN", "DISCUSSIONS_MODERATION", "DISCUSSIONS_MANAGER"))) {
            return true;
        }

        // If member is in discussions moderator groups, return true
        if ($this->Parent()->ModeratorGroups()->filter("Members.ID", $member->ID)->exists()) {
            return true;
        }

        return false;
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
