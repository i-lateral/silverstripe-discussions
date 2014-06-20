<?php

/**
 * Page type responsible for holding "discussions" and their comments
 *
 */
class DiscussionHolder extends Page {
    private static $icon = "discussions/images/speechbubble.png";

    private static $db = array();

    private static $has_many = array(
        "Discussions"   => "Discussion",
        "Categories"    => "DiscussionCategory"
    );

    private static $many_many = array(
        'ModeratorGroups' => 'Group',
        'PosterGroups' => 'Group'
    );

    private static $allowed_children = array();

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        // Add creation button if member has create permissions
        $add_button = new GridFieldAddNewInlineButton('toolbar-header-left');
        $add_button->setTitle(_t("Discussions.AddCategory","Add Category"));

        $gridField = new GridField(
            'Categories',
            '',
            $this->Categories(),
            GridFieldConfig::create()
                ->addComponent(new GridFieldButtonRow('before'))
                ->addComponent(new GridFieldToolbarHeader())
                ->addComponent(new GridFieldTitleHeader())
                ->addComponent(new GridFieldEditableColumns())
                ->addComponent(new GridFieldDeleteAction())
                ->addComponent($add_button)
        );

        $fields->addFieldToTab("Root.Main", $gridField, "Content");

        $fields->removeByName("Content");

        return $fields;
    }

    public function getSettingsFields() {
        $fields = parent::getSettingsFields();

        $groupsMap = array();
        foreach(Group::get() as $group) {
            // Listboxfield values are escaped, use ASCII char instead of &raquo;
            $groupsMap[$group->ID] = $group->getBreadcrumbs(' > ');
        }
        asort($groupsMap);


        $fields->addFieldToTab(
            "Root.Settings",
            ListboxField::create(
               "PosterGroups",
               _t("Discussion.PosterGroups","Groups that can post")
            )->setMultiple(true)
            ->setSource($groupsMap)
            ->setValue(null,$this->PosterGroups()),
            "CanViewType"
        );

        $fields->addFieldToTab(
            "Root.Settings",
            ListboxField::create(
               "ModeratorGroups",
               _t("Discussion.ModeratorGroups","Groups that can moderate")
            )->setMultiple(true)
            ->setSource($groupsMap)
            ->setValue(null,$this->ModeratorGroups()),
            "CanViewType"
        );

        return $fields;
    }

    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        // Setup Discussion Page
        $page = DiscussionHolder::get()->first();

        if(!$page) {
            $page = new DiscussionHolder();
            $page->Title = "Discussions";
            $page->URLSegment = "discussions";
            $page->Sort = 3;
            $page->write();
            $page->publish('Stage', 'Live');
            DB::alteration_message('Discussions Holder Created', 'created');
        }
    }

    public function canStartDiscussions($member = null) {
        if(!$member) $member = Member::currentUser();

        // If admin, return true
        if(Permission::check("ADMIN"))
            return true;

        // If member is in discussions moderator groups, return true
        if($this->PosterGroups()->filter("Members.ID", $member->ID)->exists())
            return true;

        return false;
    }

    /**
     * Perform database cleanup when deleting
     */
    public function onBeforeDelete() {
        parent::onBeforeDelete();

        foreach($this->Discussions() as $item) {
            $item->delete();
        }

        foreach($this->Categories() as $item) {
            $item->delete();
        }
    }
}
