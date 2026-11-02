<!-- Global Admin Footer Scripts -->
<script>
    // Theme Toggle Functionality (Light / Dark mode)
    function toggleAdminTheme() {
        const html = document.documentElement;
        const currentTheme = html.classList.contains('theme-light') ? 'light' : 'dark';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        if (newTheme === 'light') {
            html.classList.remove('theme-dark');
            html.classList.add('theme-light');
        } else {
            html.classList.remove('theme-light');
            html.classList.add('theme-dark');
        }

        try {
            localStorage.setItem('chargehub_admin_theme', newTheme);
        } catch (e) {}

        updateThemeIcon(newTheme);
    }

    function updateThemeIcon(theme) {
        const iconContainer = document.getElementById('theme-toggle-icon');
        const textContainer = document.getElementById('theme-toggle-text');
        if (iconContainer) {
            if (theme === 'light') {
                iconContainer.innerHTML = '🌙';
                if (textContainer) textContainer.textContent = 'Dark Mode';
            } else {
                iconContainer.innerHTML = '☀️';
                if (textContainer) textContainer.textContent = 'Light Mode';
            }
        }
    }

    // Auto-dismiss flash alert notifications after 4 seconds
    document.addEventListener('DOMContentLoaded', () => {
        // Init theme toggle button state
        const currentTheme = document.documentElement.classList.contains('theme-light') ? 'light' : 'dark';
        updateThemeIcon(currentTheme);

        const alerts = document.querySelectorAll('.flash-alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 400);
            }, 4500);
        });
    });

    // Mobile Sidebar Drawer Toggle
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-y-0');
            sidebar.classList.toggle('left-0');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('shadow-2xl');
        }
    }
</script>
</body>
</html>
