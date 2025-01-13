const WpStatisticsUserTracker={checkTime:WP_Statistics_Tracker_Object.jsCheckTime,isDndActive:parseInt(navigator.msDoNotTrack||window.doNotTrack||navigator.doNotTrack,10),hasTrackerInitializedOnce:!1,hitRequestSuccessful:!0,init:function(){this.hasTrackerInitializedOnce||(this.hasTrackerInitializedOnce=!0,WP_Statistics_Tracker_Object.option.isPreview)||("undefined"==typeof WP_Statistics_Tracker_Object?console.error("WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary."):(this.checkHitRequestConditions(),WP_Statistics_Tracker_Object.option.userOnline&&this.keepUserOnline()))},base64Encode:function(e){e=(new TextEncoder).encode(e);return btoa(String.fromCharCode.apply(null,e))},getPathAndQueryString:function(){var e=window.location.pathname,t=window.location.search;return this.base64Encode(e+t)},getReferred:function(){return this.base64Encode(document.referrer)},checkHitRequestConditions:function(){!WP_Statistics_Tracker_Object.option.dntEnabled||1!==this.isDndActive?this.sendHitRequest():console.log("WP Statistics: Do Not Track (DNT) is enabled. Hit request not sent.")},sendHitRequest:async function(){try{var e=this.getRequestUrl("hit"),t=new URLSearchParams({...WP_Statistics_Tracker_Object.hitParams,referred:this.getReferred(),page_uri:this.getPathAndQueryString()}).toString();const s=new XMLHttpRequest;s.open("POST",e,!0),s.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),s.send(t),s.onreadystatechange=function(){var e;4===s.readyState&&(200===s.status?(e=JSON.parse(s.responseText),this.hitRequestSuccessful=!1!==e.status):(this.hitRequestSuccessful=!1,console.warn("WP Statistics: Hit request failed with status "+s.status)))}.bind(this)}catch(e){this.hitRequestSuccessful=!1,console.error("WP Statistics: Error sending hit request:",e)}},sendOnlineUserRequest:async function(){if(this.hitRequestSuccessful)try{var e=this.getRequestUrl("online"),t=new URLSearchParams({...WP_Statistics_Tracker_Object.onlineParams,referred:this.getReferred(),page_uri:this.getPathAndQueryString()}).toString(),s=new XMLHttpRequest;s.open("POST",e,!0),s.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),s.send(t)}catch(e){console.error("WP Statistics: Error sending online user request:",e)}},keepUserOnline:function(){let t;if(WP_Statistics_Tracker_Object.option.userOnline){const s=setInterval(function(){(!WP_Statistics_Tracker_Object.option.dntEnabled||WP_Statistics_Tracker_Object.option.dntEnabled&&1!==this.isDndActive)&&this.hitRequestSuccessful&&this.sendOnlineUserRequest()}.bind(this),this.checkTime);["click","keypress","scroll","DOMContentLoaded"].forEach(e=>{window.addEventListener(e,()=>{clearTimeout(t),t=setTimeout(()=>{clearInterval(s)},18e5)})})}},getRequestUrl:function(e){let t=WP_Statistics_Tracker_Object.requestUrl+"/";return WP_Statistics_Tracker_Object.option.bypassAdBlockers?t=WP_Statistics_Tracker_Object.ajaxUrl:"hit"===e?t+=WP_Statistics_Tracker_Object.hitParams.endpoint:"online"===e&&(t+=WP_Statistics_Tracker_Object.onlineParams.endpoint),t}};
const WpStatisticsEventTracker={hasEventsInitializedOnce:!1,init:async function(){this.hasEventsInitializedOnce||(this.hasEventsInitializedOnce=!0,"undefined"!=typeof WP_Statistics_Event_Object&&WP_Statistics_Event_Object.eventAjaxUrl?(this.ajaxUrl=WP_Statistics_Event_Object.eventAjaxUrl,this.linkTrackerEnabled=WP_Statistics_Event_Object.linkTrackerEnabled,this.downloadTrackerEnabled=WP_Statistics_Event_Object.downloadTrackerEnabled,this.captureEvent()):console.error("WP_Statistics_Event_Object is not available or missing ajaxUrl."))},captureEvent:function(){document.querySelectorAll("a").forEach(t=>{t.addEventListener("click",async t=>this.handleEvent(t)),t.addEventListener("mouseup",async t=>this.handleEvent(t))})},handleEvent:async function(t){"mouseup"==t.type&&1!=t.button||(t=this.prepareEventData(t))&&await this.sendEventData(t)},prepareEventData:function(t){let e={en:t.type,et:Date.now(),eid:t.currentTarget.id,ec:t.currentTarget.className,ev:"",mb:t.button,fn:"",fx:"",m:"",tu:"",pid:""};return"A"===t.currentTarget.tagName&&(e=this.extractLinkData(t,e)),"undefined"!=typeof WP_Statistics_Tracker_Object&&(e.pid=WP_Statistics_Tracker_Object.hitParams.source_id),e},extractLinkData(t,e){var n=t.target.textContent,a=t.currentTarget.href,i=WP_Statistics_Event_Object.fileExtensions,i=new RegExp("\\.("+i.join("|")+")$","i"),n=(n&&(e.ev=n),a&&(e.tu=a),t.currentTarget.classList.contains("woocommerce-MyAccount-downloads-file")||a.includes("download_file="));if(e.wcdl=n,(i.test(a)||n)&&(t=new URL(a).pathname,e.df=n?a.substring(a.lastIndexOf("download_file=")+14).split("&").shift():"",e.dk=n?a.substring(a.lastIndexOf("key=")+4).split("&").shift():"",e.en="file_download",e.fn=n?e.df:t.substring(t.lastIndexOf("/")+1).split(".").shift(),e.fx=n?e.df:t.split(".").pop()),"click"===e.en){if(!this.linkTrackerEnabled)return!1;if(a.toLowerCase().includes(window.location.host))return!1}return!("file_download"===e.en&&!this.downloadTrackerEnabled)&&e},sendEventData:async function(t){var e=new URLSearchParams;for(const n in t)e.append(n,t[n]);try{(await fetch(this.ajaxUrl,{method:"POST",body:e})).ok}catch(t){console.error("Error:",t)}}};
document.addEventListener("DOMContentLoaded",function(){"disabled"!=WP_Statistics_Tracker_Object.option.consentLevel&&!WP_Statistics_Tracker_Object.option.trackAnonymously&&WP_Statistics_Tracker_Object.option.isWpConsentApiActive&&!wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)||(WpStatisticsUserTracker.init(),WpStatisticsEventTracker.init()),document.addEventListener("wp_listen_for_consent_change",function(t){var e,i=t.detail;for(e in i)i.hasOwnProperty(e)&&e===WP_Statistics_Tracker_Object.option.consentLevel&&"allow"===i[e]&&(WpStatisticsUserTracker.init(),WpStatisticsEventTracker.init(),WP_Statistics_Tracker_Object.option.trackAnonymously)&&WpStatisticsUserTracker.checkHitRequestConditions()})});