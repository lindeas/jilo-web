            <!-- /Main content -->

        </div>

        <!-- Footer -->
        <div id="footer">Jilo Web <?= $config['version'] ?> &copy;2024 - web interface for <a href="https://lindeas.com/jilo">Jilo</a></div>
        <!-- /Footer -->

    </div>

<script>
    // slide the sidebar to the left instead of default up
    document.getElementById('toggleSidebarButton').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

</script>

</body>

</html>
