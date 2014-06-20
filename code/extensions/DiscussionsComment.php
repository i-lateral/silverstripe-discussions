<?php

class DiscussionsComment extends DataExtension {
    // Extend default member options
    public function canView(Member $member) {
        if(!$member) $member = Member::currentUser();

        // Check if this author is on the user's block list
        if($member->BlockedMembers()->exists()) {
            if($member->BlockedMembers()->find("ID", $this->owner->AuthorID))
                return false;
        }

        return true;
    }

    public function onBeforeWrite() {
        if($this->owner->BaseClass == "Discussion") {
            $member = Member::currentUser();

            if($this->owner->ID == 0 && $member->RecieveCommentEmails) {
                // Setup notification emails for new comments
                $parent = Discussion::get()->byID($this->owner->ParentID);
                $page = $parent->Parent();

                $subject = "New reply on your discussion";
                $template = 'NewReplyEmail';
                $vars = array(
                    "Title" => $parent->Title,
                    'Link' => Controller::join_links(
                        $page->Link("view"),
                        $parent->ID,
                        "#comments-holder"
                    )
                );

                if($member->Email && $member->ID != $this->owner->AuthorID) {
                    $email = new Email(null, $member->Email, $subject);
                    $email->setTemplate($template);
                    $email->populateTemplate($vars);
                    $email->send();
                }
            }
        }
    }
}
