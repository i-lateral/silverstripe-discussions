<?php

if (class_exists('Widget')) {

    /**
     * A list of tags associated with discussions
     *
     * @package discussionforum
     */
    class DiscussionsTagsWidget extends Widget
    {

        public static $db = array(
            "Title"     => "Varchar",
            "Limit"     => "Int",
            "SortParam" => "Enum('Tag,Count', 'Tag')",
            "SortOrder" => "Enum('ASC, DESC', 'ASC')"
        );

        public static $defaults = array(
            "Title"     => "Discussion Tags",
            "Limit"     => "0",
            "Sortby"    => "Tag"
        );

        public static $cmsTitle = "Discussion Tags";
        public static $description = "Shows list of tags associated with discussion posts";

        public function getCMSFields()
        {
            $fields = parent::getCMSFields();

            $fields->merge(
                new FieldList(
                    TextField::create("Title", _t("TagCloudWidget.TILE", "Title")),
                    TextField::create("Limit", _t("TagCloudWidget.LIMIT", "Limit number of tags")),
                    OptionsetField::create("SortParam"),
                    OptionsetField::create("SortOrder")
                )
            );

            $this->extend('updateCMSFields', $fields);

            return $fields;
        }

        public function Title()
        {
            return $this->Title ? $this->Title : "Discussion Tags";
        }

        public function getTagsCollection()
        {
            $allTags = new ArrayList();
            $max = 0;
            $member = Member::currentUser();

            // Find if we need to filter tags by current discussion page
            $controller = Controller::curr();
            if (method_exists($controller, "data")) {
                $page = $controller->data();
            } else {
                $page = null;
            }

            if ($page != null && $page instanceof DiscussionPage) {
                $discussions = $page->Discussions();
            } else {
                $discussions = Discussion::get();
            }

            if ($discussions) {
                foreach ($discussions as $discussion) {
                    if ($discussion->canView($member)) {
                        $theseTags = preg_split(" *, *", trim($discussion->Tags));

                        foreach ($theseTags as $tag) {
                            if ($tag) {
                                if ($allTags->find("Tag", $tag)) {
                                    $allTags->find("Tag", $tag)->Count++;
                                } else {
                                    $allTags->push(new ArrayData(array(
                                        "Tag"   => $tag,
                                        "Count" => 1,
                                        "Link"  => Controller::join_links(
                                            $discussion->Parent()->Link("tag"),
                                            Convert::raw2url($tag)
                                        )
                                    )));
                                }

                                $tag_count = $allTags->find("Tag", $tag)->Count;
                                $max = ($tag_count > $max) ? $tag_count : $max;
                            }
                        }
                    }
                }

                if ($allTags->exists()) {
                    // First sort our tags
                    $allTags->sort($this->SortParam, $this->SortOrder);

                    // Now if a limit has been set, limit the list
                    if ($this->Limit) {
                        $allTags = $allTags->limit($this->Limit);
                    }
                }

                return $allTags;
            }

            return;
        }
    }
}
