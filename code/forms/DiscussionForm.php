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
                _t("Discussions.GiveTitle", "Give your discussion a title")
            ),
            TextAreaField::create(
                "Content",
                _t("Discussions.AddContent", "And some content (optional)")
            ),
            TextField::create(
                "Tags",
                _t("Discussions.AddTags", "Finally, add some tags (optional)")
            )->setAttribute("placeholder", "Tag 1, Tag 2")
        );

        if ($categories->exists()) {
            $fields->add(CheckboxsetField::create(
                "Categories",
                _t("Discussions.Categories", "Or Post this under a category? (optional)"),
                $categories->map()
            ));
        }

        $actions = new FieldList(
            FormAction::create("doPost")
                ->setTitle(_t("Discussions.Post", "Post"))
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
    public function doPost(array $data, Form $form)
    {
        $discussion = null;
        $new = Discussion::create();
        $member = Member::currentUser();

        // Are we editing an existing discussion or creating a new one?
        if (isset($data['ID']) && $data['ID']) {
            // Check if we are editing or creating
            $existing = Discussion::get()->byID($data['ID']);
            if($existing && $existing->canEdit($member)) {
                $discussion = $existing;
            }
        } elseif ($new->canCreate($member)) {
            $discussion = $new;
        }

        // If everything is ok, save our data
        if ($discussion) {
            $form->saveInto($discussion);
            $discussion->AuthorID = $member->ID;
            
            $page = DiscussionHolder::get()->byID($this->controller->ID);
            $discussion->ParentID = $page->ID;

            $form->saveInto($discussion);

            $discussion->write();

            return $this->controller->redirect($discussion->Link("view"));
        } else {
            return $this->controller->httpError(500);
        }
    }
}
