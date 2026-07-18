<?php
session_start();
include 'includes/auth.php';

// Destroy all session data
$_SESSION = array();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - University QA System</title>
    <link href="img/favicon.jpg" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Heebo', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 500px;
            width: 90%;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .logout-icon {
            font-size: 4rem;
            color: #4f46e5;
            margin-bottom: 1.5rem;
            animation: iconBounce 1.2s ease-in-out;
        }

        h1 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        p {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .logout-progress {
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 1.5rem 0;
        }

        .progress-bar {
            width: 0;
            height: 100%;
            background: #4f46e5;
            transition: width 5s linear;
            border-radius: 4px;
        }

        #countdown {
            font-weight: 700;
            color: #4f46e5;
            font-size: 1.2em;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes iconBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-content">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h1>You've Been Logged Out</h1>
            <p>Thank you for using the University Quality Assurance System. Your session has been securely terminated.</p>
            <div class="logout-progress">
                <div class="progress-bar"></div>
            </div>
            <p>Redirecting to login page in <span id="countdown">5</span> seconds</p>
            <a href="login.php" class="btn-primary">
                <i class="fas fa-arrow-left"></i> Return to Login Immediately
            </a>
        </div>
    </div>

    <script>
        // Start animations after DOM loads
        document.addEventListener('DOMContentLoaded', () => {
            let seconds = 5;
            const countdownElement = document.getElementById('countdown');
            const progressBar = document.querySelector('.progress-bar');
            
            // Start progress bar animation
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 100);

            // Start countdown timer
            const countdown = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                
                if(seconds <= 0) {
                    clearInterval(countdown);
                    window.location.href = 'login.php';
                }
            }, 1000);

            // Progress bar hover effect
            progressBar.addEventListener('mouseover', () => {
                progressBar.style.transform = 'scaleY(1.2)';
            });
            
            progressBar.addEventListener('mouseout', () => {
                progressBar.style.transform = 'scaleY(1)';
            });
        });
    </script>
</body>
</html>