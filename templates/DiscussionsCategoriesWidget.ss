<div class="nav-v">
    <ul class="discussions-categories">
        <% loop $Categories %>
            <li>
                <a href="$Link">$Title ($Discussions.count)</a>
            </li>
        <% end_loop %>
    </ul>
</div>
