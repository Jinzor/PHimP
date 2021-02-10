'use strict';

require('./class/Ui');

/**
 * Menu mobile "burger"
 */
var menuBtn = oid('burger-menu');
if (menuBtn) {
    menuBtn.addEventListener('click', function (e) {
        e.preventDefault();
        menuBtn.classList.toggle('open');
        oid('menu').classList.toggle('open');
    });
}

/**
 * Initialise les select "dropdown"
 */
Ui.bindDropdowns();

/**
 * Formulaires
 */
var btn = document.querySelector('button[type="submit"]');
var form = document.forms ? document.forms[0] : null;
if (form && btn && !btn.classList.contains('noloader')) {
    form.addEventListener('submit', function (e) {
        // ajoute la classe "loading" sur le bouton submit du formulaire posté
        btn.classList.add('loading');
    });
}
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Modal = function () {
    function Modal(attrs) {
        _classCallCheck(this, Modal);

        this.modal = null;
        this.modalContent = null;
        this.modalBackground = null;
        this.modalId = '';
        this.size = '';
        this.overflow = 'auto';
        this.onClose = null;
        addProperty(this, attrs);
        if (!oid(this.modalId)) {
            this.createElements();
        } else {
            this.modal = oid(this.modalId);
            this.modalContent = oid(this.modalId + '-content');
        }
        this.modalContent.style.overflow = this.overflow;
    }

    _createClass(Modal, [{
        key: 'setContentHTML',
        value: function setContentHTML(content) {
            this.modalContent.innerHTML = content;
            this.bindContent();
        }
    }, {
        key: 'setContentDom',
        value: function setContentDom(dom) {
            this.modalContent.innerHTML = '';
            this.modalContent.append(dom);
            this.bindContent();
        }
    }, {
        key: 'loadContentFromUrl',
        value: function loadContentFromUrl(url, then) {
            var _this = this;

            var self = this;
            this.startLoad();
            return new Promise(function (resolve) {
                ajx(url, 'GET', null).then(function (html) {
                    _this.endLoad();
                    self.setContentHTML(html);
                    if (then) then();
                    resolve();
                });
            });
        }
    }, {
        key: 'createElements',
        value: function createElements() {
            var _this2 = this;

            var self = this;

            this.modal = document.createElement('div');
            this.modal.className = "modal " + this.size;
            if (this.modalId.length > 0) {
                this.modal.id = this.modalId;
            }

            var btnClose = document.createElement('span');
            btnClose.innerHTML = "&times;";
            btnClose.className = "modal-close";
            btnClose.onclick = function () {
                if (this.onClose) {
                    this.onClose(this);
                }
                self.dismiss();
            };

            this.modalContent = document.createElement('div');
            this.modalContent.className = "modal-content";
            this.modalContent.id = this.modalId + "-content";

            this.modalBackground = document.createElement('div');
            this.modalBackground.className = "modal-background";
            this.modalBackground.onclick = function () {
                if (_this2.onClose) {
                    _this2.onClose(_this2);
                }
                _this2.dismiss();
            };

            this.modal.appendChild(this.modalBackground);
            this.modal.appendChild(btnClose);
            this.modal.appendChild(this.modalContent);

            document.body.appendChild(this.modal);

            this.dismissOnEchapListener = function (e) {
                if (pressEchap(e) && !["INPUT", "SELECT"].includes(e.target.tagName)) {
                    if (_this2.onClose) {
                        _this2.onClose(_this2);
                    }
                    _this2.dismiss();
                }
                e.stopPropagation();
            };

            document.addEventListener('keyup', self.dismissOnEchapListener, true);
        }
    }, {
        key: 'show',
        value: function show() {
            this.modal.classList.add('active');
        }
    }, {
        key: 'dismiss',
        value: function dismiss() {
            if (this.modal.id === this.modalId) {
                this.modal.classList.remove('active');
                if (this.modal.parentNode) {
                    this.modal.parentNode.removeChild(this.modal);
                }
            }

            document.removeEventListener('keyup', this.dismissOnEchapListener, true);
        }

        /**
         * Animation de chargement
         */

    }, {
        key: 'startLoad',
        value: function startLoad() {
            Ui.circleLoader(this.modalContent);
            this.modalContent.classList.add('loading');
        }
    }, {
        key: 'endLoad',
        value: function endLoad() {
            this.modalContent.classList.remove('loading');
        }
    }, {
        key: 'modalPost',
        value: function modalPost(form, callback, url, submitButton) {
            var _this3 = this;

            var self = this;

            if (typeof url === "undefined" || url === null) {
                url = form.getAttribute('action');
            }

            if (this.modalBackground) {
                this.modalBackground.onclick = function () {
                    self.dismiss();
                    callback(null);
                };
            }

            this.dismissOnEchapListener = function (e) {
                if (pressEchap(e)) {
                    _this3.dismiss();
                    callback(null);
                }
                e.stopPropagation();
            };

            foreach(form.getElementsByClassName('btn'), function (btn) {
                if (btn.getAttribute('data-event') === 'dismiss') {
                    btn.onclick = function (e) {
                        e.preventDefault();
                        self.dismiss();
                        callback(null);
                    };
                } else if (btn.getAttribute('type') === 'submit' && typeof submitButton === "undefined") {
                    submitButton = btn;
                }
            });

            classicPost(form, callback, url, submitButton);
        }
    }, {
        key: 'bindContent',
        value: function bindContent() {
            var self = this;
            var dismissButtons = this.modal.querySelectorAll('[data-event="dismiss"]');
            if (dismissButtons) {
                dismissButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        self.dismiss();
                    }, true);
                });
            }
        }
    }]);

    return Modal;
}();

module.exports = Modal;
global.Modal = Modal;
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Modal = require('./Modal');

var notifStack = [];

var Ui = function () {
    function Ui() {
        _classCallCheck(this, Ui);
    }

    _createClass(Ui, null, [{
        key: 'notif',


        /**
         *
         * @param message
         * @param status
         */
        value: function notif(message, status) {
            var alertItem = document.createElement('div');
            alertItem.className = "notification " + (status ? status : "");
            alertItem.innerHTML = message;

            document.body.appendChild(alertItem);

            var pos = notifStack.length;
            notifStack.push(alertItem);

            setTimeout(function () {
                alertItem.classList.add('active');
                if (pos > 0) {
                    alertItem.style.bottom = 15 + Math.round((5 + 48) * pos) + 'px';
                }
                setTimeout(function () {
                    alertItem.classList.remove('active');
                    notifStack.splice(0, 1);
                    notifStack.forEach(function (el, i) {
                        el.style.bottom = 15 + Math.round((5 + 48) * i) + 'px';
                    });
                    setTimeout(function () {
                        alertItem.remove();
                    }, 200);
                }, 5000);
            }, 200);
        }

        /**
         *
         * @param message
         * @param onConfirm
         * @param confirmTextButton
         */

    }, {
        key: 'alert',
        value: function alert(message, onConfirm, confirmTextButton) {

            var modal = new Modal({
                modalId: 'alert-modal',
                size: 'small'
            });

            if (typeof confirmTextButton === 'undefined') {
                confirmTextButton = 'Ok';
            }

            var c = document.createElement('div');
            c.className = 'confirm-container wrapper';

            var messageContainer = document.createElement('p');
            messageContainer.className = 'confirm-message';
            messageContainer.innerHTML = message;

            var btnContainer = document.createElement('div');
            btnContainer.className = 'confirm-buttons center';

            var btnValidate = document.createElement('a');
            btnValidate.href = '#';
            btnValidate.className = 'btn btn-m';
            btnValidate.onclick = function (e) {
                e.preventDefault();
                btnValidate.classList.add('loading');
                console.log(onConfirm);
                if (onConfirm) {
                    onConfirm(modal);
                } else {
                    modal.dismiss();
                    return true;
                }
            };
            btnValidate.innerHTML = confirmTextButton;

            btnContainer.append(btnValidate);

            c.append(messageContainer);
            c.append(btnContainer);
            modal.setContentDom(c);
            modal.show();
        }

        /**
         *
         * @param message
         * @param onConfirm
         * @param onCancel
         * @param confirmTextButton
         * @param cancelTextButton
         */

    }, {
        key: 'confirm',
        value: function confirm(message, onConfirm, onCancel, confirmTextButton, cancelTextButton) {
            var modal = new Modal({
                modalId: 'confirm-modal',
                size: 'small',
                onClose: onCancel
            });

            if (typeof confirmTextButton === 'undefined') {
                confirmTextButton = 'Confirmer';
            }

            if (typeof cancelTextButton === 'undefined') {
                cancelTextButton = 'Annuler';
            }

            var c = document.createElement('div');
            c.className = 'confirm-container wrapper';

            var messageContainer = document.createElement('p');
            messageContainer.className = 'confirm-message';
            messageContainer.innerHTML = message;

            var btnContainer = document.createElement('div');
            btnContainer.className = 'confirm-buttons';

            var btnValidate = document.createElement('a');
            btnValidate.href = '#';
            btnValidate.className = 'btn btn-m right';
            btnValidate.onclick = function (e) {
                e.preventDefault();
                btnValidate.classList.add('loading');
                if (onConfirm) {
                    onConfirm(modal);
                } else {
                    modal.dismiss();
                    return true;
                }
            };
            btnValidate.innerHTML = confirmTextButton;

            var btnCancel = document.createElement('a');
            btnCancel.href = '#';
            btnCancel.className = 'btn btn-m gr';
            btnCancel.onclick = function (e) {
                e.preventDefault();
                if (onCancel) {
                    onCancel(modal);
                } else {
                    modal.dismiss();
                    return false;
                }
            };
            btnCancel.innerHTML = cancelTextButton;

            btnContainer.append(btnCancel);
            btnContainer.append(btnValidate);

            c.append(messageContainer);
            c.append(btnContainer);
            modal.setContentDom(c);
            modal.show();
        }

        /**
         *
         * @param message
         * @param onConfirm
         * @param inputs string[] inputs {}
         * @param onCancel
         * @param confirmTextButton
         * @param cancelTextButton
         */

    }, {
        key: 'ask',
        value: function ask(message, onConfirm, inputs, onCancel, confirmTextButton, cancelTextButton) {
            var modal = new Modal({
                modalId: 'ask-modal',
                size: 'small'
            });

            if (typeof confirmTextButton === 'undefined' || confirmTextButton == null) {
                confirmTextButton = 'Confirmer';
            }

            if (typeof cancelTextButton === 'undefined' || cancelTextButton == null) {
                cancelTextButton = 'Annuler';
            }

            var c = document.createElement('div');
            c.className = 'confirm-container wrapper';

            var messageContainer = document.createElement('p');
            messageContainer.className = 'confirm-message';
            messageContainer.innerHTML = message;

            var inputsHTML = [];

            var inputContainer = document.createElement('div');

            var _loop = function _loop(i) {
                var input = void 0;
                var field = document.createElement('div');
                field.className = "field dir-vertical";

                switch (inputs[i].type) {
                    case 'textarea':
                        input = document.createElement("textarea");
                        input.placeholder = inputs[i].placeholder ? inputs[i].placeholder : "";
                        input.innerHTML = inputs[i].value ? inputs[i].value : "";
                        break;
                    case 'select':
                        input = document.createElement("select");
                        inputs[i].options.forEach(function (opt) {
                            var option = document.createElement("option");
                            option.value = (typeof opt === 'undefined' ? 'undefined' : _typeof(opt)) === 'object' ? opt.value : opt;
                            option.innerHTML = (typeof opt === 'undefined' ? 'undefined' : _typeof(opt)) === 'object' ? opt.content : opt;
                            input.appendChild(option);
                        });
                        break;
                    default:
                        input = document.createElement("input");
                        input.type = inputs[i].type ? inputs[i].type : 'text';
                        input.placeholder = inputs[i].placeholder ? inputs[i].placeholder : "";
                        break;
                }

                if (inputs[i].label) {
                    var label = document.createElement('label');
                    label.className = "label";
                    label.innerHTML = inputs[i].label;
                    field.appendChild(label);
                }

                input.name = inputs[i].name ? inputs[i].name : "input_" + i;
                input.className = "input";
                if (inputs[i].type !== 'textarea') {
                    input.onkeydown = function (e) {
                        if (pressEnter(e)) {
                            btnValidate.dispatchEvent(new Event("click"));
                        }
                    };
                }

                field.appendChild(input);
                inputContainer.appendChild(field);
                inputsHTML.push(input);
            };

            for (var i = 0; i < inputs.length; i++) {
                _loop(i);
            }

            var btnContainer = document.createElement('div');
            btnContainer.className = 'confirm-buttons';

            var btnValidate = document.createElement('a');
            btnValidate.href = '#';
            btnValidate.className = 'btn btn-m right';
            btnValidate.onclick = function (e) {
                e.preventDefault();
                btnValidate.classList.add('loading');
                if (onConfirm) {
                    var data = {};
                    inputsHTML.forEach(function (input) {
                        data[input.name] = input.value;
                    });
                    onConfirm(modal, data, btnValidate);
                } else {
                    modal.dismiss();
                    return true;
                }
            };
            btnValidate.innerHTML = confirmTextButton;

            var btnCancel = document.createElement('a');
            btnCancel.href = '#';
            btnCancel.className = 'btn btn-m gr';
            btnCancel.onclick = function (e) {
                e.preventDefault();
                if (onCancel) {
                    onCancel(modal);
                } else {
                    modal.dismiss();
                    return false;
                }
            };
            btnCancel.innerHTML = cancelTextButton;

            btnContainer.append(btnCancel);
            btnContainer.append(btnValidate);

            c.append(messageContainer);
            c.append(inputContainer);
            c.append(btnContainer);
            modal.setContentDom(c);
            modal.show();
        }

        /**
         *
         * @param context
         * @param switchTabEvent
         */

    }, {
        key: 'iniTabs',
        value: function iniTabs(context, switchTabEvent) {
            foreach(context.getElementsByClassName('tab-link'), function (link) {
                link.addEventListener('click', function (e) {
                    if (!link.hasAttribute('data-noprevent')) {
                        e.preventDefault();
                        Ui.setTab(context, this);
                        if (switchTabEvent) {
                            switchTabEvent();
                        }
                    }
                });
            });
        }

        /**
         *
         * @param context
         * @param linkElement
         */

    }, {
        key: 'setTab',
        value: function setTab(context, linkElement) {

            var target = void 0;

            foreach(context.getElementsByClassName('tab-link'), function (link) {
                link.classList.remove('active'); // On désactive tous les onglets
                if (typeof linkElement === 'string') {
                    if (link.getAttribute('data-target') === linkElement) {
                        target = linkElement;
                        linkElement = link;
                    }
                }
            });

            linkElement.classList.add('active');

            if (!target) {
                target = linkElement.getAttribute('data-target');
            }

            foreach(context.getElementsByClassName('tab-page'), function (page) {
                if (page.getAttribute('data-tab') === target) {
                    page.classList.add('active');
                } else {
                    page.classList.remove('active');
                }
            });
        }
    }, {
        key: 'circleLoader',
        value: function circleLoader(domElement) {
            domElement.innerHTML = '<div class="loader-container"><div class="loader"><svg class="circular-loader" viewBox="25 25 50 50" >' + '<circle class="loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="4" />' + '</svg></div></div>';
        }
    }, {
        key: 'getVisibleElements',
        value: function getVisibleElements(elements, scrollPos, offset) {
            var visibleElements = [];
            for (var i = 0; i < elements.length; i++) {
                var imgContainer = elements[i];
                if (imgContainer.offsetTop <= scrollPos + offset && imgContainer.offsetTop > scrollPos - 100) {
                    visibleElements.push(imgContainer);
                }
            }
            return visibleElements;
        }
    }, {
        key: 'setInputFilter',
        value: function setInputFilter(textbox, inputFilter, callback) {
            ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function (event) {
                textbox.addEventListener(event, function () {
                    if (inputFilter(this.value)) {
                        this.oldValue = this.value;
                        this.oldSelectionStart = this.selectionStart;
                        this.oldSelectionEnd = this.selectionEnd;
                    } else if (this.hasOwnProperty("oldValue")) {
                        this.value = this.oldValue;
                        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                    } else {
                        this.value = "";
                    }
                    if (callback) {
                        callback(this.value);
                    }
                });
            });
        }
    }, {
        key: 'bindDropdowns',
        value: function bindDropdowns() {
            foreach('dropdown', function (element) {
                var btn = element.getElementsByClassName('dropdown-btn')[0];
                var menu = element.getElementsByClassName('dropdown-menu')[0];

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    menu.classList.add('active');
                    document.body.addEventListener('click', actDropDown);
                });

                function actDropDown(e) {
                    if (e.target !== btn && e.target !== menu) {
                        // && e.target.parentNode !== menu
                        menu.classList.remove('active');
                        document.body.removeEventListener('click', actDropDown);
                    }
                }
            });
        }
    }]);

    return Ui;
}();

module.exports = Ui;
global.Ui = Ui;
"use strict";

var _typeof2 = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _typeof = "function" == typeof Symbol && "symbol" == _typeof2(Symbol.iterator) ? function (a) {
  return typeof a === "undefined" ? "undefined" : _typeof2(a);
} : function (a) {
  return a && "function" == typeof Symbol && a.constructor === Symbol && a !== Symbol.prototype ? "symbol" : typeof a === "undefined" ? "undefined" : _typeof2(a);
};function oid(a) {
  return document.getElementById(a);
}function ajx(a, b, c) {
  var d = !(3 < arguments.length && arguments[3] !== void 0) || arguments[3];return new Promise(function (e, f) {
    "undefined" == typeof b && (b = "GET"), "undefined" == typeof c && (c = ""), a.includes("source=") || (a += (a.includes("?") ? "&" : "?") + "source=ajax");var g = new XMLHttpRequest();if (g.onreadystatechange = function () {
      4 === g.readyState && (200 === g.status ? e(g.responseText, g) : (401 === g.status && d && ajx(baseurl + "services/reauth").then(function (d) {
        var e = JSON.parse(d);return e.hasOwnProperty("status") && "connected" === e.status ? ajx(a, b, c, !1) : void Ui.alert("Vous devez \xEAtre connect\xE9 pour acc\xE9der \xE0 cette page.", function () {
          window.location.href = baseurl + "login?redirect=" + window.location.href;
        }, "Se reconnecter");
      }), f(g.responseText, g)));
    }, g.open(b, a, !0), "POST" === b && !(c instanceof FormData)) if ("object" === ("undefined" == typeof c ? "undefined" : _typeof(c))) {
      var h = new FormData();for (var i in c) {
        c.hasOwnProperty(i) && ("object" === _typeof(c[i]) && (c[i] = JSON.stringify(c[i])), h.append(i, c[i]));
      }c = h;
    } else g.setRequestHeader("Content-type", "application/x-www-form-urlencoded");g.send(c);
  });
}function foreach(a, b, c) {
  "string" == typeof a && (a = document.getElementsByClassName(a));var d = 0;for ("undefined" != typeof c && (d = c); d < a.length;) {
    a.hasOwnProperty(d) && b(a[d]), d++;
  }
}function addProperty(a, b) {
  for (var c in b) {
    b.hasOwnProperty(c) && (a[c] = b[c]);
  }return a;
}function getKeyCode(a) {
  if (a.key !== void 0) return a.key;return void 0 === a.keyCode ? void 0 : a.keyCode;
}function pressEnter(a) {
  var b = getKeyCode(a);return "Enter" === b || 13 === b;
}function pressEchap(a) {
  var b = getKeyCode(a);return "Escape" === b || 27 === b;
}function classicPost(a, b, c, d) {
  a.addEventListener("submit", function (f) {
    f.preventDefault(), d && d.classList.add("loading");var e = new FormData(a);"multipart/form-data" !== a.enctype && (e = new URLSearchParams(e).toString()), ajx(c, "POST", e).then(function (a) {
      b(a);
    }).catch(function (a) {
      handleJsonResp(a);
    }).finally(function () {
      d && d.classList.remove("loading");
    });
  });
}function getSelectedOption(a) {
  return a.options[a.selectedIndex];
}function getSelectedOptionValue(a) {
  return getSelectedOption(a).value;
}function handleJsonResp(a) {
  var b = 1 < arguments.length && void 0 !== arguments[1] ? arguments[1] : "Succ\xE8s",
      c = 2 < arguments.length && void 0 !== arguments[2] ? arguments[2] : "Erreur";if (a) {
    var d = JSON.parse(a);if (d) return d && d.hasOwnProperty("status") ? "success" === d.status ? d.hasOwnProperty("message") ? Ui.notif(d.message, "success") : Ui.notif(b, "success") : d.hasOwnProperty("message") ? Ui.notif(d.message, "error") : Ui.notif(c, "error") : d.hasOwnProperty("error") && (d.error.hasOwnProperty("message") ? Ui.notif(d.error.message, "error") : Ui.notif(c, "error")), d;
  }
}function copy(a) {
  "string" == typeof a ? navigator.clipboard.writeText(a).then(function () {
    Ui.notif("Le texte a \xE9t\xE9 copi\xE9 dans le presse-papier", "success");
  }) : (a.select(), document.execCommand("copy") && Ui.notif("Le texte a \xE9t\xE9 copi\xE9 dans le presse-papier", "success"));
}function setCookie(a, b, c) {
  var d = "";if (c) {
    var e = new Date();e.setTime(e.getTime() + 1e3 * (60 * (60 * (24 * c)))), d = "; expires=" + e.toUTCString();
  }document.cookie = a + "=" + (b || "") + d + "; path=/";
}function getCookie(a) {
  for (var b, d = a + "=", e = document.cookie.split(";"), f = 0; f < e.length; f++) {
    for (b = e[f]; " " == b.charAt(0);) {
      b = b.substring(1, b.length);
    }if (0 == b.indexOf(d)) return b.substring(d.length, b.length);
  }return null;
}function eraseCookie(a) {
  document.cookie = a + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
}function loadScript(a, b) {
  var c = document.createElement("script");c.onload = function () {
    b();
  }, c.src = a, document.head.appendChild(c);
}function round(a) {
  return Math.round(100 * (a + Number.EPSILON)) / 100;
}
"use strict";

