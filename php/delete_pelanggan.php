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
    $id_user = mysqli_real_escape_string($conn, $_POST['id_user']);

    // Delete from the database
    $query_delete = "DELETE FROM user WHERE id_user = '$id_user'";
    
    if (mysqli_query($conn, $query_delete)) {
        // Redirect to success page or show success message
        header('Location: admin_pelanggan.php');
        exit();
    } else {
        echo "Error: Could not delete the record. Please try again.";
    }
} else {
    echo "Invalid request.";
}
?>
