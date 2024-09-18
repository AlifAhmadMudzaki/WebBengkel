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
$id_supplier = $nama_supplier = $alamat_supplier = $email_supplier = $no_telp = "";
$errors = array();

// Check if we're editing an existing spare part
$is_editing = isset($_GET['id_supplier']) && !empty($_GET['id_supplier']);
if ($is_editing) {
    $id_supplier = mysqli_real_escape_string($conn, $_GET['id_supplier']);
    // Fetch existing data
    $query_supplier = "SELECT * FROM supplier WHERE id_supplier = '$id_supplier'";
    $result_supplier = mysqli_query($conn, $query_supplier);
    if ($result_supplier && mysqli_num_rows($result_supplier) > 0) {
        $row_supplier = mysqli_fetch_assoc($result_supplier);
        $id_supplier = $row_supplier['id_supplier'];
        $nama_supplier = $row_supplier['nama_supplier'];
        $email_supplier = $row_supplier['email_supplier'];
        $alamat_supplier = $row_supplier['alamat_supplier'];
        $no_telp = $row_supplier['no_telp'];
    } else {
        $errors['general'] = "Error: supplier not found.";
    }
} else {
    // Redirect to add_supplier.php if no ID is provided
    header('Location: add_supplier.php');
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize inputs
    $nama_supplier = mysqli_real_escape_string($conn, $_POST['nama_supplier']);
    $email_supplier = mysqli_real_escape_string($conn, $_POST['email_supplier']);
    $alamat_supplier = mysqli_real_escape_string($conn, $_POST['alamat_supplier']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    // Update the spare part in the database
    $query_update = "UPDATE supplier 
                     SET nama_supplier = '$nama_supplier', email_supplier = '$email_supplier', 
                     alamat_supplier = '$alamat_supplier', no_telp = '$no_telp'
                     WHERE id_supplier = '$id_supplier'";
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['show_success_modal'] = "Supplier berhasil diperbarui.";
        header('Location: edit_supplier.php?id_supplier=' . $id_supplier); // Redirect to clear form
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
    <title>Edit supplier - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="input-container">
        <h2>Edit Supplier</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_supplier=' . $id_supplier; ?>" method="POST">
            <!-- ID supplier (Read-only) -->
            <div class="form-group">
                <label for="id_supplier">ID supplier:</label>
                <input type="text" id="id_supplier" name="id_supplier" value="<?php echo $id_supplier; ?>" readonly>
            </div>

            <!-- Nama supplier -->
            <div class="form-group">
                <label for="nama_supplier">Nama supplier:</label>
                <input type="text" id="nama_supplier" name="nama_supplier" value="<?php echo $nama_supplier; ?>" required>
            </div>

            <!-- alamat supplier -->
            <div class="form-group">
                <label for="alamat_supplier">Alamat supplier:</label>
                <input type="text" id="alamat_supplier" name="alamat_supplier" value="<?php echo $alamat_supplier; ?>" required">
            </div>

            <!-- Email supplier -->
            <div class="form-group">
                <label for="email_supplier">Email supplier:</label>
                <input type="email" id="email_supplier" name="email_supplier" value="<?php echo $email_supplier; ?>" required">
            </div>

            <!-- No Telp -->
            <div class="form-group">
                <label for="no_telp">No Telepon:</label>
                <input type="number" id="no_telp" name="no_telp" value="<?php echo $no_telp; ?>" required">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Update</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_supplier.php'">Kembali</button>

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
                    window.location.href = 'admin_supplier.php';
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>