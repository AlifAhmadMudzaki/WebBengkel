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
$id_penjualan = $id_penjualan = $harga = $status = $id_penjualan = "";
$errors = array();

// Fetch the next auto-incremented id_penjualan from the database
$query_penjualan = "SELECT MAX(id_penjualan) AS last_id FROM penjualan";
$result_penjualan = mysqli_query($conn, $query_penjualan);
$row_penjualan = mysqli_fetch_assoc($result_penjualan);
$id_penjualan = $row_penjualan['last_id'] ? $row_penjualan['last_id'] + 1 : 1; // Default to 1 if no records found

// Fetch penjualans from the database for the penjualan dropdown
$query_penjualan = "SELECT id_penjualan, nama_sparepart FROM penjualan";
$result_penjualan = mysqli_query($conn, $query_penjualan);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize inputs
    $id_penjualan = mysqli_real_escape_string($conn, $_POST['id_penjualan']);
    $nama_sparepart = mysqli_real_escape_string($conn, $_POST['nama_sparepart']);
    $jumlah_sparepart = mysqli_real_escape_string($conn, $_POST['jumlah_sparepart']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $harga = str_replace('.', '', $harga); // Remove thousand separators
    $harga = str_replace(',', '.', $harga); // Convert comma to period for decimal if necessary
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);

    // Insert the spare part into the 'sparepart' table
    $query_insert = "INSERT INTO penjualan (id_penjualan, nama_sparepart, jumlah_sparepart, harga, tanggal)
                     VALUES ('$id_penjualan', '$nama_sparepart', '$jumlah_sparepart', '$harga', '$tanggal')";

    if (mysqli_query($conn, $query_insert)) {
        $_SESSION['show_success_modal'] = "Penjualan berhasil ditambahkan.";
        header('Location: add_penjualan.php'); // Redirect to clear form
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
        <h2>Add Penjualan</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <!-- ID Sparepart (Read-only) -->
            <div class="form-group">
                <label for="id_penjualan">ID Penjualan :</label>
                <input type="text" id="id_penjualan" name="id_penjualan" value="<?php echo $id_penjualan; ?>" readonly>
            </div>

            <!-- Nama Sparepart -->
            <div class="form-group">
                <label for="nama_sparepart">Nama Sparepart:</label>
                <input type="text" id="nama_sparepart" name="nama_sparepart" required>
            </div>

            <div class="form-group">
                <label for="jumlah_sparepart">Jumlah Sparepart:</label>
                <input type="text" id="jumlah_sparepart" name="jumlah_sparepart" required>
            </div>

            <!-- Harga Sparepart -->
            <div class="form-group">
                <label for="harga">Harga:</label>
                <input type="text" id="harga" name="harga" required oninput="formatCurrency(this)">
            </div>

            

            <!-- Status -->
            <div class="form-group">
                <label for="tanggal">Tanggal :</label>
                <input type="date" id="tanggal" name="tanggal" required>

            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Submit</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_penjualan.php'">Kembali</button>

            <!-- General error message -->
            <?php if (isset($errors['general'])): ?>
                <p class="error"><?php echo $errors['general']; ?></p>
            <?php endif; ?>
        </form>
    </div>
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