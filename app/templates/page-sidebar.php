        <div class="row">

            <!-- Sidebar -->
            <div class="col-md-3 mb-5 sidebar-wrapper bg-light" id="sidebar">
                <div class="text-center" style="border: 1px solid #0dcaf0; height: 22px;" id="time_now">
<?php
$timeNow = new DateTime('now', new DateTimeZone($userTimezone));
?>
                    <span style="vertical-align: top; font-size: 12px;"><?= htmlspecialchars($timeNow->format('H:i')) ?>&nbsp;&nbsp;<?= htmlspecialchars($userTimezone) ?></span>
                </div>

                <div class="col-4"><button class="btn btn-sm btn-info toggle-sidebar-button" type="button" id="toggleSidebarButton" value=">>"></button></div>
                <div class="sidebar-content card ml-3 mt-3">
                    <ul class="list-group">

                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=dashboard">
                            <li class="list-group-item<?php if ($page === 'dashboard') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-chart-line" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="general jitsi stats"></i>general stats
                            </li>
                        </a>

                        <li class="list-group-item bg-light" style="border: none;"><p class="text-end mb-0"><small>logs statistics</small></p></li>

                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences">
                            <li class="list-group-item<?php if ($page === 'conferences') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-video" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="conferences"></i>conferences
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=participants">
                            <li class="list-group-item<?php if ($page === 'participants') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-users" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="participants"></i>participants
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components">
                            <li class="list-group-item<?php if ($page === 'components') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-puzzle-piece" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="components"></i>components
                            </li>
                        </a>

                        <li class="list-group-item bg-light" style="border: none;"><p class="text-end mb-0"><small>live data</small></p></li>

                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=graphs">
                            <li class="list-group-item<?php if ($page === 'graphs') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-chart-bar" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="combined graphs"></i>combined graphs
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=latest">
                            <li class="list-group-item<?php if ($page === 'latest') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-list" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="latest data"></i>latest data
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=livejs&item=config.js">
                            <li class="list-group-item<?php if ($page === 'livejs' && $item === 'config.js') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-tv" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="config.js"></i>config.js
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=livejs&item=interface_config.js">
                            <li class="list-group-item<?php if ($page === 'livejs' && $item === 'interface_config.js') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-th" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="interface_config.js"></i>interface_config.js
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=agents">
                            <li class="list-group-item<?php if ($page === 'agents') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-robot" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="jilo agents"></i>jilo agents
                            </li>
                        </a>

                        <li class="list-group-item bg-light" style="border: none;"><p class="text-end mb-0"><small>jitsi platforms settings</small></p></li>

                        <a href="<?= htmlspecialchars($app_root) ?>?page=settings">
                            <li class="list-group-item<?php if ($page === 'settings') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-cog" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="jilo settings"></i>settings
                            </li>
                        </a>
                        <a href="<?= htmlspecialchars($app_root) ?>?page=status">
                            <li class="list-group-item<?php if ($page === 'status' && $item === '') echo ' list-group-item-secondary'; else echo ' list-group-item-action'; ?>">
                                <i class="fas fa-heartbeat" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="status"></i>status
                            </li>
                        </a>
                    </ul>
                </div>
            </div>
            <!-- /Sidebar -->

            <!-- Main content -->
            <div class="col-md-9 main-content" id="mainContent">
