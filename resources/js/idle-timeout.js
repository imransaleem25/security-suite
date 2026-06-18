(function () {
    'use strict';

    var script = document.currentScript;
    if (!script) {
        return;
    }

    var configUrl = script.getAttribute('data-config-url');
    var pingUrl   = script.getAttribute('data-ping-url');

    if (!configUrl || !pingUrl) {
        return;
    }

    var idleTimer = null;
    var warnTimer = null;
    var warned    = false;

    function clearTimers() {
        if (idleTimer) {
            clearTimeout(idleTimer);
            idleTimer = null;
        }
        if (warnTimer) {
            clearTimeout(warnTimer);
            warnTimer = null;
        }
    }

    function submitLogout() {
        var form = document.getElementById('logout-form');
        if (form) {
            form.submit();
            return;
        }
        window.location.href = pingUrl.replace('/idle-ping', '/login');
    }

    function resetTimers(cfg) {
        clearTimers();
        warned = false;

        fetch(pingUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .catch(function () {});

        var warnDelay = Math.max(0, cfg.timeout_ms - cfg.warn_before);
        warnTimer = setTimeout(function () {
            warned = true;
            if (typeof window.alert === 'function') {
                window.alert('Your session will expire soon due to inactivity.');
            }
        }, warnDelay);

        idleTimer = setTimeout(submitLogout, cfg.timeout_ms);
    }

    fetch(configUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (response) {
            return response.json();
        })
        .then(function (cfg) {
            if (!cfg || !cfg.timeout_ms) {
                return;
            }

            var events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
            var onActivity = function () {
                resetTimers(cfg);
            };

            events.forEach(function (eventName) {
                document.addEventListener(eventName, onActivity, { passive: true });
            });

            resetTimers(cfg);
        })
        .catch(function () {});
})();
