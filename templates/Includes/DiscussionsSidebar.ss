<% if SideBarView %>
    <div class="discussions-sidebar unit-25 col-md-3 size1of4 typography">
        <% if $canStartDiscussions %>
            <p class="discussions-start-button">
                <a class="btn btn-big btn-lg btn-green btn-primary" href="{$Link('start')}">
                    <% _t("Discussions.StartDiscussion", "Start new discussion") %>
                </a>
            </p>
        <% end_if %>

        $SideBarView
    </div>
<% end_if %>
