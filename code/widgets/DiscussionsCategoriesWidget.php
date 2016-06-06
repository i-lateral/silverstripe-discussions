<?php

if (class_exists('Widget')) {

    /**
     * A list of tags associated with discussions
     *
     * @package discussionforum
     */
    class DiscussionsCategoriesWidget extends Widget
    {

        public static $db = array(
            "Title"     => "Varchar"
        );

        public static $defaults = array(
            "Title"     => "Discussion Categories"
        );

        public static $cmsTitle = "Discussion Categories";
        public static $description = "Shows list of categories associated with this discussion holder";

        public function getCMSFields()
        {
            $fields = parent::getCMSFields();

            $fields->merge(
                new FieldList(
                    TextField::create("Title", _t("TagCloudWidget.TILE", "Title"))
                )
            );

            $this->extend('updateCMSFields', $fields);

            return $fields;
        }

        public function Title()
        {
            return $this->Title ? $this->Title : "Discussion Categories";
        }

        /**
         * Get all Discussion Groups and set relevant links for each
         *
         * @return DataList
         */
        public function getCategories()
        {
            // Get the current associated discussion holder
            $discussion_holder = DiscussionHolder::get()
                ->filter("SideBar.ID", $this->ParentID)
                ->first();

            return $discussion_holder->Categories();
        }
    }
}
