document.addEventListener('DOMContentLoaded', function () {
    console.log("JavaScript Loaded ✅");

    const isAdmin = document.body.dataset.role === 'admin';

    document.querySelectorAll('.status-dropdown').forEach(select => {
        select.addEventListener('change', function () {
            const taskId = this.getAttribute('data-task-id');
            const status = this.value;
            const statusMessage = this.nextElementSibling; // ✔ Updated message
            const taskRow = document.getElementById(`taskRow-${taskId}`);

            console.log(`Updating status for Task ID: ${taskId} to ${status}`);

            fetch(`/task/${taskId}/updateStatus`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
                .then(response => response.json())
                .then(data => {
                    console.log("Server Response:", data);

                    if (data.success) {
                        statusMessage.style.display = 'inline'; // ✔ Updated
                        setTimeout(() => statusMessage.style.display = 'none', 2000);

                        if (!isAdmin && status === "completed") {
                            console.log(`Task ID ${taskId} marked as completed. Removing from list.`);
                            taskRow.remove();
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
