<?php
session_start(); // Start the session

date_default_timezone_set('Asia/Jakarta'); // Set to your time zone

// Prevent access if not logged in
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php'); // Redirect to login page
    exit();
}

include 'database_connection.php'; // Include DB connection

// Define variables and set to empty values
$keterangan = $tanggal = $no_antrian = $no_kendaraan = $nama_pelanggan = "";
$errors = array();

// Fetch the user's data: vehicle number and name
$id_user = mysqli_real_escape_string($conn, $_SESSION['id_user']);

// Fetch vehicle number and user name
$query_user = "SELECT no_kendaraan, nama_user FROM user WHERE id_user = '$id_user'";
$result_user = mysqli_query($conn, $query_user);
$row_user = mysqli_fetch_assoc($result_user);

$no_kendaraan = $row_user['no_kendaraan'];
$nama_pelanggan = $row_user['nama_user'];

// Calculate queue number when the form loads
$today = date('Y-m-d');
$query_queue = "SELECT MAX(no_antrian) as last_queue FROM kendaraan WHERE DATE(tanggal) = '$today'";
$result_queue = mysqli_query($conn, $query_queue);

// Check for SQL errors
if (!$result_queue) {
    die('Error: ' . mysqli_error($conn));
}

$row_queue = mysqli_fetch_assoc($result_queue);

// If there are bookings today, increment the queue number; otherwise, set it to 1
if ($row_queue['last_queue']) {
    $no_antrian = $row_queue['last_queue'] + 1;
} else {
    $no_antrian = 1;
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $no_antrian = mysqli_real_escape_string($conn, $_POST['no_antrian']);

    // Insert booking into the 'kendaraan' table
    $query_insert = "INSERT INTO kendaraan (no_antrian, id_user, nama_pelanggan, no_kendaraan, keterangan, tanggal, status)
                     VALUES ('$no_antrian', '$id_user', '$nama_pelanggan', '$no_kendaraan', '$keterangan', '$tanggal', '0')";

    if (mysqli_query($conn, $query_insert)) {
        $_SESSION['show_success_modal'] = "Booking berhasil ditambahkan.";
        header('Location: customer_booking_service.php'); // Redirect to clear form
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
    <title>Booking Service - Bengkel A3R Team</title>
    <link rel="stylesheet" href="../css/customer-style.css">
    <link rel="stylesheet" href="../css/customer-service-style.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            function setTodayDate() {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-based
                const day = String(today.getDate()).padStart(2, '0'); // Add leading zero for single digit days
                const formattedDate = `${year}-${month}-${day}`;
                document.getElementById('tanggal').value = formattedDate;
            }

            setTodayDate(); // Call the function to set the default date

            // Handle date change event
            $('#tanggal').on('change', function() {
                let selectedDate = $(this).val();
                if (selectedDate) {
                    $.ajax({
                        url: 'get_queue_number.php', // URL to fetch the queue number
                        type: 'POST',
                        data: {
                            tanggal: selectedDate
                        },
                        success: function(response) {
                            $('#no_antrian').val(response); // Update the queue number
                        },
                        error: function() {
                            alert("An error occurred while calculating the queue number.");
                        }
                    });
                }
            });
        });
    </script>
</head>

<body>
    <!-- Include header and navbar -->
    <?php include 'customer_header.php'; ?>

    <div class="booking-container">
        <h2>Booking Service</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <!-- Read-only field for No Antrian -->
            <div class="form-group">
                <label for="no_antrian">No Antrian:</label>
                <input type="text" id="no_antrian" name="no_antrian" value="<?php echo $no_antrian; ?>" readonly>
            </div>

            <!-- Read-only field for No Kendaraan -->
            <div class="form-group">
                <label for="no_kendaraan">No Kendaraan:</label>
                <input type="text" id="no_kendaraan" name="no_kendaraan" value="<?php echo $no_kendaraan; ?>" readonly>
            </div>

            <!-- Read-only field for Nama Pelanggan -->
            <div class="form-group">
                <label for="nama_pelanggan">Nama Pelanggan:</label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="<?php echo $nama_pelanggan; ?>" readonly>
            </div>

            <!-- Keterangan/Keluhan field (User fills this) -->
            <div class="form-group">
                <label for="keterangan">Keterangan/Keluhan:</label>
                <textarea name="keterangan" id="keterangan" required><?php echo $keterangan; ?></textarea>
                <?php if (isset($errors['keterangan'])): ?>
                    <span class="error"><?php echo $errors['keterangan']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Tanggal field (User fills this) -->
            <div class="form-group">
                <label for="tanggal">Tanggal:</label>
                <input type="date" name="tanggal" id="tanggal" required>
                <?php if (isset($errors['tanggal'])): ?>
                    <span class="error"><?php echo $errors['tanggal']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Submit Booking</button>
            <button type="button" class="back-button" onclick="window.location.href='customer_service.php'">Kembali</button>

            <!-- Error message for general errors -->
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
                    window.location.href = 'customer_service.php';
                };
            });
            <?php unset($_SESSION['show_success_modal']); // Clear the session variable after showing the modal 
            ?>
        <?php endif; ?>
    </script>
</body>

</html>