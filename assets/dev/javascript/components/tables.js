function addLinkToTableRow() {
    const tableInspectRows = document.querySelectorAll('.wps-table-inspect tbody tr');
    tableInspectRows.forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (!event.target.closest('.view-more')) {
                const link = row.querySelector('td:first-child a');
                if (link) {
                    if (link.target === '_blank') {
                        window.open(link.href, '_blank');
                    } else {
                        window.location.href = link.href;
                    }
                }
            }
        });
    });
}

// Check for the presence of the elements
const unlockColumnExists = document.querySelector('.wps-admin-column__unlock');
const tbodyElement = document.querySelector('.wp-list-table');
 // Add class if mini chart addon is deactivated
if (unlockColumnExists && tbodyElement) {
    tbodyElement.classList.add('wps-admin-mini-chart__unlock');
}

addLinkToTableRow();
