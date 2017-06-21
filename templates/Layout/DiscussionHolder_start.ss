<div class="units-row">
    <div class="site-content typography <% if $SideBarView %>unit-75<% end_if %>">
        <h1>New Discussion</h1>

        <% if $canStartDiscussions %>
            $Form
        <% else %>
            <% _t("Discussions.CannotStartDiscussion", "You do not have permission to start a discussion.") %>
        <% end_if %>
    </div>

    <% include DiscussionsSidebar %>
</div>
