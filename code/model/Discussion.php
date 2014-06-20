<?php

class Discussion extends DataObject {
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

    public function Link($action = null) {
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

    /**
     * Overwrite the default comments form so we can tweak a bit
     *
     * @see docs/en/Extending
     */
    public function CommentsForm() {
        if(Commenting::has_commenting($this->ownerBaseClass) && Commenting::get_config_value($this->ownerBaseClass, 'include_js')) {
            Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
            Requirements::javascript(THIRDPARTY_DIR . '/jquery-validate/lib/jquery.form.js');
            Requirements::javascript(THIRDPARTY_DIR . '/jquery-validate/jquery.validate.pack.js');
            Requirements::javascript('comments/javascript/CommentsInterface.js');
        }

        $interface = new SSViewer('DiscussionsCommentsInterface');
        $enabled = (!$this->attachedToSiteTree() || $this->owner->ProvideComments) ? true : false;

        // do not include the comments on pages which don't have id's such as security pages
        if($this->owner->ID < 0) return false;

        $controller = new CommentingController();
        $controller->setOwnerRecord($this);
        $controller->setBaseClass($this->ClassName);
        $controller->setOwnerController(Controller::curr());

        $moderatedSubmitted = Session::get('CommentsModerated');
        Session::clear('CommentsModerated');

        // Tweak the comments form a bit, so it is more user friendly
        if($enabled) {
            $form = $controller->CommentsForm();

            $form->Fields()->removeByName("Comment");

            $comment_field = TextareaField::create("Comment", "")
                ->setCustomValidationMessage(_t('CommentInterface.COMMENT_MESSAGE_REQUIRED', 'Please enter your comment'))
                ->setAttribute('data-message-required', _t('CommentInterface.COMMENT_MESSAGE_REQUIRED', 'Please enter your comment'));

            if($form->Fields()->dataFieldByName("NameView")) {
                $form
                    ->Fields()
                    ->insertBefore($comment_field, "NameView");
            } else {
                $form
                    ->Fields()
                    ->insertBefore($comment_field, "Name");
            }

        } else
            $form = false;

        // a little bit all over the show but to ensure a slightly easier upgrade for users
        // return back the same variables as previously done in comments
        return $interface->process(new ArrayData(array(
            'CommentHolderID'           => Commenting::get_config_value($this->ClassName, 'comments_holder_id'),
            'PostingRequiresPermission' => Commenting::get_config_value($this->ClassName, 'required_permission'),
            'CanPost'                   => Commenting::can_member_post($this->ClassName),
            'RssLink'                   => "CommentingController/rss",
            'RssLinkPage'               => "CommentingController/rss/". $this->ClassName . '/'.$this->ID,
            'CommentsEnabled'           => $enabled,
            'Parent'                    => $this,
            'AddCommentForm'            => $form,
            'ModeratedSubmitted'        => $moderatedSubmitted,
            'Comments'                  => $this->getComments()
        )));
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
     * Check if members who liked this would like a notification
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();

        foreach($this->LikedBy() as $member) {

        }
    }

    /**
     * Perform database cleanup when deleting
     */
    public function onBeforeDelete() {
        parent::onBeforeDelete();

        foreach($this->getComments() as $comment) {
            $comment->delete();
        }
    }
}
