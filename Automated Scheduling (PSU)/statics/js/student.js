document.addEventListener('DOMContentLoaded', function () {
    // Get all search input fields
    const searchInputs = document.querySelectorAll('.input-group input');

    // Function to handle table search
    function handleSearch(input, table) {
        const tableRows = table.querySelectorAll('tbody tr'); // Get rows of the specific table
        
        input.addEventListener('input', function () {
            const query = input.value.toLowerCase(); // Get the search query and convert it to lowercase
            tableRows.forEach(function (row) {
                const rowText = row.textContent.toLowerCase(); // Get text content of each row
                if (rowText.includes(query)) {
                    row.style.display = ''; // Show row if it matches the search query
                } else {
                    row.style.display = 'none'; // Hide row if it doesn't match the search query
                }
            });
        });
    }

    // Initialize search functionality for each input field and corresponding table
    const tables = document.querySelectorAll('.table__body table'); // All tables in the page
    tables.forEach(function(table, index) {
        const searchInput = searchInputs[index]; // Get the corresponding search input for each table
        if (searchInput) {
            handleSearch(searchInput, table);
        }
    });

    // Function to export tables to PDF
    function exportTableToPDF(tableId, filename) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Convert HTML table to PDF with autoTable
        doc.text(filename, 10, 10); // Add title to PDF
        doc.autoTable({ html: tableId, startY: 20 }); // Convert table to PDF with autoTable
        doc.save(filename + ".pdf");
    }

    // Function to export tables to Excel
    function exportTableToExcel(tableId, filename) {
        const table = document.querySelector(tableId);
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
        XLSX.writeFile(workbook, filename + ".xlsx");
    }

    // Attach event listeners to buttons for 'curriculums' section
    document.getElementById('toPDF-curriculums').addEventListener('click', function() {
        exportTableToPDF('#curriculums table', 'Curriculums'); // Export the curriculum table to PDF
    });

    document.getElementById('toEXCEL-curriculums').addEventListener('click', function() {
        exportTableToExcel('#curriculums table', 'Curriculums'); // Export the curriculum table to Excel
    });

});
