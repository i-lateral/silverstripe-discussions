<div class="main container">
    <div class="units-row row">
        <div class="content-container site-content unit typography <% if $SideBarView %>col-md-9 size3of4 unit-75<% end_if %>">
            <h1>New Discussion</h1>

            <% if $canStartDiscussions %>
                $Form
            <% else %>
                <% _t("Discussions.CannotStartDiscussion", "You do not have permission to start a discussion.") %>
            <% end_if %>
        </div>

        <% include DiscussionsSidebar %>
    </div>
</div>