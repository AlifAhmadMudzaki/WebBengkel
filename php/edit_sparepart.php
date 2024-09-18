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

// Check if we're editing an existing spare part
$is_editing = isset($_GET['id_sparepart']) && !empty($_GET['id_sparepart']);
if ($is_editing) {
    $id_sparepart = mysqli_real_escape_string($conn, $_GET['id_sparepart']);
    // Fetch existing data
    $query_supplier = "SELECT * FROM sparepart WHERE id_sparepart = '$id_sparepart'";
    $result_sparepart = mysqli_query($conn, $query_supplier);
    if ($result_sparepart && mysqli_num_rows($result_sparepart) > 0) {
        $row_sparepart = mysqli_fetch_assoc($result_sparepart);
        $nama_sparepart = $row_sparepart['nama_sparepart'];
        $harga_sparepart = $row_sparepart['harga_sparepart'];
        $status = $row_sparepart['status'];
        $id_supplier = $row_sparepart['id_supplier'];
    } else {
        $errors['general'] = "Error: Sparepart not found.";
    }
} else {
    // Redirect to add_sparepart.php if no ID is provided
    header('Location: add_sparepart.php');
    exit();
}

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

    // Update the spare part in the database
    $query_update = "UPDATE sparepart 
                     SET nama_sparepart = '$nama_sparepart', harga_sparepart = '$harga_sparepart', status = '$status', id_supplier = '$id_supplier'
                     WHERE id_sparepart = '$id_sparepart'";
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['show_success_modal'] = "Sparepart berhasil diperbarui.";
        header('Location: edit_sparepart.php?id_sparepart=' . $id_sparepart); // Redirect to clear form
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
    <title>Edit Sparepart - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="input-container">
        <h2>Edit Sparepart</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_sparepart=' . $id_sparepart; ?>" method="POST">
            <!-- ID Sparepart (Read-only) -->
            <div class="form-group">
                <label for="id_sparepart">ID Sparepart:</label>
                <input type="text" id="id_sparepart" name="id_sparepart" value="<?php echo $id_sparepart; ?>" readonly>
            </div>

            <!-- Nama Sparepart -->
            <div class="form-group">
                <label for="nama_sparepart">Nama Sparepart:</label>
                <input type="text" id="nama_sparepart" name="nama_sparepart" value="<?php echo $nama_sparepart; ?>" required>
            </div>

            <!-- Harga Sparepart -->
            <div class="form-group">
                <label for="harga_sparepart">Harga Sparepart:</label>
                <input type="text" id="harga_sparepart" name="harga_sparepart" value="<?php echo $harga_sparepart; ?>" required oninput="formatCurrency(this)">
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="1" <?php if ($status == "1") echo "selected"; ?>>Tersedia</option>
                    <option value="0" <?php if ($status == "0") echo "selected"; ?>>Kosong</option>
                </select>
            </div>

            <!-- ID Supplier (Dropdown from database) -->
            <div class="form-group">
                <label for="id_supplier">Supplier:</label>
                <select id="id_supplier" name="id_supplier" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php while ($row_supplier = mysqli_fetch_assoc($result_supplier)) { ?>
                        <option value="<?php echo $row_supplier['id_supplier']; ?>" <?php if ($id_supplier == $row_supplier['id_supplier']) echo "selected"; ?>>
                            <?php echo $row_supplier['id_supplier'] . ' - ' . $row_supplier['nama_supplier']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Update</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_sparepart.php'">Kembali</button>

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
                    window.location.href = 'admin_sparepart.php';
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>