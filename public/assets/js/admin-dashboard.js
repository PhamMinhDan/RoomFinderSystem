/**
 * Admin Dashboard – RoomFinder
 * SPA-style navigation + interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initNavigation();
    initUserDropdown();
    initBarChart();
    initApprovalCheckboxes();
    initTabBtns();
    initMobileMenu();
});

/* ── SIDEBAR COLLAPSE ─────────────────────────────────────── */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const btn     = document.getElementById('collapseBtn');
    if (!btn || !sidebar) return;

    const collapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
    if (collapsed) sidebar.classList.add('collapsed');

    btn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('adminSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
}

/* ── SPA NAVIGATION ───────────────────────────────────────── */
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link[data-page]');
    const pages    = document.querySelectorAll('.page');

    navLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = link.dataset.page;
            navigateTo(target);

            // Close mobile sidebar
            const sidebar = document.getElementById('sidebar');
            if (sidebar) sidebar.classList.remove('mobile-open');
        });
    });

    // Check URL hash on load
    const hash = window.location.hash.replace('#', '') || 'dashboard';
    navigateTo(hash);
}

function navigateTo(pageId) {
    const pages    = document.querySelectorAll('.page');
    const navLinks = document.querySelectorAll('.nav-link[data-page]');

    pages.forEach(p => p.classList.add('hidden'));
    navLinks.forEach(l => l.classList.remove('active'));

    const targetPage = document.getElementById('page-' + pageId);
    const targetLink = document.querySelector(`.nav-link[data-page="${pageId}"]`);

    if (targetPage) targetPage.classList.remove('hidden');
    if (targetLink) targetLink.classList.add('active');

    window.location.hash = pageId;
    window.scrollTo(0, 0);
}

/* ── USER DROPDOWN ────────────────────────────────────────── */
function initUserDropdown() {
    document.addEventListener('click', e => {
        const user = document.getElementById('topbarUser');
        const menu = document.getElementById('userDropdownMenu');
        if (!user || !menu) return;

        if (!user.contains(e.target)) {
            menu.classList.remove('open');
            const chevron = document.getElementById('topbarChevron');
            if (chevron) chevron.style.transform = '';
        }
    });
}

function toggleUserMenu() {
    const menu    = document.getElementById('userDropdownMenu');
    const chevron = document.getElementById('topbarChevron');
    if (!menu) return;
    const open = menu.classList.toggle('open');
    if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
}

/* ── BAR CHART ────────────────────────────────────────────── */
function initBarChart() {
    const container = document.getElementById('barChart');
    if (!container) return;

    const data = [
        { label: 'T2', value: 45 },
        { label: 'T3', value: 72 },
        { label: 'T4', value: 88 },
        { label: 'T5', value: 61 },
        { label: 'T6', value: 94 },
        { label: 'T7', value: 58 },
        { label: 'CN', value: 37 },
    ];

    const max = Math.max(...data.map(d => d.value));

    data.forEach(d => {
        const heightPct = Math.round((d.value / max) * 100);
        const item = document.createElement('div');
        item.className = 'bar-item';
        item.innerHTML = `
            <div class="bar-value-label" style="font-size:10px;color:var(--gray-500);font-weight:600;margin-bottom:4px">${d.value}</div>
            <div class="bar-fill" style="height:0%;width:100%" data-height="${heightPct}%" title="${d.label}: ${d.value} tin"></div>
            <div class="bar-label">${d.label}</div>
        `;
        container.appendChild(item);
    });

    // Animate bars in
    requestAnimationFrame(() => {
        setTimeout(() => {
            document.querySelectorAll('.bar-fill').forEach(bar => {
                bar.style.transition = 'height .6s cubic-bezier(.4,0,.2,1)';
                bar.style.height = bar.dataset.height;
            });
        }, 100);
    });
}

/* ── TAB BUTTONS ──────────────────────────────────────────── */
function initTabBtns() {
    document.querySelectorAll('.card-hdr-tabs').forEach(group => {
        group.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    });
}

/* ── APPROVAL CHECKBOXES ─────────────────────────────────── */
function initApprovalCheckboxes() {
    document.querySelectorAll('.approval-cb').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.approval-row');
            if (!row) return;
            row.style.background = this.checked ? '#eff6ff' : '';
        });
    });
}

/* ── MOBILE MENU ──────────────────────────────────────────── */
function initMobileMenu() {
    const btn     = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    if (!btn || !sidebar) return;

    btn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
    });

    // Close on overlay click
    document.addEventListener('click', e => {
        if (sidebar.classList.contains('mobile-open') &&
            !sidebar.contains(e.target) &&
            !btn.contains(e.target)) {
            sidebar.classList.remove('mobile-open');
        }
    });
}

/* ── APPROVAL ACTIONS ─────────────────────────────────────── */
function approveListing(btn) {
    const row   = btn.closest('.approval-row');
    const badge = row.querySelector('.status-badge');
    if (badge) {
        badge.className = 'status-badge approved';
        badge.textContent = 'Đã duyệt';
    }
    const btns = row.querySelector('.approval-btns');
    if (btns) {
        btns.innerHTML = '<span style="font-size:12px;color:var(--green);font-weight:600"><i class="fas fa-check"></i> Đã duyệt</span>';
    }
    showToast('Đã duyệt tin đăng!', 'success');
}

function rejectListing(btn) {
    const row   = btn.closest('.approval-row');
    const badge = row.querySelector('.status-badge');
    if (badge) {
        badge.className = 'status-badge rejected';
        badge.textContent = 'Từ chối';
    }
    const btns = row.querySelector('.approval-btns');
    if (btns) {
        btns.innerHTML = '<span style="font-size:12px;color:var(--red);font-weight:600"><i class="fas fa-times"></i> Từ chối</span>';
    }
    showToast('Đã từ chối tin đăng.', 'error');
}

function approveSelected() {
    const checked = document.querySelectorAll('.approval-cb:checked');
    if (!checked.length) { showToast('Vui lòng chọn ít nhất một tin!', 'warn'); return; }
    checked.forEach(cb => {
        const btn = cb.closest('.approval-row')?.querySelector('.btn-approve-sm');
        if (btn) approveListing(btn);
    });
    showToast(`Đã duyệt ${checked.length} tin!`, 'success');
}

function rejectSelected() {
    const checked = document.querySelectorAll('.approval-cb:checked');
    if (!checked.length) { showToast('Vui lòng chọn ít nhất một tin!', 'warn'); return; }
    checked.forEach(cb => {
        const btn = cb.closest('.approval-row')?.querySelector('.btn-reject-sm');
        if (btn) rejectListing(btn);
    });
}

/* ── TOAST ────────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
    const existing = document.querySelector('.admin-toast');
    if (existing) existing.remove();

    const colors = {
        success: { bg: '#d1fae5', color: '#065f46', icon: 'fa-check-circle' },
        error:   { bg: '#fee2e2', color: '#991b1b', icon: 'fa-times-circle' },
        warn:    { bg: '#fef3c7', color: '#92400e', icon: 'fa-exclamation-circle' },
    };
    const c = colors[type] || colors.success;

    const toast = document.createElement('div');
    toast.className = 'admin-toast';
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        background:${c.bg}; color:${c.color};
        padding:12px 18px; border-radius:12px;
        font-size:13px; font-weight:600;
        display:flex; align-items:center; gap:8px;
        box-shadow:0 4px 20px rgba(0,0,0,.12);
        animation:toastIn .3s ease;
        font-family:'Be Vietnam Pro',sans-serif;
    `;
    toast.innerHTML = `<i class="fas ${c.icon}"></i> ${msg}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastOut .3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Toast animations
const style = document.createElement('style');
style.textContent = `
    @keyframes toastIn  { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    @keyframes toastOut { from { opacity:1; } to { opacity:0; transform:translateY(12px); } }
`;
document.head.appendChild(style);