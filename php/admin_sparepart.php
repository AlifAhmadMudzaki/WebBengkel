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
  <title>Kendaraan - Bengkel A3R Team</title>
  <link rel="stylesheet" href="../css/customer-style.css">
  <link rel="stylesheet" href="../css/customer-service-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <!-- Include header and navbar -->
  <?php include 'admin_header.php'; ?>

  <div class="table-container">
    <h2>SPAREPART</h2>

    <div class="search-bar">
      <label for="search">Cari :</label>
      <input type="text" id="search" placeholder="Search...">
    </div>

    <div class="button-container-left">
      <form action="add_sparepart.php" method="GET">
        <button type="submit" class="input-data-button">
          <i class="fas fa-pencil-alt"></i>Input Data</button>
      </form>
    </div>

    <table id="sparepartTable">
      <thead>
        <tr>
          <th>Id_Sparepart</th>
          <th>Nama_Sparepart</th>
          <th>Harga_Sparepart</th>
          <th>Status</th>
          <th>id_supplier</th>
          <th>Aksi</th>


        </tr>
      </thead>
      <tbody>
        <?php
        include 'database_connection.php'; // Include your DB connection



        $query = "SELECT id_sparepart, nama_sparepart, harga_sparepart, id_supplier, 
                status FROM sparepart ";
        $result = mysqli_query($conn, $query);

        function getStatusClass($status)
        {
          switch ($status) {
            case 0:
              return 'status-empty'; // Kosong
            case 1:
              return 'status-available'; // Tersedia

            default:
              return ''; // Fallback if an unexpected status is found
          }
        }

        function getStatusText($status)
        {
          switch ($status) {
            case 0:
              return 'Kosong';
            case 1:
              return 'Tersedia';
            default:
              return 'Unknown'; // Fallback if an unexpected status is found
          }
        }

        while ($row = mysqli_fetch_assoc($result)) {
          $statusClass = getStatusClass($row['status']); // Should return a string
          $statusText = getStatusText($row['status']); // Should return a string
          $formattedHarga = 'Rp ' . number_format($row['harga_sparepart'], 0, ',', '.');
          echo "<tr>";
          echo "<td>{$row['id_sparepart']}</td>";
          echo "<td>{$row['nama_sparepart']}</td>";
          echo "<td>{$formattedHarga}</td>";
          echo "<td class='{$statusClass}'>{$statusText}</td>";
          echo "<td>{$row['id_supplier']}</td>";
          echo "<td>
                    <a href='edit_sparepart.php?id_sparepart={$row['id_sparepart']}' class='edit-button'>Edit</a>
                    <form action='delete_sparepart.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='id_sparepart' value='{$row['id_sparepart']}'>
                        <button type='submit' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</button>
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
      const rows = document.querySelectorAll('#sparepartTable tbody tr');

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