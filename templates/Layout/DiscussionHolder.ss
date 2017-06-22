<div class="main container">
    <div class="units-row row">
        <div class="content-container site-content unit typography <% if $SideBarView %>col-md-9 size3of4 unit-75<% end_if %>">
            <% if $CurrentCategory %>
                <h1>Current category: "{$CurrentCategory.Title}"</h1>
            <% else %>
                <h1>$Title</h1>
            <% end_if %>

            <% if not $SideBarView && $canStartDiscussions %>
                <p class="discussions-start-button line row units-row">
                    <% include StartDiscussionButton %>
                </p>
            <% end_if %>

            <% if $ViewableDiscussions.Count == 0 %>
                <p>There are currenty no discussions.</p>

                <p class="discussions-start-button">
                    <% include StartDiscussionButton %>
                </p>
            <% else %>
                <div class="discussions">
                    <% loop $ViewableDiscussions %>
                        <div class="discussion units-row">
                            <div class="discussion-content unit-80">
                                <h2>
                                    <a href="{$Link('view')}">$Title</a>
                                    <% if $Liked %>
                                        <span class="label label-blue">
                                            <%t Discussions.Liked "Liked" %>
                                        </span>
                                    <% end_if %>
                                </h2>

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
                                    $Content.Summary(50)

                                    <a href="{$Link(view)}">
                                        <%t Discussions.ReadFullDiscussion "Read Full Discussion" %>
                                    </a>
                                </p>

                                <% include DiscussionMeta %>
                            </div>
                        </div>

                        <hr/>
                    <% end_loop %>

                    <% with $ViewableDiscussions %>
                        <% if $MoreThanOnePage %>
                            <ul class="pagination">
                                <% if $NotFirstPage %>
                                    <li><a class="prev" href="$PrevLink">Prev</a></li>
                                <% end_if %>
                                <% loop $Pages %>
                                    <% if $CurrentBool %>
                                        <li><span>$PageNum</span></li>
                                    <% else %>
                                        <% if $Link %>
                                            <li><a href="$Link">$PageNum</a></li>
                                        <% else %>
                                            <li>...</li>
                                        <% end_if %>
                                    <% end_if %>
                                    <% end_loop %>
                                <% if $NotLastPage %>
                                    <li><a class="next" href="$NextLink">Next</a></li>
                                <% end_if %>
                            </ul>
                        <% end_if %>
                    <% end_with %>

                </div>
            <% end_if %>
        </div>

        <% include DiscussionsSidebar %>
    </div>
</div>