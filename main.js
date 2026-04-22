/*
 * FILE: main.js
 * PURPOSE: Client-side interactivity for the FORGE GYM platform.
 *
 * DESCRIPTION:
 * - Mobile navigation toggle (hamburger opens full-screen nav)
 * - Quick-access dropdown menu (3-line button next to "Join now")
 * - Header scroll shadow
 */

(function () {
  'use strict';

  /* ── Mobile navigation toggle ────────────────────────────── */
  var navToggle = document.getElementById('nav-toggle');
  var mainNav   = document.getElementById('main-nav');

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      var isOpen = navToggle.getAttribute('aria-expanded') === 'true';
      navToggle.setAttribute('aria-expanded', String(!isOpen));
      mainNav.classList.toggle('is-open', !isOpen);
      document.body.style.overflow = !isOpen ? 'hidden' : '';
    });

    mainNav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('is-open');
        document.body.style.overflow = '';
      });
    });
  }

  /* ── Quick-access dropdown menu ──────────────────────────── */
  var menuBtn      = document.getElementById('quick-menu-btn');
  var menuDropdown = document.getElementById('quick-menu-dropdown');

  if (menuBtn && menuDropdown) {

    menuBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      /* Toggle the .is-open class — CSS controls visibility */
      menuDropdown.classList.toggle('is-open');
      var isNowOpen = menuDropdown.classList.contains('is-open');
      menuBtn.setAttribute('aria-expanded', String(isNowOpen));
    });

    document.addEventListener('click', function (e) {
      if (!menuBtn.contains(e.target) && !menuDropdown.contains(e.target)) {
        menuDropdown.classList.remove('is-open');
        menuBtn.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        menuDropdown.classList.remove('is-open');
        menuBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ── Header shadow on scroll ─────────────────────────────── */
  var header = document.querySelector('.header');
  if (header) {
    window.addEventListener('scroll', function () {
      header.style.boxShadow = window.scrollY > 10
        ? '0 2px 20px rgba(0, 0, 0, 0.7)'
        : 'none';
    }, { passive: true });
  }

}());