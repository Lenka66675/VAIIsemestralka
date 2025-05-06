document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("click", async function (e) {
        if (!e.target.classList.contains("deleteProjectButton")) return;

        e.preventDefault();

        const projectId = e.target.dataset.id;
        const url = e.target.dataset.url;
        const deleteButton = e.target;

        if (!projectId || !url) {
            console.error("❌ Project ID or URL is missing.");
            return;
        }

        showDeletePopup("Are you sure you want to delete this project?", async () => {
            console.log("Deleting Project ID:", projectId, "URL:", url);

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
                    console.error("❌ Failed to delete project:", result.message);
                    return;
                }

                console.log("✅ Project deleted successfully!");
                showPopup("✔ Project deleted successfully!");

                deleteButton.textContent = "✔ Deleted";
                deleteButton.style.backgroundColor = "transparent";
                deleteButton.style.color = "black";
                deleteButton.style.fontWeight = "bold";
                deleteButton.style.cursor = "default";
                deleteButton.style.border = "none";

                setTimeout(() => {
                    const projectCard = deleteButton.closest(".project-card");
                    if (projectCard) projectCard.remove();
                }, 2000);

                setTimeout(() => {
                    window.location.href = "/projects";
                }, 500);

            } catch (error) {
                console.error("❌ Unexpected Error:", error);
            }
        });
    });
});


function showDeletePopup(message, onConfirm) {
    const existingPopup = document.querySelector(".custom-popup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.classList.add("custom-popup");
    popup.innerHTML = `
        <p>${message}</p>
        <button class="confirm-delete">Yes, delete</button>
        <button class="cancel-delete">Cancel</button>
    `;
    document.body.appendChild(popup);

    document.querySelector(".confirm-delete").addEventListener("click", () => {
        popup.remove();
        onConfirm();
    });

    document.querySelector(".cancel-delete").addEventListener("click", () => {
        popup.remove();
    });
}


function showPopup(message) {
    const existingPopup = document.querySelector(".custom-popup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.classList.add("custom-popup");
    popup.innerHTML = `<p>${message}</p> <button onclick="this.parentElement.remove()">OK</button>`;
    document.body.appendChild(popup);
}
