/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla.submitbutton = (task) => {
  'use strict';

  if (task === 'actionlogs.exportLogs') {
    Joomla.submitform(task, document.getElementById('exportForm'));

    return;
  }

  if (task === 'actionlogs.exportSelectedLogs') {
    // Get id of selected action logs item and pass it to export form hidden input
    const cids = [];
    const elements = [].slice.call(document.querySelectorAll("input[name='cid[]']:checked"));

    if (elements.length) {
      elements.forEach((element) => {
        cids.push(element.value);
      });
    }

    document.exportForm.cids.value = cids.join(',');
    Joomla.submitform(task, document.getElementById('exportForm'));

    return;
  }

  Joomla.submitform(task);
};
