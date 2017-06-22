<?php

class DiscussionHolder_Controller extends Page_Controller
{
    public static $allowed_actions = array(
        'my',
        'liked',
        'view',
        'start',
        'edit',
        'like',
        'block',
        'remove',
        'category',
        'DiscussionForm',
    );

    /**
     * Permissions check to see if the current user can start
     * discussions
     *
     * @return Boolean
     */
    public function canStartDiscussions()
    {
        $member = Member::currentUser();

        if (!$member) {
            return false;
        }

        return $member->canStartDiscussions();
    }

    /**
     * Return the currently viewing group from the URL
     *
     * @return string
     */
    public function getCategory()
    {
        if ($this->request->param('Action') == 'category') {
            $id = $this->request->param('ID');
            return $this
                ->Categories()
                ->filter("URLSegment", $id)
                ->first();
        } else {
            return "";
        }
    }

    /**
     * Overwrite the default template if we are looking at specific
     * actions
     *
     * @return string
     */
    public function getTitle()
    {
        $action = $this->request->param('Action');

        if ($action == "liked") {
            return _t("Discussions.LikedTitle", "Discussions I have liked");
        } elseif ($action == "my") {
            return _t("Discussions.MyTitle", "Discussions I have started");
        } else {
            return $this->data()->Title;
        }
    }

    /**
     * Get a filtered list of discussions by can view rights.
     *
     * This method is basically the meat and potates of this module and does most
     * of the crunch work of finding the relevant discussion list and ensuring
     * the current user is allowed to view it.
     *
     * @return DataList
     */
    public function ViewableDiscussions()
    {
        $category = $this->getCategory();
        $member = Member::currentUser();
        $discussions_to_view = new ArrayList();

        if ($category) {
            $discussions = Discussion::get()
                ->filter(array(
                    "ParentID" => $this->ID,
                    "Categories.ID:ExactMatch" => $category->ID
                ));
        } elseif ($this->request->param('Action') == 'liked') {
            $discussions = $member
                ->LikedDiscussions();
        } elseif ($this->request->param('Action') == 'my') {
            $discussions = Discussion::get()
                ->filter(array(
                    "ParentID" => $this->ID,
                    "AuthorID" => $member->ID
                ));
        } else {
            $discussions = $this->Discussions();
        }

        foreach ($discussions as $discussion) {
            if ($discussion->canView($member)) {
                $discussions_to_view->add($discussion);
            }
        }

        $this->extend("updateViewableDiscussions", $discussions_to_view);

        return new PaginatedList($discussions_to_view, $this->request);
    }

    /**
     * Start a new discussion
     *
     */
    public function start()
    {
        $member = Member::currentUser();
        
        return $this->customise(array(
            "Form" => $this->DiscussionForm()->addExtraClass('forms')
        ));
    }

    /**
     * Edit an existing discussion
     */
    public function edit()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        // If not discussion, return a 404
        if (!$discussion) {
            return $this->httpError(404);
        }

        // If the current user cannot edit, return a 500
        if (!$discussion->canEdit($member)) {
            return $this->httpError(500);
        }

        $form = $this
            ->DiscussionForm($discussion)
            ->addExtraClass('forms');

        // Rename post button on edit
        $post_button  = $form
            ->Actions()
            ->dataFieldByName("action_doPost");
        
        if ($post_button) {
            $post_button->setTitle(_t("Discussions.Update", "Update"));
        }

        $form->loadDataFrom($discussion);

        $vars = array(
            'Form' => $form
        );

        return $this->customise($vars);
    }

    /**
     * View a particular discussion by ID, if the user has the rights to do so
     *
     */
    public function view()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canView($member)) {
            $date = new SS_Datetime();

            $vars = array("Discussion"  => $discussion);

            $this->extend("updateViewVars", $vars);

            return $this->customise($vars);
        } else {
            return $this->redirect($this->Link());
        }
    }

    /**
     * Remove a particular discussion by ID, if the user has the rights to do so
     *
     */
    public function remove()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canView($member)) {
            $this->setSessionMessage('message', _t("Discussions.Deleted", "Deleted") . " '{$discussion->Title}'");
            $discussion->delete();
        }

        return $this->redirect($this->Link());
    }

    /**
     * Like a particular discussion by ID
     *
     */
    public function like()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canView($member)) {
            $this->setSessionMessage("message good", _t("Discussions.Liked", "Liked") . " '{$discussion->Title}'");
            $member->LikedDiscussions()->add($discussion);
            $member->write();

            $author = $discussion->Author();

            // Send a notification (if the author wants it)
            if ($author && $author->RecieveLikedEmails && $author->Email && ($member->ID != $author->ID)) {
                if (DiscussionHolder::config()->send_email_from) {
                    $from = DiscussionHolder::config()->send_email_from;
                } else {
                    $from = Email::config()->admin_email;
                }

                $subject = _t(
                    "Discussions.LikedDiscussionSubject",
                    "{Nickname} liked your discussion",
                    null,
                    array("Nickname" => $member->Nickname)
                );

                // Vars for the emails
                $vars = array(
                    "Title" => $discussion->Title,
                    "Member" => $member,
                    'Link' => Controller::join_links(
                        $this->Link("view"),
                        $discussion->ID,
                        "#comments-holder"
                    )
                );

                $email = new Email($from, $author->Email, $subject);
                $email->setTemplate('LikedDiscussionEmail');
                $email->populateTemplate($vars);
                $email->send();
            }
        }

        return $this->redirect(Controller::join_links($this->Link("view"), $discussion->ID));
    }

    /**
     * Factory method for creating and configuring the setup form
     *
     * @return DiscussionForm
     */
    public function DiscussionForm($discussion = null)
    {
        $form = DiscussionForm::create(
            $this,
            'DiscussionForm',
            $discussion
        );

        // Extension API
        $this->extend("updateDiscussionForm", $form);

        return $form;
    }
}