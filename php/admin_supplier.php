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
    <h2>SUPPLIER</h2>

    <div class="search-bar">
      <label for="search">Cari :</label>
      <input type="text" id="search" placeholder="Search...">
    </div>

    <div class="button-container-left">
      <form action="add_supplier.php" method="GET">
        <button type="submit" class="input-data-button">
          <i class="fas fa-pencil-alt"></i>Input Data</button>
      </form>
    </div>

    <table id="sparepartTable">
      <thead>
        <tr>
          <th>Id Supplier</th>
          <th>Nama Supplier</th>
          <th>Alamat Supplier</th>
          <th>Email Supplier</th>
          <th>No Hp Supplier</th>
          <th>Aksi</th>

        </tr>
      </thead>
      <tbody>
        <?php
        include 'database_connection.php'; // Include your DB connection

        $query = "SELECT id_supplier, nama_supplier, alamat_supplier, email_supplier, no_telp FROM supplier ";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>";
          echo "<td>{$row['id_supplier']}</td>";
          echo "<td>{$row['nama_supplier']}</td>";
          echo "<td>{$row['alamat_supplier']}</td>";
          echo "<td>{$row['email_supplier']}</td>";
          echo "<td>{$row['no_telp']}</td>";
          echo "<td>
                    <a href='edit_supplier.php?id_supplier={$row['id_supplier']}' class='edit-button'>Edit</a>
                    <form action='delete_supplier.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='id_supplier' value='{$row['id_supplier']}'>
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