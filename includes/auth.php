<?php
function login($email, $password, $role, $pdo) {
    $table = ($role === 'lecturer') ? 'lecturers' : 'administrators';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user[$role.'_id'];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $user['email'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    $_SESSION = array();
    session_destroy();
    header('Location: index.php');
    exit();
}
?>