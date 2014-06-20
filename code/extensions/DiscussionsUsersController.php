<?php
/**
 * Extension for Users Account Controller that provides abilities to
 * edit avatar image and nickname, as well as adds notification settings
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package discussions
 */
class DiscussionsUsersController extends Extension {

    private static $allowed_actions = array(
        "notificationsettings",
        "NotificationForm"
    );

    public function notificationsettings() {
        $member = Member::currentUser();

        $this
            ->owner
            ->customise(array(
                "ClassName" => "AccountPage",
                "Title" => _t('Discussions.NotificationSettings','Notification Settings'),
                "Form"  => $this->owner->NotificationForm()->loadDataFrom($member)
            ));

        $this
            ->owner
            ->extend("onBeforeNotificationSettings");

        return $this
            ->owner
            ->renderWith(array(
                "Users_Account",
                "Users",
                "Page"
            ));
    }

    /**
     * Factory for generating a change password form. The form can be expanded
     * using an extension class and calling the updateChangePasswordForm method.
     *
     * @return Form
     */
    public function NotificationForm() {
        $fields = FieldList::create(
            HiddenField::create("ID"),
            CheckboxField::create(
                "RecieveCommentEmails",
                _t("Discussions.RecieveCommentEmails","Recieve emails when one of my discussions is replied to")
            ),
            CheckboxField::create(
                "RecieveNewDiscussionEmails",
                _t("Discussions.ReveiveNewDiscussionEmails","Recieve emails when a new discussion is started")
            ),
            CheckboxField::create(
                "RecieveLikedEmails",
                _t("Discussions.ReveiveLikedEmails","Recieve emails when one of my discussions is liked")
            ),
            CheckboxField::create(
                "RecieveLikedReplyEmails",
                _t("Discussions.ReveiveLikedReplyEmails","Recieve emails when a discussion I like is replied to")
            )
        );

        $actions = FieldList::create(
            LiteralField::create(
                "CancelLink",
                '<a href="' . $this->owner->Link() . '" class="btn btn-red">'. _t("Users.CANCEL", "Cancel") .'</a>'
            ),
            FormAction::create("doSaveNotificationSettings", _t("Discussions.Save","Save"))
                ->addExtraClass("btn")
                ->addExtraClass("btn-green")
        );

        $form = Form::create($this->owner,"NotificationForm", $fields, $actions);

        $this
            ->owner
            ->extend("updateNotificationForm", $form);

        return $form;
    }

    /**
     * Register a new member
     *
     * @param array $data User submitted data
     * @param Form $form The used form
     */
    function doSaveNotificationSettings($data, $form) {
        $filter = array();
        $member = Member::get()->byID($data["ID"]);

        // Check that a mamber isn't trying to mess up another users profile
        if(Member::currentUserID() && $member->canEdit(Member::currentUser())) {
            // Save member
            $form->saveInto($member);
            $member->write();
            $this->owner->setSessionMessage(
                "message success",
                _t("Discussions.NotificationSettingsUpdated","Notification settings updated")
            );

            return $this->owner->redirect($this->owner->Link());
        } else
            $this->owner->setSessionMessage(
                "message error",
                _t("Discussions.CannotEditAccount","You cannot edit this account")
            );

        return $this->owner->redirectBack();
    }

    /**
     * Add fields used by this module to the profile editing form
     *
     */
    public function updateEditAccountForm($form) {
        $id = Member::currentUserID();



        // Add Nickname Field
        $form
            ->Fields()
            ->insertBefore(TextField::create("Nickname"),"FirstName");

        $avatar_field = UploadField::create("Avatar",_t("Discussions.ChooseProfileImage","Choose your profile image"))
            ->setFolderName("profile/{$id}")
            ->setCanAttachExisting(false)
            ->setCanPreviewFolder(false)
            ->setConfig('fileEditFields', FieldList::create())
            ->setConfig('fileEditActions', FieldList::create())
            ->setForm($form);

        $avatar_field->overwriteWarning = false;

        $avatar_field
            ->getValidator()
            ->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

        $form
            ->Fields()
            ->insertBefore($avatar_field, "Nickname");

        $form
            ->Fields()
            ->add(TextField::create("URL","URL"));
    }

    /**
     * Add commerce specific links to account menu
     *
     */
    public function updateAccountMenu($menu) {
        $menu->add(new ArrayData(array(
            "ID"    => 10,
            "Title" => _t('Discussions.NotificationSettings','Notification Settings'),
            "Link"  => $this->owner->Link("notificationsettings")
        )));
    }
}
