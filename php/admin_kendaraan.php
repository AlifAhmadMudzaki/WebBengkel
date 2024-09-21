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

        

        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>";
          echo "<td>{$row['id_service']}</td>";
          echo "<td>{$row['no_kendaraan']}</td>";
          echo "<td>{$row['id_user']}</td>";
          echo "<td>{$row['no_antrian']}</td>";
          echo "<td>{$row['nama_pelanggan']}</td>";
          echo "<td>{$row['keterangan']}</td>";
          echo "<td>{$row['tanggal']}</td>";
          echo "<td>{$row['status']}</td>";

          echo "<td>
                    <a href='edit_kendaraan.php?id_user={$row['id_user']}' class='edit-button'>Edit</a>
                    <form action='delete_kendaraan.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='id_user' value='{$row['id_user']}'>
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
        // Get the second column (Nama_user)
        const userName = row.querySelectorAll('td')[1].textContent.toLowerCase();

        // Check if the spare part name contains the search term
        if (userName.includes(searchValue)) {
          row.style.display = ''; // Show the row
        } else {
          row.style.display = 'none'; // Hide the row
        }
      });
    });
  </script>



</body>

</html>