import regeneratorRuntime from "regenerator-runtime";
import React from "react";
import ReactDOM from "react-dom";
import SiteEdit from "./SiteEdit";
import SettingsEditor from "./SettingsEditor"
import AutoSettings from "./AutoSettings";
import SkiplistEdit from "./SkiplistEdit";
import AutoRules from "./AutoRules";
import PrebotsEdit from "./PrebotsEdit";

import 'bootstrap/js/dist/collapse';

import "../../../sass/trd.scss"

import "hint.css/hint.min.css"

if(window.location.pathname.match(/site\/.*?\/edit/i)) {
    window.onload = function () {
      var site = document.getElementById("site-edit").getAttribute("data-site");
      ReactDOM.render(<SiteEdit name={site} />, document.getElementById('site-edit'));
    }
}

if(window.location.pathname.match(/^\/auto\//i)) {
    var site = document.getElementById("auto-settings-filter").getAttribute("data-section");
    ReactDOM.render(<AutoSettings section={site} />, document.getElementById('auto-settings-filter'));
}

if(window.location.pathname.match(/^\/skiplist\/list/i)) {
    ReactDOM.render(<SkiplistEdit />, document.getElementById('skiplist-edit'));
}

if(window.location.pathname.match(/^\/prebots\/list/i)) {
    ReactDOM.render(<PrebotsEdit />, document.getElementById('prebots-edit'));
}

if(window.location.pathname.match(/^\/settings\/edit/i)) {
    ReactDOM.render(<SettingsEditor />, document.getElementById('settings-edit'));
}

if(window.location.pathname.match(/^\/autorules/i)) {
    ReactDOM.render(<AutoRules />, document.getElementById('autorules-edit'));
}

function live (eventType, elementQuerySelector, cb) {
    document.addEventListener(eventType, function (event) {

        var qs = document.querySelectorAll(elementQuerySelector);

        if (qs) {
            var el = event.target, index = -1;
            while (el && ((index = Array.prototype.indexOf.call(qs, el)) === -1)) {
                el = el.parentElement;
            }

            if (index > -1) {
                cb.call(el, event);
            }
        }
    });
}

live('click', '[data-help]', function(event) {
  var path = event.target.getAttribute("data-help");
  showHelp(path);
});
document.querySelector("#help .close").onclick = hideHelp;

function showHelp(path) {
  window.fetch('/api/doc/parse/' + path)
  .then(function(response) {
    return response.text();
  })
  .then(function(text) {
    document.querySelector("#help .content").innerHTML = text;
    document.querySelector("#help").classList.add("visible");
  })

}
function hideHelp(e) {
  document.querySelector("#help").classList.remove("visible");
  e.preventDefault();
}

if (!Array.prototype.sortIgnoreCase) {
    Array.prototype.sortIgnoreCase = function () {
        return this.sort(function (a, b) {
            return a.toLowerCase().localeCompare(b.toLowerCase());
        });
    };
}
