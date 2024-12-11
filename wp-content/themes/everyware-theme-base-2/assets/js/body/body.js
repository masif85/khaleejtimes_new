window.$ = require('jquery');
require('bootstrap');
require('../body/menu');
require('./opener');
require('./concept');
require('./search');
require('./plyr');


// Back to top button
$('#button-back-to-top').click(function () {
    $('body,html').animate({scrollTop: 0}, 400);
    return false;
});

// Keyboard focus

function addKeyboardFocus(e) {
    "use strict";
    e = e || event;
    let activeElement;
    if (e.keyCode === 9) {
        activeElement = document.activeElement;
        if (activeElement.tagName.toLowerCase() === 'a') {
            $('a:focus').addClass('keyboard-focus');
        }
    }
}

function removeKeyboardFocus(e) {
    "use strict";
    e = e || event;
    let activeElement;
    if (e.keyCode === 9) {
        activeElement = document.activeElement;
        if (activeElement.tagName.toLowerCase() === 'a') {
            $('a:focus').removeClass('keyboard-focus');
        }
    }
}

let body = document.querySelector('body');
body.addEventListener('keyup', addKeyboardFocus);
body.addEventListener('keydown', removeKeyboardFocus);
