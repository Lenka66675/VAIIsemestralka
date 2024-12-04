
    document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault(); // Zastav odosielanie formulára

    const name = document.querySelector('input[name="name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const date = document.querySelector('input[name="date"]').value.trim();
    const description = document.querySelector('input[name="description"]').value.trim();

    let errors = [];

    if (!name) errors.push('Name is required.');
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) errors.push('Valid email is required.');
    if (!date) errors.push('Date is required.');

    if (errors.length > 0) {
    alert(errors.join('\n')); // Zobraz chyby
    return;
}

    // Ak sú údaje validné, môžeš formulár odoslať:
    this.submit();
});
