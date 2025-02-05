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

            // ✅ Vyčistenie predchádzajúcich chýb
            clearErrors();

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

            // ✅ Odstránime staré event listenery, aby sa nepridávali viackrát
            saveButton.replaceWith(saveButton.cloneNode(true));
            const newSaveButton = row.querySelector(".saveTaskButton");

            // Po kliknutí na SAVE
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

        // ✅ Vymazanie predchádzajúcich chýb
        clearErrors();
        descriptionCell.classList.remove("error");
        deadlineInput.classList.remove("error");

        let errors = [];

        // ✅ Validácia description
        if (!description) {
            errors.push("Description is required.");
            descriptionCell.classList.add("error");
        }

        // ✅ Validácia deadline (musí byť v budúcnosti)
        const today = new Date().toISOString().split("T")[0]; // Aktuálny dátum vo formáte YYYY-MM-DD
        if (!deadline || deadline <= today) {
            errors.push("Deadline must be in the future.");
            deadlineInput.classList.add("error");
        }

        // ✅ Ak sú chyby, zobraz ich vo vyskakovacom okne a zastav ukladanie
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

                // ✅ Aktualizácia textu
                descriptionCell.textContent = description;
                deadlineText.textContent = deadline;
                priorityText.textContent = priority.charAt(0).toUpperCase() + priority.slice(1);

                // ✅ Skryť inputy a zobraziť text
                deadlineInput.classList.add("d-none");
                deadlineText.classList.remove("d-none");
                priorityInput.classList.add("d-none");
                priorityText.classList.remove("d-none");

                // ✅ Zobrazenie "✔ Updated"
                editButton.innerHTML = "<span class='updated-message'>✔ Updated</span>";
                editButton.classList.remove("d-none");
                saveButton.classList.add("d-none");

                // ✅ Po 2 sekundách resetovať tlačidlo Edit do pôvodného stavu
                setTimeout(() => {
                    editButton.innerHTML = "Edit";
                }, 2000);

                // ✅ Deaktivovať editáciu
                descriptionCell.removeAttribute("contenteditable");
            } else {
                showPopup("Failed to update the task: " + (result.message || "Unknown error."));
            }
        } catch (error) {
            console.error("Error:", error);
            showPopup("An unexpected error occurred.");
        }
    }

    // ✅ Funkcia na vymazanie chýb
    function clearErrors() {
        document.querySelectorAll(".error").forEach(el => el.classList.remove("error"));
    }

    // ✅ Funkcia na zobrazenie popupu
    function showPopup(message) {
        // Ak už popup existuje, odstránime ho pred zobrazením nového
        const existingPopup = document.querySelector(".custom-popup");
        if (existingPopup) existingPopup.remove();

        const popup = document.createElement("div");
        popup.classList.add("custom-popup");
        popup.innerHTML = `<p>${message}</p> <button onclick="this.parentElement.remove()">OK</button>`;
        document.body.appendChild(popup);
    }
});
