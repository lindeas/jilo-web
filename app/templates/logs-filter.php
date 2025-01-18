                    <!-- Logs filter -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="get" action="" class="row g-3 align-items-end">
                                <input type="hidden" name="page" value="logs">
                                <input type="hidden" name="tab" value="<?= htmlspecialchars($widget['scope']) ?>">

                                <div class="col-md-3">
                                    <label for="from_time" class="form-label">From date</label>
                                    <input type="date" class="form-control" id="from_time" name="from_time" value="<?= htmlspecialchars($_REQUEST['from_time'] ?? '') ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="until_time" class="form-label">Until date</label>
                                    <input type="date" class="form-control" id="until_time" name="until_time" value="<?= htmlspecialchars($_REQUEST['until_time'] ?? '') ?>">
                                </div>

<?php if ($widget['scope'] === 'system') { ?>
                                <div class="col-md-2">
                                    <label for="id" class="form-label">User ID</label>
                                    <input type="text" class="form-control" id="id" name="id" value="<?= htmlspecialchars($_REQUEST['id'] ?? '') ?>" placeholder="Enter user ID">
                                </div>
<?php } ?>

                                <div class="col-md">
                                    <label for="message" class="form-label">Message</label>
                                    <input type="text" class="form-control" id="message" name="message" value="<?= htmlspecialchars($_REQUEST['message'] ?? '') ?>" placeholder="Search in log messages">
                                </div>

                                <div class="col-md-auto">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="?page=logs&tab=<?= htmlspecialchars($widget['scope']) ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /Logs filter -->
