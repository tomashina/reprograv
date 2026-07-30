(function () {
  'use strict';

  var COOKIE_NAME = 'rg_cookie_consent';
  var COOKIE_DAYS = 182;
  var modal = null;
  var opener = null;
  var applied = {analytics: false, marketing: false};

  var isEnglish = (document.documentElement.lang || '').toLowerCase().indexOf('en') === 0;
  var copy = isEnglish ? {
    privacyTitle: 'Your privacy, your choice',
    privacyDescription: 'Necessary cookies keep the shop working. With your permission we use analytics to improve the site and marketing cookies to measure advertising.',
    privacyLink: 'Privacy policy',
    privacyHref: 'index.php?route=information/information&information_id=3',
    settings: 'Settings',
    acceptAll: 'Accept all',
    necessaryOnly: 'Necessary only',
    preferencesTitle: 'Cookie settings',
    close: 'Close',
    save: 'Save selection',
    aboutTitle: 'About your choice',
    aboutDescription: 'You can change your choice at any time using the “Cookie settings” link in the footer.',
    necessaryTitle: 'Necessary cookies',
    necessaryDescription: 'Required for the cart, login, language, currency, security and saving your choice. They cannot be disabled.',
    analyticsTitle: 'Analytics',
    analyticsDescription: 'Analytics helps us understand how the shop is used and improve its content. It loads only after consent.',
    marketingTitle: 'Marketing',
    marketingDescription: 'Marketing cookies measure advertising performance and relevant webshop events. They load only after consent.',
    alwaysOn: 'Always on'
  } : {
    privacyTitle: 'Vaša privatnost, vaš izbor',
    privacyDescription: 'Nužni kolačići omogućuju rad trgovine. Uz vaše dopuštenje koristimo analitiku za poboljšanje stranice i marketinške kolačiće za mjerenje oglasa.',
    privacyLink: 'Pravila privatnosti',
    privacyHref: 'index.php?route=information/information&information_id=3',
    settings: 'Postavke',
    acceptAll: 'Prihvati sve',
    necessaryOnly: 'Samo nužni',
    preferencesTitle: 'Postavke kolačića',
    close: 'Zatvori',
    save: 'Spremi odabir',
    aboutTitle: 'O vašem izboru',
    aboutDescription: 'Izbor možete promijeniti u bilo kojem trenutku poveznicom „Postavke kolačića” u podnožju.',
    necessaryTitle: 'Nužni kolačići',
    necessaryDescription: 'Omogućuju košaricu, prijavu, jezik, valutu, sigurnost i pamćenje vašeg izbora. Ne mogu se isključiti.',
    analyticsTitle: 'Analitika',
    analyticsDescription: 'Analitika nam pomaže razumjeti korištenje trgovine i poboljšati sadržaj. Učitava se tek nakon pristanka.',
    marketingTitle: 'Marketing',
    marketingDescription: 'Marketinški kolačići mjere učinak oglasa i relevantne događaje u webshopu. Učitavaju se tek nakon pristanka.',
    alwaysOn: 'Uvijek uključeno'
  };

  function readCookie() {
    var prefix = COOKIE_NAME + '=';
    var cookies = document.cookie ? document.cookie.split(';') : [];

    for (var index = 0; index < cookies.length; index += 1) {
      var item = cookies[index].replace(/^\s+/, '');
      if (item.indexOf(prefix) !== 0) continue;

      try {
        var value = JSON.parse(decodeURIComponent(item.substring(prefix.length)));
        if (value && value.revision === 1 && value.categories) return value;
      } catch (error) {
        return null;
      }
    }

    return null;
  }

  function writeCookie(categories) {
    var expires = new Date();
    expires.setDate(expires.getDate() + COOKIE_DAYS);
    var value = encodeURIComponent(JSON.stringify({
      revision: 1,
      consentedAt: new Date().toISOString(),
      categories: {
        necessary: true,
        analytics: !!categories.analytics,
        marketing: !!categories.marketing
      }
    }));
    var cookie = COOKIE_NAME + '=' + value + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
    if (window.location.protocol === 'https:') cookie += '; Secure';
    document.cookie = cookie;
  }

  function expireCookie(name) {
    var paths = ['/', window.location.pathname || '/'];
    paths.forEach(function (path) {
      document.cookie = name + '=; Max-Age=0; path=' + path + '; SameSite=Lax';
    });
  }

  function clearCategoryCookies(category) {
    var names = document.cookie ? document.cookie.split(';').map(function (item) {
      return item.split('=')[0].replace(/^\s+|\s+$/g, '');
    }) : [];

    names.forEach(function (name) {
      if (category === 'analytics' && (/^_ga/.test(name) || name === '_gid' || name === '_gat')) {
        expireCookie(name);
      }

      if (category === 'marketing' && (name === '_fbp' || name === '_fbc' || /^_gcl/.test(name))) {
        expireCookie(name);
      }
    });
  }

  function applyConsent(categories, mayReload) {
    var analytics = !!categories.analytics;
    var marketing = !!categories.marketing;
    var analyticsRevoked = applied.analytics && !analytics && window.gkAnalyticsLoaded;
    var marketingRevoked = applied.marketing && !marketing && window.gkMarketingLoaded;

    if (typeof window.gkSetGoogleConsent === 'function') {
      window.gkSetGoogleConsent(analytics, marketing);
    }

    if (analytics && typeof window.gkLoadAnalytics === 'function') {
      window.gkLoadAnalytics();
    } else if (!analytics) {
      clearCategoryCookies('analytics');
    }

    if (marketing && typeof window.gkLoadMarketing === 'function') {
      window.gkLoadMarketing();
    } else if (!marketing) {
      clearCategoryCookies('marketing');
    }

    applied = {analytics: analytics, marketing: marketing};
    window.rgCookieConsent = {
      necessary: true,
      analytics: analytics,
      marketing: marketing
    };

    try {
      document.dispatchEvent(new CustomEvent('reprograv:consent', {detail: window.rgCookieConsent}));
    } catch (error) {
      var event = document.createEvent('CustomEvent');
      event.initCustomEvent('reprograv:consent', false, false, window.rgCookieConsent);
      document.dispatchEvent(event);
    }

    if (mayReload && (analyticsRevoked || marketingRevoked)) {
      window.location.reload();
    }
  }

  function consentButton(label, action, secondary) {
    return '<button type="button" class="rg-consent-button' + (secondary ? ' rg-consent-button-secondary' : '') + '" data-consent-action="' + action + '">' + label + '</button>';
  }

  function closeButton() {
    return '<button type="button" class="rg-consent-close" data-consent-action="close" aria-label="' + copy.close + '" title="' + copy.close + '"><span aria-hidden="true"></span></button>';
  }

  function categorySection(id, title, description, checked, locked) {
    return '<section class="rg-consent-category">' +
      '<div class="rg-consent-category-row">' +
        '<button type="button" class="rg-consent-category-toggle" data-consent-disclosure="' + id + '" aria-expanded="false" aria-controls="rg-consent-description-' + id + '">' +
          '<span class="rg-consent-chevron" aria-hidden="true"></span><span>' + title + '</span>' +
        '</button>' +
        (locked ?
          '<span class="rg-consent-always-on">' + copy.alwaysOn + '<span class="rg-consent-switch is-checked is-locked" aria-hidden="true"></span></span>' :
          '<label class="rg-consent-switch' + (checked ? ' is-checked' : '') + '">' +
            '<input type="checkbox" data-consent-category="' + id + '"' + (checked ? ' checked' : '') + ' aria-label="' + title + '">' +
            '<span aria-hidden="true"></span>' +
          '</label>') +
      '</div>' +
      '<div id="rg-consent-description-' + id + '" class="rg-consent-category-description" hidden>' + description + '</div>' +
    '</section>';
  }

  function syncViewportHeight() {
    if (!modal) return;

    var viewportHeight = window.visualViewport && window.visualViewport.height ?
      window.visualViewport.height :
      window.innerHeight;

    if (viewportHeight) {
      modal.style.setProperty('--rg-consent-viewport-height', Math.round(viewportHeight) + 'px');
    }
  }

  function renderShell(content, preferences, allowClose) {
    closeModal(false);
    opener = document.activeElement;
    modal = document.createElement('div');
    modal.id = 'rg-consent-root';
    modal.innerHTML =
      '<div class="rg-consent-overlay" data-consent-overlay></div>' +
      '<div class="rg-consent-dialog' + (preferences ? ' rg-consent-preferences' : ' rg-consent-notice') + '" role="dialog" aria-modal="true" aria-labelledby="rg-consent-title">' +
        (allowClose ? closeButton() : '') +
        content +
      '</div>';
    document.body.appendChild(modal);
    syncViewportHeight();
    document.body.classList.add('rg-consent-open');
    bindModalEvents();

    var focusTarget = modal.querySelector('button');
    if (focusTarget) focusTarget.focus();
  }

  function showNotice() {
    var content =
      '<div class="rg-consent-accent"></div>' +
      '<div class="rg-consent-body">' +
        '<h2 id="rg-consent-title">' + copy.privacyTitle + '</h2>' +
        '<p>' + copy.privacyDescription + ' <a href="' + copy.privacyHref + '">' + copy.privacyLink + '</a>.</p>' +
      '</div>' +
      '<div class="rg-consent-footer rg-consent-notice-actions">' +
        consentButton(copy.acceptAll, 'accept-all', false) +
        consentButton(copy.necessaryOnly, 'necessary-only', false) +
        consentButton(copy.settings, 'preferences', true) +
      '</div>';

    renderShell(content, false, false);
  }

  function showPreferences() {
    var saved = readCookie();
    var categories = saved ? saved.categories : {analytics: false, marketing: false};
    var content =
      '<div class="rg-consent-accent"></div>' +
      '<div class="rg-consent-preferences-header"><h2 id="rg-consent-title">' + copy.preferencesTitle + '</h2></div>' +
      '<div class="rg-consent-preferences-body">' +
        '<div class="rg-consent-about"><h3>' + copy.aboutTitle + '</h3><p>' + copy.aboutDescription + '</p></div>' +
        categorySection('necessary', copy.necessaryTitle, copy.necessaryDescription, true, true) +
        categorySection('analytics', copy.analyticsTitle, copy.analyticsDescription, !!categories.analytics, false) +
        categorySection('marketing', copy.marketingTitle, copy.marketingDescription, !!categories.marketing, false) +
      '</div>' +
      '<div class="rg-consent-footer rg-consent-preferences-actions">' +
        '<div>' + consentButton(copy.acceptAll, 'accept-all', false) + consentButton(copy.necessaryOnly, 'necessary-only', false) + '</div>' +
        consentButton(copy.save, 'save', true) +
      '</div>';

    renderShell(content, true, true);
  }

  function closeModal(restoreFocus) {
    if (!modal) return;
    modal.parentNode.removeChild(modal);
    modal = null;
    document.body.classList.remove('rg-consent-open');
    if (restoreFocus && opener && typeof opener.focus === 'function') opener.focus();
  }

  function saveAndClose(categories) {
    writeCookie(categories);
    applyConsent(categories, true);
    closeModal(true);
  }

  function bindModalEvents() {
    modal.addEventListener('click', function (event) {
      var actionTarget = event.target.closest('[data-consent-action]');
      if (actionTarget) {
        var action = actionTarget.getAttribute('data-consent-action');

        if (action === 'accept-all') saveAndClose({analytics: true, marketing: true});
        if (action === 'necessary-only') saveAndClose({analytics: false, marketing: false});
        if (action === 'preferences') showPreferences();
        if (action === 'close') {
          if (readCookie()) closeModal(true);
          else showNotice();
        }
        if (action === 'save') {
          var analytics = modal.querySelector('[data-consent-category="analytics"]');
          var marketing = modal.querySelector('[data-consent-category="marketing"]');
          saveAndClose({
            analytics: !!(analytics && analytics.checked),
            marketing: !!(marketing && marketing.checked)
          });
        }
        return;
      }

      var disclosure = event.target.closest('[data-consent-disclosure]');
      if (disclosure) {
        var id = disclosure.getAttribute('data-consent-disclosure');
        var description = modal.querySelector('#rg-consent-description-' + id);
        var expanded = disclosure.getAttribute('aria-expanded') === 'true';
        disclosure.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        description.hidden = expanded;
        return;
      }

      var switchControl = event.target.closest('.rg-consent-switch');
      if (switchControl && !switchControl.classList.contains('is-locked')) {
        window.setTimeout(function () {
          var input = switchControl.querySelector('input');
          switchControl.classList.toggle('is-checked', !!(input && input.checked));
        }, 0);
      }
    });
  }

  function keepFocus(event) {
    if (!modal) return;

    if (event.key === 'Escape' && readCookie()) {
      event.preventDefault();
      closeModal(true);
      return;
    }

    if (event.key !== 'Tab') return;
    var focusable = Array.prototype.slice.call(modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')).filter(function (element) {
      return element.offsetWidth || element.offsetHeight || element.getClientRects().length;
    });
    if (!focusable.length) return;
    var first = focusable[0];
    var last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function boot() {
    var saved = readCookie();
    if (saved) {
      applyConsent(saved.categories, false);
    } else {
      applyConsent({analytics: false, marketing: false}, false);
      showNotice();
    }

    document.addEventListener('click', function (event) {
      var trigger = event.target.closest('[data-cookie-consent-trigger]');
      if (!trigger) return;
      event.preventDefault();
      showPreferences();
    });

    document.addEventListener('keydown', keepFocus);
    window.addEventListener('resize', syncViewportHeight);

    if (window.visualViewport) {
      window.visualViewport.addEventListener('resize', syncViewportHeight);
    }
  }

  window.ReprogravCookieConsent = {
    showPreferences: showPreferences,
    consent: function () {
      var saved = readCookie();
      return saved ? saved.categories : {necessary: true, analytics: false, marketing: false};
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
}());
