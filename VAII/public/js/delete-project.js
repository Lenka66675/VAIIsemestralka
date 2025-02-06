document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("click", async function (e) {
        if (!e.target.classList.contains("deleteProjectButton")) return; // 🎯 DELETE button only

        e.preventDefault();

        const projectId = e.target.dataset.id;
        const url = e.target.dataset.url;
        const deleteButton = e.target;

        if (!projectId || !url) {
            console.error("❌ Project ID or URL is missing.");
            return;
        }

        // ✅ Namiesto confirm() zobrazíme vlastný popup
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

                // 🆕 Nahradenie tlačidla Delete správou "✔ Deleted"
                deleteButton.textContent = "✔ Deleted";
                deleteButton.style.backgroundColor = "transparent";
                deleteButton.style.color = "black";
                deleteButton.style.fontWeight = "bold";
                deleteButton.style.cursor = "default";
                deleteButton.style.border = "none";

                // Po 2 sekundách odstránime celý projektový blok
                setTimeout(() => {
                    const projectCard = deleteButton.closest(".project-card");
                    if (projectCard) projectCard.remove();
                }, 2000);

                // ✅ Po úspešnom odstránení presmerujeme späť na stránku projektov
                setTimeout(() => {
                    window.location.href = "/projects"; // 🏠 URL môže byť iná podľa tvojho routingu
                }, 500); // ⏳ Po 0.5 sekunde

            } catch (error) {
                console.error("❌ Unexpected Error:", error);
            }
        });
    });
});

/* ✅ Funkcia na zobrazenie potvrdenia DELETE */
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

/* ✅ Funkcia na zobrazenie info popupu */
function showPopup(message) {
    const existingPopup = document.querySelector(".custom-popup");
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement("div");
    popup.classList.add("custom-popup");
    popup.innerHTML = `<p>${message}</p> <button onclick="this.parentElement.remove()">OK</button>`;
    document.body.appendChild(popup);
}
