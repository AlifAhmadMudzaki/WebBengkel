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
$id_service = $no_kendaraan = $id_user = $no_antrian = $nama_pelanggan = $keterangan = $tanggal = $status = "";
$errors = array();

// Check if we're editing an existing spare part
$is_editing = isset($_GET['id_service']) && !empty($_GET['id_service']);
if ($is_editing) {
    $id_service = mysqli_real_escape_string($conn, $_GET['id_service']);
    // Fetch existing data
    $query_service = "SELECT * FROM service WHERE id_service = '$id_service'";
    $result_service = mysqli_query($conn, $query_service);
    if ($result_service && mysqli_num_rows($result_service) > 0) {
        $row_service = mysqli_fetch_assoc($result_service);
        $id_service = $row_service['id_service'];
        $no_kendaraan = $row_service['no_kendaraan'];
        $id_user = $row_service['id_user'];
        $no_antrian = $row_service['no_antrian'];
        $nama_pelanggan = $row_service['nama_pelanggan'];
        $keterangan = $row_service['keterangan'];
        $tanggal = $row_service['tanggal'];
        $status = $row_service['status'];
    } else {
        $errors['general'] = "Error: service not found.";
    }
} else {
    // Redirect to add_service.php if no ID is provided
    header('Location: add_kendaraan.php');
    exit();
}

// Fetch services from the database for the service dropdown
$query_service = "SELECT id_service, nama_pelanggan FROM kendaraan";
$result_service = mysqli_query($conn, $query_service);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize inputs
    $id_service = mysqli_real_escape_string($conn, $_POST['id_service']);
    $no_kendaraan = mysqli_real_escape_string($conn, $_POST['no_kendaraan']);
    $id_user = mysqli_real_escape_string($conn, $_POST['id_user']);
    $no_antrian = mysqli_real_escape_string($conn, $_POST['no_antrian']);
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);


    // Update the spare part in the database
    $query_update = "UPDATE kendaraan 
                     SET no_kendaraan = '$no_kendaraan', id_user = '$id_user', no_antrian = '$no_antrian', nama_pelanggan = '$nama_pelanggan', keterangan = '$keterangan', tanggal = '$tanggal' 
                     WHERE id_service = '$id_service'";
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['show_success_modal'] = "Service Kendaraan Berhasil Diperbarui.";
        header('Location: edit_kendaraan.php?id_service=' . $id_service); // Redirect to clear form
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
    <title>Edit Service Kendaraan - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="input-container">
        <h2>Edit service</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_service=' . $id_service; ?>" method="POST">
            <!-- ID service (Read-only) -->
            <div class="form-group">
                <label for="id_service">ID Service:</label>
                <input type="text" id="id_service" name="id_service" value="<?php echo $id_service; ?>" readonly>
            </div>

            <!-- Nama service -->
            <div class="form-group">
                <label for="no_kendaraan">No Kendaraan:</label>
                <input type="text" id="no_kendaraan" name="no_kendaraan" value="<?php echo $no_kendaraan; ?>" required>
            </div>

            <!-- Harga service -->
            <div class="form-group">
                <label for="id_user">:ID User</label>
                <input type="number" id="id_user" name="id_user" value="<?php echo $id_user; ?>" required oninput="formatCurrency(this)">
            </div>

            <div class="form-group">
                <label for="no_antrian">:No Antrian</label>
                <input type="number" id="no_antrian" name="no_antrian" value="<?php echo $no_antrian ?>" required oninput="formatCurrency(this)">
            </div>

            <div class="form-group">
                <label for="nama_pelanggan">:Nama Pelanggan</label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="<?php echo $nama_pelanggan ?>" required oninput="formatCurrency(this)">
            </div>

            <div class="form-group">
                <label for="keterangan">:Keterangan</label>
                <input type="text" id="keterangan" name="keterangan" value="<?php echo $keterangan ?>" required oninput="formatCurrency(this)">
            </div>

            <div class="form-group">
                <label for="tanggal">:Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="<?php echo $tanggal ?>" required oninput="formatCurrency(this)">
            </div>
            
            <!-- Status -->
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="1" <?php if ($status == "1") echo "selected"; ?>>Sedang Diperbaiki</option>
                    <option value="0" <?php if ($status == "0") echo "selected"; ?>>Selesai Diperbaiki</option>
                </select>
            </div>

            <!-- ID service (Dropdown from database) -->
            <div class="form-group">
                <label for="id_service">Service:</label>
                <select id="id_service" name="id_service" required>
                    <option value="">-- Pilih service --</option>
                    <?php while ($row_service = mysqli_fetch_assoc($result_service)) { ?>
                        <option value="<?php echo $row_service['id_service']; ?>" <?php if ($id_service == $row_service['id_service']) echo "selected"; ?>>
                            <?php echo $row_service['id_service'] . ' - ' . $row_service['nama_pelanggan']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Update</button>
            <button type="button" class="back-button" onclick="window.location.href='admin_kendaraan.php'">Kembali</button>

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
                    window.location.href = 'admin_kendaraan.php';
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>