document.addEventListener("DOMContentLoaded", function () {
    const priorityFilter = document.getElementById("priorityFilter");
    const taskRows = document.querySelectorAll("tbody tr");

    function filterTasks() {
        const selectedPriority = priorityFilter.value.toLowerCase();

        taskRows.forEach(row => {
            const priorityElement = row.querySelector(".taskPriorityText");
            const priority = priorityElement ? priorityElement.innerText.trim().toLowerCase() : "";

            if (selectedPriority === "all" || priority === selectedPriority) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    priorityFilter.addEventListener("change", filterTasks);
});
