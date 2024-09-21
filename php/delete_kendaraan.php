<?php
session_start(); // Always start session at the top of the page

// Prevent access if not logged in
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php'); // Redirect to login page
    exit();
}

include 'database_connection.php'; // Include DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize ID
    $id_service = mysqli_real_escape_string($conn, $_POST['id_service']);

    // Delete from the database
    $query_delete = "DELETE FROM kendaraan WHERE id_service = '$id_service'";
    
    if (mysqli_query($conn, $query_delete)) {
        // Redirect to success page or show success message
        header('Location: admin_kendaraan.php');
        exit();
    } else {
        echo "Error: Could not delete the record. Please try again.";
    }
} else {
    echo "Invalid request.";
}
?>
