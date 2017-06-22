<p class="discussion-meta">
    <em>$Created.Ago</em>

    <% if $Author.Nickname %>
        <%t Discussion.By "by" %>
        <strong>$Author.Nickname</strong>
    <% end_if %>

    |

    <% if $LikedBy.Count %>
        $LikedBy.Count
        <% if $LikedBy.Count == 1 %>
            Like;
        <% else %>
            Likes;
        <% end_if %>
    <% end_if %>

    $Comments.Count

    <% if $Comments.Count == 1 %>
        <%t Discussions.Reply "Reply" %>
    <% else %>
        <%t Discussions.Replies "Replies" %>
    <% end_if %>

    |

    <% if $Categories.exists %>
        <%t Discussions.PostedIn "Posted In" %>:
        <% loop $Categories %>
            <a href="$Link">$Title</a><% if not $Last %>,<% end_if %>
        <% end_loop %>
    <% end_if %>
</p>