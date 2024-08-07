        <div class="row">

            <!-- Sidebar -->
            <div class="col-md-3 sidebar-wrapper bg-light" id="sidebar">
                <div class="col-4"><button class="btn btn-sm btn-info toggle-sidebar-button" type="button" id="toggleSidebarButton" value=">>"></button></div>
                <div class="sidebar-content card ml-3 mt-3">
                    <ul class="list-group">
                        <a href="?page=front">
                            <li class="list-group-item<?php if ($page === 'front') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">general stats</li>
                        </a>
                        <a href="?page=conferences">
                            <li class="list-group-item<?php if ($page === 'conferences') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">conferences</li>
                        </a>
                        <a href="?page=participants">
                            <li class="list-group-item<?php if ($page === 'participants') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">participants</li>
                        </a>
                        <a href="?page=components">
                            <li class="list-group-item<?php if ($page === 'components') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">components</li>
                        </a>
                        <li class="list-group-item bg-light" style="border-left: none; border-right: none;"></li>
                        <a href="?page=config">
                            <li class="list-group-item<?php if ($page === 'config') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">config</li>
                        </a>
                        <a href="?page=logs">
                            <li class="list-group-item<?php if ($page === 'logs') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">logs</li>
                        </a>
                    </ul>
                </div>
            </div>
            <!-- /Sidebar -->

            <!-- Main content -->
            <div class="col-md-9 main-content" id="mainContent">
