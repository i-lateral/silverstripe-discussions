<% if SideBarView %>
    <div class="discussions-sidebar unit-25 col-md-3 size1of4 typography">
        <% if $canStartDiscussions %>
            <p class="discussions-start-button">
                <% include StartDiscussionButton %>
            </p>
        <% end_if %>

        $SideBarView
    </div>
<% end_if %>
