<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../acc/login.php");
    exit();
?>