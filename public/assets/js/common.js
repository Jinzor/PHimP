/**
 *
 * @param el
 * @returns {HTMLElement}
 */
function oid(el) {
    return document.getElementById(el);
}

/**
 *
 * @param url
 * @param method
 * @param data
 * @param reauthAuto
 * @returns {Promise<any>}
 */
function ajx(url, method, data, reauthAuto = true) {
    return new Promise(function (resolve, reject) {
        if (typeof (method) === "undefined") {
            method = "GET";
        }
        if (typeof (data) === "undefined") {
            data = "";
        }

        if (!url.includes("source=")) {
            url += (url.includes('?') ? '&' : '?') + "source=ajax";
        }

        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    resolve(xhr.responseText, xhr);
                } else {
                    if (xhr.status === 401 && reauthAuto) {
                        // Tentative de reconnexion (via cookies)
                        ajx(baseurl + 'services/reauth').then((response) => {
                            var resp = JSON.parse(response);
                            if (resp.hasOwnProperty('status') && resp.status === 'connected') {
                                // Nouvelle tentative d'appel
                                return ajx(url, method, data, false);
                            } else {
                                Ui.alert('Vous devez être connecté pour accéder à cette page.', function (e) {
                                    // Sinon on renvoie sur la page de connexion
                                    window.location.href = baseurl + 'login?redirect=' + window.location.href;
                                }, 'Se reconnecter');
                            }
                        });
                    }
                    reject(xhr.responseText, xhr);
                }
            }
        };
        xhr.open(method, url, true);
        if (method === "POST" && !(data instanceof FormData)) {
            if (typeof data === 'object') {
                var form_data = new FormData();
                for (let k in data) {
                    if (data.hasOwnProperty(k)) {
                        if (typeof data[k] === 'object') {
                            data[k] = JSON.stringify(data[k]);
                        }
                        form_data.append(k, data[k]);
                    }
                }
                data = form_data;
            } else {
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            }
        }
        xhr.send(data);
    });
}

/**
 * Foreach loop
 *
 * @param elements classname or dom array
 * @param callable
 * @param offset
 */
function foreach(elements, callable, offset) {
    if (typeof elements === 'string') {
        elements = document.getElementsByClassName(elements);
    }
    let i = 0;
    if (typeof offset !== 'undefined') {
        i = offset;
    }
    while (i < elements.length) {
        if (elements.hasOwnProperty(i)) {
            callable(elements[i]);
        }
        i++;
    }
}

/**
 *
 * @param context
 * @param properties
 * @returns {*}
 */
function addProperty(context, properties) {
    for (let key in properties) {
        if (properties.hasOwnProperty(key)) {
            context[key] = properties[key];
        }
    }
    return context;
}

/**
 *
 * @param e
 * @returns {*}
 */
function getKeyCode(e) {
    if (e.key !== undefined) {
        return e.key;
    } else if (e.keyCode !== undefined) {
        return e.keyCode;
    }
}

/**
 *
 * @param e
 * @returns {boolean}
 */
function pressEnter(e) {
    const keyCode = getKeyCode(e);
    return keyCode === 'Enter' || keyCode === 13;
}

/**
 *
 * @param e
 * @returns {boolean}
 */
function pressEchap(e) {
    const keyCode = getKeyCode(e);
    return keyCode === 'Escape' || keyCode === 27;
}

/**
 *
 * @param form
 * @param callback
 * @param url
 * @param submitButton
 */
function classicPost(form, callback, url, submitButton) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitButton) {
            submitButton.classList.add('loading');
        }
        var data = new FormData(form);
        if (form.enctype !== "multipart/form-data") {
            data = new URLSearchParams(data).toString();
        }
        ajx(url, 'POST', data).then(
            (resp) => {
                callback(resp);
            }).catch(
            (resp) => {
                handleJsonResp(resp);
            }).finally(
            () => {
                if (submitButton) {
                    submitButton.classList.remove('loading');
                }
            });
    });
}

/**
 *
 * @param el
 * @returns {*}
 */
function getSelectedOption(el) {
    return el.options[el.selectedIndex];
}

/**
 *
 * @param el
 * @returns {*}
 */
function getSelectedOptionValue(el) {
    return getSelectedOption(el).value;
}

/**
 *
 * @param resp JSON
 * @param defaultSuccessMessage
 * @param defaultErrorMessage
 */
function handleJsonResp(resp, defaultSuccessMessage = 'Succès', defaultErrorMessage = 'Erreur') {
    if (!resp) return;
    var data = JSON.parse(resp);
    if (!data) return;
    if (data && data.hasOwnProperty('status')) {
        if (data.status === 'success') {
            if (data.hasOwnProperty('message')) {
                Ui.notif(data.message, 'success');
            } else {
                Ui.notif(defaultSuccessMessage, 'success');
            }
        } else {
            if (data.hasOwnProperty('message')) {
                Ui.notif(data.message, 'error');
            } else {
                Ui.notif(defaultErrorMessage, 'error');
            }
        }
    } else {
        if (data.hasOwnProperty('error')) {
            if (data.error.hasOwnProperty('message')) {
                Ui.notif(data.error.message, 'error');
            } else {
                Ui.notif(defaultErrorMessage, 'error');
            }
        }
    }

    return data;
}

function copy(input) {
    if (typeof input === "string") {
        navigator.clipboard.writeText(input).then(() => {
            Ui.notif('Le texte a été copié dans le presse-papier', 'success');
        });
    } else {
        input.select();
        if (document.execCommand("copy")) {
            Ui.notif('Le texte a été copié dans le presse-papier', 'success');
        }
    }
}

function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function loadScript(src, callback) {
    var script = document.createElement('script');
    script.onload = function () {
        callback()
    };
    script.src = src;
    document.head.appendChild(script);
}

function round(num) {
    return Math.round((num + Number.EPSILON) * 100) / 100;
}
