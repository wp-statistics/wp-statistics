// Check for the presence of the elements
const unlockColumnExists = document.querySelector('.wps-admin-column__unlock');
const tbodyElement = document.querySelector('.wp-list-table');
 // Add class if mini chart addon is deactivated
if (unlockColumnExists && tbodyElement) {
    tbodyElement.classList.add('wps-admin-mini-chart__unlock');
}