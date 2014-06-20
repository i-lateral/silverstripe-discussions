<div class="units-row comment" id="<% if isPreview %>comment-preview<% else %>$Permalink<% end_if %>">
    <% if $Gravatar %>
        <img class="gravatar" style="float: left; margin-right: 1em;" src="$Gravatar" alt="Gravatar for $Name" title="Gravatar for $Name" />
    <% else_if $Author.Avatar %>
        <img class="gravatar" style="float: left; margin-right: 1em;" src="$Author.Avatar.CroppedImage(75,75).URL" alt="Avatar for $Name" title="Avatar for $Name" />
    <% end_if %>

    $EscapedComment
</div>

<% if not isPreview %>
    <p class="info">
        <% if $URL %>
            <% _t('CommentsInterface_singlecomment_ss.PBY','Posted by') %> <a href="$URL.URL" rel="nofollow">$Author.Nickname.XML</a>, $Created.Nice ($Created.Ago)
        <% else %>
            <% _t('CommentsInterface_singlecomment_ss.PBY','Posted by') %> $Author.Nickname.XML, $Created.Nice ($Created.Ago)
        <% end_if %>
    </p>

    <% if $ApproveLink || $SpamLink || $HamLink || $DeleteLink %>
        <p class="action-links btn-group">
            <% if ApproveLink %>
                <a href="$ApproveLink.ATT" class="btn btn-green approve"><% _t('Discussions.ApproveReply', 'Approve this reply') %></a>
            <% end_if %>
            <% if SpamLink %>
                <a href="$SpamLink.ATT" class="btn btn-red spam"><% _t('Discussions.IsSpam','This is spam') %></a>
            <% end_if %>
            <% if HamLink %>
                <a href="$HamLink.ATT" class="btn btn-blue ham"><% _t('Discussions.IsHam','This reply is not spam') %></a>
            <% end_if %>
            <% if DeleteLink %>
                <a href="$DeleteLink.ATT" class="btn btn-red delete"><% _t('Discussions.RemoveReply','Remove this reply') %></a>
            <% end_if %>
        </p>
    <% end_if %>
<% end_if %>
