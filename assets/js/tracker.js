window.WpStatisticsUserTracker||(window.WpStatisticsUserTracker={lastUrl:window.location.href,originalPushState:history.pushState,originalReplaceState:history.replaceState,checkTime:WP_Statistics_Tracker_Object.jsCheckTime,isDndActive:parseInt(navigator.msDoNotTrack||window.doNotTrack||navigator.doNotTrack,10),hasTrackerInitializedOnce:!1,hitRequestSuccessful:!0,init:function(){this.hasTrackerInitializedOnce||(this.hasTrackerInitializedOnce=!0,WP_Statistics_Tracker_Object.option.isPreview)||(void 0===WP_Statistics_Tracker_Object?console.error("WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary."):(this.checkHitRequestConditions(),WP_Statistics_Tracker_Object.option.userOnline&&this.keepUserOnline()),this.trackUrlChange())},base64Encode:function(t){t=(new TextEncoder).encode(t);return btoa(String.fromCharCode.apply(null,t))},getPathAndQueryString:function(){var t=window.location.pathname,e=window.location.search;return this.base64Encode(t+e)},getReferred:function(){return this.base64Encode(document.referrer)},checkHitRequestConditions:function(){!WP_Statistics_Tracker_Object.option.dntEnabled||1!==this.isDndActive?this.sendHitRequest():console.log("WP Statistics: Do Not Track (DNT) is enabled. Hit request not sent.")},sendHitRequest:async function(){try{var t=this.getRequestUrl("hit"),i=new URLSearchParams({...WP_Statistics_Tracker_Object.hitParams,referred:this.getReferred(),page_uri:this.getPathAndQueryString()}).toString();let e=new XMLHttpRequest;e.open("POST",t,!0),e.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),e.send(i),e.onreadystatechange=function(){var t;4===e.readyState&&(200===e.status?(t=JSON.parse(e.responseText),this.hitRequestSuccessful=!1!==t.status):(this.hitRequestSuccessful=!1,console.warn("WP Statistics: Hit request failed with status "+e.status)))}.bind(this)}catch(t){this.hitRequestSuccessful=!1,console.error("WP Statistics: Error sending hit request:",t)}},sendOnlineUserRequest:async function(){if(this.hitRequestSuccessful)try{var t=this.getRequestUrl("online"),e=new URLSearchParams({...WP_Statistics_Tracker_Object.onlineParams,referred:this.getReferred(),page_uri:this.getPathAndQueryString()}).toString(),i=new XMLHttpRequest;i.open("POST",t,!0),i.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),i.send(e)}catch(t){console.error("WP Statistics: Error sending online user request:",t)}},keepUserOnline:function(){let i;if(WP_Statistics_Tracker_Object.option.userOnline){let e=setInterval(function(){WP_Statistics_Tracker_Object.option.dntEnabled&&(WP_Statistics_Tracker_Object.option.dntEnabled,1===this.isDndActive)||!this.hitRequestSuccessful||this.sendOnlineUserRequest()}.bind(this),this.checkTime);["click","keypress","scroll","DOMContentLoaded"].forEach(t=>{window.addEventListener(t,()=>{clearTimeout(i),i=setTimeout(()=>{clearInterval(e)},18e5)})})}},getRequestUrl:function(t){let e=WP_Statistics_Tracker_Object.requestUrl+"/";return WP_Statistics_Tracker_Object.option.bypassAdBlockers?e=WP_Statistics_Tracker_Object.ajaxUrl:"hit"===t?e+=WP_Statistics_Tracker_Object.hitParams.endpoint:"online"===t&&(e+=WP_Statistics_Tracker_Object.onlineParams.endpoint),e},updateTrackerObject:function(){var t=document.getElementById("wp-statistics-tracker-js-extra");if(t)try{var e=t.innerHTML.match(/var\s+WP_Statistics_Tracker_Object\s*=\s*(\{[\s\S]*?\});/);e&&e[1]&&(WP_Statistics_Tracker_Object=JSON.parse(e[1]))}catch(t){console.error("WP Statistics: Error parsing WP_Statistics_Tracker_Object",t)}},trackUrlChange:function(){let t=this;window.removeEventListener("popstate",t.handleUrlChange),history.pushState=function(){t.originalPushState.apply(history,arguments),t.handleUrlChange()},history.replaceState=function(){t.originalReplaceState.apply(history,arguments),t.handleUrlChange()},window.addEventListener("popstate",function(){t.handleUrlChange()})},handleUrlChange:function(){window.location.href!==this.lastUrl&&(this.lastUrl=window.location.href,this.updateTrackerObject(),this.hasTrackerInitializedOnce=!1,this.init())}});
window.WpStatisticsEventTracker||(window.WpStatisticsEventTracker={hasEventsInitializedOnce:!1,downloadTracker:!1,linkTracker:!1,init:async function(){this.hasEventsInitializedOnce||WP_Statistics_Tracker_Object.isLegacyEventLoaded||(this.hasEventsInitializedOnce=!0,"undefined"!=typeof WP_Statistics_DataPlus_Event_Object&&(this.downloadTracker=WP_Statistics_DataPlus_Event_Object.options.downloadTracker,this.linkTracker=WP_Statistics_DataPlus_Event_Object.options.linkTracker,this.downloadTracker||this.linkTracker)&&this.captureEvent())},captureEvent:function(){document.querySelectorAll("a").forEach(t=>{t.addEventListener("click",async t=>this.handleEvent(t)),t.addEventListener("mouseup",async t=>this.handleEvent(t))})},handleEvent:async function(t){"mouseup"==t.type&&1!=t.button||(t=this.prepareEventData(t))&&await this.sendEventData(t)},prepareEventData:function(t){let e={en:t.type,et:Date.now(),eid:t.currentTarget.id,ec:t.currentTarget.className,ev:"",mb:t.button,fn:"",fx:"",m:"",tu:"",pid:""};return"A"===t.currentTarget.tagName&&(e=this.extractLinkData(t,e)),"undefined"!=typeof WP_Statistics_Tracker_Object&&(e.pid=WP_Statistics_Tracker_Object.hitParams.source_id),e},extractLinkData(t,e){var n=t.target.textContent,a=t.currentTarget.href,i=WP_Statistics_DataPlus_Event_Object.fileExtensions,i=new RegExp("\\.("+i.join("|")+")$","i"),n=(n&&(e.ev=n),a&&(e.tu=a),t.currentTarget.classList.contains("woocommerce-MyAccount-downloads-file")||a.includes("download_file="));if(e.wcdl=n,(i.test(a)||n)&&(t=new URL(a).pathname,e.df=n?a.substring(a.lastIndexOf("download_file=")+14).split("&").shift():"",e.dk=n?a.substring(a.lastIndexOf("key=")+4).split("&").shift():"",e.en="file_download",e.fn=n?e.df:t.substring(t.lastIndexOf("/")+1).split(".").shift(),e.fx=n?e.df:t.split(".").pop()),"click"===e.en){if(!this.linkTracker)return!1;if(a.toLowerCase().includes(window.location.host))return!1}return!("file_download"===e.en&&!this.downloadTracker)&&e},sendEventData:async function(t){var e,n=new URLSearchParams;for(e in t)n.append(e,t[e]);try{var a=WP_Statistics_DataPlus_Event_Object.eventAjaxUrl;if(!a)throw new Error("DataPlus Event Ajax URL is not defined.");(await fetch(a,{method:"POST",keepalive:!0,body:n})).ok}catch(t){console.error("Error:",t)}}});
document.addEventListener("DOMContentLoaded",function(){"disabled"!=WP_Statistics_Tracker_Object.option.consentLevel&&!WP_Statistics_Tracker_Object.option.trackAnonymously&&WP_Statistics_Tracker_Object.option.isWpConsentApiActive&&!wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)||(WpStatisticsUserTracker.init(),WpStatisticsEventTracker.init()),document.addEventListener("wp_listen_for_consent_change",function(t){var e,i=t.detail;for(e in i)i.hasOwnProperty(e)&&e===WP_Statistics_Tracker_Object.option.consentLevel&&"allow"===i[e]&&(WpStatisticsUserTracker.init(),WpStatisticsEventTracker.init(),WP_Statistics_Tracker_Object.option.trackAnonymously)&&WpStatisticsUserTracker.checkHitRequestConditions()})});