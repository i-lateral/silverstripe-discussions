<?php

class DiscussionHolder_Controller extends Page_Controller
{
    public static $allowed_actions = array(
        'discussionForm',
        'my',
        'liked',
        'view',
        'start',
        'edit',
        'like',
        'report',
        'block',
        'remove',
        'tag',
        'category'
    );

    /**
     * Return the currently viewing tag from the URL
     *
     * @return string
     */
    public function getTag()
    {
        if ($this->request->param('Action') == 'tag') {
            $tag = $this->request->param('ID');
            $tag = ucwords(str_replace("-", " ", urldecode($tag)));
            return Convert::raw2xml($tag);
        } else {
            return "";
        }
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
        $tag = $this->getTag();
        $category = $this->getCategory();
        $member = Member::currentUser();
        $discussions_to_view = new ArrayList();

        if ($tag) {
            $SQL_tag = Convert::raw2sql($tag);

            $discussions = Discussion::get()
                ->filter("ParentID", $this->ID)
                ->where("\"Discussion\".\"Tags\" LIKE '%$SQL_tag%'");
        } elseif ($category) {
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
        $startForm = $this
            ->discussionForm()
            ->addExtraClass('forms');

        $vars = array(
            'Form' => $startForm
        );

        return $this->customise($vars);
    }

    /**
     * Edit an existing discussion
     */
    public function edit()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canEdit($member)) {
            $startForm = $this
                ->discussionForm($discussion)
                ->addExtraClass('forms');

            $vars = array(
                'Form' => $startForm
            );

            return $this->customise($vars);
        } else {
            return $this->redirect($this->Link());
        }
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
     * Report a particular discussion by ID. This will add a report object that
     * is associated with a discussion object and the user that reported it.
     *
     */
    public function report()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canView($member)) {
            $report = new ReportedDiscussion();
            $report->DiscussionID = $discussion->ID;
            $report->ReporterID = $member->ID;
            $report->write();

            $discussion->Reports()->add($report);
            $discussion->write();

            $this->setSessionMessage('message', _t("Discussions.Reported", "Reported") . " '{$discussion->Title}'");
        }

        return $this->redirect(Controller::join_links(
            $this->Link("view"),
            $discussion->ID
        ));
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
     * Block a particular member by ID.
     *
     */
    public function block()
    {
        $member = Member::currentUser();
        $block = Member::get()->byID($this->request->param("ID"));

        if ($block) {
            $member->BlockedMembers()->add($block);
            $member->write();
            $block->write();

            $this->setSessionMessage("message bad", _t("Discussions.Blocked", "Blocked") . " '{$block->FirstName} {$block->Surname}'");
        }

        return $this->redirectBack();
    }

    /**
     * Factory method for creating and configuring the setup form
     *
     * @return DiscussionForm
     */
    public function discussionForm($discussion = null)
    {
        $form = DiscussionForm::create($this, 'discussionForm', $discussion);

        if ($this->request->isPOST()) {
            $form->loadDataFrom($this->request->postVars());
        } elseif ($discussion != null && $discussion instanceof Discussion) {
            $form->loadDataFrom($discussion);
        }

        // Extension API
        $this->extend("updateDiscussionForm", $form);

        return $form;
    }
}
