// Function to submit location deletion to server
window.submitDeleteLocation = async function(placeId) {
    const formData = new FormData();
    formData.append('id', placeId);
    
    console.log('Submitting delete for place ID:', placeId);
    
    const response = await fetch('../controllers/admin/delete_map_data.php', {
        method: 'POST',
        body: formData
    });
    
    const json = await response.json();
    return json;
};

function deleteLocation() {
    const deleteBtn = document.getElementById('deleteLocation');
    const placeId = deleteBtn.getAttribute('data-location-id');
    
    if (!placeId) {
        console.error('No place ID found for deletion');
        return;
    }
    
    if (confirm('Are you sure you want to delete this location?')) {
        submitDeleteLocation(placeId).then(response => {
            if (response.success) {
                alert('Location deleted successfully');
                // Optionally, refresh the map or UI here
                location.reload();
            } else {
                alert('Error deleting location: ' + response.message);
            }
        }).catch(error => {
            console.error('Error during deletion request:', error);
            alert('An unexpected error occurred. Please try again.');
        });
    } else {
        console.log('Deletion cancelled by user');
    }
}

// Attach delete button event listener when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById('deleteLocation');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', deleteLocation);
    }
});
