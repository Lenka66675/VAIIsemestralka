document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".editTaskButton").forEach(editButton => {
        editButton.addEventListener("click", function () {
            const taskId = this.dataset.id;
            const row = document.querySelector(`#taskRow-${taskId}`);

            const descriptionCell = row.querySelector('.editable[data-name="description"]');
            const deadlineText = row.querySelector(`.taskDeadlineText[data-id="${taskId}"]`);
            const deadlineInput = row.querySelector(`.taskDeadlineInput[data-id="${taskId}"]`);
            const priorityText = row.querySelector(`.taskPriorityText[data-id="${taskId}"]`);
            const priorityInput = row.querySelector(`.taskPriorityInput[data-id="${taskId}"]`);
            const saveButton = row.querySelector(".saveTaskButton");

            clearErrors();

            descriptionCell.setAttribute("contenteditable", "true");
            descriptionCell.focus();
            deadlineText.classList.add("d-none");
            deadlineInput.classList.remove("d-none");
            priorityText.classList.add("d-none");
            priorityInput.classList.remove("d-none");

            editButton.classList.add("d-none");
            saveButton.classList.remove("d-none");

            saveButton.replaceWith(saveButton.cloneNode(true));
            const newSaveButton = row.querySelector(".saveTaskButton");

            newSaveButton.addEventListener("click", async function () {
                await saveTask(taskId, descriptionCell, deadlineInput, priorityInput, editButton, newSaveButton, deadlineText, priorityText);
            });
        });
    });

    async function saveTask(taskId, descriptionCell, deadlineInput, priorityInput, editButton, saveButton, deadlineText, priorityText) {
        const url = `/task/${taskId}/update`;
        const description = descriptionCell.textContent.trim();
        const deadline = deadlineInput.value;
        const priority = priorityInput.value;

        clearErrors();
        descriptionCell.classList.remove("error");
        deadlineInput.classList.remove("error");

        let errors = [];

        if (!description) {
            errors.push("Description is required.");
            descriptionCell.classList.add("error");
        }

        const today = new Date().toISOString().split("T")[0];
        if (!deadline || deadline <= today) {
            errors.push("Deadline must be in the future.");
            deadlineInput.classList.add("error");
        }

        if (errors.length > 0) {
            showPopup(errors.join("<br>"));
            return;
        }

        try {
            const response = await fetch(url, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({
                    description: description,
                    deadline: deadline,
                    priority: priority
                })
            });

            const result = await response.json();

            if (result.success) {
                console.log("Task updated successfully!");

                descriptionCell.textContent = description;
                deadlineText.textContent = deadline;
                priorityText.textContent = priority.charAt(0).toUpperCase() + priority.slice(1);

                deadlineInput.classList.add("d-none");
                deadlineText.classList.remove("d-none");
                priorityInput.classList.add("d-none");
                priorityText.classList.remove("d-none");

                editButton.innerHTML = "<span class='updated-message'>✔ Updated</span>";
                editButton.classList.remove("d-none");
                saveButton.classList.add("d-none");

                setTimeout(() => {
                    editButton.innerHTML = "Edit";
                }, 2000);

                descriptionCell.removeAttribute("contenteditable");
            } else {
                showPopup("Failed to update the task: " + (result.message || "Unknown error."));
            }
        } catch (error) {
            console.error("Error:", error);
            showPopup("An unexpected error occurred.");
        }
    }


    function clearErrors() {
        document.querySelectorAll(".error").forEach(el => el.classList.remove("error"));
    }

    function showPopup(message) {
        const existingPopup = document.querySelector(".custom-popup");
        if (existingPopup) existingPopup.remove();

        const popup = document.createElement("div");
        popup.classList.add("custom-popup");
        popup.innerHTML = `<p>${message}</p> <button onclick="this.parentElement.remove()">OK</button>`;
        document.body.appendChild(popup);
    }
});
