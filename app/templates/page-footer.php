
<?php if ($page !== 'login' && $page !== 'register') { ?>
            <!-- /Main content -->
            </div>

        </div>

<?php } ?>
        <!-- Footer -->
        <div id="footer">Jilo Web <?= htmlspecialchars($config['version']) ?> &copy;2024 - web interface for <a href="https://lindeas.com/jilo">Jilo</a></div>
        <!-- /Footer -->

    </div>

    <script src="static/sidebar.js"></script>
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
</body>

</html>
