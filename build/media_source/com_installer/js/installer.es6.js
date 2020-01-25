/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  const installPackageButtonId = 'installbutton_package';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbuttonpackage = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_package.value === '') {
        alert(Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'upload';
        form.submit();
      }
    };

    Joomla.submitbuttonfolder = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_directory.value === '') {
        alert(Joomla.JText._('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'folder';
        form.submit();
      }
    };

    Joomla.submitbuttonurl = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        alert(Joomla.JText._('PLG_INSTALLER_URLINSTALLER_NO_URL'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbutton4 = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbuttonUpload = () => {
      const form = document.getElementById('uploadForm');

      // do field validation
      if (form.install_package.value === '') {
        alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
      } else {
        Joomla.displayLoader();

        form.submit();
      }
    };

    Joomla.displayLoader = () => {
      const loading = document.getElementById('loading');
      if (loading) {
        loading.classList.remove('hidden');
      }
    };

    const loading = document.getElementById('loading');
    const installer = document.getElementById('installer-install');

    if (loading && installer) {
      loading.style.top = parseInt(installer.offsetTop - window.pageYOffset, 10);
      loading.style.left = 0;
      loading.style.width = '100%';
      loading.style.height = '100%';
      loading.classList.add('hidden');
      loading.style.marginTop = '-10px';
    }

    document.getElementById(installPackageButtonId).addEventListener('click', (event) => {
      event.preventDefault();
      Joomla.submitbuttonpackage();
    });
  });
})(Joomla);

document.addEventListener('DOMContentLoaded', () => {
  if (typeof FormData === 'undefined') {
    document.querySelector('#legacy-uploader').classList.remove('hidden');
    document.querySelector('#uploader-wrapper').classList.add('hidden');
    return;
  }

  let uploading = false;
  const dragZone = document.querySelector('#dragarea');
  const fileInput = document.querySelector('#install_package');
  const button = document.querySelector('#select-file-button');
  const returnUrl = document.querySelector('#installer-return').value;
  const progress = document.getElementById('upload-progress');
  const progressBar = progress.querySelectorAll('.bar')[0];
  const percentage = progress.querySelectorAll('.uploading-number')[0];
  let uploadUrl = 'index.php?option=com_installer&task=install.ajax_upload';

  function showError(res) {
    dragZone.setAttribute('data-state', 'pending');
    let message = Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');
    if (res == null) {
      message = Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');
    } else if (typeof res === 'string') {
      // Let's remove unnecessary HTML
      message = res.replace(/(<([^>]+)>|\s+)/g, ' ');
    } else if (res.message) {
      ({ message } = res);
    }
    Joomla.renderMessages({ error: [message] });
  }

  if (returnUrl) {
    uploadUrl += `&return=${returnUrl}`;
  }

  button.addEventListener('click', () => {
    fileInput.click();
  });

  fileInput.addEventListener('change', () => {
    if (uploading) {
      return;
    }
    Joomla.submitbuttonpackage();
  });

  dragZone.addEventListener('dragenter', (event) => {
    event.preventDefault();
    event.stopPropagation();

    dragZone.classList.add('hover');

    return false;
  });

  // Notify user when file is over the drop area
  dragZone.addEventListener('dragover', (event) => {
    event.preventDefault();
    event.stopPropagation();

    dragZone.classList.add('hover');

    return false;
  });

  dragZone.addEventListener('dragleave', (event) => {
    event.preventDefault();
    event.stopPropagation();
    dragZone.classList.remove('hover');

    return false;
  });

  dragZone.addEventListener('drop', (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (uploading) {
      return;
    }

    dragZone.classList.remove('hover');

    const files = event.target.files || event.dataTransfer.files;

    if (!files.length) {
      return;
    }

    const file = files[0];
    const data = new FormData();

    data.append('install_package', file);
    data.append('installtype', 'upload');
    dragZone.setAttribute('data-state', 'uploading');
    progressBar.setAttribute('aria-valuenow', 0);

    uploading = true;
    progressBar.style.width = 0;
    percentage.textContent = '0';

    // Upload progress
    const progressCallback = (evt) => {
      if (evt.lengthComputable) {
        const percentComplete = evt.loaded / evt.total;
        const number = Math.round(percentComplete * 100);
        progressBar.css('width', `${number}%`);
        progressBar.setAttribute('aria-valuenow', number);
        percentage.textContent = `${number}`;
        if (number === 100) {
          dragZone.setAttribute('data-state', 'installing');
        }
      }
    };

    Joomla.request({
      url: uploadUrl,
      method: 'POST',
      perform: true,
      data,
      headers: { 'Content-Type': 'false' },
      uploadProgressCallback: progressCallback,
      onSuccess: (response) => {
        if (!response) {
          showError(response);
          return;
        }

        let res;

        try {
          res = JSON.parse(response);
        } catch (e) {
          showError(e);

          return;
        }

        if (!res.success && !res.data) {
          showError(res);

          return;
        }

        // Always redirect that can show message queue from session
        if (res.data.redirect) {
          window.location.href = res.data.redirect;
        } else {
          window.location.href = 'index.php?option=com_installer&view=install';
        }
      },
      onError: (error) => {
        uploading = false;
        if (error.status === 200) {
          const res = error.responseText || error.responseJSON;
          showError(res);
        } else {
          showError(error.statusText);
        }
      },
    });
  });
});
