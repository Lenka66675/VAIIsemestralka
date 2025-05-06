document.addEventListener("DOMContentLoaded", function () {
    let uploadForm = document.getElementById("uploadForm");
    let uploadBtn = document.getElementById("uploadBtn");
    let loadingSpinner = document.getElementById("loadingSpinner");
    let backendErrors = document.getElementById("backendErrors");

    uploadForm.addEventListener("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(uploadForm);

        backendErrors.innerHTML = "";
        backendErrors.style.display = "none";
        backendErrors.classList.remove("error-message", "success-message");

        uploadBtn.disabled = true;
        uploadBtn.innerHTML = "⏳ Loading...";
        loadingSpinner.style.display = "block";

        fetch(uploadForm.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                backendErrors.style.display = "block";

                if (status === 200 && body.success) {
                    backendErrors.innerHTML = "✅ " + body.message;
                    backendErrors.classList.add("success-message");

                    setTimeout(() => {
                        backendErrors.style.opacity = "0";
                        setTimeout(() => {
                            backendErrors.style.display = "none";
                            backendErrors.style.opacity = "1";
                        }, 500);
                    }, 3000);
                } else {
                    backendErrors.innerHTML = "❌ " + body.message;
                    backendErrors.classList.add("error-message");

                    setTimeout(() => {
                        backendErrors.style.opacity = "0";
                        setTimeout(() => {
                            backendErrors.style.display = "none";
                            backendErrors.style.opacity = "1";
                        }, 500);
                    }, 5000);
                }
            })
            .catch(() => {
                backendErrors.innerHTML = "❌ Nastala chyba pri nahrávaní.";
                backendErrors.classList.add("error-message");
                backendErrors.style.display = "block";

                setTimeout(() => {
                    backendErrors.style.opacity = "0";
                    setTimeout(() => {
                        backendErrors.style.display = "none";
                        backendErrors.style.opacity = "1";
                    }, 500);
                }, 5000);
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload';
                loadingSpinner.style.display = "none";
            });
    });
});
