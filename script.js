(function () {
  var input = document.getElementById('cv');
  var nameEl = document.getElementById('fileName');
  var chip = document.getElementById('fileChip');
  var dropzone = document.getElementById('dropzone');
  if (!input || !nameEl) return;

  function showName(file) {
    nameEl.textContent = file ? file.name : '';
    if (chip) chip.style.display = file ? 'block' : 'none';
  }

  input.addEventListener('change', function () {
    showName(input.files && input.files[0]);
  });

  if (dropzone) {
    ['dragenter', 'dragover'].forEach(function (eventName) {
      dropzone.addEventListener(eventName, function (event) {
        event.preventDefault();
        dropzone.classList.add('active');
      });
    });
    ['dragleave', 'drop'].forEach(function (eventName) {
      dropzone.addEventListener(eventName, function (event) {
        event.preventDefault();
        dropzone.classList.remove('active');
      });
    });
    dropzone.addEventListener('drop', function (event) {
      if (event.dataTransfer && event.dataTransfer.files.length) {
        input.files = event.dataTransfer.files;
        showName(event.dataTransfer.files[0]);
      }
    });
  }
})();
