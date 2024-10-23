// Example: Confirm before deleting a user or equipment
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-danger');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const confirmDelete = confirm('Are you sure you want to delete this item?');
            if (!confirmDelete) {
                event.preventDefault();
            }
        });
    });
});
