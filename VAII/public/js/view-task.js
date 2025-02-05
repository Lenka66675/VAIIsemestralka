document.addEventListener('DOMContentLoaded', function () {
    console.log("🟢 Task Modal JS Loaded ✅");

    const modal = document.getElementById('taskModal');
    const closeButton = document.querySelector('.close');
    const taskDescription = document.getElementById('taskDescription');
    const solutionContainer = document.getElementById('existingSolution');
    const attachmentContainer = document.getElementById('existingAttachment');
    const isAdmin = document.body.dataset.role === 'admin';
    let currentTaskId = null;

    // 🔹 Ak nie je admin, nájdeme inputy na pridanie riešenia
    let solutionText, solutionFile, saveSolutionButton;
    if (!isAdmin) {
        solutionText = document.getElementById('solutionText');
        solutionFile = document.getElementById('solutionFile');
        saveSolutionButton = document.querySelector('.saveSolutionButton');
    }

    // 🔹 Funkcia na otvorenie modálu
    function openModal(taskId, description) {
        currentTaskId = taskId;
        taskDescription.textContent = description;
        solutionContainer.innerHTML = "";
        attachmentContainer.innerHTML = "";

        console.log(`🟢 Opening modal for Task ID: ${taskId}`);

        // ✅ Vyčisti inputy na riešenie, ale len ak nie je admin
        if (!isAdmin) {
            document.getElementById('solutionText').value = ""; // ✅ Vymazanie starého textu
            document.getElementById('solutionFile').value = ""; // ✅ Vymazanie starého súboru
        }

        if (isAdmin) {
            fetch(`/task/${taskId}/getAllSolutions`)
                .then(response => response.json())
                .then(data => {
                    if (data.solutions) {
                        data.solutions.forEach(sol => {
                            solutionContainer.innerHTML += `<p><strong>${sol.user}:</strong> ${sol.solution}</p>`;
                            if (sol.attachments && sol.attachments.length > 0) {
                                attachmentContainer.innerHTML += `<p><strong>Attachments:</strong></p>`;
                                sol.attachments.forEach(file => {
                                    let cleanFileName = file.split('/').pop().replace(/^\d+_/, ''); // ✅ Odstráni čísla na začiatku názvu
                                    attachmentContainer.innerHTML += `
                                <p>
                                    <a href="${file}" class="downloadFileLink" download>
                                        📎 ${cleanFileName}
                                    </a>
                                </p>`;
                                });
                            }
                        });
                    }
                });
        } else {
            fetch(`/task/${taskId}/getSolution`)
                .then(response => response.json())
                .then(data => {
                    if (data.solution) {
                        solutionContainer.innerHTML = `<p><strong>Solution:</strong> ${data.solution}</p>`;
                    }
                    if (data.attachments && data.attachments.length > 0) {
                        attachmentContainer.innerHTML = `<p><strong>Attachments:</strong></p>`;
                        data.attachments.forEach(file => {
                            let cleanFileName = file.split('/').pop().replace(/^\d+_/, ''); // ✅ Odstráni čísla na začiatku názvu
                            attachmentContainer.innerHTML += `
                        <p>
                            <a href="${file}" class="downloadFileLink" download>
                                📎 ${cleanFileName}
                            </a>
                        </p>`;
                        });
                    } else {
                        attachmentContainer.innerHTML = `<p>No attachment found</p>`;
                    }
                });
        }

        modal.classList.add('show');
        modal.style.display = "block";
    }




    // 🔹 Kliknutie na "View Details"
    document.querySelectorAll('.viewTaskButton').forEach(button => {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            const taskId = this.dataset.id;
            const row = document.getElementById(`taskRow-${taskId}`);
            const description = row.querySelector('.editable[data-name="description"]').textContent.trim();

            openModal(taskId, description);
        });
    });

    // 🔹 Zatvorenie modálu
    closeButton.addEventListener('click', function () {
        modal.classList.remove('show');
        modal.style.display = "none";
    });

    // 🔹 Ak nie je admin, umožníme používateľovi uložiť riešenie
    if (!isAdmin) {
        saveSolutionButton.addEventListener('click', async function () {
            if (!currentTaskId) return;

            const solution = solutionText.value.trim();
            const files = solutionFile.files; // Môže byť viacero súborov

            if (!solution && files.length === 0) {
                alert("❌ Please enter a solution or upload a file.");
                return;
            }

            const formData = new FormData();
            formData.append('solution', solution);
            for (let i = 0; i < files.length; i++) {
                formData.append('attachments[]', files[i]); // Podporuje viac súborov
            }
            formData.append('_method', 'PATCH');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute("content"));

            try {
                const response = await fetch(`/task/${currentTaskId}/saveSolution`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✔ Solution saved successfully!');
                    modal.classList.remove('show');
                    modal.style.display = "none";
                } else {
                    alert('❌ Failed to save solution: ' + result.message);
                }
            } catch (error) {
                console.error('Error saving solution:', error);
                alert('❌ An unexpected error occurred.');
            }
        });
    }

    // 🔹 Skryť úlohy po označení ako "Completed"
    document.querySelectorAll('.status-dropdown').forEach(select => {
        select.addEventListener('change', function () {
            const taskId = this.dataset.taskId;
            const newStatus = this.value;

            fetch(`/task/${taskId}/updateStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: newStatus })
            }).then(response => response.json()).then(data => {
                if (data.success && newStatus === "completed") {
                    document.getElementById(`taskRow-${taskId}`).remove();
                }
            }).catch(error => console.error('Error updating status:', error));
        });
    });
});
