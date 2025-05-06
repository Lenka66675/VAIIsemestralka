document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("editProjectModal");
    const closeModal = modal.querySelector(".close");
    const editProjectForm = document.getElementById("editProjectForm");

    document.querySelectorAll(".editProjectButton").forEach(button => {
        button.addEventListener("click", function () {
            const projectId = this.dataset.id;
            console.log("🟢 Edit button clicked for project ID:", projectId);

            fetch(`/projects/${projectId}/edit-data`)
                .then(response => response.json())
                .then(project => {
                    console.log("🔹 Project data loaded:", project);

                    document.getElementById("editProjectId").value = project.id;
                    document.getElementById("editProjectName").value = project.name;
                    document.getElementById("editProjectDescription").value = project.description;

                    const imagePreview = document.getElementById("editProjectImagePreview");
                    if (project.image) {
                        imagePreview.src = project.image;
                        imagePreview.style.display = "block";
                    } else {
                        imagePreview.style.display = "none";
                    }

                    const attachmentsContainer = document.getElementById("editProjectAttachments");
                    attachmentsContainer.innerHTML = "";

                    if (project.attachments && project.attachments.length > 0) {
                        project.attachments.forEach(attachment => {
                            const fileName = attachment.split("/").pop();
                            const attachmentElement = document.createElement("li");
                            attachmentElement.innerHTML = `📁 <a href="${attachment}" download>${fileName}</a>`;
                            attachmentsContainer.appendChild(attachmentElement);
                        });
                    }

                    modal.style.display = "block";
                })
                .catch(error => console.error("❌ Failed to load project data:", error));
        });
    });

    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
    });

    editProjectForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const projectId = document.getElementById("editProjectId").value;
        const nameInput = document.getElementById("editProjectName");
        const imageInput = document.getElementById("editProjectImage");
        const attachmentsInput = document.getElementById("editProjectAttachments");

        const formData = new FormData(editProjectForm);
        formData.append("_method", "PUT");

        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        let errors = [];

        const name = nameInput.value.trim();
        if (name.length < 3 || name.length > 255) {
            errors.push({ input: nameInput, message: "❗ Project name must be between 3 and 255 characters." });
        }

        if (imageInput.files.length > 0) {
            const file = imageInput.files[0];
            const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
            if (!allowedTypes.includes(file.type)) {
                errors.push({ input: imageInput, message: "❗ Only JPEG, PNG, JPG, and GIF images are allowed." });
            }
            if (file.size > 5 * 1024 * 1024) { // 5MB
                errors.push({ input: imageInput, message: "❗ Image size cannot exceed 5MB." });
            }
        }

        if (attachmentsInput.files.length > 0) {
            for (let file of attachmentsInput.files) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    errors.push({ input: attachmentsInput, message: `❗ Each attachment cannot exceed 10MB: ${file.name}` });
                }
            }
        }

        if (errors.length > 0) {
            errors.forEach(error => {
                showError(error.input, error.message);
            });
            return;
        }

        console.log("🟡 Submitting update for project ID:", projectId);

        try {
            const response = await fetch(`/projects/${projectId}/update`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").getAttribute("content")
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error("❌ Server returned an error");
            }

            const result = await response.json();
            console.log("🔹 Response from server:", result);

            if (result.success) {
                console.log("✅ Project updated successfully!");

                document.querySelector(`#projectCard-${projectId} .project-title`).textContent = formData.get("name");
                document.querySelector(`#projectCard-${projectId} .project-description`).textContent = formData.get("description");

                const imageInput = document.getElementById("editProjectImage");
                if (imageInput.files.length > 0) {
                    const newImageUrl = URL.createObjectURL(imageInput.files[0]);
                    document.querySelector(`#projectCard-${projectId} img`).src = newImageUrl;
                }

                const attachmentsContainer = document.querySelector("#projectAttachmentsList");
                attachmentsContainer.innerHTML = "";

                if (result.attachments && result.attachments.length > 0) {
                    result.attachments.forEach(attachment => {
                        const fileName = attachment.split("/").pop();
                        const attachmentElement = document.createElement("li");
                        attachmentElement.innerHTML = `📁 <a href="${attachment}" download>${fileName}</a>`;
                        attachmentsContainer.appendChild(attachmentElement);
                    });
                }

                modal.style.display = "none";
                showPopup("✅ Project updated successfully!");
            } else {
                showPopup("❌ Failed to update project.");
            }
        } catch (error) {
            console.error("❌ Error updating project:", error);
            showPopup("❌ An error occurred while updating the project.");
        }
    });
});


function showError(input, message) {
    input.classList.add('input-error');
    const errorMsg = document.createElement('p');
    errorMsg.classList.add('error-message');
    errorMsg.innerHTML = `<span class="exclamation">❗</span> ${message}`;
    input.parentElement.appendChild(errorMsg);
}


function showPopup(message) {
    console.log("🔔 showPopup called with message:", message);

    const existingPopup = document.querySelector(".custom-popup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.classList.add("custom-popup");
    popup.innerHTML = `<p>${message}</p> <button onclick="this.parentElement.remove()">OK</button>`;
    document.body.appendChild(popup);

    setTimeout(() => {
        popup.remove();
    }, 3000);
}
