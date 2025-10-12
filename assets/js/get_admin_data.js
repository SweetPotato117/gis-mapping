
        
// Search Admin Js

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('adminSearch');
    const searchResults = document.getElementById('searchResults');
    const adminSelect = document.getElementById('admin');
    let searchTimeout;

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Hide results if search is empty
        if (searchTerm.length === 0) {
            searchResults.style.display = 'none';
            return;
        }

        // Set timeout to avoid too many requests
        searchTimeout = setTimeout(() => {
            searchAdmins(searchTerm);
        }, 300);
    });

    searchInput.addEventListener('focus', function() {
        const searchTerm = this.value.trim();
        if (searchTerm.length > 0) {
            searchResults.style.display = 'block';
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });

    function searchAdmins(searchTerm) {
        const url = `../controllers/admin/get_admins.php?search=${encodeURIComponent(searchTerm)}&page=1`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayResults(data.items);
            })
            .catch(error => {
                console.error('Error fetching admins:', error);
                searchResults.innerHTML = '<div class="search-result-item">Error loading results</div>';
                searchResults.style.display = 'block';
            });
    }

    function displayResults(admins) {
        searchResults.innerHTML = '';
        
        if (admins.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item">No admins found</div>';
        } else {
            admins.forEach(admin => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.textContent = admin.text;
                resultItem.setAttribute('data-value', admin.id);
                
                resultItem.addEventListener('click', function() {
                    searchInput.value = admin.text;
                    adminSelect.value = admin.id;
                    searchResults.style.display = 'none';
                });
                
                searchResults.appendChild(resultItem);
            });
        }
        
        searchResults.style.display = 'block';
    }

    // Load initial popular admins
    searchAdmins('');
});

