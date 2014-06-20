<p>
    <%t Discussions.NewCreatedReplySubject "{Nickname} replied to your discussion" Nickname=$Author.Nickname %>
    <strong>$Title</strong>
</p>

<p>
    <strong><%t Discussions.NicknameSaid "{Nickname} said" Nickname=$Author.Nickname %></strong><br/>
    <em>$Comment</em>
</p>

<p>
    <%t Discussions.ViewFullDiscussion "View the full discussion at" %>
    <a href="$Link">$Link</a>
</p>
