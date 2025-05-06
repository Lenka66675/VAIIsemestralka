document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.create-project-form');

    if (!form) {
        console.error('⚠ Form was not found.');
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const nameInput = document.querySelector('input[name="name"]');
        const imageInput = document.querySelector('input[name="image"]');
        const attachmentsInput = document.querySelector('input[name="attachments[]"]');

        if (!nameInput || !imageInput || !attachmentsInput) {
            console.error('⚠ Some input fields were not found.');
            return;
        }

        const name = nameInput.value.trim();
        const image = imageInput.files.length > 0 ? imageInput.files[0] : null;
        const attachments = attachmentsInput.files;

        document.querySelectorAll('.error-message').forEach(error => error.remove());
        [nameInput, imageInput, attachmentsInput].forEach(input => {
            input.classList.remove('input-error');
        });

        let errors = [];

        if (!name) {
            errors.push({ input: nameInput, message: '<span class="exclamation">❗</span> Project name is required.' });
        } else if (name.length < 3 || name.length > 255) {
            errors.push({ input: nameInput, message: '<span class="exclamation">❗</span> Name must be between 3 and 255 characters.' });
        }

        if (image) {
            const validImageTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!validImageTypes.includes(image.type)) {
                errors.push({ input: imageInput, message: '<span class="exclamation">❗</span> Only JPEG, PNG, JPG, and GIF images are allowed.' });
            }
            if (image.size > 5 * 1024 * 1024) {
                errors.push({ input: imageInput, message: '<span class="exclamation">❗</span> Image size cannot exceed 5MB.' });
            }
        }

        if (attachments.length > 0) {
            for (const file of attachments) {
                if (file.size > 10 * 1024 * 1024) {
                    errors.push({ input: attachmentsInput, message: '<span class="exclamation">❗</span> Each attachment cannot exceed 10MB.' });
                    break;
                }
            }
        }

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

        this.submit();
    });
});
