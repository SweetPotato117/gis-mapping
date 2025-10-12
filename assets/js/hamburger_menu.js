// Hamburger Menu Toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const dropdownMenu = document.getElementById('dropdownMenu');

hamburgerBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    hamburgerBtn.classList.toggle('active');
    dropdownMenu.classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!hamburgerBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        hamburgerBtn.classList.remove('active');
        dropdownMenu.classList.remove('show');
    }
});

// Close dropdown when clicking a menu item
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', () => {
        hamburgerBtn.classList.remove('active');
        dropdownMenu.classList.remove('show');
    });
});