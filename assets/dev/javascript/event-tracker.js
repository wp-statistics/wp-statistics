if (!window.WpStatisticsEventTracker) {
    window.WpStatisticsEventTracker = {
        // Prevent init() from running more than once
        hasEventsInitializedOnce: false,
        isTrackingEnabled: false,
        downloadTracker: false,
        linkTracker: false,
        guardedLegacyEventTracker: null,

        init: async function () {
            this.isTrackingEnabled = true;

            if (WP_Statistics_Tracker_Object.isLegacyEventLoaded) {
                this.guardLegacyEventTracker();
                return;
            }

            if (this.hasEventsInitializedOnce) {
                return;
            }
            this.hasEventsInitializedOnce = true;

            // Capture click and download events when DataPlus is active
            if (typeof WP_Statistics_DataPlus_Event_Object !== 'undefined') {
                this.downloadTracker = WP_Statistics_DataPlus_Event_Object.options.downloadTracker;
                this.linkTracker = WP_Statistics_DataPlus_Event_Object.options.linkTracker;

            if (this.downloadTracker || this.linkTracker) {
                this.captureEvent();
            }
        }

        // Attach handleCustomEvent to window object
        window.wp_statistics_event = this.handleCustomEvent.bind(this);

        // Capture custom events when Marketing is active
        if (typeof WP_Statistics_Marketing_Event_Object !== 'undefined') {
            this.captureCustomClickEvents();
        }
    },

    stop: function () {
        this.isTrackingEnabled = false;
    },

    guardLegacyEventTracker: function () {
        // Older Data Plus declares this with a top-level `let`, so it is a global binding, not a window property.
        const legacyTracker = typeof wpStatisticsDataPlusEvents !== 'undefined' ? wpStatisticsDataPlusEvents : window.wpStatisticsDataPlusEvents;

        if (!legacyTracker || typeof legacyTracker.sendEventData !== 'function' || this.guardedLegacyEventTracker === legacyTracker) {
            return;
        }

        const eventTracker = this;
        const sendEventData = legacyTracker.sendEventData;

        legacyTracker.sendEventData = function () {
            if (!eventTracker.isTrackingEnabled) {
                return Promise.resolve();
            }

            return sendEventData.apply(this, arguments);
        };
        this.guardedLegacyEventTracker = legacyTracker;
    },

    // Handles preparing and sending marketing custom events to the server
    handleCustomEvent: function (eventName, eventData = {}) {
        // The nonce travels in the POST body, so no tracking URL with a query string is printed in the page HTML.
        // customEventAjaxUrl only exists on pages cached before this change.
        const ajaxUrl = WP_Statistics_Tracker_Object.customEventAjaxUrl || WP_Statistics_Tracker_Object.ajaxUrl;

        // Add timestamp
        eventData.timestamp = Date.now();

        // If resource_id is not set, set it to the source_id by default
        if (!eventData.resource_id) {
            eventData.resource_id = WP_Statistics_Tracker_Object.hitParams.source_id;
        }

        const data = {
            event_name: eventName,
            event_data: JSON.stringify(eventData)
        };

        if (WP_Statistics_Tracker_Object.customEventNonce) {
            data.action = 'wp_statistics_custom_event';
            data.nonce = WP_Statistics_Tracker_Object.customEventNonce;
        }

        this.sendEventData(data, ajaxUrl);
    },

    // Captures custom click events from Marketing
    captureCustomClickEvents: function () {
        const events = WP_Statistics_Marketing_Event_Object.events.clicks;

        events.forEach(event => {
            // Skip if event has no selector
            if (!event.selector) {
                return;
            }

            // Check if the custom event scope is global or specific to a page
            if (event.scope != null && event.scope != WP_Statistics_Tracker_Object.hitParams.source_id) {
                return;
            }

            const elements = document.querySelectorAll(`${event.selector}`);

            elements.forEach(element => {
                element.addEventListener('click', (e) => {
                    const eventData = {
                        text: e.target.textContent,
                        id: e.currentTarget.id,
                        class: e.currentTarget.className,
                        target: e.currentTarget.href
                    };

                    this.handleCustomEvent(event.name, eventData)
                });
            });
        });
    },

    // DataPlus click and download events
    captureEvent: function () {
        const elementsToObserve = document.querySelectorAll('a');
        elementsToObserve.forEach(element => {
            element.addEventListener('click', async (event) => await this.handleEvent(event));
            element.addEventListener('mouseup', async (event) => await this.handleEvent(event));
        });
    },

        handleEvent: async function (event) {
            if (event.type == 'mouseup' && event.button != 1) {
                // Only track middle click if the event was mouseup
                return;
            }

        const eventData = this.prepareEventData(event);
        if (eventData) {
            const ajaxUrl = WP_Statistics_DataPlus_Event_Object.eventAjaxUrl;
            await this.sendEventData(eventData, ajaxUrl);
        }
    },

        prepareEventData: function (event) {
            let eventData = {
                en: event.type,
                et: Date.now(),
                eid: event.currentTarget.id,
                ec: event.currentTarget.className,
                ev: '',
                mb: event.button,
                fn: '',
                fx: '',
                m: '',
                tu: '',
                pid: '',
            };

            // Extract event data from a tag
            if (event.currentTarget.tagName === 'A') {
                eventData = this.extractLinkData(event, eventData);
            }

            // Add page ID to eventData object
            if (typeof WP_Statistics_Tracker_Object !== 'undefined') {
                eventData.pid = WP_Statistics_Tracker_Object.hitParams.source_id;
            }

            return eventData;
        },

        extractLinkData(event, eventData) {
            const targetValue = event.target.textContent;
            const targetUrl = event.currentTarget.href;
            const fileExtensions = WP_Statistics_DataPlus_Event_Object.fileExtensions;
            const fileExtRegex = new RegExp('\\.(' + fileExtensions.join('|') + ')$', 'i');

            // Get target value from textContent
            if (targetValue) {
                eventData.ev = targetValue;
            }

            // Only real links are tracked: no href, "#" or javascript: links are skipped
            if (!targetUrl || /^(javascript|data|about):/i.test(targetUrl)) {
                return false;
            }

            // Get target url from href attribute
            eventData.tu = targetUrl;

            // Detect if the link is a WooCommerce download file
            const isWooCommerceDownloadLink = event.currentTarget.classList.contains('woocommerce-MyAccount-downloads-file') || targetUrl.includes('download_file=');
            eventData.wcdl = isWooCommerceDownloadLink;

            // Find the file: at the end of the link (/files/report.pdf), as before, or in one of the
            // known download parameters (/?protected_download=report.pdf). Other parameters are not
            // checked, so search, share and player links are not counted as downloads.
            let fileName = '';
            const linkPath = targetUrl.split(/[?#]/).shift();
            if (fileExtRegex.test(linkPath)) {
                // Paths keep percent-encoding (My%20Report.pdf), so decode them once
                fileName = linkPath.substring(linkPath.lastIndexOf('/') + 1);
                try {
                    fileName = decodeURIComponent(fileName);
                } catch (e) {
                    // Leave malformed encoding (100%.pdf) as it is
                }
            }
            if (!fileName) {
                const queryKeys = (typeof WP_Statistics_Tracker_Object !== 'undefined' && Array.isArray(WP_Statistics_Tracker_Object.downloadQueryKeys)) ? WP_Statistics_Tracker_Object.downloadQueryKeys : [];
                try {
                    // searchParams.get() already decodes the value, so it is not decoded again
                    const params = new URL(targetUrl).searchParams;
                    const key = queryKeys.find(k => fileExtRegex.test(params.get(k) || ''));
                    fileName = key ? params.get(key) : '';
                } catch (e) {
                    // Not a valid URL, so not a download link
                }
                fileName = fileName.substring(fileName.lastIndexOf('/') + 1);
            }

            // Extract file name and extension from <a> tag href
            if (fileName || isWooCommerceDownloadLink) {
                let wcParams = null;
                try {
                    wcParams = new URL(targetUrl).searchParams;
                } catch (e) {
                    // Not a valid URL
                }
                eventData.df = isWooCommerceDownloadLink && wcParams ? (wcParams.get('download_file') || '') : '';
                eventData.dk = isWooCommerceDownloadLink && wcParams ? (wcParams.get('key') || '') : '';
                eventData.en = 'file_download';

                // If the link is a WooCommerce download file, add a non empty value to file name and extension (so that they don't get skipped in the parser)
                eventData.fn = isWooCommerceDownloadLink ? eventData.df : fileName.substring(0, fileName.lastIndexOf('.'));
                eventData.fx = isWooCommerceDownloadLink ? eventData.df : fileName.split('.').pop();

                // Data Plus before 2.9.0 drops a file name with "%" (100%.pdf, file%20name.pdf). Send the
                // exact name as `fnr` (read by newer Data Plus) and, in `fn`, the name those versions accept.
                if (!isWooCommerceDownloadLink && eventData.fn.indexOf('%') !== -1) {
                    eventData.fnr = eventData.fn;
                    let legacyName = eventData.fn;
                    try {
                        legacyName = decodeURIComponent(legacyName);
                    } catch (e) {
                        // Malformed encoding (100%): keep it and drop the "%" below
                    }
                    eventData.fn = legacyName.replace(/%/g, '').trim() || eventData.fn;
                }
            }

            // If it's a click event (a middle click arrives as mouseup)
            if (eventData.en === 'click' || eventData.en === 'mouseup') {
                // If link tracker is disabled, skip tracking
                if (!this.linkTracker) return false;

                // If target link is internal, skip tracking
                // Compare hostnames instead of substring matching to avoid false positives from query strings
                try {
                    const targetHost = new URL(targetUrl).host;
                    if (targetHost === window.location.host) return false;
                } catch (e) {
                    // If URL parsing fails, fall back to allowing tracking (non-http schemes may be handled server-side)
                }
            }

            // If it's a download event
            if (eventData.en === 'file_download') {
                // If download tracker is disabled, skip tracking
                if (!this.downloadTracker) return false;
            }

            return eventData;
        },

    sendEventData: async function (eventData, ajaxUrl) {
        if (!this.isTrackingEnabled) {
            return;
        }

        const formData = new URLSearchParams();
        for (const key in eventData) {
            formData.append(key, eventData[key]);
        }

        if (!ajaxUrl) {
            throw new Error('AJAX URL is not defined.');
        }

        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                keepalive: true,
                body: formData
            });

                if (response.ok) {
                    // Response processing can be done here if needed
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        // Additional methods can be added here if needed
    };
}