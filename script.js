document.addEventListener('DOMContentLoaded', () => {
    const deleteLinks = document.querySelectorAll('.delete-link');
    deleteLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            const confirmed = window.confirm('Voulez-vous vraiment supprimer cet étudiant ?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    const searchInput = document.querySelector('#search-input');
    const rows = document.querySelectorAll('.student-row');

    if (searchInput && rows.length) {
        searchInput.addEventListener('input', () => {
            const value = searchInput.value.toLowerCase().trim();

            rows.forEach((row) => {
                const text = (row.dataset.search || '').toLowerCase();
                row.style.display = (!value || text.includes(value)) ? '' : 'none';
            });
        });
    }
});
