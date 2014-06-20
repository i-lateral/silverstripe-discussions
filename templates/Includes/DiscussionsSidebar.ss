<% if SideBarView %>
    <div class="discussions-sidebar unit-25 typography">
        <% if $canStartDiscussions %>
            <p class="discussions-start-button">
                <a class="btn btn-big btn-green" href="{$Link('start')}">
                    <% _t("Discussions.StartDiscussion", "Start new discussion") %>
                </a>
            </p>
        <% end_if %>

        $SideBarView
    </div>
<% end_if %>
