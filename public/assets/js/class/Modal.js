class Modal {

    constructor(attrs) {
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

    setContentHTML(content) {
        this.modalContent.innerHTML = content;
        this.bindContent();
    }

    setContentDom(dom) {
        this.modalContent.innerHTML = '';
        this.modalContent.append(dom);
        this.bindContent();
    }

    loadContentFromUrl(url, then) {
        const self = this;
        this.startLoad();
        return new Promise(resolve => {
            ajx(url, 'GET', null).then((html) => {
                this.endLoad();
                self.setContentHTML(html);
                if (then) then();
                resolve();
            });
        });
    }

    createElements() {
        const self = this;

        this.modal = document.createElement('div');
        this.modal.className = "modal " + this.size;
        if (this.modalId.length > 0) {
            this.modal.id = this.modalId;
        }

        let btnClose = document.createElement('span');
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
        this.modalBackground.onclick = () => {
            if (this.onClose) {
                this.onClose(this);
            }
            this.dismiss();
        };

        this.modal.appendChild(this.modalBackground);
        this.modal.appendChild(btnClose);
        this.modal.appendChild(this.modalContent);

        document.body.appendChild(this.modal);

        this.dismissOnEchapListener = (e) => {
            if (pressEchap(e) && !["INPUT", "SELECT"].includes(e.target.tagName)) {
                if (this.onClose) {
                    this.onClose(this);
                }
                this.dismiss();
            }
            e.stopPropagation();
        };

        document.addEventListener('keyup', self.dismissOnEchapListener, true);
    }

    show() {
        this.modal.classList.add('active');
    }

    dismiss() {
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
    startLoad() {
        Ui.circleLoader(this.modalContent);
        this.modalContent.classList.add('loading');
    }

    endLoad() {
        this.modalContent.classList.remove('loading');
    }

    modalPost(form, callback, url, submitButton) {
        const self = this;

        if (typeof url === "undefined" || url === null) {
            url = form.getAttribute('action');
        }

        if (this.modalBackground) {
            this.modalBackground.onclick = function () {
                self.dismiss();
                callback(null);
            };
        }

        this.dismissOnEchapListener = (e) => {
            if (pressEchap(e)) {
                this.dismiss();
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
                }
            } else if (btn.getAttribute('type') === 'submit' && typeof submitButton === "undefined") {
                submitButton = btn;
            }
        });

        classicPost(form, callback, url, submitButton);
    }

    bindContent() {
        const self = this;
        const dismissButtons = this.modal.querySelectorAll('[data-event="dismiss"]');
        if (dismissButtons) {
            dismissButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    self.dismiss()
                }, true);
            });
        }
    }
}

module.exports = Modal;
global.Modal = Modal;
