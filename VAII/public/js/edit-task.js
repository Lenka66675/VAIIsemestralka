document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.editTaskButton').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const row = document.querySelector(`#taskRow-${id}`);
            const cells = row.querySelectorAll('.taskName, .taskEmail, .taskDate, .taskDescription');


            row.classList.add('editing-row');

            cells.forEach(cell => {
                cell.setAttribute('contenteditable', 'true');
                cell.classList.add('editing');

                if (!cell.dataset.listener) {
                    cell.addEventListener('blur', handleBlur);
                    cell.addEventListener('keypress', handleKeyPress);
                    cell.dataset.listener = 'true';
                }
            });

            this.textContent = 'Editing';
            this.disabled = true;
        });
    });

    let isUpdating = false;

    async function handleBlur(event) {
        if (!isUpdating) {
            await updateCell(event.target);
        }
    }

    async function handleKeyPress(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (!isUpdating) {
                await updateCell(event.target);
            }
            event.target.blur();
        }
    }

    async function updateCell(cell) {
        const id = cell.dataset.id;
        const name = cell.dataset.name;
        const value = cell.textContent.trim();
        const url = cell.dataset.url;

        isUpdating = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    [name]: value,
                    _method: 'PUT'
                })
            });

            const result = await response.json();

            if (result.success) {
                alert('Task updated successfully!');

                const row = cell.closest('tr');
                const editButton = document.querySelector(`.editTaskButton[data-id="${id}"]`);

                row.querySelectorAll('.editing').forEach(editCell => {
                    editCell.removeAttribute('contenteditable');
                    editCell.classList.remove('editing');
                });
                row.classList.remove('editing-row');

                editButton.textContent = 'Edit';
                editButton.disabled = false;
            } else {
                alert('Failed to update the task: ' + (result.message || 'Unknown error.'));
                console.error(result);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        } finally {
            isUpdating = false;
        }
    }
});
