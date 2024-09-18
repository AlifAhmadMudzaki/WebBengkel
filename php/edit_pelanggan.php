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
$id_user = $nama_user = $username = $password = $alamat = $no_hp = $no_kendaraan = "";
$errors = array();

// Check if we're editing an existing spare part
$is_editing = isset($_GET['id_user']) && !empty($_GET['id_user']);
if ($is_editing) {
    $id_user = mysqli_real_escape_string($conn, $_GET['id_user']);
    // Fetch existing data
    $query_user = "SELECT * FROM user WHERE id_user = '$id_user'";
    $result_user = mysqli_query($conn, $query_user);
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $row_user = mysqli_fetch_assoc($result_user);
        $id_user = $row_user['id_user'];
        $nama_user = $row_user['nama_user'];
        $username = $row_user['username'];
        $password = $row_user['password'];
        $alamat = $row_user['alamat'];
        $no_hp = $row_user['no_hp'];
        $no_kendaraan = $row_user['no_kendaraan'];
    } else {
        $errors['general'] = "Error: user not found.";
    }
} else {
    // Redirect to add_user.php if no ID is provided
    header('Location: add_user.php');
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize inputs
    $nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $id_user = mysqli_real_escape_string($conn, $_POST['id_user']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $no_kendaraan = mysqli_real_escape_string($conn, $_POST['no_kendaraan']);
    // Update the spare part in the database
    $query_update = "UPDATE user 
                     SET nama_user = '$nama_user', username = '$username', password = '$password', 
                     alamat = '$alamat', no_hp = '$no_hp', no_kendaraan = '$no_kendaraan'
                     WHERE id_user = '$id_user'";
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['show_success_modal'] = "Pelanggan berhasil diperbarui.";
        header('Location: edit_pelanggan.php?id_user=' . $id_user); // Redirect to clear form
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
    <title>Edit Pelanggan - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="input-container">
        <h2>Edit Pelanggan</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_user=' . $id_user; ?>" method="POST">
            <!-- ID user (Read-only) -->
            <div class="form-group">
                <label for="id_user">ID User:</label>
                <input type="text" id="id_user" name="id_user" value="<?php echo $id_user; ?>" readonly>
            </div>

            <!-- Nama user -->
            <div class="form-group">
                <label for="nama_user">Nama User:</label>
                <input type="text" id="nama_user" name="nama_user" value="<?php echo $nama_user; ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="text" id="password" name="password" value="<?php echo $password; ?>" required>
            </div>

            <!-- alamat user -->
            <div class="form-group">
                <label for="alamat_user">Alamat:</label>
                <input type="text" id="alamat" name="alamat" value="<?php echo $alamat; ?>" required">
            </div>

            <!-- Email user -->
            <div class="form-group">
                <label for="no_hp">No_Hp:</label>
                <input type="number" id="no_hp" name="no_hp" value="<?php echo $no_hp; ?>" required">
            </div>

            <!-- No Telp -->
            <div class="form-group">
                <label for="no_kendaraan">No Kendaraan</label>
                <input type="text" id="no_kendaraan" name="no_kendaraan" value="<?php echo $no_kendaraan; ?>" required">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Update</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_pelanggan.php'">Kembali</button>

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
                    window.location.href = 'admin_pelanggan.php';
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>