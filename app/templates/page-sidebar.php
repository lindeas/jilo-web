        <div class="row">

            <!-- Sidebar -->
            <div class="col-md-3 sidebar-wrapper bg-light" id="sidebar">
                <div class="col-4"><button class="btn btn-sm btn-info toggle-sidebar-button" type="button" id="toggleSidebarButton" value=">>"></button></div>
                <div class="sidebar-content card ml-3 mt-3">
                    <ul class="list-group">

                        <li class="list-group-item bg-light" style="border: none;"><p class="text-end mb-0"><small>statistics</small></p></li>

                        <a href="<?= $app_root ?>?platform=<?= $platform_id ?>&page=front">
                            <li class="list-group-item<?php if ($page === 'front') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-chart-line" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="general jitsi stats"></i>general stats
                            </li>
                        </a>
                        <a href="<?= $app_root ?>?platform=<?= $platform_id ?>&page=conferences">
                            <li class="list-group-item<?php if ($page === 'conferences') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-video" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="conferences"></i>conferences
                            </li>
                        </a>
                        <a href="<?= $app_root ?>?platform=<?= $platform_id ?>&page=participants">
                            <li class="list-group-item<?php if ($page === 'participants') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-users" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="participants"></i>participants
                            </li>
                        </a>
                        <a href="<?= $app_root ?>?platform=<?= $platform_id ?>&page=components">
                            <li class="list-group-item<?php if ($page === 'components') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-puzzle-piece" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="components"></i>components
                            </li>
                        </a>

                        <li class="list-group-item bg-light" style="border: none;"><p class="text-end mb-0"><small>jilo-web config</small></p></li>

                        <a href="<?= $app_root ?>?page=config">
                            <li class="list-group-item<?php if ($page === 'config' && $item === '') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-wrench" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="configuration"></i>config
                            </li>
                        </a>
                        <a href="<?= $app_root ?>?page=logs">
                            <li class="list-group-item<?php if ($page === 'logs') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-list" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="logs"></i>logs
                            </li>
                        </a>

                        <li class="list-group-item bg-light" style="border: none;"><p class="text-end mb-0"><small>current Jitsi platform</small></p></li>

                        <a href="<?= $app_root ?>?platform=<?= $platform_id ?>&page=config&item=configjs">
                            <li class="list-group-item<?php if ($page === 'config' && $item === 'configjs') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-tv" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="configuration"></i>config.js
                            </li>
                        </a>
                        <a href="<?= $app_root ?>?platform=<?= $platform_id ?>&page=config&item=interfaceconfigjs">
                            <li class="list-group-item<?php if ($page === 'config' && $item === 'interfaceconfigjs') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-th" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="configuration"></i>interface_config.js
                            </li>
                        </a>
                    </ul>
                </div>
            </div>
            <!-- /Sidebar -->

            <!-- Main content -->
            <div class="col-md-9 main-content" id="mainContent">
