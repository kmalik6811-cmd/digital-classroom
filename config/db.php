<?php
require_once __DIR__ . '/env.php';

// Reuse existing connection to avoid "Too many connections" on shared hosting
if (!isset($GLOBALS['conn']) || !$GLOBALS['conn']) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    $GLOBALS['conn'] = $conn;

    // Auto-close connection when script finishes
    register_shutdown_function(function () {
        if (isset($GLOBALS['conn']) && $GLOBALS['conn']) {
            mysqli_close($GLOBALS['conn']);
        }
    });
} else {
    $conn = $GLOBALS['conn'];
}
?>
