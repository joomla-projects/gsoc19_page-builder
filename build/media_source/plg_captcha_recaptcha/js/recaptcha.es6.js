/**
 * @package     Joomla.JavaScript
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((window, document, Joomla) => {
  Joomla.initReCaptcha2 = () => {
    'use strict';

    const elements = [].slice.call(document.getElementsByClassName('g-recaptcha'));
    const optionKeys = ['sitekey', 'theme', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'];

    elements.forEach((element) => {
      let options = {};

      if (element.dataset) {
        options = element.dataset;
      } else {
        optionKeys.forEach((key) => {
          const optionKeyFq = `data-${key}`;
          if (element.hasAttribute(optionKeyFq)) {
            options[key] = element.getAttribute(optionKeyFq);
          }
        });
      }

      // Set the widget id of the recaptcha item
      element.setAttribute(
        'data-recaptcha-widget-id',
        window.grecaptcha.render(element, options),
      );
    });
  };
})(window, document, Joomla);
