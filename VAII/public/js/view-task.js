document.addEventListener('DOMContentLoaded', function () {
    console.log("🟢 Task Modal JS Loaded ✅");

    const modal = document.getElementById('taskModal');
    const closeButton = document.querySelector('.close');
    const taskDescription = document.getElementById('taskDescription');
    const solutionContainer = document.getElementById('existingSolution');
    const attachmentContainer = document.getElementById('existingAttachment');
    const isAdmin = document.body.dataset.role === 'admin';
    let currentTaskId = null;


    let solutionText, solutionFile, saveSolutionButton;
    if (!isAdmin) {
        solutionText = document.getElementById('solutionText');
        solutionFile = document.getElementById('solutionFile');
        saveSolutionButton = document.querySelector('.saveSolutionButton');
    }

    function openModal(taskId, description) {
        currentTaskId = taskId;
        taskDescription.textContent = description;
        solutionContainer.innerHTML = "";
        attachmentContainer.innerHTML = "";

        console.log(`🟢 Opening modal for Task ID: ${taskId}`);

        if (!isAdmin) {
            document.getElementById('solutionText').value = "";
            document.getElementById('solutionFile').value = "";
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
                                    let cleanFileName = file.split('/').pop().replace(/^\d+_/, '');
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
                            let cleanFileName = file.split('/').pop().replace(/^\d+_/, '');
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




    document.querySelectorAll('.viewTaskButton').forEach(button => {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            const taskId = this.dataset.id;
            const row = document.getElementById(`taskRow-${taskId}`);
            const description = row.querySelector('.editable[data-name="description"]').textContent.trim();

            openModal(taskId, description);
        });
    });

    closeButton.addEventListener('click', function () {
        modal.classList.remove('show');
        modal.style.display = "none";
    });

    if (!isAdmin) {
        saveSolutionButton.addEventListener('click', async function () {
            if (!currentTaskId) return;

            const solution = solutionText.value.trim();
            const files = solutionFile.files;

            solutionText.classList.remove('input-error');
            document.getElementById('solutionFile').classList.remove('input-error');
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            const allowedExtensions = ['pdf', 'docx', 'xlsx', 'csv', 'jpg', 'png'];
            const maxFileSize = 5 * 1024 * 1024; // 5MB
            let hasError = false;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    showError('solutionFile', `Invalid file format: ${file.name}. Allowed: ${allowedExtensions.join(', ')}`);
                    hasError = true;
                }

                if (file.size > maxFileSize) {
                    showError('solutionFile', `File too large: ${file.name}. Max: 5MB.`);
                    hasError = true;
                }
            }

            if (!solution && files.length === 0) {
                showError('solutionText', "Please enter a solution or upload a file.");
                hasError = true;
            }

            if (hasError) return;

            const formData = new FormData();
            formData.append('solution', solution);
            for (let i = 0; i < files.length; i++) {
                formData.append('attachments[]', files[i]);
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
                    showError('solutionText', '❌ Failed to save solution: ' + result.message);
                }
            } catch (error) {
                console.error('Error saving solution:', error);
                showError('solutionText', '❌ An unexpected error occurred.');
            }
        });
        function showError(inputId, message) {
            const input = document.getElementById(inputId);
            input.classList.add('input-error');

            const errorMessage = document.createElement('p');
            errorMessage.classList.add('error-message');
            errorMessage.textContent = message;

            input.parentNode.appendChild(errorMessage);
        }



    }

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
