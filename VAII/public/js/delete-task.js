document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("click", async function (e) {
        if (!e.target.classList.contains("deleteTaskButton")) return; // 🎯 DELETE button only

        e.preventDefault();

        const taskId = e.target.dataset.id;
        const url = e.target.dataset.url;
        const deleteButton = e.target;

        if (!taskId || !url) {
            console.error("Task ID or URL is missing.");
            return;
        }

        console.log("Deleting Task ID:", taskId, "URL:", url);


        try {
            const response = await fetch(url, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
                    "Accept": "application/json"
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                console.error("Failed to delete task:", result.message);
                return;
            }

            console.log("Task deleted successfully!");

            deleteButton.textContent = "✔ Deleted";
            deleteButton.style.backgroundColor = "transparent";
            deleteButton.style.color = "black";
            deleteButton.style.fontWeight = "bold";
            deleteButton.style.cursor = "default";
            deleteButton.style.border = "none";

            setTimeout(() => {
                const row = document.getElementById(`taskRow-${taskId}`);
                if (row) row.remove();
            }, 2000);

        } catch (error) {
            console.error("Unexpected Error:", error);
        }
    });
});
