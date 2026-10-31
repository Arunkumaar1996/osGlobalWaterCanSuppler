/**
 * AquaFlow Platform Core JavaScript
 * Zero-Node Runtime Bundle (jQuery + Alpine helper utilities + PWA Engine)
 */

// 1. Configure jQuery AJAX CSRF Header
if (window.jQuery) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

// 2. Global Floating Toast Notification System
window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const colorClasses = {
        success: 'bg-emerald-600 text-white',
        error: 'bg-rose-600 text-white',
        warning: 'bg-amber-500 text-white',
        info: 'bg-sky-600 text-white'
    }[type] || 'bg-slate-800 text-white';

    toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl transform transition-all duration-300 translate-y-2 opacity-0 text-sm font-medium ${colorClasses}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-white/80 hover:text-white">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

// 3. Online / Offline Connectivity Monitor
window.addEventListener('offline', () => {
    window.showToast('You are currently offline. Check your internet connection.', 'warning');
});

window.addEventListener('online', () => {
    window.showToast('Internet connection restored.', 'success');
});

// 4. Register PWA Service Worker (HTTPS or localhost)
if ('serviceWorker' in navigator && (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('AquaFlow PWA Service Worker registered:', registration.scope);

                // Check for updates
                registration.onupdatefound = () => {
                    const installingWorker = registration.installing;
                    if (installingWorker) {
                        installingWorker.onstatechange = () => {
                            if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                window.showToast('New version available! Refreshing...', 'info');
                            }
                        };
                    }
                };
            })
            .catch((error) => {
                console.warn('AquaFlow PWA Service Worker registration failed:', error);
            });
    });
}
