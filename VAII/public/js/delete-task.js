document.querySelectorAll('.deleteTaskButton').forEach(button => {
    button.addEventListener('click', async function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this task?')) return;

        const url = this.dataset.url;

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json' // Dôležité pre JSON odpoveď
                }
            });

            if (!response.ok) {
                // Spracovanie HTTP chyby
                const error = await response.json();
                console.error('Error:', error);
                alert(`Failed to delete task: ${error.message || 'Unknown error'}`);
                return;
            }

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                const taskRow = document.getElementById(`taskRow-${result.id}`);
                if (taskRow) taskRow.remove();
            } else {
                alert('Failed to delete the task.');
            }
        } catch (error) {
            console.error('Unexpected Error:', error);
            alert('An unexpected error occurred.');
        }
    });
});

