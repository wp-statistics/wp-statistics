/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/dev/javascript/pages/data-migration/step1.js":
/*!*************************************************************!*\
  !*** ./assets/dev/javascript/pages/data-migration/step1.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _images_info_icon_svg__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../../../images/info-icon.svg */ "./assets/images/info-icon.svg");





const Step1 = ({
  handleStep
}) => {
  const [option, setOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)("a");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    style: {
      fontFamily: 500,
      fontSize: 24,
      lineHeight: 1.3,
      margin: "8px 0px"
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)("We’ve updated WP Statistics to use a faster, more efficient database structure!", "wp-gutenberg")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      color: "#56585A",
      fontSize: 16
    }
  }, "By running this migration, you\u2019ll safely move all your", " ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      color: "#000"
    }
  }, "older stats"), " ", "into the new system. Any visits recorded", " ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      color: "#000"
    }
  }, "after"), " ", "your update are already being stored in the new format, so you won\u2019t lose any current data"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      backgroundColor: "#F6FAFF",
      border: "1px solid #4FA1FF66",
      borderRadius: "8px",
      padding: "16px",
      margin: "16px 0px",
      display: "flex",
      alignItems: "start",
      gap: 8
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    src: _images_info_icon_svg__WEBPACK_IMPORTED_MODULE_4__["default"],
    style: {
      width: "20px",
      height: "20px",
      marginTop: 5
    },
    alt: "info-icon"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: "10px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px"
    }
  }, "We recommend making a complete backup of your WordPress site. This is just in case you ever need to revert changes.", " ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: "#",
    style: {
      textDecoration: "underline"
    }
  }, "Learn how to back up your site")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px"
    }
  }, "Keep in mind the migration could take time. (anywhere from minutes to a few hours, depending on your site\u2019s size and server resources)."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px"
    }
  }, "You can pause, cancel, or restart the migration at any point. Your old data remains untouched until the process fully completes."))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "16px"
    }
  }, "When you\u2019re ready, simply choose your preferred migration option below and click Next. You\u2019re in full control, and your data will remain safe every step of the way."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    style: {
      fontFamily: 500,
      fontSize: 24,
      lineHeight: 1.3,
      marginTop: 32,
      marginBottom: 16
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)("Choose Your Preferred Migration", "wp-gutenberg")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: "12px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, {
    style: {
      border: option === "1" ? "2px solid #1e87f0" : "1px solid #ccc",
      borderRadius: 8,
      padding: "24px",
      cursor: "pointer",
      boxShadow: "none"
    },
    onClick: () => {}
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, {
    style: {
      padding: "0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      width: "100%"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px",
      fontFamily: 500,
      color: "#1E1E20",
      fontWeight: "700"
    }
  }, "Full Detailed Migration"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "radio",
    id: `1`,
    name: "migration-option",
    value: "1",
    checked: option === "1",
    onChange: () => setOption("1")
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      padding: "8px 0px",
      color: "#56585A"
    }
  }, "Moves all your historical data\u2014visitors, devices, referral sources, search engines, and more\u2014into the new database structure."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    style: {
      listStyle: "disc",
      paddingLeft: "30px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Estimated Time:"), " ", "Depending on your site\u2019s traffic history and server resources, this process can range from a few minutes to several hours."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Who It\u2019s For:"), " ", "Users who want to preserve every bit of their analytics data without losing any detail.")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, {
    style: {
      border: option === "2" ? "2px solid #1e87f0" : "1px solid #ccc",
      borderRadius: 8,
      padding: "24px",
      cursor: "pointer",
      boxShadow: "none"
    },
    onClick: () => {}
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, {
    style: {
      padding: "0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      width: "100%"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px",
      fontFamily: 500,
      color: "#1E1E20",
      fontWeight: "700"
    }
  }, "Summary-Only Migration"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "radio",
    id: `2`,
    name: "migration-option",
    value: "2",
    checked: option === "2",
    onChange: () => setOption("2")
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      padding: "8px 0px",
      color: "#56585A"
    }
  }, "Quickly transfers only the visitor counts and page-view totals for older data. You\u2019ll lose detailed information (like devices, referrers, and search engines) for past visitors."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    style: {
      listStyle: "disc",
      paddingLeft: "30px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Estimated Time:"), " ", "Typically much faster than a full migration, often just a few minutes."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Who It\u2019s For:"), " ", "Users who just need high-level trends and want the process done ASAP."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", null, "Learn more about Summary-Only Migration"))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, {
    style: {
      border: option === "3" ? "2px solid #1e87f0" : "1px solid #ccc",
      borderRadius: 8,
      padding: "24px",
      cursor: "pointer",
      boxShadow: "none"
    },
    onClick: () => {}
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, {
    style: {
      padding: "0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      width: "100%"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px",
      fontFamily: 500,
      color: "#1E1E20",
      fontWeight: "700"
    }
  }, "Hybrid Migration"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "radio",
    id: `2`,
    name: "migration-option",
    value: "3",
    checked: option === "3",
    onChange: () => setOption("3")
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      padding: "8px 0px",
      color: "#56585A"
    }
  }, "Imports full, detailed stats for your most recent history\u2014by default the last 90 days, while older data is brought in as summary-only."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    style: {
      listStyle: "disc",
      paddingLeft: "30px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Estimated Time:"), " ", "Longer than summary-only, but faster than a full detailed migration."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Who It\u2019s For:"), " ", "Users who want to retain granular data for a recent timeframe while speeding up the migration for\u2028older records.")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: "10px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Enter the number of days to migrate with full detail:"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    style: {
      outline: "none",
      border: "1px solid #DADCE0",
      width: "46px",
      height: "32px",
      borderRadius: "3px"
    }
  })))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardFooter, {
    style: {
      display: "flex",
      justifyContent: "flex-end",
      paddingTop: "32px",
      paddingBottom: "32px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    style: {
      background: "#404BF2",
      outline: "none",
      border: "none",
      padding: "12px 16px",
      borderRadius: "4px",
      color: "white",
      cursor: "pointer"
    },
    onClick: () => handleStep("step2")
  }, "Next Step")));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Step1);

/***/ }),

/***/ "./assets/dev/javascript/pages/data-migration/step2.js":
/*!*************************************************************!*\
  !*** ./assets/dev/javascript/pages/data-migration/step2.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);




const Step2 = ({
  handleStep
}) => {
  const [option, setOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)("a");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Card, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.CardBody, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    style: {
      fontFamily: 500,
      fontSize: 24,
      lineHeight: 1.3,
      marginTop: "8px",
      marginBottom: "16px"
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)("Confirmation Step", "wp-gutenberg")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Card, {
    style: {
      border: "1px solid #EEEFF1",
      borderRadius: 8,
      padding: "24px",
      cursor: "pointer",
      boxShadow: "none",
      background: "#FAFAFB"
    },
    onClick: () => {}
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.CardBody, {
    style: {
      padding: "0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      width: "100%"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px",
      fontFamily: 500,
      color: "#1E1E20",
      fontWeight: "700"
    }
  }, "Full Detailed Migration"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "radio",
    disabled: true,
    id: `1`,
    name: "migration-option",
    value: "1",
    checked: true
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      padding: "8px 0px",
      color: "#56585A"
    }
  }, "Moves all your historical data\u2014visitors, devices, referral sources, search engines, and more\u2014into the new database structure."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    style: {
      listStyle: "disc",
      paddingLeft: "30px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Estimated Time:"), " ", "Depending on your site\u2019s traffic history and server resources, this process can range from a few minutes to several hours."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Who It\u2019s For:"), " ", "Users who want to preserve every bit of their analytics data without losing any detail.")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      gap: "10px",
      flexDirection: "column",
      marginTop: "28px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "15px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "What\u2019s Next?"), " ", "We\u2019ll migrate all of your historical data\u2014visitors, devices, search engines, referrers, and more\u2014into the new database structure."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "15px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "What\u2019s Migrated?"), " ", "Absolutely everything from your past analytics, so you retain complete visibility into your site\u2019s historical data.", " "), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "15px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "What\u2019s Lost?"), " ", "Nothing! All detailed stats will be preserved.", " "), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "15px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Estimated Time:"), " ", "Depending on the size of your site and server performance, it can take anywhere from minutes to a few hours."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      padding: "15px 0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "15px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "bold"
    }
  }, "Regardless of the choice,"), " ", "you could also include these reminders at the bottom of the confirmation step:"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    style: {
      listStyle: "disc",
      paddingLeft: "22px",
      margin: "5px 0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      fontSize: "14px"
    }
  }, "You can pause, cancel, or restart the migration at any time."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      fontSize: "14px"
    }
  }, "Nothing is deleted from your old data source until the migration is fully complete."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontSize: "14px"
    }
  }, "Need more details or help? ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: ""
  }, "Check our Migration FAQs or contact support."))))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.CardFooter, {
    style: {
      flexDirection: "column"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontWeight: "500",
      fontSize: "16px"
    }
  }, "Ready to proceed?"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      fontWeight: "400",
      fontSize: "14px",
      color: "#56585A",
      paddingTop: "4px"
    }
  }, "You can", " ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "500",
      color: "#000"
    }
  }, "go back"), " ", "to change the number of days or pick a different migration method.Or, click", " ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      fontWeight: "500",
      color: "#000"
    }
  }, "Start Migration"), " ", "to begin.")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      width: "100%",
      padding: "10px 0px"
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      cursor: "pointer"
    },
    onClick: () => handleStep("step1")
  }, `< Go Back`), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    style: {
      background: "#404BF2",
      outline: "none",
      border: "none",
      padding: "12px 16px",
      borderRadius: "4px",
      color: "white",
      cursor: "pointer"
    },
    onClick: () => {}
  }, "Start Migration"))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Step2);

/***/ }),

/***/ "./assets/images/info-icon.svg":
/*!*************************************!*\
  !*** ./assets/images/info-icon.svg ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ReactComponent: () => (/* binding */ SvgInfoIcon),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
var _path;
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }

var SvgInfoIcon = function SvgInfoIcon(props) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("svg", _extends({
    xmlns: "http://www.w3.org/2000/svg",
    width: 15,
    height: 15,
    fill: "none"
  }, props), _path || (_path = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("path", {
    fill: "#3088EC",
    d: "M7.501 14.585a7.083 7.083 0 1 1 0-14.167 7.083 7.083 0 0 1 0 14.167m-.708-7.792v4.25h1.416v-4.25zm0-2.833v1.416h1.416V3.96z"
  })));
};

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAxNSAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTcuNTAxMDYgMTQuNTg0NkMzLjU4OTA0IDE0LjU4NDYgMC40MTc3MjUgMTEuNDEzMyAwLjQxNzcyNSA3LjUwMTNDMC40MTc3MjUgMy41ODkyOCAzLjU4OTA0IDAuNDE3OTY5IDcuNTAxMDYgMC40MTc5NjlDMTEuNDEzIDAuNDE3OTY5IDE0LjU4NDQgMy41ODkyOCAxNC41ODQ0IDcuNTAxM0MxNC41ODQ0IDExLjQxMzMgMTEuNDEzIDE0LjU4NDYgNy41MDEwNiAxNC41ODQ2Wk02Ljc5MjczIDYuNzkyOTdWMTEuMDQzSDguMjA5MzlWNi43OTI5N0g2Ljc5MjczWk02Ljc5MjczIDMuOTU5NjRWNS4zNzYzSDguMjA5MzlWMy45NTk2NEg2Ljc5MjczWiIgZmlsbD0iIzMwODhFQyIvPgo8L3N2Zz4K");

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*************************************************************!*\
  !*** ./assets/dev/javascript/pages/data-migration/index.js ***!
  \*************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _step1__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./step1 */ "./assets/dev/javascript/pages/data-migration/step1.js");
/* harmony import */ var _step2__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./step2 */ "./assets/dev/javascript/pages/data-migration/step2.js");







const Page = () => {
  const [step, setStep] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)("step1");
  const handleStep = item => {
    setStep(item);
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wrap",
    style: {
      maxWidth: 774
    }
  }, step == "step1" && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_step1__WEBPACK_IMPORTED_MODULE_4__["default"], {
    handleStep: handleStep
  }), step == "step2" && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_step2__WEBPACK_IMPORTED_MODULE_5__["default"], {
    handleStep: handleStep
  }));
};

// Initialize the admin page
document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("wps-data-migration-page");
  if (container) {
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.render)((0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Page, null), container);
  }
});
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Page);
})();

/******/ })()
;
//# sourceMappingURL=react-bundle.js.map