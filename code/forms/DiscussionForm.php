<?php

/**
 * Form used when creating a new discussion
 *
 * @return NewDiscussionForm
 */
class DiscussionForm extends Form
{

    public function __construct($controller, $name, $discussion = null)
    {
        // Get member and upload path
        $member = Member::currentUser();

        $fields = new FieldList(
            HiddenField::create("ID"),
            TextField::create("Title", _t("Discussions.GiveTitle", "Give your discussion a title")),
            TextAreaField::create("Content", _t("Discussions.AddContent", "And some content (optional)")),
            TextField::create("Tags", _t("Discussions.AddTags", "Finally, add some tags (optional)"))
                ->setAttribute("placeholder", "Tag 1, Tag 2")
        );

        if ($controller->Categories()->exists()) {
            $fields->add(CheckboxsetField::create(
                "Categories",
                _t("Discussions.Categories", "Or Post this under a category? (optional)"),
                $controller->Categories()->map()
            ));
        }

        $actions = new FieldList(
            FormAction::create("post")->setTitle(_t("Discussions.Post", "Post"))
        );

        $validator = new RequiredFields(
            "Title",
            "Content"
        );

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    /**
     * Process the submitted form data and save to database
     *
     * @return Redirect
     */
    public function post(array $data, Form $form)
    {
        $discussion = null;
        $page = DiscussionHolder::get()->byID($this->controller->ID);
        $member = Member::currentUser();

        if ($this->controller->canStartDiscussions($member)) {
            // Check if we are editing or creating
            if (isset($data['ID']) && $data['ID']) {
                $discussion = Discussion::get()->byID($data['ID']);
            }

            if (!$discussion || $discussion == null) {
                $discussion = Discussion::create();
            }

            $form->saveInto($discussion);
            $discussion->AuthorID = $member->ID;
            $discussion->ParentID = $page->ID;

            $form->saveInto($discussion);

            $discussion->write();

            $discussion_url = Controller::join_links(
                $this->controller->Link("view"),
                $discussion->ID
            );

            return $this->controller->redirect($discussion_url);
        } else {
            return $this->controller->httpError(404);
        }
    }
}
