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
  <title>Service Kendaraan - Bengkel A3R Team</title>
  <link rel="stylesheet" href="../css/customer-style.css">
  <link rel="stylesheet" href="../css/admin-style.css">
  <link rel="stylesheet" href="../css/customer-service-style.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <!-- Include header and navbar -->
  <?php include 'admin_header.php'; ?>

  <div class="table-container">
    <h2>Service Kendaraan</h2>

    <div class="search-bar">
      <label for="search">Cari :</label>
      <input type="text" id="search" placeholder="Search...">

      <!-- Add Date Filter -->
      <label for="date-filter">Tanggal :</label>
      <input type="date" id="date-filter">
    </div>

    <table id="sparepartTable">
      <thead>
        <tr>
          <th>Id_Service</th>
          <th>No_Kendaraan</th>
          <th>Id_User</th>
          <th>No_Antrian</th>
          <th>Nama_Pelanggan</th>
          <th>Keterangan</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Aksi</th>

        </tr>
      </thead>
      <tbody>
        <?php
        include 'database_connection.php'; // Include your DB connection



        $query = "SELECT id_service, no_kendaraan, id_user, no_antrian, nama_pelanggan, keterangan, tanggal, status
                 FROM kendaraan ";
        $result = mysqli_query($conn, $query);
        function getStatusClass($status)
        {
          switch ($status) {
            case 0:
              return 'status-waiting'; // Menunggu
            case 1:
              return 'status-repairing'; // Sedang Diperbaiki
            case 2:
              return 'status-completed'; // Selesai
            default:
              return ''; // Fallback if an unexpected status is found
          }
        }

        function getStatusText($status)
        {
          switch ($status) {
            case 0:
              return 'Menunggu';
            case 1:
              return 'Sedang Diperbaiki';
            case 2:
              return 'Selesai';
            default:
              return 'Tidak Ada'; // Fallback if an unexpected status is found
          }
        }
        if (mysqli_num_rows($result) > 0){
          while ($row = mysqli_fetch_assoc($result)) {
            $statusClass = getStatusClass($row['status']);
            $statusText = getStatusText($row['status']);
            echo "<tr>";
            echo "<td>{$row['id_service']}</td>";
            echo "<td>{$row['no_kendaraan']}</td>";
            echo "<td>{$row['id_user']}</td>";
            echo "<td>{$row['no_antrian']}</td>";
            echo "<td>{$row['nama_pelanggan']}</td>";
            echo "<td>{$row['keterangan']}</td>";
            echo "<td>{$row['tanggal']}</td>";
            echo "<td class='{$statusClass}'>{$statusText}</td>";
  
  
  
            echo "<td>
                      <a href='edit_kendaraan.php?id_service={$row['id_service']}' class='edit-button'>Edit</a>
                      <form action='delete_kendaraan.php' method='POST' style='display:inline;'>
                          <input type='hidden' name='id_service' value='{$row['id_service']}'>
                          <button type='submit' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</button>
                      </form>
                    </td>";
            echo "</tr>";
          }
        } else {
          echo "<tr>";
          echo "<td colspan='9' style='text-align: center;'> No data available</td>";
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
  <script>
    document.getElementById('search').addEventListener('input', filterTable);
    document.getElementById('date-filter').addEventListener('input', filterTable);

    function filterTable() {
      // Get the search input value and the date filter value
      const searchValue = document.getElementById('search').value.toLowerCase();
      const dateFilterValue = document.getElementById('date-filter').value;

      // Get all rows from the table
      const rows = document.querySelectorAll('#sparepartTable tbody tr');

      // Loop through the rows and filter based on the search and date inputs
      rows.forEach(function(row) {
        // Get the values for the Nama_Pelanggan (second column) and Tanggal (seventh column)
        const userName = row.querySelectorAll('td')[4].textContent.toLowerCase(); // Nama_Pelanggan
        const dateValue = row.querySelectorAll('td')[6].textContent; // Tanggal

        // Convert date value to ISO format for easy comparison
        const formattedDate = new Date(dateValue).toISOString().slice(0, 10);

        // Show row if search matches and date filter is either empty or matches the row date
        const searchMatch = userName.includes(searchValue);
        const dateMatch = !dateFilterValue || formattedDate === dateFilterValue;

        if (searchMatch && dateMatch) {
          row.style.display = ''; // Show the row
        } else {
          row.style.display = 'none'; // Hide the row
        }
      });
    }
  </script>



</body>

</html>