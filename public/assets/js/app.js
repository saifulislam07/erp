/*
 * ERP front-end helpers.
 *
 * Exposes a single global, `ERP`, with the toast + dialog API used across the
 * admin panel. Everything degrades gracefully: if SweetAlert2 has not loaded
 * the confirm helpers fall back to the browser's native confirm().
 */
(function (window, document) {
    'use strict';

    var ERP = window.ERP || {};

    /* ---------------------------------------------------------- toasts */

    var TOAST_ICONS = {
        success: 'fas fa-check',
        error: 'fas fa-exclamation',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info'
    };

    var TOAST_TITLES = {
        success: 'Success',
        error: 'Error',
        warning: 'Warning',
        info: 'Notice'
    };

    function toastStack() {
        var el = document.getElementById('erp-toasts');

        if (!el) {
            el = document.createElement('div');
            el.id = 'erp-toasts';
            document.body.appendChild(el);
        }

        return el;
    }

    function dismissToast(node) {
        if (!node || node.dataset.leaving === '1') {
            return;
        }

        node.dataset.leaving = '1';
        node.classList.add('is-leaving');
        window.setTimeout(function () {
            if (node.parentNode) {
                node.parentNode.removeChild(node);
            }
        }, 200);
    }

    /**
     * Show a toast.
     *
     * @param {string} type    success | error | warning | info
     * @param {string} message body text
     * @param {object} [opts]  {title, timeout} — timeout 0 keeps it until dismissed
     */
    ERP.toast = function (type, message, opts) {
        if (!message) {
            return;
        }

        type = TOAST_ICONS[type] ? type : 'info';
        opts = opts || {};

        var timeout = typeof opts.timeout === 'number' ? opts.timeout : 4500;
        var node = document.createElement('div');

        node.className = 'erp-toast erp-toast--' + type;
        node.setAttribute('role', type === 'error' ? 'alert' : 'status');
        node.innerHTML =
            '<span class="erp-toast__icon"><i class="' + TOAST_ICONS[type] + '"></i></span>' +
            '<span class="erp-toast__body">' +
            '<span class="erp-toast__title"></span>' +
            '<span class="erp-toast__text"></span>' +
            '</span>' +
            '<button type="button" class="erp-toast__close" aria-label="Dismiss">&times;</button>' +
            '<span class="erp-toast__bar"></span>';

        node.querySelector('.erp-toast__title').textContent = opts.title || TOAST_TITLES[type];
        node.querySelector('.erp-toast__text').textContent = message;
        node.querySelector('.erp-toast__close').addEventListener('click', function () {
            dismissToast(node);
        });

        toastStack().appendChild(node);
        window.requestAnimationFrame(function () {
            node.classList.add('is-visible');
        });

        if (timeout > 0) {
            var bar = node.querySelector('.erp-toast__bar');
            bar.style.transition = 'transform ' + timeout + 'ms linear';
            window.requestAnimationFrame(function () {
                bar.style.transform = 'scaleX(0)';
            });

            var timer = window.setTimeout(function () {
                dismissToast(node);
            }, timeout);

            node.addEventListener('mouseenter', function () {
                window.clearTimeout(timer);
                bar.style.transition = 'none';
            });

            node.addEventListener('mouseleave', function () {
                timer = window.setTimeout(function () {
                    dismissToast(node);
                }, 1800);
            });
        }
    };

    ERP.success = function (message, opts) { ERP.toast('success', message, opts); };
    ERP.error = function (message, opts) { ERP.toast('error', message, opts); };
    ERP.warning = function (message, opts) { ERP.toast('warning', message, opts); };
    ERP.info = function (message, opts) { ERP.toast('info', message, opts); };

    /* --------------------------------------------------------- dialogs */

    function swalDefaults(extra) {
        return Object.assign({
            buttonsStyling: true,
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'erp-swal-popup' }
        }, extra || {});
    }

    /**
     * Ask for confirmation. Resolves to true/false.
     *
     * @param {object} [opts] {title, text, confirmText, cancelText, danger, icon}
     * @returns {Promise<boolean>}
     */
    ERP.confirm = function (opts) {
        opts = opts || {};

        var title = opts.title || 'Are you sure?';
        var text = opts.text || '';

        if (!window.Swal) {
            return Promise.resolve(window.confirm(text ? title + '\n\n' + text : title));
        }

        return window.Swal.fire(swalDefaults({
            title: title,
            text: text,
            icon: opts.icon || (opts.danger ? 'warning' : 'question'),
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Confirm',
            cancelButtonText: opts.cancelText || 'Cancel',
            customClass: {
                popup: 'erp-swal-popup',
                confirmButton: opts.danger ? 'erp-swal-confirm--danger' : ''
            }
        })).then(function (result) {
            return !!result.isConfirmed;
        });
    };

    /**
     * Confirmation tuned for destructive actions.
     */
    ERP.confirmDelete = function (opts) {
        opts = opts || {};

        return ERP.confirm({
            title: opts.title || 'Delete this record?',
            text: opts.text || 'This cannot be undone.',
            confirmText: opts.confirmText || 'Delete',
            cancelText: opts.cancelText || 'Keep it',
            danger: true
        });
    };

    /**
     * Blocking notice dialog (used for messages too long for a toast).
     */
    ERP.alert = function (title, text, icon) {
        if (!window.Swal) {
            window.alert(text ? title + '\n\n' + text : title);

            return Promise.resolve();
        }

        return window.Swal.fire(swalDefaults({
            title: title,
            text: text || '',
            icon: icon || 'info',
            confirmButtonText: 'OK'
        }));
    };

    /* ------------------------------------------------- declarative hooks */

    /*
     * Any form carrying `data-confirm` (optionally `data-confirm-text` and
     * `data-confirm-danger`) is intercepted once and submitted only after the
     * user confirms. Covers every delete button in the panel without per-page
     * JavaScript.
     */
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || !form.matches || !form.matches('[data-confirm]')) {
            return;
        }

        if (form.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();

        var danger = form.dataset.confirmDanger !== '0';

        ERP.confirm({
            title: form.dataset.confirm || 'Are you sure?',
            text: form.dataset.confirmText || '',
            confirmText: form.dataset.confirmButton || (danger ? 'Delete' : 'Confirm'),
            danger: danger
        }).then(function (ok) {
            if (!ok) {
                return;
            }

            form.dataset.confirmed = '1';
            // requestSubmit keeps native validation; older engines fall back.
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });

    /*
     * Links with `data-confirm` behave the same way.
     */
    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a[data-confirm]') : null;

        if (!link) {
            return;
        }

        event.preventDefault();

        ERP.confirm({
            title: link.dataset.confirm || 'Are you sure?',
            text: link.dataset.confirmText || '',
            danger: link.dataset.confirmDanger !== '0'
        }).then(function (ok) {
            if (ok) {
                window.location.href = link.href;
            }
        });
    });

    /*
     * Guard against double submission: once a submit button has been used it is
     * disabled and shows a spinner until the page navigates away.
     */
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form.matches || !form.matches('form') || form.hasAttribute('data-no-submit-guard')) {
            return;
        }

        window.setTimeout(function () {
            form.querySelectorAll('button[type="submit"]:not([data-no-submit-guard])').forEach(function (btn) {
                btn.disabled = true;
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i>' + btn.textContent.trim();
            });
        }, 0);
    });

    /*
     * Marks required inputs in their label so the form communicates what is
     * mandatory without every Blade template repeating the asterisk.
     */
    function markRequiredLabels() {
        document.querySelectorAll('.form-group :required').forEach(function (input) {
            var group = input.closest('.form-group');
            var label = group ? group.querySelector('label') : null;

            if (label && !label.querySelector('.req')) {
                var star = document.createElement('span');
                star.className = 'req';
                star.textContent = '*';
                label.appendChild(star);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        markRequiredLabels();

        // Flash messages handed over from the server as toasts.
        (window.ERP_FLASH || []).forEach(function (flash) {
            ERP.toast(flash.type, flash.message);
        });
    });

    window.ERP = ERP;
})(window, document);
