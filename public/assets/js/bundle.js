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
