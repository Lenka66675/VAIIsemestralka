document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#taskForm'); // Použi správny selektor
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const nameInput = document.querySelector('input[name="name"]');
            const emailInput = document.querySelector('input[name="email"]');
            const dateInput = document.querySelector('input[name="date"]');
            const descriptionInput = document.querySelector('textarea[name="description"]');

            if (!nameInput || !emailInput || !dateInput || !descriptionInput) {
                console.error('One or more input fields not found.');
                return;
            }

            const name = nameInput.value.trim();
            const email = emailInput.value.trim();
            const date = dateInput.value.trim();
            const description = descriptionInput.value.trim();

            [nameInput, emailInput, dateInput, descriptionInput].forEach(input => {
                input.classList.remove('error');
            });

            let errors = [];

            if (!name) {
                errors.push('Name is required.');
                nameInput.classList.add('error');
            }

            if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
                errors.push('Valid email is required.');
                emailInput.classList.add('error');
            }

            if (!date) {
                errors.push('Date is required.');
                dateInput.classList.add('error');
            }

            if (!description) {
                errors.push('Description is required.');
                descriptionInput.classList.add('error');
            }

            if (errors.length > 0) {
                alert(errors.join('\n'));
                return;
            }

            this.submit();
        });
    } else {
        console.error('Form not found on the page.');
    }
});
