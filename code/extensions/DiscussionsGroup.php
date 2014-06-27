<?php

class DiscussionsGroup extends DataExtension {

    private static $belongs_many_many = array(
        "ModeratedDiscussions"  => "DiscussionHolder"
    );

    /**
     * Add default records to database.
     *
     * This function is called whenever the database is built, after the
     * database tables have all been created.
     */
    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        // Add default poster group if it doesn't exist
        $poster = Group::get()
            ->filter("Code",'discussions-posters')
            ->first();

        if(!$poster) {
            $poster = new Group();
            $poster->Code = 'discussions-posters';
            $poster->Title = _t('Discussions.DefaultGroupTitlePosters', 'Discussion Posters');
            $poster->Sort = 1;
            $poster->write();
            Permission::grant($poster->ID, 'DISCUSSIONS_REPLY');
            DB::alteration_message('Discussion Poster Group Created', 'created');
        }

        // Add default modrator group if none exists
        $moderator = Permission::get_groups_by_permission('DISCUSSIONS_MODERATION')
            ->first();

        if(!$moderator) {
            $moderator = new Group();
            $moderator->Code = 'discussions-moderators';
            $moderator->Title = _t('Discussions.DefaultGroupTitleModerators', 'Discussion Moderators');
            $moderator->Sort = 0;
            $moderator->write();
            Permission::grant($moderator->ID, 'DISCUSSIONS_MODERATION');
            DB::alteration_message('Discussion Moderator Group Created', 'created');
        }

        // Now add these groups to a discussion holder (if one exists)
        foreach(DiscussionHolder::get() as $page) {
            if(!$page->PosterGroups()->count()) {
                $page->PosterGroups()->add($poster);
                $page->write();
                DB::alteration_message('Added Poster Group to Discussions Holder', 'created');
            }

            if(!$page->ModeratorGroups()->count()) {
                $page->ModeratorGroups()->add($moderator);
                $page->write();
                DB::alteration_message('Added Moderator Group to Discussions Holder', 'created');
            }
        }
    }

}
