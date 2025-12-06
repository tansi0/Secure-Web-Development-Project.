<!-- Redirects to Index page -->
<?php
session_start();
session_destroy();
header("Location: index.html");
exit();
?>