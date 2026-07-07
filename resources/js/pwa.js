const DISMISS_KEY = 'pwa_install_dismissed_until';
const DISMISS_DAYS = 7;

let deferredPrompt = null;
let pendingInstall = false;

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
}

function isIos() {
    return /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
}

function isAndroid() {
    return /android/i.test(navigator.userAgent);
}

function isDismissed() {
    const raw = localStorage.getItem(DISMISS_KEY);

    if (!raw) {
        return false;
    }

    const until = Number.parseInt(raw, 10);

    return Number.isFinite(until) && Date.now() < until;
}

function getBanner() {
    return document.getElementById('pwa-install-banner');
}

function getDownloadButton() {
    return getBanner()?.querySelector('[data-pwa-download]');
}

function setDownloadButtonState(label, disabled = false) {
    const button = getDownloadButton();

    if (!button) {
        return;
    }

    button.textContent = label;
    button.toggleAttribute('disabled', disabled);
}

function hideBanner() {
    const banner = getBanner();

    if (!banner) {
        return;
    }

    banner.classList.remove('pwa-install-banner--visible');
    banner.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('pwa-banner-visible');
}

function showBanner() {
    const banner = getBanner();

    if (!banner || isStandalone() || isDismissed()) {
        return;
    }

    banner.classList.add('pwa-install-banner--visible');
    banner.setAttribute('aria-hidden', 'false');
    document.body.classList.add('pwa-banner-visible');
}

function dismissBanner() {
    localStorage.setItem(DISMISS_KEY, String(Date.now() + DISMISS_DAYS * 86400000));
    hideBanner();
}

function showInstallHelp() {
    const siteName = getBanner()?.dataset.siteName || 'this app';

    if (typeof Swal !== 'undefined') {
        if (isIos()) {
            Swal.fire({
                icon: 'info',
                title: 'Install on iPhone',
                html: `
                    <ol style="text-align:left;margin:0;padding-left:1.25rem;line-height:1.6;">
                        <li>Tap the <strong>Share</strong> button in Safari</li>
                        <li>Scroll down and tap <strong>Add to Home Screen</strong></li>
                        <li>Tap <strong>Add</strong> to install ${siteName}</li>
                    </ol>
                `,
                confirmButtonText: 'Got it',
                confirmButtonColor: '#0891b2',
            });

            return;
        }

        const browserHint = isAndroid()
            ? 'Tap the menu <strong>(⋮)</strong> and choose <strong>Install app</strong> or <strong>Add to Home screen</strong>.'
            : 'Open the browser menu and choose <strong>Install app</strong> or click the install icon in the address bar.';

        Swal.fire({
            icon: 'info',
            title: 'Install the app',
            html: `<p style="line-height:1.6;margin:0;">${browserHint}</p>`,
            confirmButtonText: 'Got it',
            confirmButtonColor: '#0891b2',
        });

        return;
    }

    window.alert(isIos()
        ? 'Tap Share, then Add to Home Screen to install the app.'
        : 'Open your browser menu and choose Install app.');
}

async function runNativeInstall() {
    if (!deferredPrompt) {
        return false;
    }

    setDownloadButtonState('Installing…', true);

    deferredPrompt.prompt();

    const { outcome } = await deferredPrompt.userChoice;
    deferredPrompt = null;

    if (outcome === 'accepted') {
        hideBanner();
        return true;
    }

    setDownloadButtonState('Download', false);

    return false;
}

async function ensureServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    try {
        await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        await navigator.serviceWorker.ready;
    } catch {
        // Ignore registration errors and fall back to manual install help.
    }
}

function waitForInstallPrompt(timeoutMs = 2500) {
    if (deferredPrompt) {
        return Promise.resolve(true);
    }

    return new Promise((resolve) => {
        const timer = window.setTimeout(() => {
            window.removeEventListener('pwa-installable', onReady);
            resolve(!!deferredPrompt);
        }, timeoutMs);

        function onReady() {
            window.clearTimeout(timer);
            window.removeEventListener('pwa-installable', onReady);
            resolve(!!deferredPrompt);
        }

        window.addEventListener('pwa-installable', onReady);
    });
}

async function downloadApp() {
    if (isStandalone()) {
        return;
    }

    if (deferredPrompt) {
        await runNativeInstall();
        return;
    }

    if (isIos()) {
        showInstallHelp();
        return;
    }

    setDownloadButtonState('Preparing…', true);
    pendingInstall = true;

    await ensureServiceWorker();
    await waitForInstallPrompt();

    if (deferredPrompt) {
        pendingInstall = false;
        await runNativeInstall();
        return;
    }

    pendingInstall = false;
    setDownloadButtonState('Download', false);
    showInstallHelp();
}

function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
}

function initPwaInstallBanner() {
    if (isStandalone() || isDismissed()) {
        return;
    }

    const banner = getBanner();

    if (!banner) {
        return;
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        window.dispatchEvent(new CustomEvent('pwa-installable'));

        if (pendingInstall) {
            pendingInstall = false;
            runNativeInstall();
            return;
        }

        showBanner();
    });

    banner.querySelector('[data-pwa-download]')?.addEventListener('click', downloadApp);
    banner.querySelector('[data-pwa-dismiss]')?.addEventListener('click', dismissBanner);
    window.addEventListener('appinstalled', hideBanner);

    window.setTimeout(showBanner, 1200);
}

function bootPwa() {
    registerServiceWorker();
    initPwaInstallBanner();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPwa);
} else {
    bootPwa();
}

export {};
