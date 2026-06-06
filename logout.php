<?php
// logout.php — Proses Logout
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login
header('Location: /campus_events/index.php?logout=1');
exit;
?>
