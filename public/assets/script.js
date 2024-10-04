// Wait for the DOM to fully load
document.addEventListener('DOMContentLoaded', function () {
    // Add event listeners for forms, if necessary
    const folderForm = document.querySelector('form'); // Assuming the form is for creating a new folder

    if (folderForm) {
        folderForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(folderForm);
            const action = folderForm.action;

            // Send AJAX request to create a new folder
            fetch(action, {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (response.ok) {
                    // Refresh the file list or redirect
                    window.location.reload(); // Refresh the page to show new folder
                } else {
                    alert('Failed to create folder.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }

    // Add event listeners for file editing
    const editForm = document.querySelector('form[action*="edit.php"]');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(editForm);
            const action = editForm.action;

            // Send AJAX request to save the file
            fetch(action, {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (response.ok) {
                    // Redirect to file browser after saving
                    window.location.href = 'file_browser.php';
                } else {
                    alert('Failed to save file.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
