import './bootstrap';

const root = document.documentElement;
const sidebar = document.getElementById('app-sidebar');
const overlay = document.getElementById('sidebar-overlay');
const sidebarOpen = document.getElementById('sidebar-open');

const setSidebar = (open) => {
    if (!sidebar || !overlay) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !open);
    sidebar.classList.toggle('translate-x-0', open);
    overlay.classList.toggle('hidden', !open);
    root.classList.toggle('overflow-hidden', open);
    sidebarOpen?.setAttribute('aria-expanded', String(open));

    if (!open) {
        sidebarOpen?.focus();
    }
};

sidebarOpen?.addEventListener('click', () => setSidebar(true));

document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => setSidebar(false));
});

overlay?.addEventListener('click', () => setSidebar(false));

const publicToggle = document.getElementById('public-menu-toggle');
const publicMenu = document.getElementById('public-menu');

publicToggle?.addEventListener('click', () => {
    if (!publicMenu) {
        return;
    }

    const open = publicMenu.classList.toggle('hidden') === false;
    publicToggle.setAttribute('aria-expanded', String(open));
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    if (publicMenu && !publicMenu.classList.contains('hidden')) {
        publicMenu.classList.add('hidden');
        publicToggle?.setAttribute('aria-expanded', 'false');
    }

    if (window.innerWidth < 1024 && sidebar && !sidebar.classList.contains('-translate-x-full')) {
        setSidebar(false);
    }
});

document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[data-flash]')?.remove());
});
