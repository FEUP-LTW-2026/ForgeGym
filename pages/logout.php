<?php
session_start();
session_unset();
session_destroy();

// Redireciona de volta para a página inicial
header("Location: ../index.php");
exit;