document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault();


    const nameInput = document.querySelector('input[name="name"]');
    const emailInput = document.querySelector('input[name="email"]');
    const dateInput = document.querySelector('input[name="date"]');
    const descriptionInput = document.querySelector('input[name="description"]');


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
