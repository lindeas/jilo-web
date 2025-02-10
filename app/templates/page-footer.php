
<?php if ($page !== 'login' && $page !== 'register') { ?>
            <!-- /Main content -->
            </div>

        </div>

<?php } ?>
        <!-- Footer -->
        <div id="footer">Jilo Web <?= htmlspecialchars($config['version']) ?> &copy;2024-<?= date('Y') ?> - web interface for <a href="https://lindeas.com/jilo">Jilo</a></div>
        <!-- /Footer -->

    </div>

<?php if (isset($currentUser) && $page !== 'logout') { ?>
    <script src="static/js/sidebar.js"></script>
<?php } ?>

<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>


<script>
// dismissible messages
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap alerts
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        var closeButton = alert.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                alert.classList.remove('show');
                setTimeout(function() {
                    alert.remove();
                }, 150);
            });
        }
    });
});
</script>

</body>

</html>
