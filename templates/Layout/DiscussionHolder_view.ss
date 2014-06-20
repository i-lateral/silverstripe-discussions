<% require css(discussionforum/css/flexslider.css) %>

<% require javascript(discussionforum/thirdparty/jquery/jquery.js) %>
<% require javascript(discussionforum/javascript/flexslider.js) %>
<% require javascript(discussionforum/javascript/DiscussionForum.js) %>


<div class="units-row">
    <div class="site-content typography <% if $SideBarView %>unit-75<% end_if %>">
        <% with $Discussion %>
            <h1>
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

            <% if $Author.Avatar %>
                <img
                    class="avatar"
                    style="float: left; margin: 0 1em 1em 0;"
                    src="$Author.Avatar.CroppedImage(75,75).URL"
                    alt="Avatar for {$Author.Nickname}"
                    title="Avatar for {$Author.Nickname}"
                />
            <% end_if %>

            <p>
                <strong>$Author.Nickname</strong>
                $Created.Ago |

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

            <% if $Content %>
                <p>$Content</p>
            <% end_if %>

            <p>
                 <% if $TagsCollection.exists %>
                    <% _t("Discussions.Tags", "Tags") %>:
                    <% loop $TagsCollection %>
                        <a href="$Link">$Tag</a><% if $Last %>;<% else %>,<% end_if %>
                    <% end_loop %>
                <% end_if %>

                <% if $Categories.exists %>
                    <% _t("Discussions.Categories", "Categories") %>:
                    <% loop $Categories %>
                        <a href="$Link">$Title</a><% if $Last %>;<% else %>,<% end_if %>
                    <% end_loop %>
                <% end_if %>
            </p>

            <div class="units-row-end">
                <p class="unit-push-right">
                    <% if not $Liked %>
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
