/**
 * Modern Theme JavaScript
 * 
 * This file contains theme-specific JavaScript functionality.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Add smooth scrolling to anchor links
    $('a[href^="#"]').on('click', function(e) {
        if (this.hash !== '') {
            e.preventDefault();
            
            const hash = this.hash;
            $('html, body').animate(
                { scrollTop: $(hash).offset().top - 20 },
                800
            );
        }
    });
    
    // Handle form validation feedback
    $('form.needs-validation').on('submit', function(event) {
        if (this.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Add active class to current nav item
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    $('.nav-link').each(function() {
        if ($(this).attr('href') === currentPage) {
            $(this).addClass('active');
            $(this).closest('.nav-item').addClass('active');
        }
    });
    
    // Handle sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.documentElement.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', 
                document.documentElement.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        });
    }
    
    // Handle dropdown menus
    $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
        }
        const $subMenu = $(this).next('.dropdown-menu');
        $subMenu.toggleClass('show');
        
        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function() {
            $('.dropdown-submenu .show').removeClass('show');
        });
        
        return false;
    });
});

// Theme switcher functionality
function setTheme(themeName) {
    localStorage.setItem('theme', themeName);
    document.documentElement.className = themeName;
}

// Immediately-invoked function to set the theme on initial load
(function () {
    const savedTheme = localStorage.getItem('theme') || 'light-theme';
    setTheme(savedTheme);
})();
