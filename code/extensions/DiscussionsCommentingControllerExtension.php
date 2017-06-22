<?php

/**
 * Extension to Commenting Controller to allow customisation of form
 *
 */
class DiscussionCommentControllerExtension extends Extension {

    public function alterCommentForm($form) {
        
        if ($this->owner->getBaseClass() == "Discussion") {
            $fields = $form->Fields();
            
            $fields->removeByName("NameView");
            $fields->removeByName("URL");
        }
    }

}
