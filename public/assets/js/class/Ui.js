const Modal = require('./Modal');

var notifStack = [];

class Ui {

    /**
     *
     * @param message
     * @param status
     */
    static notif(message, status) {
        let alertItem = document.createElement('div');
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
                notifStack.forEach((el, i) => {
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
    static alert(message, onConfirm, confirmTextButton) {

        let modal = new Modal({
            modalId: 'alert-modal',
            size: 'small'
        });

        if (typeof confirmTextButton === 'undefined') {
            confirmTextButton = 'Ok';
        }

        let c = document.createElement('div');
        c.className = 'confirm-container wrapper';

        let messageContainer = document.createElement('p');
        messageContainer.className = 'confirm-message';
        messageContainer.innerHTML = message;

        let btnContainer = document.createElement('div');
        btnContainer.className = 'confirm-buttons center';

        let btnValidate = document.createElement('a');
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
    static confirm(message, onConfirm, onCancel, confirmTextButton, cancelTextButton) {
        let modal = new Modal({
            modalId: 'confirm-modal',
            size: 'small',
            onClose: onCancel,
        });

        if (typeof confirmTextButton === 'undefined') {
            confirmTextButton = 'Confirmer';
        }

        if (typeof cancelTextButton === 'undefined') {
            cancelTextButton = 'Annuler';
        }

        let c = document.createElement('div');
        c.className = 'confirm-container wrapper';

        let messageContainer = document.createElement('p');
        messageContainer.className = 'confirm-message';
        messageContainer.innerHTML = message;

        let btnContainer = document.createElement('div');
        btnContainer.className = 'confirm-buttons';

        let btnValidate = document.createElement('a');
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

        let btnCancel = document.createElement('a');
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
    static ask(message, onConfirm, inputs, onCancel, confirmTextButton, cancelTextButton) {
        let modal = new Modal({
            modalId: 'ask-modal',
            size: 'small'
        });

        if (typeof confirmTextButton === 'undefined' || confirmTextButton == null) {
            confirmTextButton = 'Confirmer';
        }

        if (typeof cancelTextButton === 'undefined' || cancelTextButton == null) {
            cancelTextButton = 'Annuler';
        }

        let c = document.createElement('div');
        c.className = 'confirm-container wrapper';

        let messageContainer = document.createElement('p');
        messageContainer.className = 'confirm-message';
        messageContainer.innerHTML = message;

        let inputsHTML = [];

        let inputContainer = document.createElement('div');

        for (let i = 0; i < inputs.length; i++) {
            let input;
            let field = document.createElement('div');
            field.className = "field dir-vertical";

            switch (inputs[i].type) {
                case 'textarea':
                    input = document.createElement("textarea");
                    input.placeholder = inputs[i].placeholder ? inputs[i].placeholder : "";
                    input.innerHTML = inputs[i].value ? inputs[i].value : "";
                    break;
                case 'select':
                    input = document.createElement("select");
                    inputs[i].options.forEach(opt => {
                        const option = document.createElement("option");
                        option.value = typeof opt === 'object' ? opt.value : opt;
                        option.innerHTML = typeof opt === 'object' ? opt.content : opt;
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
                const label = document.createElement('label');
                label.className = "label";
                label.innerHTML = inputs[i].label;
                field.appendChild(label);
            }

            input.name = inputs[i].name ? inputs[i].name : "input_" + i;
            input.className = "input";
            if (inputs[i].type !== 'textarea') {
                input.onkeydown = (e) => {
                    if (pressEnter(e)) {
                        btnValidate.dispatchEvent(new Event("click"))
                    }
                };
            }

            field.appendChild(input);
            inputContainer.appendChild(field);
            inputsHTML.push(input);
        }

        let btnContainer = document.createElement('div');
        btnContainer.className = 'confirm-buttons';

        let btnValidate = document.createElement('a');
        btnValidate.href = '#';
        btnValidate.className = 'btn btn-m right';
        btnValidate.onclick = function (e) {
            e.preventDefault();
            btnValidate.classList.add('loading');
            if (onConfirm) {
                let data = {};
                inputsHTML.forEach(input => {
                    data[input.name] = input.value;
                });
                onConfirm(modal, data, btnValidate);
            } else {
                modal.dismiss();
                return true;
            }
        };
        btnValidate.innerHTML = confirmTextButton;

        let btnCancel = document.createElement('a');
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
    static iniTabs(context, switchTabEvent) {
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
    static setTab(context, linkElement) {

        let target;

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

    static circleLoader(domElement) {
        domElement.innerHTML = '<div class="loader-container"><div class="loader"><svg class="circular-loader" viewBox="25 25 50 50" >' +
            '<circle class="loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="4" />' +
            '</svg></div></div>';
    }

    static getVisibleElements(elements, scrollPos, offset) {
        let visibleElements = [];
        for (var i = 0; i < elements.length; i++) {
            const imgContainer = elements[i];
            if (imgContainer.offsetTop <= scrollPos + offset && imgContainer.offsetTop > scrollPos - 100) {
                visibleElements.push(imgContainer);
            }
        }
        return visibleElements;
    }

    static setInputFilter(textbox, inputFilter, callback) {
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

    static bindDropdowns() {
        foreach('dropdown', function (element) {
            const btn = element.getElementsByClassName('dropdown-btn')[0];
            const menu = element.getElementsByClassName('dropdown-menu')[0];

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                menu.classList.add('active');
                document.body.addEventListener('click', actDropDown);
            });

            function actDropDown(e) {
                if (e.target !== btn && e.target !== menu) { // && e.target.parentNode !== menu
                    menu.classList.remove('active');
                    document.body.removeEventListener('click', actDropDown);
                }
            }
        });
    }
}

module.exports = Ui;
global.Ui = Ui;
