const WpStatisticsEventTracker = {
    // Prevent init() from running more than once
    hasEventsInitializedOnce: false,
    downloadTracker: false,
    linkTracker: false,

    init: async function () {
        if (this.hasEventsInitializedOnce || WP_Statistics_Tracker_Object.isLegacyEventLoaded) {
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
    },

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
            await this.sendEventData(eventData);
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
        const targetValue    = event.target.textContent;
        const targetUrl      = event.currentTarget.href;
        const fileExtensions = WP_Statistics_DataPlus_Event_Object.fileExtensions;
        const fileExtRegex   = new RegExp('\\.(' + fileExtensions.join('|') + ')$', 'i');

        // Get target value from textContent
        if (targetValue) {
            eventData.ev = targetValue;
        }

        // Get target url from href attribute
        if (targetUrl) {
            eventData.tu = targetUrl;
        }

        // Detect if the link is a WooCommerce download file
        const isWooCommerceDownloadLink = event.currentTarget.classList.contains('woocommerce-MyAccount-downloads-file') || targetUrl.includes('download_file=');
        eventData.wcdl = isWooCommerceDownloadLink;

        // Extract file name and extension from <a> tag href
        if (fileExtRegex.test(targetUrl) || isWooCommerceDownloadLink) {
            const url      = new URL(targetUrl);
            const pathname = url.pathname;
            eventData.df   = isWooCommerceDownloadLink ? targetUrl.substring(targetUrl.lastIndexOf('download_file=') + 14).split('&').shift() : '';
            eventData.dk   = isWooCommerceDownloadLink ? targetUrl.substring(targetUrl.lastIndexOf('key=') + 4).split('&').shift() : '';
            eventData.en   = 'file_download';

            // If the link is a WooCommerce download file, add a non empty value to file name and extension (so that they don't get skipped in the parser)
            eventData.fn   = isWooCommerceDownloadLink ? eventData.df : pathname.substring(pathname.lastIndexOf('/') + 1).split('.').shift();
            eventData.fx   = isWooCommerceDownloadLink ? eventData.df : pathname.split('.').pop();
        }

        // If it's a click event
        if (eventData.en === 'click') {
            // If link tracker is disabled, skip tracking
            if (!this.linkTracker) return false;

            // If target link is internal, skip tracking
            if (targetUrl.toLowerCase().includes(window.location.host)) return false;
        }

        // If it's a download event
        if (eventData.en === 'file_download') {
            // If download tracker is disabled, skip tracking
            if (!this.downloadTracker) return false;
        }

        return eventData;
    },

    sendEventData: async function (eventData) {
        const formData = new URLSearchParams();
        for (const key in eventData) {
            formData.append(key, eventData[key]);
        }

        try {
            const ajaxUrl = WP_Statistics_DataPlus_Event_Object.eventAjaxUrl;

            if (!ajaxUrl) {
                throw new Error('DataPlus Event Ajax URL is not defined.');
            }

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