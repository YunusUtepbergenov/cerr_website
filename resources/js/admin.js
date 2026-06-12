(function () {
            const STORAGE_KEY = 'cerr-admin-sidebar-collapsed';
            const isMobile = () => window.innerWidth < 992;

            const setup = () => {
                const toggle = document.getElementById('sidebar-toggle');
                const sidebar = document.getElementById('admin-sidebar');
                const backdrop = document.getElementById('sidebar-backdrop');
                if (!toggle || !sidebar || !backdrop || toggle.dataset.bound === '1') return;
                toggle.dataset.bound = '1';

                if (! isMobile() && localStorage.getItem(STORAGE_KEY) === '1') {
                    sidebar.classList.add('is-collapsed');
                }

                const closeMobile = () => {
                    sidebar.classList.remove('is-open');
                    backdrop.classList.remove('is-open');
                };

                const handleToggle = () => {
                    if (isMobile()) {
                        const opening = ! sidebar.classList.contains('is-open');
                        sidebar.classList.toggle('is-open', opening);
                        backdrop.classList.toggle('is-open', opening);
                    } else {
                        const collapsing = ! sidebar.classList.contains('is-collapsed');
                        sidebar.classList.toggle('is-collapsed', collapsing);
                        localStorage.setItem(STORAGE_KEY, collapsing ? '1' : '0');
                    }
                };

                toggle.addEventListener('click', handleToggle);
                backdrop.addEventListener('click', closeMobile);
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMobile(); });
                window.addEventListener('resize', () => {
                    if (! isMobile()) {
                        closeMobile();
                        sidebar.classList.toggle('is-collapsed', localStorage.getItem(STORAGE_KEY) === '1');
                    } else {
                        sidebar.classList.remove('is-collapsed');
                    }
                });
            };
            if (document.readyState !== 'loading') setup();
            else document.addEventListener('DOMContentLoaded', setup);
            document.addEventListener('livewire:navigated', setup);
        })();

(function () {
    const setup = () => {
        const btn = document.getElementById('theme-toggle');
        if (!btn || btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', () => {
            const next = document.documentElement.getAttribute('data-admin-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-admin-theme', next);
            document.documentElement.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('cerr-admin-theme', next); } catch (e) {}
        });
    };
    if (document.readyState !== 'loading') setup();
    else document.addEventListener('DOMContentLoaded', setup);
    document.addEventListener('livewire:navigated', setup);
})();

(function () {
    document.addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-href]');
        if (!row || e.target.closest('a, button, input, select, label')) return;
        if (window.getSelection && String(window.getSelection())) return;
        window.location.href = row.dataset.href;
    });
})();
