<?php
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie da sessão no browser para o login seguinte ser 100% limpo
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão no servidor
session_destroy();

// Redirecionar para a página inicial (que está uma pasta acima)
header("Location: ../index.php");
exit;