<div class="nav-v">
    <ul class="tags">
        <% loop $TagsCollection %>
            <li>
                <a href="$Link">$Tag ($Count)</a>
            </li>
        <% end_loop %>
    </ul>
</div>
