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

            // Zobrazenie vstupov na editáciu
            descriptionCell.setAttribute("contenteditable", "true");
            descriptionCell.focus();
            deadlineText.classList.add("d-none");
            deadlineInput.classList.remove("d-none");
            priorityText.classList.add("d-none");
            priorityInput.classList.remove("d-none");

            // Skryť tlačidlo Edit a zobraziť Save
            editButton.classList.add("d-none");
            saveButton.classList.remove("d-none");

            // Po kliknutí na SAVE
            saveButton.addEventListener("click", async function () {
                await saveTask(taskId, descriptionCell, deadlineInput, priorityInput, editButton, saveButton, deadlineText, priorityText);
            }, { once: true });
        });
    });

    async function saveTask(taskId, descriptionCell, deadlineInput, priorityInput, editButton, saveButton, deadlineText, priorityText) {
        const url = `/task/${taskId}/update`;
        const description = descriptionCell.textContent.trim();
        const deadline = deadlineInput.value;
        const priority = priorityInput.value;

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

                // Aktualizácia textu
                descriptionCell.textContent = description;
                deadlineText.textContent = deadline;
                priorityText.textContent = priority.charAt(0).toUpperCase() + priority.slice(1);

                // Skryť inputy a zobraziť text
                deadlineInput.classList.add("d-none");
                deadlineText.classList.remove("d-none");
                priorityInput.classList.add("d-none");
                priorityText.classList.remove("d-none");

                // **Zobrazenie "Updated" namiesto tlačidla Edit**
                editButton.innerHTML = "<span class='updated-message'>✔ Updated</span>";
                editButton.style.backgroundColor = "transparent";
                editButton.style.fontWeight = "bold";
                editButton.style.cursor = "default";
                editButton.style.border = "none";
                editButton.style.padding = "5px 10px";
                editButton.classList.remove("d-none");

                // Skryť tlačidlo Save
                saveButton.classList.add("d-none");

                // **Po 2 sekundách resetovať tlačidlo Edit do pôvodného stavu**
                setTimeout(() => {
                    editButton.innerHTML = "Edit";
                    editButton.style.backgroundColor = "";
                    editButton.style.fontWeight = ""; // **Toto zabezpečí, že sa nevráti BOLD**
                    editButton.style.cursor = "pointer";
                    editButton.style.border = "";
                    editButton.style.padding = "";
                }, 2000);

                // Deaktivovať editáciu
                descriptionCell.removeAttribute("contenteditable");
            } else {
                alert("Failed to update the task: " + (result.message || "Unknown error."));
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An unexpected error occurred.");
        }
    }
});
