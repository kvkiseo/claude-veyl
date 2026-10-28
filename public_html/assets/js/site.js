/* Veyl — site behavior. No frameworks, no dependencies.
   Everything here is progressive: with JavaScript switched off the site still
   works, the accordion is open, and checkout falls back to a normal form post. */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $(selector, root) { return (root || document).querySelector(selector); }
  function $$(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body)
    }).then(function (response) {
      return response.json().catch(function () { return { ok: false, error: 'Unexpected reply from the server.' }; });
    });
  }

  /* ---------------------------------------------------------------- navigation */
  function initNav() {
    var nav = $('[data-nav]');
    if (!nav) return;
    var burger = $('.nav__burger', nav);
    if (!burger) return;

    function setOpen(open) {
      nav.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }

    burger.addEventListener('click', function () {
      setOpen(!nav.classList.contains('is-open'));
    });

    $$('.nav__mobile a', nav).forEach(function (link) {
      link.addEventListener('click', function () { setOpen(false); });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) {
        setOpen(false);
        burger.focus();
      }
    });
  }

  /* ----------------------------------------------------------------- accordion */
  function initAccordions() {
    $$('[data-acc]').forEach(function (group) {
      $$('.acc__q', group).forEach(function (button) {
        var panel = document.getElementById(button.getAttribute('aria-controls'));
        if (!panel) return;

        button.addEventListener('click', function () {
          var open = button.getAttribute('aria-expanded') === 'true';
          button.setAttribute('aria-expanded', open ? 'false' : 'true');
          panel.hidden = open;
        });
      });
    });
  }

  /* -------------------------------------------------------------- reveal on scroll */
  function initReveal() {
    var items = $$('.reveal');
    if (!items.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      items.forEach(function (item) { item.classList.add('is-in'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

    items.forEach(function (item) { observer.observe(item); });

    // Safety net: if the observer has not fired for something after a few seconds,
    // show it anyway. Content must never be stuck invisible.
    window.setTimeout(function () {
      items.forEach(function (item) { item.classList.add('is-in'); });
    }, 3000);
  }

  /* --------------------------------------------------------------- the buy box */
  function initBuy() {
    var box = $('[data-buy]');
    if (!box || box.getAttribute('data-buyable') !== '1') return;

    var slug = box.getAttribute('data-slug');
    var csrf = box.getAttribute('data-csrf');
    var form = $('#pay-form', box);
    var codeInput = $('#code', box);
    var applyButton = $('[data-code-apply]', box);
    var codeMessage = $('[data-code-msg]', box);
    var payMessage = $('[data-pay-msg]', box);
    var priceNow = $('[data-price-now]', box);
    var priceWas = $('[data-price-was]', box);
    var saving = $('[data-saving]', box);
    var consent = $('#consent', box);
    var slot = $('[data-paypal-slot]', box);
    var fallback = $('[data-fallback]', box);

    var listPrice = priceNow ? priceNow.textContent : '';
    var appliedCode = '';

    // Messages about the code go next to the code field; messages about the
    // payment go next to the PayPal buttons, where the buyer is looking.
    function say(text, kind) {
      if (!codeMessage) return;
      codeMessage.textContent = text;
      codeMessage.className = 'msg' + (kind ? ' msg--' + kind : '');
    }

    function sayPay(text, kind) {
      if (!payMessage) return;
      payMessage.textContent = text;
      payMessage.className = 'msg' + (kind ? ' msg--' + kind : '');
    }

    function showPrice(total, wasPrice, label) {
      if (priceNow) priceNow.textContent = total;
      if (priceWas) {
        priceWas.textContent = wasPrice || '';
        priceWas.hidden = !wasPrice;
      }
      if (saving) {
        saving.textContent = label ? label + ' applied' : '';
        saving.hidden = !label;
      }
    }

    /* --- applying a code */
    function applyCode() {
      var typed = codeInput ? codeInput.value.trim() : '';

      if (typed === '') {
        appliedCode = '';
        showPrice(listPrice, '', '');
        say('', '');
        return Promise.resolve();
      }

      if (applyButton) applyButton.disabled = true;
      say('Checking…', '');

      return postJson('/api/code.php', { slug: slug, code: typed, _csrf: csrf })
        .then(function (result) {
          if (result && result.ok) {
            appliedCode = result.code;
            showPrice(result.total, result.list, result.label);
            say('Code ' + result.code + ' applied. You pay ' + result.total + '.', 'ok');
          } else {
            appliedCode = '';
            showPrice(listPrice, '', '');
            say((result && result.error) || 'That code could not be checked. Please try again.', 'bad');
          }
        })
        .catch(function () {
          appliedCode = '';
          showPrice(listPrice, '', '');
          say('We could not reach the server. Please check your connection and try again.', 'bad');
        })
        .then(function () {
          if (applyButton) applyButton.disabled = false;
        });
    }

    if (applyButton) applyButton.addEventListener('click', applyCode);
    if (codeInput) {
      codeInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
          event.preventDefault();
          applyCode();
        }
      });
      // A code arriving in the address bar is checked straight away.
      if (codeInput.value.trim() !== '') applyCode();
    }

    /* --- PayPal buttons */
    var configEl = $('#veyl-paypal');
    if (!configEl) return;

    var config;
    try {
      config = JSON.parse(configEl.textContent);
    } catch (error) {
      return;
    }
    if (!config.clientId || config.clientId.indexOf('PASTE') === 0) return;

    var sdk = document.createElement('script');
    sdk.src = 'https://www.paypal.com/sdk/js?client-id=' + encodeURIComponent(config.clientId) +
              '&currency=' + encodeURIComponent(config.currency) +
              '&components=buttons&intent=capture&disable-funding=credit';
    sdk.async = true;

    sdk.onerror = function () {
      // The buyer keeps the plain form button, which works without the SDK.
      sayPay('The PayPal buttons did not load. Use the button below instead \u2014 it takes you to the same PayPal checkout.', '');
    };

    sdk.onload = function () {
      if (!window.paypal || !window.paypal.Buttons) return;

      window.paypal.Buttons({
        style: { layout: 'vertical', shape: 'pill', color: 'black', label: 'pay', height: 48 },

        onClick: function (data, actions) {
          if (consent && !consent.checked) {
            sayPay('Please tick the box to confirm you have read the Terms and Disclaimer.', 'bad');
            consent.focus();
            return actions.reject();
          }
          return actions.resolve();
        },

        createOrder: function () {
          return postJson('/api/create-order.php', {
            slug: slug,
            code: appliedCode,
            consent: 1,
            _csrf: csrf
          }).then(function (result) {
            if (result && result.id) return result.id;
            throw new Error((result && result.error) || 'Checkout could not be started.');
          });
        },

        onApprove: function (data) {
          return postJson('/api/capture-order.php', { orderID: data.orderID, _csrf: csrf })
            .then(function (result) {
              if (result && result.ok && result.token) {
                window.location.href = '/thank-you/#t=' + encodeURIComponent(result.token);
                return;
              }
              throw new Error((result && result.error) || 'The payment could not be completed.');
            });
        },

        onCancel: function () {
          sayPay('Payment canceled. You have not been charged.', '');
        },

        onError: function (error) {
          sayPay('Something went wrong with PayPal. You have not been charged. Please try again, or use the button below.', 'bad');
          if (window.console) window.console.error(error);
        }
      }).render(slot).then(function () {
        // The PayPal buttons rendered, so the plain fallback button is no longer needed.
        if (fallback) fallback.hidden = true;
      }).catch(function () {
        if (fallback) fallback.hidden = false;
      });
    };

    document.head.appendChild(sdk);
  }

  /* ------------------------------------------------------- the confirmation page */
  function initThankYou() {
    var panel = $('[data-order-panel]');
    if (!panel) return;

    var loading = $('[data-order-loading]', panel);
    var ok = $('[data-order-ok]', panel);
    var missing = $('[data-order-missing]', panel);
    var title = $('[data-order-title]', panel);
    var badge = $('[data-order-badge]', panel);

    var TITLES = {
      loading: 'Thank you',
      ok: 'Thank you. Here is your guide.',
      missing: 'We could not find that order'
    };

    function show(which) {
      if (loading) loading.hidden = which !== 'loading';
      if (ok) ok.hidden = which !== 'ok';
      if (missing) missing.hidden = which !== 'missing';
      if (title) title.textContent = TITLES[which];
      if (badge) badge.hidden = which !== 'ok';
    }

    var match = /[#&]t=([^&]+)/.exec(window.location.hash || '');
    if (!match) {
      show('missing');
      return;
    }

    fetch('/api/order-links.php?t=' + encodeURIComponent(decodeURIComponent(match[1])), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    })
      .then(function (response) { return response.json(); })
      .then(function (order) {
        if (!order || !order.ok) {
          show('missing');
          return;
        }
        var idEl = $('[data-order-id]', panel);
        var emailEl = $('[data-order-email]', panel);
        var productEl = $('[data-order-product]', panel);
        var linkEl = $('[data-order-link]', panel);

        if (idEl) idEl.textContent = order.orderId;
        if (emailEl) emailEl.textContent = order.email || 'your PayPal email address';
        if (productEl) productEl.textContent = order.product;
        if (linkEl) {
          linkEl.setAttribute('href', order.url);
          linkEl.setAttribute('download', '');
        }
        show('ok');
      })
      .catch(function () { show('missing'); });
  }

  /* --------------------------------------------------------- resend link form */
  function initResend() {
    var form = $('[data-resend]');
    if (!form) return;

    var message = $('[data-resend-msg]');
    var button = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (event) {
      var emailField = form.querySelector('input[name="email"]');
      if (!emailField || !emailField.value.trim()) return;

      event.preventDefault();
      if (button) button.disabled = true;
      if (message) {
        message.textContent = 'Sending…';
        message.className = 'msg';
      }

      postJson('/api/resend.php', {
        email: emailField.value.trim(),
        _csrf: form.querySelector('input[name="_csrf"]').value
      })
        .then(function (result) {
          if (message) {
            message.textContent = (result && (result.message || result.error)) || 'Please try again.';
            message.className = 'msg ' + (result && result.ok ? 'msg--ok' : 'msg--bad');
          }
        })
        .catch(function () {
          if (message) {
            message.textContent = 'We could not reach the server. Please try again in a moment.';
            message.className = 'msg msg--bad';
          }
        })
        .then(function () {
          if (button) button.disabled = false;
        });
    });
  }

  function start() {
    initNav();
    initAccordions();
    initReveal();
    initBuy();
    initThankYou();
    initResend();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
