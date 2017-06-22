<div class="main container">
    <div class="units-row row">
        <div class="content-container site-content unit typography <% if $SideBarView %>col-md-9 size3of4 unit-75<% end_if %>">
            <% with $Discussion %>
                <h1 class="col-md-75 unit size3of4">
                    $Title
                    <% if $Liked %>
                        <span class="liked label label-blue">
                            <% _t("Discussions.Liked", "Liked") %>
                        </span>
                    <% end_if %>
                </h1>

                <% include DiscussionMeta %>

                <% if $Author.Avatar %>
                    <img
                        class="avatar"
                        style="float: left; margin: 0 1em 1em 0;"
                        src="$Author.Avatar.CroppedImage(75,75).URL"
                        alt="Avatar for {$Author.Nickname}"
                        title="Avatar for {$Author.Nickname}"
                    />
                <% end_if %>

                <p>$Content</p>

                <div class="units-row-end">
                    <p>
                        <% if $canLike %>
                            <a href="{$Top.Link('like')}/{$ID}" class="btn btn-sm btn-blue btn-info">
                                <% _t("Discussions.LikeThis", "Like this") %>
                            </a>
                        <% end_if %>

                        <% if not $Author.ID == $CurrentMember.ID %>
                            <a href="{$Top.Link('block')}/{$Author.ID}" class="btn btn-sm btn-red btn-danger">
                                Block this person?
                            </a>
                        <% end_if %>

                        <% if $CanDelete %>
                            <a href="{$Top.Link('remove')}/{$ID}" class="btn btn-sm btn-red btn-danger">
                                Delete
                            </a>
                        <% end_if %>

                        <% if $CanEdit %>
                            <a href="{$Top.Link('edit')}/{$ID}" class="btn btn-sm btn-green btn-success">
                                Edit
                            </a>
                        <% end_if %>

                        <a class="btn btn-red btn-sm btn-default" href="$Up.Link">
                            <%t Discussions.BackToDiscussions "Back to Discussions" %>
                        </a>
                    </p>
                </div>

                <hr />

                $CommentsForm
            <% end_with %>
        </div>

        <% include DiscussionsSidebar %>
    </div>
</div>