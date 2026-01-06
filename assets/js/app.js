/**
 * =====================================================
 * MedFlow - Main JavaScript
 * نظام الإشعارات والتأكيدات المخصص
 * =====================================================
 */

// تهيئة التطبيق
document.addEventListener('DOMContentLoaded', function () {
    initializeApp();
    createNotificationContainer();
    createModalContainer();
});

/**
 * تهيئة التطبيق
 */
function initializeApp() {
    initButtonEffects();
    initAlerts();
    initDropdowns();
}

/**
 * إنشاء حاوية الإشعارات
 */
function createNotificationContainer() {
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        document.body.appendChild(container);
    }
}

/**
 * إنشاء حاوية المودال
 */
function createModalContainer() {
    if (!document.getElementById('custom-modal-container')) {
        const container = document.createElement('div');
        container.id = 'custom-modal-container';
        document.body.appendChild(container);
    }
}

/**
 * =====================================================
 * نظام الإشعارات (Toast Notifications)
 * =====================================================
 */
function MedFlowNotify(message, type = 'info', duration = 4000) {
    const container = document.getElementById('notification-container');
    if (!container) createNotificationContainer();

    const icons = {
        success: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
        error: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`
    };

    const notification = document.createElement('div');
    notification.className = `mf-notification mf-notification-${type}`;
    notification.innerHTML = `
        <div class="mf-notification-icon">${icons[type] || icons.info}</div>
        <div class="mf-notification-content">
            <p class="mf-notification-message">${message}</p>
        </div>
        <button class="mf-notification-close" onclick="this.parentElement.remove()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="mf-notification-progress"></div>
    `;

    document.getElementById('notification-container').appendChild(notification);

    // تحريك شريط التقدم
    const progress = notification.querySelector('.mf-notification-progress');
    progress.style.animation = `notificationProgress ${duration}ms linear forwards`;

    // إزالة بعد المدة المحددة
    setTimeout(() => {
        notification.classList.add('mf-notification-hide');
        setTimeout(() => notification.remove(), 300);
    }, duration);

    return notification;
}

// اختصارات
const notify = {
    success: (msg, duration) => MedFlowNotify(msg, 'success', duration),
    error: (msg, duration) => MedFlowNotify(msg, 'error', duration),
    warning: (msg, duration) => MedFlowNotify(msg, 'warning', duration),
    info: (msg, duration) => MedFlowNotify(msg, 'info', duration)
};

/**
 * =====================================================
 * نظام التأكيدات (Confirm Dialog)
 * =====================================================
 */
function MedFlowConfirm(options = {}) {
    return new Promise((resolve) => {
        const {
            title = 'تأكيد',
            message = 'هل أنت متأكد؟',
            confirmText = 'تأكيد',
            cancelText = 'إلغاء',
            type = 'info', // info, warning, danger, success
            icon = null
        } = typeof options === 'string' ? { message: options } : options;

        const icons = {
            info: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
            warning: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
            danger: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
            success: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`
        };

        const overlay = document.createElement('div');
        overlay.className = 'mf-modal-overlay';
        overlay.innerHTML = `
            <div class="mf-modal mf-modal-${type}">
                <div class="mf-modal-icon">${icon || icons[type] || icons.info}</div>
                <h3 class="mf-modal-title">${title}</h3>
                <p class="mf-modal-message">${message}</p>
                <div class="mf-modal-actions">
                    <button class="mf-modal-btn mf-modal-btn-cancel">${cancelText}</button>
                    <button class="mf-modal-btn mf-modal-btn-confirm mf-modal-btn-${type}">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // تحريك الظهور
        requestAnimationFrame(() => {
            overlay.classList.add('mf-modal-show');
        });

        const closeModal = (result) => {
            overlay.classList.remove('mf-modal-show');
            setTimeout(() => overlay.remove(), 200);
            resolve(result);
        };

        overlay.querySelector('.mf-modal-btn-cancel').onclick = () => closeModal(false);
        overlay.querySelector('.mf-modal-btn-confirm').onclick = () => closeModal(true);
        overlay.onclick = (e) => { if (e.target === overlay) closeModal(false); };

        // ESC للإغلاق
        const handleEsc = (e) => {
            if (e.key === 'Escape') {
                closeModal(false);
                document.removeEventListener('keydown', handleEsc);
            }
        };
        document.addEventListener('keydown', handleEsc);
    });
}

/**
 * =====================================================
 * نظام المدخلات (Prompt Dialog)
 * =====================================================
 */
function MedFlowPrompt(options = {}) {
    return new Promise((resolve) => {
        const {
            title = 'إدخال',
            message = '',
            placeholder = '',
            defaultValue = '',
            confirmText = 'تأكيد',
            cancelText = 'إلغاء',
            inputType = 'text'
        } = typeof options === 'string' ? { title: options } : options;

        const overlay = document.createElement('div');
        overlay.className = 'mf-modal-overlay';
        overlay.innerHTML = `
            <div class="mf-modal mf-modal-prompt">
                <h3 class="mf-modal-title">${title}</h3>
                ${message ? `<p class="mf-modal-message">${message}</p>` : ''}
                <input type="${inputType}" class="mf-modal-input" placeholder="${placeholder}" value="${defaultValue}">
                <div class="mf-modal-actions">
                    <button class="mf-modal-btn mf-modal-btn-cancel">${cancelText}</button>
                    <button class="mf-modal-btn mf-modal-btn-confirm mf-modal-btn-info">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const input = overlay.querySelector('.mf-modal-input');
        requestAnimationFrame(() => {
            overlay.classList.add('mf-modal-show');
            input.focus();
            input.select();
        });

        const closeModal = (result) => {
            overlay.classList.remove('mf-modal-show');
            setTimeout(() => overlay.remove(), 200);
            resolve(result);
        };

        overlay.querySelector('.mf-modal-btn-cancel').onclick = () => closeModal(null);
        overlay.querySelector('.mf-modal-btn-confirm').onclick = () => closeModal(input.value);
        input.onkeydown = (e) => { if (e.key === 'Enter') closeModal(input.value); };
        overlay.onclick = (e) => { if (e.target === overlay) closeModal(null); };
    });
}

/**
 * =====================================================
 * الدوال المساعدة القديمة (للتوافق)
 * =====================================================
 */
function showAlert(type, message, duration = 5000) {
    return MedFlowNotify(message, type, duration);
}

function initButtonEffects() {
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.left = e.offsetX + 'px';
            ripple.style.top = e.offsetY + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

function initAlerts() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const target = document.querySelector(this.dataset.dropdown);
            if (target) target.classList.toggle('show');
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}

async function fetchData(url, options = {}) {
    const defaultOptions = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    };

    const mergedOptions = { ...defaultOptions, ...options };

    try {
        const response = await fetch(url, mergedOptions);
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'حدث خطأ في الطلب');
        return data;
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
}

function formatDate(dateString, locale = 'ar-EG') {
    const date = new Date(dateString);
    return date.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatNumber(number, decimals = 0) {
    return new Intl.NumberFormat('ar-EG', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        notify.success('تم النسخ بنجاح');
        return true;
    } catch (err) {
        notify.error('فشل في النسخ');
        return false;
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
