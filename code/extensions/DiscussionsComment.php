<?php

class DiscussionsComment extends DataExtension
{
    // Extend default member options
    public function canView(Member $member)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        // Check if this author is on the user's block list
        if ($member->BlockedMembers()->exists()) {
            if ($member->BlockedMembers()->find("ID", $this->owner->AuthorID)) {
                return false;
            }
        }

        return true;
    }

    public function onBeforeWrite()
    {
        if ($this->owner->BaseClass == "Discussion" && $this->owner->ID == 0) {
            $discussion = Discussion::get()
                ->byID($this->owner->ParentID);

            $discussion_author = $discussion->Author();
            $holder = $discussion->Parent();

            $author = Member::get()
                ->byID($this->owner->AuthorID);

            // Get our default email from address
            if (DiscussionHolder::config()->send_emails_from) {
                $from = DiscussionHolder::config()->send_email_from;
            } else {
                $from = Email::config()->admin_email;
            }

            // Vars for the emails
            $vars = array(
                "Title" => $discussion->Title,
                "Author" => $author,
                "Comment" => $this->owner->Comment,
                'Link' => Controller::join_links(
                    $holder->Link("view"),
                    $discussion->ID,
                    "#comments-holder"
                )
            );

            // Send email to discussion owner
            if (
                $discussion_author &&
                $discussion_author->Email &&
                $discussion_author->RecieveCommentEmails &&
                ($discussion_author->ID != $this->owner->AuthorID)
            ) {
                $subject = _t(
                    "Discussions.NewCreatedReplySubject",
                    "{Nickname} replied to your discussion",
                    null,
                    array("Nickname" => $author->Nickname)
                );

                $email = new Email($from, $discussion_author->Email, $subject);
                $email->setTemplate('NewCreatedReplyEmail');
                $email->populateTemplate($vars);
                $email->send();
            }

            // Send to anyone who liked this, if they want notifications
            foreach ($discussion->LikedBy() as $liked) {
                if ($liked->RecieveLikedReplyEmails && $liked->Email && ($liked->ID != $author->ID)) {
                    $subject = _t(
                        "Discussions.NewLikedReplySubject",
                        "{Nickname} replied to your liked discussion",
                        null,
                        array("Nickname" => $author->Nickname)
                    );

                    $email = new Email($from, $liked->Email, $subject);
                    $email->setTemplate('NewLikedReplyEmail');
                    $email->populateTemplate($vars);
                    $email->send();
                }
            }
        }
    }
}
