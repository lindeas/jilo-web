
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var toggleButton = document.getElementById('toggleSidebarButton');
    var timeNow = document.getElementById('time_now');

    // update localStorage based on the current state
    function updateStorage() {
        var isSidebarCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarState', isSidebarCollapsed ? 'collapsed' : 'expanded');
    }

    // apply saved state
    function applySavedState() {
        var savedState = localStorage.getItem('sidebarState');
        if (savedState === 'collapsed') {
            toggleButton.value = ">>";
            toggleButton.textContent = ">>";
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            timeNow.style.display = 'none';
        } else {
            toggleButton.value = "<<";
            toggleButton.textContent = "<<";
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
            timeNow.style.display = 'block';
        }
    }

    // Initialize
    applySavedState();

    toggleButton.addEventListener('click', function () {
        // toggle sidebar and main content
        sidebar.classList.toggle('collapsed');
        document.documentElement.classList.toggle('sidebar-collapsed');
        mainContent.classList.toggle('expanded');
        // Toggle the value between ">>" and "<<"
        if (toggleButton.value === ">>") {
          toggleButton.value = "<<";
          toggleButton.textContent = "<<";
          timeNow.style.display = 'block';
        } else {
          toggleButton.value = ">>";
          toggleButton.textContent = ">>";
          timeNow.style.display = 'none';
        }

        // Update with the new state
        updateStorage();
    });
});
