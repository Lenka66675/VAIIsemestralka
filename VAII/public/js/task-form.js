document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.create-task-form');

    if (!form) {
        console.error('⚠ Form was not found.');
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const deadlineInput = document.querySelector('input[name="deadline"]');
        const priorityInput = document.querySelector('select[name="priority"]');
        const descriptionInput = document.querySelector('textarea[name="description"]');
        const usersContainer = document.querySelector('.checkbox-list');
        const usersInputs = document.querySelectorAll('input[name="users[]"]:checked');

        if (!deadlineInput || !priorityInput || !descriptionInput || !usersContainer) {
            console.error('⚠ Some input fields were not found.');
            return;
        }

        const deadline = deadlineInput.value.trim();
        const priority = priorityInput.value.trim();
        const description = descriptionInput.value.trim();
        const users = Array.from(usersInputs).map(input => input.value);

        // ✅ Remove previous error messages
        document.querySelectorAll('.error-message').forEach(error => error.remove());
        [deadlineInput, priorityInput, descriptionInput, usersContainer].forEach(input => {
            input.classList.remove('input-error');
        });

        let errors = [];

        // ✅ Deadline validation (must be in the future)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedDate = new Date(deadline);

        if (!deadline) {
            errors.push({ input: deadlineInput, message: '<span class="exclamation">❗</span> Deadline is required.' });
        } else if (selectedDate <= today) {
            errors.push({ input: deadlineInput, message: '<span class="exclamation">❗</span> Deadline must be in the future.' });
        }

        // ✅ Priority validation
        if (!priority) {
            errors.push({ input: priorityInput, message: '<span class="exclamation">❗</span> Priority must be selected.' });
        }

        // ✅ Description validation
        if (!description) {
            errors.push({ input: descriptionInput, message: '<span class="exclamation">❗</span> Description is required.' });
        }

        // ✅ User selection validation
        if (users.length === 0) {
            errors.push({ input: usersContainer, message: '<span class="exclamation">❗</span> You must select at least one user.' });
        }

        // ✅ Display errors if any
        if (errors.length > 0) {
            errors.forEach(error => {
                error.input.classList.add('input-error');

                let errorMsg = document.createElement('p');
                errorMsg.classList.add('error-message');
                errorMsg.innerHTML = error.message;

                error.input.parentElement.appendChild(errorMsg);
            });
            return;
        }

        // ✅ Submit the form if no errors
        this.submit();
    });
});
