<?php

class Discussion_Controller extends Controller
{
    
    /**
     * The URL this controller can be accessed from.
     * 
     * @var String
     * @config
     */
    private static $url_segment = "discuss";
    
    public static $allowed_actions = array(
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
        'category',
        'DiscussionForm',
    );

    /**
     * Return the currently viewing tag from the URL
     *
     * @return string
     */
    public function getCurrentTag()
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
     * Return the currently viewing category from the URL
     *
     * @return string
     */
    public function getCurrentCategory()
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
     * Get the relative URL for this controller
     * 
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::join_links(
            $this->config()->url_segment,
            $action
        );
    }

    /**
     * Get the absolute URL for this controller
     * 
     * @return string
     */
    public function AbsoluteLink($action = null)
    {
        return Controller::join_links(
            Director::absoluteBaseURL(),
            $this->Link($action)
        );
    }
    
    /**
     * Get a list of all categories
     * 
     * @return DataList
     */
    public function Categories()
    {
        return DiscussionCategory::get();
    }
    
    /**
     * Get a list of all discussions
     * 
     * @return DataList
     */
    public function Discussions()
    {
        return Discussion::get();
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
    
    public function canStartDiscussions() {
        if (Permission::check(array("ADMIN", "DISCUSSIONS_POSTING"))) {
            return true;
        } else {
            return false;
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
        $tag = $this->getCurrentTag();
        $category = $this->getCurrentCategory();
        $member = Member::currentUser();
        $discussions_to_view = new ArrayList();

        if ($tag) {
            $SQL_tag = Convert::raw2sql($tag);

            $discussions = Discussion::get();
            $discussions = $discussions->filter("ParentID", $this->ID);
            
            $discussions = $discussions->where("\"Discussion\".\"Tags\" LIKE '%$SQL_tag%'");
        } elseif ($category) {
            $filter = array(
                "Categories.ID:ExactMatch" => $category->ID,
                "ParentID" => $this->ID
            );
            
            $discussions = Discussion::get()->filter($filter);
        } elseif ($this->request->param('Action') == 'liked') {
            $discussions = $member->LikedDiscussions();
        } elseif ($this->request->param('Action') == 'my') {
            $filter = array(
                "AuthorID" => $member->ID,
                "ParentID" => $this->ID
            );
            
            $discussions = Discussion::get()->filter($filter);
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
     * Default action
     *
     */
    public function index()
    {
        $this->extend("onBeforeIndex");
        
        return $this->renderWith(array(
            "Discussion",
            "Page"
        ));
    }
    
    /**
     * Action to display a category
     *
     */
    public function category()
    {
        $this->extend("onBeforeCategory");
        
        return $this->renderWith(array(
            "Discussion_category",
            "Discussion",
            "Page"
        ));
    }
    
    /**
     * Action to display a category
     *
     */
    public function tag()
    {
        $this->extend("onBeforeTag");
        
        return $this->renderWith(array(
            "Discussion_tag",
            "Discussion",
            "Page"
        ));
    }
    
    /**
     * Start a new discussion
     *
     */
    public function start()
    {
        $form = $this
            ->DiscussionForm()
            ->addExtraClass('forms');

        $vars = array(
            'Form' => $form
        );

        $this->customise($vars);
        
        $this->extend("onBeforeStart");
        
        return $this->renderWith(array(
            "Discussion_start",
            "Discussion",
            "Page"
        ));
    }

    /**
     * Edit an existing discussion
     * 
     */
    public function edit()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canEdit($member)) {
            $form = $this
                ->DiscussionForm()
                ->loadDataFrom($discussion)
                ->addExtraClass('forms');

            $vars = array(
                'Form' => $form
            );

            $this->customise($vars);
            
            $this->extend("onBeforeEdit");
            
            return $this->renderWith(array(
                "Discussion_edit",
                "Discussion",
                "Page"
            ));
        } else {
            return $this->httpError(404);
        }
    }

    /**
     * View a particular discussion by ID, if the user has the rights to
     * do so
     *
     */
    public function view()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canView($member)) {
            $date = new SS_Datetime();

            $vars = array("Discussion" => $discussion);

            $this->customise($vars);
            
            $this->extend("onBeforeView");
            
            return $this->renderWith(array(
                "Discussion_view",
                "Discussion",
                "Page"
            ));
        } else {
            return $this->httpError(404);
        }
    }

    /**
     * Remove a particular discussion by ID, if the user has the rights
     * to do so
     *
     */
    public function remove()
    {
        $member = Member::currentUser();
        $discussion = Discussion::get()->byID($this->request->param("ID"));

        if ($discussion && $discussion->canDelete($member)) {
            $this->setSessionMessage('message', _t("Discussions.Deleted", "Deleted") . " '{$discussion->Title}'");
            $discussion->delete();
        }

        return $this->redirectBack();
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
    public function DiscussionForm()
    {
        $form = DiscussionForm::create($this,'DiscussionForm');

        // Extension API
        $this->extend("updateDiscussionForm", $form);

        return $form;
    }
}
