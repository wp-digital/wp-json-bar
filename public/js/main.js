(function (settings) {

    // Yep, old-school - ES5 with even XMLHttpRequest for better cross browser support.

    'use strict';

    var isDOMParserSupported = (function () {
        if (!window.DOMParser) {
            return false;
        }

        var parser = new DOMParser();

        try {
            parser.parseFromString('x', 'text/html');
        } catch (e) {
            return false;
        }

        return true;
    })();

    if (!isDOMParserSupported) {
        return;
    }

    var polling = {
        href: '',
        xhr: null,
        nonce: settings.nonce,
        timeout: null,
        adminBar: null,

        init: function (adminBar) {
            polling.initHref();
            polling.adminBar = adminBar;

            return polling;
        },

        initHref: function () {
            polling.href = window.location.href.split('#')[0];

            return polling;
        },

        tick: function () {
            polling.timeout = setTimeout(polling.onTick, settings.interval * 1000);

            return polling;
        },

        onTick: function () {
            var href = polling.href;

            polling.initHref();

            if (polling.href === href) {
                polling.tick();

                return;
            }

            polling.adminBar.innerHTML = '';
            polling.adminBar.className += ' innocode-json-bar_is-loading';

            var urlParts = polling.href.split('?');
            var url = urlParts[0] + '?' + settings.query_var + '=true';

            if (urlParts[1]) {
                url += '&' + urlParts[1];
            }

            if (polling.xhr !== null) {
                polling.xhr.abort();
            }

            polling.xhr = new XMLHttpRequest();

            polling.xhr.withCredentials = true;

            polling.xhr.open('GET', url);

            polling.xhr.setRequestHeader('Accept', 'application/json');
            polling.xhr.setRequestHeader('Content-Type', 'application/json');
            polling.xhr.setRequestHeader('X-WP-Nonce', polling.nonce);

            polling.xhr.onload = function () {
                if (polling.xhr.status !== 200) {
                    return;
                }

                polling.nonce = polling.xhr.getResponseHeader('X-WP-Nonce');
                polling.render();
            };
            polling.xhr.onloadend = function () {
                polling.xhr = null;
                polling.tick();
            };

            polling.xhr.send();

            return polling;
        },

        render() {
            var response;

            try {
                response = JSON.parse(polling.xhr.response);

                if (typeof response.html !== 'undefined') {
                    polling.replaceAdminBar(polling.stringToHTML(response.html));
                }
            } catch (e) {}

            return polling;
        },

        replaceAdminBar(adminBar) {
            var parent = polling.adminBar.parentNode;

            parent.insertBefore(adminBar, polling.adminBar);
            parent.removeChild(polling.adminBar);
            polling.adminBar = adminBar;

            if (window.innocodeJSONBarInit) {
                window.innocodeJSONBarInit();
            }

            return polling;
        },

        stringToHTML(str) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(str, 'text/html');

            return doc.getElementById('wpadminbar');
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var adminBar = document.getElementById('wpadminbar');

        if (adminBar && ('querySelectorAll' in adminBar)) {
            polling.init(adminBar).tick();
        }
    });

})(window.innocodeJSONBar || {});
