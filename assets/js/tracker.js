let WP_Statistics_CheckTime=WP_Statistics_Tracker_Object.jsCheckTime,WP_Statistics_Dnd_Active=parseInt(navigator.msDoNotTrack||window.doNotTrack||navigator.doNotTrack,10),hasTrackerInitializedOnce=!1,referred=encodeURIComponent(document.referrer),wpStatisticsUserOnline={hitRequestSuccessful:!0,init:function(){hasTrackerInitializedOnce||(hasTrackerInitializedOnce=!0,"undefined"==typeof WP_Statistics_Tracker_Object?console.error("WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary."):(this.checkHitRequestConditions(),WP_Statistics_Tracker_Object.option.userOnline&&this.keepUserOnline()))},checkHitRequestConditions:function(){!WP_Statistics_Tracker_Object.option.dntEnabled||1!==WP_Statistics_Dnd_Active?this.sendHitRequest():console.log("WP Statistics: Do Not Track (DNT) is enabled. Hit request not sent.")},sendHitRequest:async function(){try{var t=this.getRequestUrl("hit"),s=new URLSearchParams({...WP_Statistics_Tracker_Object.hitParams,referred:referred}).toString();let e=new XMLHttpRequest;e.open("POST",t,!0),e.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),e.send(s),e.onreadystatechange=function(){var t;4===e.readyState&&(200===e.status?(t=JSON.parse(e.responseText),this.hitRequestSuccessful=!1!==t.status):(this.hitRequestSuccessful=!1,console.warn("WP Statistics: Hit request failed with status "+e.status)))}.bind(this)}catch(t){this.hitRequestSuccessful=!1,console.error("WP Statistics: Error sending hit request:",t)}},sendOnlineUserRequest:async function(){if(this.hitRequestSuccessful)try{var t=this.getRequestUrl("online"),e=new URLSearchParams({...WP_Statistics_Tracker_Object.onlineParams,referred:referred}).toString(),s=new XMLHttpRequest;s.open("POST",t,!0),s.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),s.send(e)}catch(t){}},keepUserOnline:function(){let s;if(WP_Statistics_Tracker_Object.option.userOnline){let e=setInterval(function(){(!WP_Statistics_Tracker_Object.option.dntEnabled||WP_Statistics_Tracker_Object.option.dntEnabled&&1!==WP_Statistics_Dnd_Active)&&this.hitRequestSuccessful&&this.sendOnlineUserRequest()}.bind(this),WP_Statistics_CheckTime);["click","keypress","scroll","DOMContentLoaded"].forEach(t=>{window.addEventListener(t,()=>{clearTimeout(s),s=setTimeout(()=>{clearInterval(e)},18e5)})})}},getRequestUrl:function(t){let e=WP_Statistics_Tracker_Object.requestUrl+"/";return WP_Statistics_Tracker_Object.option.bypassAdBlockers?e=WP_Statistics_Tracker_Object.ajaxUrl:"hit"===t?e+=WP_Statistics_Tracker_Object.hitParams.endpoint:"online"===t&&(e+=WP_Statistics_Tracker_Object.onlineParams.endpoint),e}};document.addEventListener("DOMContentLoaded",function(){"disabled"!=WP_Statistics_Tracker_Object.option.consentLevel&&!WP_Statistics_Tracker_Object.option.trackAnonymously&&WP_Statistics_Tracker_Object.option.isWpConsentApiActive&&!wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)||wpStatisticsUserOnline.init(),document.addEventListener("wp_listen_for_consent_change",function(t){var e,s=t.detail;for(e in s)s.hasOwnProperty(e)&&e===WP_Statistics_Tracker_Object.option.consentLevel&&"allow"===s[e]&&(wpStatisticsUserOnline.init(),WP_Statistics_Tracker_Object.option.trackAnonymously)&&wpStatisticsUserOnline.checkHitRequestConditions()})});