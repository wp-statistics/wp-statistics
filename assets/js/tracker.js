const WpStatisticsUserTracker={lastUrl:window.location.href,originalPushState:history.pushState,originalReplaceState:history.replaceState,checkTime:WP_Statistics_Tracker_Object.jsCheckTime,isDndActive:parseInt(navigator.msDoNotTrack||window.doNotTrack||navigator.doNotTrack,10),hasTrackerInitializedOnce:!1,hitRequestSuccessful:!0,init:function(){this.hasTrackerInitializedOnce||(this.hasTrackerInitializedOnce=!0,WP_Statistics_Tracker_Object.option.isPreview)||("undefined"==typeof WP_Statistics_Tracker_Object?console.error("WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary."):(this.checkHitRequestConditions(),WP_Statistics_Tracker_Object.option.userOnline&&this.keepUserOnline()),this.trackUrlChange())},base64Encode:function(t){t=(new TextEncoder).encode(t);return btoa(String.fromCharCode.apply(null,t))},getPathAndQueryString:function(){var t=window.location.pathname,e=window.location.search;return this.base64Encode(t+e)},getReferred:function(){return this.base64Encode(document.referrer)},checkHitRequestConditions:function(){!WP_Statistics_Tracker_Object.option.dntEnabled||1!==this.isDndActive?this.sendHitRequest():console.log("WP Statistics: Do Not Track (DNT) is enabled. Hit request not sent.")},sendHitRequest:async function(){try{var t=this.getRequestUrl("hit"),e=new URLSearchParams({...WP_Statistics_Tracker_Object.hitParams,referred:this.getReferred(),page_uri:this.getPathAndQueryString()}).toString();const i=new XMLHttpRequest;i.open("POST",t,!0),i.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),i.send(e),i.onreadystatechange=function(){var t;4===i.readyState&&(200===i.status?(t=JSON.parse(i.responseText),this.hitRequestSuccessful=!1!==t.status):(this.hitRequestSuccessful=!1,console.warn("WP Statistics: Hit request failed with status "+i.status)))}.bind(this)}catch(t){this.hitRequestSuccessful=!1,console.error("WP Statistics: Error sending hit request:",t)}},sendOnlineUserRequest:async function(){if(this.hitRequestSuccessful)try{var t=this.getRequestUrl("online"),e=new URLSearchParams({...WP_Statistics_Tracker_Object.onlineParams,referred:this.getReferred(),page_uri:this.getPathAndQueryString()}).toString(),i=new XMLHttpRequest;i.open("POST",t,!0),i.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),i.send(e)}catch(t){console.error("WP Statistics: Error sending online user request:",t)}},keepUserOnline:function(){let t;if(WP_Statistics_Tracker_Object.option.userOnline){const e=setInterval(function(){(!WP_Statistics_Tracker_Object.option.dntEnabled||WP_Statistics_Tracker_Object.option.dntEnabled&&1!==this.isDndActive)&&this.hitRequestSuccessful&&this.sendOnlineUserRequest()}.bind(this),this.checkTime),i=()=>{clearTimeout(t),t=setTimeout(()=>{clearInterval(e)},18e5)};["click","keypress","scroll","DOMContentLoaded"].forEach(t=>{window.addEventListener(t,()=>{window.removeEventListener(t,i),window.addEventListener(t,i)})})}},getRequestUrl:function(t){let e=WP_Statistics_Tracker_Object.requestUrl+"/";return WP_Statistics_Tracker_Object.option.bypassAdBlockers?e=WP_Statistics_Tracker_Object.ajaxUrl:"hit"===t?e+=WP_Statistics_Tracker_Object.hitParams.endpoint:"online"===t&&(e+=WP_Statistics_Tracker_Object.onlineParams.endpoint),e},updateTrackerObject:function(){var t=document.getElementById("wp-statistics-tracker-js-extra");if(t)try{WP_Statistics_Tracker_Object=JSON.parse(t.innerHTML.replace("var WP_Statistics_Tracker_Object = ","").replace(";",""))}catch(t){}},trackUrlChange:function(){const t=this;window.removeEventListener("popstate",t.handleUrlChange),history.pushState=function(){t.originalPushState.apply(history,arguments),t.handleUrlChange()},history.replaceState=function(){t.originalReplaceState.apply(history,arguments),t.handleUrlChange()},window.addEventListener("popstate",function(){t.handleUrlChange()})},handleUrlChange:function(){window.location.href!==this.lastUrl&&(this.lastUrl=window.location.href,this.updateTrackerObject(),this.hasTrackerInitializedOnce=!1,this.init())}};
const WpStatisticsEventTracker={hasEventsInitializedOnce:!1,downloadTracker:!1,linkTracker:!1,init:async function(){this.hasEventsInitializedOnce||WP_Statistics_Tracker_Object.isLegacyEventLoaded||(this.hasEventsInitializedOnce=!0,"undefined"!=typeof WP_Statistics_DataPlus_Event_Object&&(this.downloadTracker=WP_Statistics_DataPlus_Event_Object.options.downloadTracker,this.linkTracker=WP_Statistics_DataPlus_Event_Object.options.linkTracker,this.downloadTracker||this.linkTracker)&&this.captureEvent(),"undefined"!=typeof WP_Statistics_Marketing_Event_Object&&(window.wp_statistics_event=this.handleCustomEvent.bind(this),this.captureCustomEvents()))},handleCustomEvent:function(t,e={}){var n=WP_Statistics_Marketing_Event_Object.events.custom,a=WP_Statistics_Marketing_Event_Object.customEventAjaxUrl;e.timestamp=Date.now(),e.resource_id||(e.resource_id=WP_Statistics_Tracker_Object.hitParams.source_id),n.includes(t)?(n={event_name:t,event_data:JSON.stringify(e)},this.sendEventData(n,a)):console.log("WP Statistics: Unrecognized custom event: "+t)},captureCustomEvents:function(){WP_Statistics_Marketing_Event_Object.events.clicks.forEach(e=>{!e.selector||null!=e.scope&&e.scope!=WP_Statistics_Tracker_Object.hitParams.source_id||document.querySelectorAll(""+e.selector).forEach(t=>{t.addEventListener("click",t=>{t={text:t.target.textContent,id:t.currentTarget.id,class:t.currentTarget.className,target:t.currentTarget.href};this.handleCustomEvent(e.name,t)})})})},captureEvent:function(){document.querySelectorAll("a").forEach(t=>{t.addEventListener("click",async t=>this.handleEvent(t)),t.addEventListener("mouseup",async t=>this.handleEvent(t))})},handleEvent:async function(t){var e;"mouseup"==t.type&&1!=t.button||(t=this.prepareEventData(t))&&(e=WP_Statistics_DataPlus_Event_Object.eventAjaxUrl,await this.sendEventData(t,e))},prepareEventData:function(t){let e={en:t.type,et:Date.now(),eid:t.currentTarget.id,ec:t.currentTarget.className,ev:"",mb:t.button,fn:"",fx:"",m:"",tu:"",pid:""};return"A"===t.currentTarget.tagName&&(e=this.extractLinkData(t,e)),"undefined"!=typeof WP_Statistics_Tracker_Object&&(e.pid=WP_Statistics_Tracker_Object.hitParams.source_id),e},extractLinkData(t,e){var n=t.target.textContent,a=t.currentTarget.href,i=WP_Statistics_DataPlus_Event_Object.fileExtensions,i=new RegExp("\\.("+i.join("|")+")$","i"),n=(n&&(e.ev=n),a&&(e.tu=a),t.currentTarget.classList.contains("woocommerce-MyAccount-downloads-file")||a.includes("download_file="));if(e.wcdl=n,(i.test(a)||n)&&(t=new URL(a).pathname,e.df=n?a.substring(a.lastIndexOf("download_file=")+14).split("&").shift():"",e.dk=n?a.substring(a.lastIndexOf("key=")+4).split("&").shift():"",e.en="file_download",e.fn=n?e.df:t.substring(t.lastIndexOf("/")+1).split(".").shift(),e.fx=n?e.df:t.split(".").pop()),"click"===e.en){if(!this.linkTracker)return!1;if(a.toLowerCase().includes(window.location.host))return!1}return!("file_download"===e.en&&!this.downloadTracker)&&e},sendEventData:async function(t,e){var n=new URLSearchParams;for(const a in t)n.append(a,t[a]);if(!e)throw new Error("AJAX URL is not defined.");try{(await fetch(e,{method:"POST",keepalive:!0,body:n})).ok}catch(t){console.error("Error:",t)}}};
document.addEventListener("DOMContentLoaded",function(){"disabled"!=WP_Statistics_Tracker_Object.option.consentLevel&&!WP_Statistics_Tracker_Object.option.trackAnonymously&&WP_Statistics_Tracker_Object.option.isWpConsentApiActive&&!wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)||(WpStatisticsUserTracker.init(),WpStatisticsEventTracker.init()),document.addEventListener("wp_listen_for_consent_change",function(t){var e,i=t.detail;for(e in i)i.hasOwnProperty(e)&&e===WP_Statistics_Tracker_Object.option.consentLevel&&"allow"===i[e]&&(WpStatisticsUserTracker.init(),WpStatisticsEventTracker.init(),WP_Statistics_Tracker_Object.option.trackAnonymously)&&WpStatisticsUserTracker.checkHitRequestConditions()})});