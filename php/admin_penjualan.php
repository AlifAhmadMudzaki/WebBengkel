<?php
session_start(); // Always start session at the top of the page

// Prevent back button after logout by disabling cache
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

// Check if session is valid
if (!isset($_SESSION['id_user'])) {
  header('Location: ../index.php'); // Redirect to login page
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penjualan - Bengkel A3R Team</title>
  <link rel="stylesheet" href="../css/customer-style.css">
  <link rel="stylesheet" href="../css/customer-service-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <!-- Include header and navbar -->
  <?php include 'admin_header.php'; ?>

  <div class="table-container">
    <h2>PENJUALAN (Bulanan)</h2>

    <div class="search-bar">
      <label for="search">Cari :</label>
      <input type="text" id="search" placeholder="Search...">
    </div>

    <div class="filter-bar">
      <form method="GET" action="">
        <label for="month">Bulan:</label>
        <select name="month" id="month">
          <?php
          // Array of months in Indonesian
          $monthsIndo = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
          ];

          // Get current month or selected month
          $currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');

          // Loop through the Indonesian month array and generate <option> tags
          foreach ($monthsIndo as $monthValue => $monthName) {
            $selected = ($currentMonth == $monthValue) ? 'selected' : ''; // Default to current month
            echo "<option value='{$monthValue}' {$selected}>{$monthName}</option>";
          }
          ?>
        </select>

        <label for="year">Tahun:</label>
        <select name="year" id="year">
          <?php
          $currentYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
          for ($y = date('Y') - 5; $y <= date('Y'); $y++) {
            $selectedYear = ($currentYear == $y) ? 'selected' : '';
            echo "<option value='{$y}' {$selectedYear}>{$y}</option>";
          }
          ?>
        </select>

        <button type="submit" class="filter-button">Filter</button>
      </form>
    </div>

    <div class="button-container-left">
      <form action="add_penjualan.php" method="GET">
        <button type="submit" class="input-data-button">
          <i class="fas fa-pencil-alt"></i>Input Data</button>
      </form>
    </div>

    <table id="penjualanTable">
      <thead>
        <tr>
          <th>Id_Penjualan</th>
          <th>Nama_Sparepart</th>
          <th>Jumlah_Sparepart</th>
          <th>Harga</th>
          <th>Total</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        include 'database_connection.php'; // Include your DB connection

        // Get selected month and year from form, default to current month/year
        $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
        $selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

        // Query to get penjualan for the selected month
        $query = "SELECT id_penjualan, nama_sparepart, jumlah_sparepart, harga, tanggal,
                  (jumlah_sparepart * harga) AS total
                  FROM penjualan 
                  WHERE MONTH(tanggal) = '$selectedMonth' AND YEAR(tanggal) = '$selectedYear'";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
          $formattedHarga = 'Rp ' . number_format($row['harga'], 0, ',', '.');
          $formattedTotal = 'Rp ' . number_format($row['total'], 0, ',', '.');
          $formattedDate = date('d-m-Y', strtotime($row['tanggal']));

          echo "<tr>";
          echo "<td>{$row['id_penjualan']}</td>";
          echo "<td>{$row['nama_sparepart']}</td>";
          echo "<td>{$row['jumlah_sparepart']}</td>";
          echo "<td>{$formattedHarga}</td>";
          echo "<td>{$formattedTotal}</td>";
          echo "<td>{$formattedDate}</td>";
          echo "<td>
                    <a href='edit_penjualan.php?id_penjualan={$row['id_penjualan']}' class='edit-button'>Edit</a>
                    <form action='delete_penjualan.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='id_penjualan' value='{$row['id_penjualan']}'>
                        <button type='submit' class='delete-button' onclick='return confirm(\"Apakah Anda yakin ingin menghapus item ini?\")'>Delete</button>
                    </form>
                  </td>";
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <script>
    // Function to filter table rows based on search input
    document.getElementById('search').addEventListener('input', function() {
      // Get the search input value
      const searchValue = this.value.toLowerCase();

      // Get all rows from the table
      const rows = document.querySelectorAll('#penjualanTable tbody tr');

      // Loop through the rows and filter based on the input
      rows.forEach(function(row) {
        // Get the second column (Nama_Sparepart)
        const sparepartName = row.querySelectorAll('td')[1].textContent.toLowerCase();

        // Check if the spare part name contains the search term
        if (sparepartName.includes(searchValue)) {
          row.style.display = ''; // Show the row
        } else {
          row.style.display = 'none'; // Hide the row
        }
      });
    });
  </script>
</body>

</html>