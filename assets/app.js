import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import { gsap } from "gsap";

console.log('JavaScript file loaded');

const menuButton = document.getElementById('burger');
const menu = document.getElementById('menu');
const closeButton = document.getElementById('close');
window.addEventListener('resize', handleResize);
handleResize();

if(closeButton) {
    closeButton.addEventListener('click', closeMenu);
}

if(menuButton) {
    menuButton.addEventListener('click', toggleMenu);
}

function toggleMenu() {
    menu.classList.remove('hidden');
    menu.classList.add('flex');
    document.body.classList.toggle('overflow-hidden');
}

function closeMenu() {
    menu.classList.add('hidden');
    menu.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function handleResize() {
        if (window.innerWidth >= 768) { // md breakpoint
            menu.classList.remove('hidden');
            menu.classList.add('flex');
            menu.classList.add('gap-10');
            document.body.classList.remove('overflow-hidden');
        } else {
            menu.classList.add('hidden');
            menu.classList.remove('flex');
            menu.classList.remove('gap-10');
            document.body.classList.remove('overflow-hidden');
        }
}