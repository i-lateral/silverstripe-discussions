<?php

/**
 * Form used when creating a new discussion
 *
 * @return NewDiscussionForm
 */
class DiscussionForm extends Form
{

    public function __construct($controller, $name)
    {
        // Get member and upload path
        $member = Member::currentUser();
        $categories = $controller->Categories();

        $fields = new FieldList(
            HiddenField::create("ID"),
            TextField::create(
                "Title",
                _t("Discussions.GiveDiscussionTitle", "Give your discussion a title")
            ),
            TextAreaField::create(
                "Content",
                _t("Discussions.AddSomeContent", "And some content")
            )->setRows(20)
        );

        if ($categories->exists()) {
            $fields->add(CheckboxSetField::create(
                "Categories",
                _t("Discussions.PostUnderCategory", "Or post this under a category? (optional)"),
                $categories->map()
            ));
        }

        $actions = new FieldList(
            FormAction::create("doPost")
                ->setTitle(_t("Discussions.StartDiscussion", "StartDiscussion"))
        );

        $validator = new RequiredFields(
            "Title",
            "Content"
        );

        parent::__construct(
            $controller,
            $name,
            $fields,
            $actions,
            $validator
        );
    }

    /**
     * Process the submitted form data and save to database
     *
     * @return Redirect
     */
    public function doPost(array $data, Form $form)
    {
        $discussion = null;
        $member = Member::currentUser();

        // Are we editing an existing discussion or creating a new one?
        if (isset($data['ID']) && $data['ID']) {
            // Check if we are editing or creating
            $existing = Discussion::get()->byID($data['ID']);
            if($existing && $existing->canEdit($member)) {
                $discussion = $existing;
            }
        } elseif ($member && $member->canStartDiscussions()) {
            $discussion = Injector::inst()->create("Discussion");
            $discussion->AuthorID = $member->ID;
            $discussion->ParentID = $this->controller->ID;
        }

        // If everything is ok, save our data
        if ($discussion) {
            $form->saveInto($discussion);
            $discussion->write();

            return $this->controller->redirect($discussion->Link("view"));
        } else {
            return $this->controller->httpError(500);
        }
    }
}
