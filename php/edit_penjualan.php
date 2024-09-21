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
$id_penjualan = $nama_sparepart = $jumlah_sparepart = $harga = $tanggal = "";
$errors = array();

// Check if we're editing an existing spare part
$is_editing = isset($_GET['id_penjualan']) && !empty($_GET['id_penjualan']);
if ($is_editing) {
    $id_penjualan = mysqli_real_escape_string($conn, $_GET['id_penjualan']);
    // Fetch existing data
    $query_supplier = "SELECT * FROM penjualan WHERE id_penjualan = '$id_penjualan'";
    $result_penjualan = mysqli_query($conn, $query_supplier);
    if ($result_penjualan && mysqli_num_rows($result_penjualan) > 0) {
        $row_penjualan = mysqli_fetch_assoc($result_penjualan);
        $nama_sparepart = $row_penjualan['nama_sparepart'];
        $harga = $row_penjualan['harga'];
        $tanggal = $row_penjualan['tanggal'];
    } else {
        $errors['general'] = "Error: penjualan not found.";
    }
} else {
    // Redirect to add_penjualan.php if no ID is provided
    header('Location: add_penjualan.php');
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize inputs
    $nama_sparepart = mysqli_real_escape_string($conn, $_POST['nama_sparepart']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $harga = str_replace('.', '', $harga); // Remove thousand separators
    $harga = str_replace(',', '.', $harga); // Convert comma to period for decimal if necessary
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);

    // Update the spare part in the database
    $query_update = "UPDATE penjualan 
                     SET nama_sparepart = '$nama_sparepart', harga = '$harga', tanggal = '$tanggal'
                     WHERE id_penjualan = '$id_penjualan'";
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['show_success_modal'] = "Penjualan berhasil diperbarui.";
        header('Location: edit_penjualan.php?id_penjualan=' . $id_penjualan); // Redirect to clear form
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
    <title>Edit Penjualan - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="input-container">
        <h2>Edit penjualan</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_penjualan=' . $id_penjualan; ?>" method="POST">
            <!-- ID penjualan (Read-only) -->
            <div class="form-group">
                <label for="id_penjualan">ID penjualan:</label>
                <input type="text" id="id_penjualan" name="id_penjualan" value="<?php echo $id_penjualan; ?>" readonly>
            </div>

            <!-- Nama penjualan -->
            <div class="form-group">
                <label for="nama_sparepart">Nama penjualan:</label>
                <input type="text" id="nama_sparepart" name="nama_sparepart" value="<?php echo $nama_sparepart; ?>" required>
            </div>

            <!-- Harga penjualan -->
            <div class="form-group">
                <label for="harga">Harga penjualan:</label>
                <input type="text" id="harga" name="harga" value="<?php echo $harga; ?>" required oninput="formatCurrency(this)">
            </div>

            <div class="form-group">
                <label for="tanggal">Tanggal :</label>
                <input type="date" id="tanggal" name="tanggal" value="<?php echo $tanggal ?>" required>
            </div>


            <!-- Submit Button -->
            <button type="submit" class="submit-button">Update</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_penjualan.php'">Kembali</button>

            <!-- General error message -->
            <?php if (isset($errors['general'])): ?>
                <p class="error"><?php echo $errors['general']; ?></p>
            <?php endif; ?>
        </form>

        <!-- Success Modal -->
        <div id="successModal" class="modal">
            <div class="modal-content">
                <h3 id="modalMessage"></h3>
                <button id="closeModal">OK</button>
            </div>
        </div>
    </div>

    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/[^,\d]/g, '');
            const parts = value.split(',');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = parts.join(',');
        }

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
                    window.location.href = 'admin_penjualan.php';
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>