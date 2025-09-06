function toggleMenu() {
  const menu = document.querySelector('.menu');
  menu.classList.toggle('active');
}

function showSection(sectionId) {
  document.querySelectorAll('.content-section').forEach(sec => {
    sec.classList.remove('active');
  });
  document.getElementById(sectionId).classList.add('active');
}

// Hold suspicious transaction
function holdTransaction(btn) {
  const row = btn.closest('tr');
  row.style.backgroundColor = '#fdd';
  row.cells[8].textContent = 'Held'; // Update status column
}

// Filter by category
function filterByCategory() {
  const filter = document.getElementById('categoryFilter').value;
  const rows = document.querySelectorAll('#transactionTable tbody tr');
  rows.forEach(row => {
    const category = row.cells[6].textContent;
    row.style.display = (filter === '' || category === filter) ? '' : 'none';
  });
  updateSummary();
}

// Filter by date
function filterByDate() {
  const from = new Date(document.getElementById('dateFrom').value);
  const to = new Date(document.getElementById('dateTo').value);
  const rows = document.querySelectorAll('#transactionTable tbody tr');
  rows.forEach(row => {
    const date = new Date(row.cells[1].textContent);
    row.style.display = (!from && !to) || (date >= from && date <= to) ? '' : 'none';
  });
  updateSummary();
}

// Update summary totals
function updateSummary() {
  const rows = document.querySelectorAll('#transactionTable tbody tr');
  let income = 0, expense = 0;
  rows.forEach(row => {
    if (row.style.display !== 'none') {
      const amount = parseFloat(row.cells[7].textContent);
      const category = row.cells[6].textContent;
      if (category === 'Income') income += amount;
      if (category === 'Expense') expense += amount;
    }
  });
  document.getElementById('totalIncome').textContent = income;
  document.getElementById('totalExpense').textContent = expense;
}

// Initial summary
updateSummary();

// Download CSV/Excel
function downloadExcel() {
  const table = document.getElementById('transactionTable');
  let csv = [];
  for (let row of table.rows) {
    let rowData = [];
    for (let cell of row.cells) rowData.push(cell.innerText);
    csv.push(rowData.join(","));
  }
  const blob = new Blob([csv.join("\n")], { type: "text/csv" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "audit_report.csv";
  link.click();
}
