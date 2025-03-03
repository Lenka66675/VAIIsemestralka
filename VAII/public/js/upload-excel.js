document.addEventListener("DOMContentLoaded", function () {
    let uploadForm = document.getElementById("uploadForm");
    let uploadBtn = document.getElementById("uploadBtn");
    let loadingSpinner = document.getElementById("loadingSpinner");
    let backendErrors = document.getElementById("backendErrors");

    uploadForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Zastaví predvolený submit

        let formData = new FormData(uploadForm);

        // Vymažeme predchádzajúce chyby
        backendErrors.innerHTML = "";
        backendErrors.style.display = "none";

        // Skontrolujeme typ súboru ešte pred odoslaním
        let fileInput = document.getElementById("fileInput");
        let file = fileInput.files[0];

        if (file) {
            let allowedTypes = ["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.ms-excel"];
            if (!allowedTypes.includes(file.type)) {
                backendErrors.innerHTML = "❌ Povolené sú len Excel súbory (.xls, .xlsx)";
                backendErrors.style.display = "block";
                return;
            }
        }

        // Skryjeme tlačidlo a zobrazíme loading
        uploadBtn.classList.add("uploading");
        uploadBtn.innerHTML = "⏳ Nahráva sa...";
        loadingSpinner.style.display = "block";

        // Odošleme dáta na server cez AJAX
        fetch(uploadForm.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // Ak je úspech, refresh
                } else {
                    // Ak sú chyby, zobrazíme ich
                    backendErrors.innerHTML = "❌ " + (data.message || "Chyba pri spracovaní súboru.");
                    backendErrors.style.display = "block";
                    uploadBtn.classList.remove("uploading");
                    uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload';
                    loadingSpinner.style.display = "none";
                }
            })
            .catch(error => {
                backendErrors.innerHTML = "❌ Nastala chyba pri nahrávaní.";
                backendErrors.style.display = "block";
                uploadBtn.classList.remove("uploading");
                uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload';
                loadingSpinner.style.display = "none";
            });
    });
});
