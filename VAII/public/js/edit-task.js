document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.editTaskButton').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const row = document.querySelector(`#taskRow-${id}`);
            const cells = row.querySelectorAll('.taskName, .taskEmail, .taskDate, .taskDescription');

            // Aktivovať úpravy pre všetky bunky v riadku
            cells.forEach(cell => {
                cell.setAttribute('contenteditable', 'true');
                cell.classList.add('editing');

                // Uloženie zmeny pri strate fokusu
                cell.addEventListener('blur', async function () {
                    await updateCell(this);
                });

                // Uloženie zmeny pri stlačení Enter
                cell.addEventListener('keypress', async function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        await updateCell(this);
                        this.blur(); // Odstráni fokus
                    }
                });
            });

            // Zmeniť text tlačidla na "Editing..." počas úpravy
            this.textContent = 'Editing...';
            this.disabled = true; // Zabraňuje opätovnému kliknutiu na tlačidlo
        });
    });

    async function updateCell(cell) {
        const id = cell.dataset.id;
        const name = cell.dataset.name;
        const value = cell.textContent.trim();
        const url = cell.dataset.url;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    [name]: value,
                    _method: 'PUT' // Laravel vyžaduje PUT metódu
                })
            });

            const result = await response.json();

            if (result.success) {
                alert('Task updated successfully!');

                // Deaktivovať úpravy po úspešnom odoslaní
                cell.removeAttribute('contenteditable');
                cell.classList.remove('editing');

                // Vrátiť tlačidlo do pôvodného stavu
                const editButton = document.querySelector(`.editTaskButton[data-id="${id}"]`);
                editButton.textContent = 'Edit';
                editButton.disabled = false;
            } else {
                alert('Failed to update the task: ' + (result.message || 'Unknown error.'));
                console.error(result);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        }
    }
});
