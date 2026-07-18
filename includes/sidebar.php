<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">
        <a href="dashboard.php" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary"><i class="fa fa-graduation-cap me-2"></i>UNI QA</h3>
        </a>
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" src="img/defaultAvatar.jpg" alt="User" style="width: 40px; height: 40px;">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 user-email" title="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'User' ?>"><?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'User' ?></h6>
                <span><?= isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : '' ?></span>
            </div>
        </div>
        <div class="navbar-nav w-100">
            <a href="dashboard.php" class="nav-item nav-link<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' active' : '' ?>">
                <i class="fa fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="lo_dashboard.php" class="nav-item nav-link<?= basename($_SERVER['PHP_SELF']) === 'lo_dashboard.php' ? ' active' : '' ?>">
                <i class="fa fa-tachometer-alt me-2"></i>Learning Outcomes Dashboard
            </a>
            <a href="lecturer_courses.php" class="nav-item nav-link<?= basename($_SERVER['PHP_SELF']) === 'lecturer_courses.php' ? ' active' : '' ?>">
                <i class="fa fa-book me-2"></i>Courses
            </a>
            <a href="questions.php" class="nav-item nav-link<?= basename($_SERVER['PHP_SELF']) === 'questions.php' ? ' active' : '' ?>">
                <i class="fa fa-question-circle me-2"></i>Questions
            </a>
            <a href="404.html" class="nav-item nav-link<?= basename($_SERVER['PHP_SELF']) === '404.html' ? ' active' : '' ?>">
                <i class="fa fa-chart-bar me-2"></i>Analytics
            </a>
            <a href="404.html" class="nav-item nav-link<?= basename($_SERVER['PHP_SELF']) === '404.html' ? ' active' : '' ?>">
                <i class="fa fa-cog me-2"></i>Settings
            </a>
        </div>
    </nav>
</div>
