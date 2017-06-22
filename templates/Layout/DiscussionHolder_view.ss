<div class="main container">
    <div class="units-row row">
        <div class="content-container site-content unit typography <% if $SideBarView %>col-md-9 size3of4 unit-75<% end_if %>">
            <% with $Discussion %>
                <div class="row line units-row">
                    <h1 class="col-md-75 unit size3of4">
                        $Title
                        <% if $Liked %>
                            <span class="label label-blue">
                                <% _t("Discussions.Liked", "Liked") %>
                            </span>
                        <% end_if %>
                        <% if $Reported %>
                            <span class="label label-red">
                                <% _t("Discussions.Reported", "Reported") %>
                            </span>
                        <% end_if %>
                    </h1>
                    
                    <p class="col-md-25 unit size1of4">
                        <a href="$Up.Link">
                            &laquo; <%t Discussions.BackToDiscussions "Back to Discussions" %>
                        </a>
                    </p>
                </div>

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

                <p>
                    <%t Discussions.PostedBy "Posted by" %>
                    <strong>$Author.Nickname</strong>
                    $Created.Ago;

                    <% if $LikedBy.Count %>
                        $LikedBy.Count
                        <% if $LikedBy.Count == 1 %>
                            <% _t("Discussions.Like", "Like") %>;
                        <% else %>
                            <% _t("Discussions.Likes", "Likes") %>;
                        <% end_if %>
                    <% end_if %>

                    $Comments.Count
                    <% if $Comments.Count == 1 %>
                        <% _t("Discussions.Comment", "Comment") %>;
                    <% else %>
                        <% _t("Discussions.Comments", "Comments") %>;
                    <% end_if %>
                </p>

                <p>
                    <% if $Categories.exists %>
                        <% _t("Discussions.Categories", "Categories") %>:
                        <% loop $Categories %>
                            <a href="$Link">$Title</a><% if $Last %>;<% else %>,<% end_if %>
                        <% end_loop %>
                    <% end_if %>
                </p>

                <div class="units-row-end">
                    <p class="unit-push-right">
                        <% if $canLike %>
                            <a href="{$Top.Link('like')}/{$ID}" class="btn btn-blue"><% _t("Discussions.LikeThis", "Like this") %></a>
                        <% end_if %>

                        <% if not $Reported %>
                            <a href="{$Top.Link('report')}/{$ID}" class="btn btn-red"><% _t("Discussions.ReportAbuse", "Report abuse") %></a>
                        <% end_if %>

                        <% if not $Author.ID == $CurrentMember.ID %>
                            <a href="{$Top.Link('block')}/{$Author.ID}" class="btn btn-red">Block this person?</a>
                        <% end_if %>

                        <% if $CanDelete %><a href="{$Top.Link('remove')}/{$ID}" class="btn btn-red">Delete</a><% end_if %>
                        <% if $CanEdit %><a href="{$Top.Link('edit')}/{$ID}" class="btn btn-green">Edit</a><% end_if %>
                    </p>
                </div>

                $CommentsForm
            <% end_with %>
        </div>

        <% include DiscussionsSidebar %>
    </div>
</div>