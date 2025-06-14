<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | eTraCCS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff8c00;
            --primary-light: rgba(255, 140, 0, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --bg-dark: #1a1a1a;
            --bg-darker: #121212;
            --card-bg: rgba(40, 40, 40, 0.7);
            --card-border: rgba(255, 255, 255, 0.1);
            --success: #4CAF50;
            --warning: #FFC107;
            --danger: #F44336;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-darker);
            color: var(--text-primary);
            background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        /* Navigation */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            transition: all 0.3s ease;
        }

        header.scrolled {
            padding: 15px 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        nav a {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            position: relative;
            transition: all 0.3s ease;
        }

        nav a:hover {
            color: var(--primary);
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        nav a:hover::after {
            width: 100%;
        }

        .cta-button {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            background: #e67e00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 140, 0, 0.3);
        }

        .secondary-button {
            background: transparent;
            color: var(--text-primary);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            border: 2px solid var(--primary);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .secondary-button:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        /* 404 Content */
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 40px 80px;
            position: relative;
            text-align: center;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,140,0,0.1) 0%, rgba(0,0,0,0) 70%);
            animation: rotate 20s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-content {
            max-width: 800px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 60px;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 5px 15px rgba(255, 140, 0, 0.3);
        }

        .error-title {
            font-size: 36px;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .error-message {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-image {
            max-width: 300px;
            margin: 0 auto 30px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Footer */
        footer {
            background: var(--bg-dark);
            padding: 30px 40px;
            border-top: 1px solid var(--card-border);
            text-align: center;
        }

        .copyright {
            color: var(--text-secondary);
            font-size: 14px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .error-content {
                padding: 40px 20px;
            }

            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 28px;
            }

            .error-message {
                font-size: 16px;
            }

            .error-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .error-image {
                max-width: 200px;
            }
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 60px;
            }

            .error-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <header id="header">
        <a href="/" class="logo">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span>eTraCCS</span>
        </a>
        
        <nav>
            <ul>
                <li><a href="/#features">Features</a></li>
                <li><a href="/#testimonials">Testimonials</a></li>
                <li><a href="/#about">About</a></li>
                <li><a href="/#contact">Contact</a></li>
            </ul>
        </nav>
        
        <div class="hero-buttons">
            @auth
                <a href="{{ route('dashboard') }}" class="cta-button">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="cta-button">Log in</a>
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="secondary-button">Register</a>
                @endif
            @endauth
        </div>
    </header>

    <!-- 404 Content -->
    <section class="error-container">
        <div class="error-content">
            <div class="error-image">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--primary)" width="100%" height="100%">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                </svg>
            </div>
            <div class="error-code">404</div>
            <h1 class="error-title">Oops! Page Not Found</h1>
            <p class="error-message">
                The page you're looking for doesn't exist or has been moved. 
                Don't worry though, you can find your way back to our amazing alumni portal.
            </p>
            <div class="error-buttons">
                <a href="/" class="cta-button">Return Home</a>
                <a href="/#contact" class="secondary-button">Contact Support</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="copyright">
            &copy; {{ date('Y') }} eTraCCS. All rights reserved.
        </div>
    </footer>
</body>
</html>