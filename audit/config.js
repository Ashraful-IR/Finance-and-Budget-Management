// Toggle sidebar
function toggleMenu() {
  document.querySelector('.menu').classList.toggle('active');
}

// Show section
function showSection(sectionId) {
  document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
  document.getElementById(sectionId).classList.add('active');
}

// Hold suspicious transaction
function holdTransaction(btn) {
  const row = btn.closest('tr');
  row.style.backgroundColor = 'rgba(254, 17, 17, 0.7)';
  row.cells[8].textContent = 'Held';
  updateSummary('transactionTableDashboard', 'totalIncomeDashboard', 'totalExpenseDashboard', 'totalSavingsDashboard', 'totalOfficialDashboard');
}

// Filter by category
function filterByCategory(section) {
  const filterValue = document.getElementById(`categoryFilter${section}`).value;
  const table = document.getElementById(`transactionTable${section}`);
  const rows = table.querySelectorAll('tbody tr');

  rows.forEach(row => {
    const designation = row.cells[5]?.textContent.trim(); // Make sure 6 is correct
    const shouldShow = !filterValue || designation === filterValue;
    row.style.display = shouldShow ? '' : 'none';
  });

  // updateSummary(...); // optional
}




// Filter by status
function filterByStatus(section) {
  const filter = document.getElementById(`statusFilter${section}`).value;
  const rows = document.querySelectorAll(`#transactionTable${section} tbody tr`);
  rows.forEach(row => {
    row.style.display = (filter === '' || row.cells[8].textContent === filter) ? '' : 'none';
  });
  updateSummary(`transactionTable${section}`, `totalIncome${section}`, `totalExpense${section}`, `totalSavings${section}`, `totalOfficial${section}`);
}

// Filter by date
function filterByDate(section) {
  const from = document.getElementById(`dateFrom${section}`).value;
  const to = document.getElementById(`dateTo${section}`).value;
  const rows = document.querySelectorAll(`#transactionTable${section} tbody tr`);
  rows.forEach(row => {
    const date = new Date(row.cells[1].textContent);
    const show = (!from || date >= new Date(from)) && (!to || date <= new Date(to));
    row.style.display = show ? '' : 'none';
  });
  updateSummary(`transactionTable${section}`, `totalIncome${section}`, `totalExpense${section}`, `totalSavings${section}`, `totalOfficial${section}`);
}

// Update summary totals
function updateSummary(tableId, incomeId, expenseId, savingsId, officialId) {
  const rows = document.querySelectorAll(`#${tableId} tbody tr`);
  let income = 0, expense = 0, savings = 0, official = 0;

  rows.forEach(row => {
    if (row.style.display !== 'none') {
      const amount = parseFloat(row.cells[7].textContent);
      const category = row.cells[6].textContent;
      if (category === 'Income') income += amount;
      if (category === 'Expense') expense += amount;
      if (category === 'Savings') savings += amount;
      if (category === 'Official') official += amount;
    }
  });

  document.getElementById(incomeId).textContent = income;
  document.getElementById(expenseId).textContent = expense;
  document.getElementById(savingsId).textContent = savings;
  document.getElementById(officialId).textContent = official;
}
function filterByID(section) {
  const filter = document.getElementById(`idFilter${section}`).value.trim();
  const rows = document.querySelectorAll(`#transactionTable${section} tbody tr`);
  rows.forEach(row => {
    row.style.display = (filter === '' || row.cells[0].textContent === filter) ? '' : 'none';
  });
  updateSummary(`transactionTable${section}`, `totalIncome${section}`, `totalExpense${section}`, `totalSavings${section}`, `totalOfficial${section}`);
}



// Download CSV
function downloadExcel(tableId) {
  const table = document.getElementById(tableId);
  let csv = [];

  for (let row of table.rows) {
    let rowData = [];
    for (let i = 0; i < row.cells.length; i++) {
      if (i === 9) continue; // skip column index 4 (Last Name)
      rowData.push(row.cells[i].innerText);
    }
    csv.push(rowData.join(","));
  }

  const blob = new Blob([csv.join("\n")], { type: "text/csv" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "audit_report.csv";
  link.click();
}


// Initial summary
updateSummary('transactionTableDashboard', 'totalIncomeDashboard', 'totalExpenseDashboard', 'totalSavingsDashboard', 'totalOfficialDashboard');
