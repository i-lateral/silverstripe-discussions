<?php

if (class_exists('Widget')) {

    /**
     * Generates a discussion menu, so you can see your liked comments
     * your personal discussions, etc
     *
     * @package discussions
     */
    class DiscussionsMenuWidget extends Widget
    {

        public static $db = array(
            "Title"     => "Varchar"
        );

        public static $defaults = array(
            "Title"     => "View Discussions"
        );

        public static $cmsTitle = "Discussion Menu";
        public static $description = "Shows a user menu for this discussion holder";

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
            return $this->Title ? $this->Title : "Discussion Menu";
        }

        /**
         * Get all Discussion Groups and set relevant links for each
         *
         * @return DataList
         */
        public function getMenu()
        {
            // Get the current associated discussion holder
            $discussion_holder = DiscussionHolder::get()
                ->filter("SideBar.ID", $this->ParentID)
                ->first();

            $menu = new ArrayList();

            $menu->add(new ArrayData(array(
                "ID"    => 0,
                "Title" => _t('Discussions.All', "All"),
                "Link"  => $discussion_holder->Link()
            )));

            $menu->add(new ArrayData(array(
                "ID"    => 10,
                "Title" => _t('Discussions.Liked', "Liked"),
                "Link"  => $discussion_holder->Link("liked")
            )));

            $menu->add(new ArrayData(array(
                "ID"    => 20,
                "Title" => _t('Discussions.Started', "I started"),
                "Link"  => $discussion_holder->Link("my")
            )));

            $this->extend("updateMenu", $menu);

            return $menu->sort("ID", "ASC");
        }
    }
}
