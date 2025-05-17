// Handles the location dropdown selection
function handleLocationChange(selectElement) {
  const selectedValue = selectElement.value;
  const selectedText = selectElement.options[selectElement.selectedIndex].text;

  if (selectedValue === 'new') {
    window.location.href = 'new_location.php';
    return;
  }

  if (selectedValue) {
    document.getElementById('envlog_data_form').style.display = 'flex';
    document.getElementById('location_id').value = selectedValue;
    document.getElementById('place_name').textContent = selectedText;
    document
      .querySelectorAll('[data-envlog-location-control]')
      .forEach((el) => {
        el.style.display = 'none';
      });
    closeSuccess();
  }
}

// Form submission: track field order
document.querySelector('form').addEventListener('submit', function (e) {
  const inputs = [...this.elements].filter(
    (el) => el.name && el.type !== 'hidden'
  );
  const order = inputs.map((input) => input.name);
  document.getElementById('field_order').value = JSON.stringify(order);
});

// QR Code Scanner
const successSound = document.getElementById('success-sound');
const errorSound = document.getElementById('error-sound');

const scanner = new Html5QrcodeScanner('reader', {
  qrbox: { width: 200, height: 200 },
  fps: 10,
  videoConstraints: {
    facingMode: { exact: 'environment' },
  },
});

scanner.render(onScanSuccess, onScanError);

function onScanSuccess(result) {
  const selectMenu = document.querySelector('select[name="location"]');
  const resultText = document.querySelector('#result');
  const options = Array.from(selectMenu.options);
  const matchedOption = options.find((option) => option.text === result);

  if (matchedOption) {
    successSound.play();
    selectMenu.value = matchedOption.value;
    handleLocationChange(selectMenu);
  } else {
    errorSound.play();
    resultText.innerHTML = `
      <div class="envlog_alert envlog_error">
        <button class="envlog_alert_close_btn" aria-label="Close">&times;</button>
        <p><strong>${result}</strong> is not a location in the database.
          ${
            NEW_LOCATIONS
              ? `<a href="new_location.php?new_loc=${encodeURIComponent(
                  result
                )}">Click here</a> to setup this new location.`
              : `<a href="index.php">Click here</a> to scan again or select an existing location from the menu below.`
          }
        </p>
      </div>`;
  }
  closeSuccess();
  scanner.clear();
}

function onScanError(err) {
  console.error(err);
}

// Close alerts
document.addEventListener('DOMContentLoaded', () => {
  document
    .querySelectorAll('.envlog_alert .envlog_alert_close_btn')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        removeSuccessParamFromUrl();
        const alert = btn.closest('.envlog_alert');
        if (alert) {
          alert.style.display = 'none';
        }
      });
    });
});

function closeSuccess() {
  const successBox = document.querySelector('.envlog_success');
  if (successBox) {
    successBox.style.display = 'none';
    removeSuccessParamFromUrl();
  }
}

// Remove success=1 when the alert box is closed
function removeSuccessParamFromUrl() {
  const url = new URL(window.location);
  if (url.searchParams.has('success')) {
    url.searchParams.delete('success');
    window.history.replaceState({}, document.title, url.toString());
  }
}

// Listen for location
document.addEventListener('DOMContentLoaded', () => {
  const locationSelect = document.getElementById('locationSelect');
  if (locationSelect) {
    locationSelect.addEventListener('change', () =>
      handleLocationChange(locationSelect)
    );

    // Automatically trigger location handling if editing
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit_id') && locationSelect.value) {
      handleLocationChange(locationSelect);
    }
  }
});

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('envlog_alert_close_btn')) {
    e.target.parentElement.remove();
  }
});
