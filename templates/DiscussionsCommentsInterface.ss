<% if $CommentsEnabled %>
	<div id="$CommentHolderID" class="comments-holder-container">
		<h3><% _t('Discussions.Replies','Replies') %></h3>

		<div class="comments-holder">
			<% if $PagedComments %>
				<ul class="comments-list root-level">
					<% loop $PagedComments %>
						<li class="comment $EvenOdd<% if FirstLast %> $FirstLast <% end_if %> $SpamClass">
							<% include DiscussionsCommentsInterface_singlecomment %>
						</li>
					<% end_loop %>
				</ul>
				<% with $PagedComments %>
					<% include CommentPagination %>
				<% end_with %>
			<% end_if %>

			<p class="no-comments-yet"<% if $PagedComments.Count %> style='display: none' <% end_if %> ><% _t('CommentsInterface_ss.NOCOMMENTSYET','No one has commented on this page yet.') %></p>

		</div>

		<hr />

		<div id="discussion-post-reply" class="post-comments-form">
			<h4><% _t('Discussions.PostAReply','Post a Reply') %></h4>

			<% if $AddCommentForm %>
				<% if $canPostComment %>
					<% if $ModeratedSubmitted %>
						<p id="moderated" class="message good"><% _t('CommentsInterface_ss.AWAITINGMODERATION', 'Your comment has been submitted and is now awaiting moderation.') %></p>
					<% end_if %>
					$AddCommentForm
				<% else %>
					<p><% _t('CommentsInterface_ss.COMMENTLOGINERROR', 'You cannot post comments until you have logged in') %><% if $PostingRequiredPermission %>,<% _t('CommentsInterface_ss.COMMENTPERMISSIONERROR', 'and that you have an appropriate permission level') %><% end_if %>.
						<a href="Security/login?BackURL={$Parent.Link}" title="<% _t('CommentsInterface_ss.LOGINTOPOSTCOMMENT', 'Login to post a comment') %>"><% _t('CommentsInterface_ss.COMMENTPOSTLOGIN', 'Login Here') %></a>.
					</p>
				<% end_if %>
			<% else %>
				<p><% _t('CommentsInterface_ss.COMMENTSDISABLED', 'Posting comments has been disabled') %>.</p>
			<% end_if %>
		</div>

		<% if $DeleteAllLink %>
			<p class="delete-comments">
				<a href="$DeleteAllLink"><% _t('CommentsInterface_ss.DELETEALLCOMMENTS','Delete all comments on this page') %></a>
			</p>
		<% end_if %>
	</div>
<% end_if %>
