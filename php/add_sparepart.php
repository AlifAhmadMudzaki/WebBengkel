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

include 'database_connection.php'; // Include DB connection

// Define variables and initialize to empty values
$id_sparepart = $nama_sparepart = $harga_sparepart = $status = $id_supplier = "";
$errors = array();

// Fetch the next auto-incremented id_sparepart from the database
$query_supplier = "SELECT MAX(id_sparepart) AS last_id FROM sparepart";
$result_sparepart = mysqli_query($conn, $query_supplier);
$row_sparepart = mysqli_fetch_assoc($result_sparepart);
$id_sparepart = $row_sparepart['last_id'] ? $row_sparepart['last_id'] + 1 : 1; // Default to 1 if no records found

// Fetch suppliers from the database for the supplier dropdown
$query_supplier = "SELECT id_supplier, nama_supplier FROM supplier";
$result_supplier = mysqli_query($conn, $query_supplier);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize inputs
    $nama_sparepart = mysqli_real_escape_string($conn, $_POST['nama_sparepart']);
    $harga_sparepart = mysqli_real_escape_string($conn, $_POST['harga_sparepart']);
    $harga_sparepart = str_replace('.', '', $harga_sparepart); // Remove thousand separators
    $harga_sparepart = str_replace(',', '.', $harga_sparepart); // Convert comma to period for decimal if necessary
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $id_supplier = mysqli_real_escape_string($conn, $_POST['id_supplier']);

    // Insert the spare part into the 'sparepart' table
    $query_insert = "INSERT INTO sparepart (id_sparepart, nama_sparepart, harga_sparepart, status, id_supplier)
                     VALUES ('$id_sparepart', '$nama_sparepart', '$harga_sparepart', '$status', '$id_supplier')";

    if (mysqli_query($conn, $query_insert)) {
        $_SESSION['show_success_modal'] = "Sparepart berhasil ditambahkan.";
        header('Location: add_sparepart.php'); // Redirect to clear form
        exit(); // Ensure script stops after the redirect
    } else {
        $errors['general'] = "Error: Could not process your request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sparepart - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="input-container">
        <h2>Add Sparepart</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <!-- ID Sparepart (Read-only) -->
            <div class="form-group">
                <label for="id_sparepart">ID Sparepart:</label>
                <input type="text" id="id_sparepart" name="id_sparepart" value="<?php echo $id_sparepart; ?>" readonly>
            </div>

            <!-- Nama Sparepart -->
            <div class="form-group">
                <label for="nama_sparepart">Nama Sparepart:</label>
                <input type="text" id="nama_sparepart" name="nama_sparepart" required>
            </div>

            <!-- Harga Sparepart -->
            <div class="form-group">
                <label for="harga_sparepart">Harga Sparepart:</label>
                <input type="text" id="harga_sparepart" name="harga_sparepart" required oninput="formatCurrency(this)">
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="1">Tersedia</option>
                    <option value="0">Kosong</option>
                </select>
            </div>

            <!-- ID Supplier (Dropdown from database) -->
            <div class="form-group">
                <label for="id_supplier">Supplier:</label>
                <select id="id_supplier" name="id_supplier" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php while ($row_supplier = mysqli_fetch_assoc($result_supplier)) { ?>
                        <option value="<?php echo $row_supplier['id_supplier']; ?>">
                            <?php echo $row_supplier['id_supplier'] . ' - ' . $row_supplier['nama_supplier']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Submit</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_sparepart.php'">Kembali</button>

            <!-- General error message -->
            <?php if (isset($errors['general'])): ?>
                <p class="error"><?php echo $errors['general']; ?></p>
            <?php endif; ?>
    </div>

    </form>
    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3 id="modalMessage"></h3>
            <button id="closeModal">OK</button>
        </div>
    </div>

    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/[^,\d]/g, '');
            const parts = value.split(',');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = parts.join(',');
        }
        // Show success alert if session is set

        // Show custom modal if session is set
        <?php if (isset($_SESSION['show_success_modal'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('successModal');
                const modalMessage = document.getElementById('modalMessage');
                modalMessage.textContent = "<?php echo $_SESSION['show_success_modal']; ?>";
                modal.style.display = 'flex'; // Show the modal

                const closeModal = document.getElementById('closeModal');
                closeModal.onclick = function() {
                    modal.style.display = 'none'; // Close modal
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>