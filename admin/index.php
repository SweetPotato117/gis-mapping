<?php
// No PHP code needed at the top for this page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Cauayan City Map</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <link rel="stylesheet" href="../assets/css/admin-shell.css?v=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: var(--account-accent);
            --primary-dark: #07534e;
            --secondary-color: #4a90e2;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --text-primary: #333;
            --text-secondary: #666;
            --border-color: var(--account-line);
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--account-canvas);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
        }

        /* Info Panel */
        .info-panel {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .info-panel::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            top: -50%;
            left: -50%;
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .info-content {
            position: relative;
            z-index: 1;
        }

        .info-panel h1 {
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .info-panel p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .info-panel .logo {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .toggle-btn {
            background: white;
            color: var(--primary-color);
            border: 2px solid white;
            padding: 12px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .toggle-btn:hover {
            background: transparent;
            color: white;
        }

        /* Form Panel */
        .form-panel {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-wrapper {
            position: relative;
            width: 100%;
        }

        .form-container {
            position: absolute;
            width: 100%;
            transition: all 0.6s ease;
            opacity: 0;
            transform: translateX(100%);
            pointer-events: none;
        }

        .form-container.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            position: relative;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--text-primary);
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.fa-envelope,
        .input-wrapper i.fa-lock,
        .input-wrapper i.fa-user,
        .input-wrapper i.fa-phone {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
            pointer-events: none;
        }

        .form-group input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(9, 107, 100, 0.1);
        }

        .form-group input.error {
            border-color: var(--danger-color);
        }

        .error-message {
            color: var(--danger-color);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 16px;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(9, 107, 100, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider::before {
            margin-right: 15px;
        }

        .divider::after {
            margin-left: 15px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr;
                max-width: 450px;
            }

            .info-panel {
                padding: 40px 30px;
            }

            .info-panel h1 {
                font-size: 24px;
            }

            .info-panel .logo {
                font-size: 60px;
            }

            .form-panel {
                padding: 40px 30px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .form-panel {
                padding: 30px 20px;
            }

            .info-panel {
                padding: 30px 20px;
            }
        }

        :root {
            --primary-color: var(--account-accent);
            --primary-dark: #07534e;
            --secondary-color: #4a90e2;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --text-primary: var(--account-ink);
            --text-secondary: var(--account-muted);
            --border-color: var(--account-line);
            --shadow: 0 28px 80px rgba(27, 55, 33, 0.16);
        }

        body {
            min-height: 100vh;
            height: auto;
            padding: clamp(18px, 4vw, 48px);
            color: var(--text-primary);
            background:
                radial-gradient(ellipse at 12% 10%, rgba(9, 107, 100, 0.11), transparent 34%),
                radial-gradient(ellipse at 92% 88%, rgba(9, 107, 100, 0.08), transparent 32%),
                var(--account-canvas);
        }

        .auth-container {
            width: min(1120px, 100%);
            max-width: none;
            min-height: 680px;
            grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 26px;
            box-shadow: var(--shadow);
        }

        .info-panel {
            align-items: stretch;
            justify-content: space-between;
            padding: clamp(30px, 5vw, 56px);
            background: linear-gradient(150deg, #096b64 0%, #15525a 55%, var(--account-header) 100%);
            text-align: left;
        }

        .info-panel::before {
            inset: 0;
            width: auto;
            height: auto;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
            background-size: 34px 34px;
            animation: none;
            opacity: 0.55;
        }

        .info-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            background: radial-gradient(circle at 82% 18%, rgba(255, 255, 255, 0.12), transparent 36%);
            pointer-events: none;
        }

        .brand-lockup,
        .info-content,
        .info-footer {
            position: relative;
            z-index: 1;
        }

        .brand-lockup {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.13);
            font-size: 20px;
        }

        .brand-copy {
            display: grid;
            gap: 3px;
        }

        .brand-copy strong {
            font-size: 15px;
            letter-spacing: 0.01em;
        }

        .brand-copy span {
            color: rgba(255, 255, 255, 0.72);
            font-size: 12px;
        }

        .info-content {
            width: 100%;
            max-width: 440px;
            margin: 48px auto;
        }

        .info-eyebrow,
        .form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .info-eyebrow {
            color: rgba(255, 255, 255, 0.78);
        }

        .info-panel h1 {
            margin: 18px 0 14px;
            font-size: clamp(32px, 4vw, 46px);
            line-height: 1.08;
            letter-spacing: -0.035em;
        }

        .info-panel p {
            max-width: 390px;
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
            line-height: 1.7;
        }

        .map-preview {
            position: relative;
            height: 155px;
            margin: 30px 0 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 18px;
            background:
                linear-gradient(30deg, transparent 47%, rgba(255, 255, 255, 0.12) 48%, rgba(255, 255, 255, 0.12) 51%, transparent 52%),
                linear-gradient(145deg, rgba(190, 219, 167, 0.2), rgba(233, 239, 191, 0.1));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.13);
        }

        .map-preview::before,
        .map-preview::after {
            content: '';
            position: absolute;
            width: 130%;
            height: 14px;
            border: 3px solid rgba(255, 255, 255, 0.42);
            border-right: 0;
            border-left: 0;
            border-radius: 50%;
            transform: rotate(-15deg);
        }

        .map-preview::before {
            top: 26px;
            left: -12%;
        }

        .map-preview::after {
            right: -30%;
            bottom: 12px;
            transform: rotate(28deg);
        }

        .map-road {
            position: absolute;
            z-index: 1;
            width: 160%;
            height: 6px;
            border-radius: 99px;
            background: rgba(255, 255, 255, 0.42);
            transform: rotate(22deg);
        }

        .map-road-one {
            top: 72px;
            left: -24%;
        }

        .map-road-two {
            top: 60px;
            left: -16%;
            height: 4px;
            opacity: 0.65;
            transform: rotate(-34deg);
        }

        .map-pin {
            position: absolute;
            z-index: 2;
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border: 2px solid rgba(255, 255, 255, 0.9);
            border-radius: 50% 50% 50% 5px;
            background: #f5fff3;
            color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(20, 52, 27, 0.25);
            transform: rotate(-45deg);
        }

        .map-pin i {
            font-size: 13px;
            transform: rotate(45deg);
        }

        .map-pin-one {
            top: 34px;
            left: 28%;
        }

        .map-pin-two {
            right: 24%;
            bottom: 28px;
            width: 25px;
            height: 25px;
            background: #d9eed7;
        }

        .map-location-card {
            position: absolute;
            right: 16px;
            bottom: 14px;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
            color: var(--text-primary);
            box-shadow: 0 8px 20px rgba(18, 45, 25, 0.16);
        }

        .map-location-card > i {
            color: var(--primary-color);
        }

        .map-location-card span {
            display: grid;
            gap: 2px;
            font-size: 12px;
            font-weight: 700;
        }

        .map-location-card small {
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 400;
        }

        .toggle-btn {
            min-height: 46px;
            margin-top: 20px;
            padding: 0 20px;
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.96);
            color: var(--primary-dark);
            font-size: 14px;
        }

        .toggle-btn:hover {
            background: transparent;
        }

        .info-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 12px;
        }

        .info-footer i {
            font-size: 11px;
        }

        .form-panel {
            align-items: center;
            padding: clamp(32px, 5.5vw, 68px);
            background: #fff;
        }

        .form-wrapper {
            width: 100%;
            max-width: 460px;
        }

        .form-eyebrow {
            margin-bottom: 14px;
            color: var(--primary-color);
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            margin-bottom: 8px;
            color: var(--text-primary);
            font-size: clamp(26px, 3vw, 32px);
            letter-spacing: -0.03em;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
        }

        .form-container {
            inset: 0;
            transform: translateX(18px);
            visibility: hidden;
            transition:
                opacity 0.25s ease,
                transform 0.25s ease,
                visibility 0s linear 0.25s;
        }

        .form-container.active {
            visibility: visible;
            transition-delay: 0s;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            margin-bottom: 8px;
            color: #344239;
            font-size: 13px;
            font-weight: 650;
        }

        .optional-label {
            margin-left: 4px;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 400;
        }

        .input-wrapper {
            min-width: 0;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            z-index: 1;
            color: #849087;
            font-size: 15px;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .form-group input {
            height: 54px;
            padding: 0 48px 0 45px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #f9fbf9;
            color: var(--text-primary);
            font-size: 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .form-group input::placeholder {
            color: #929d95;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(9, 107, 100, 0.11);
        }

        .form-group input.error {
            border-color: var(--danger-color);
            background: #fffafa;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 7px;
            z-index: 2;
            display: grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #6f7e73;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle:hover {
            background: #edf4ee;
            color: var(--primary-dark);
        }

        .password-toggle:focus-visible,
        .toggle-btn:focus-visible,
        .submit-btn:focus-visible,
        .form-footer button:focus-visible {
            outline: 3px solid rgba(74, 144, 226, 0.45);
            outline-offset: 2px;
        }

        .form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .error-message {
            margin-top: 6px;
            line-height: 1.4;
        }

        .submit-btn {
            min-height: 54px;
            margin-top: 8px;
            border-radius: 12px;
            background: var(--primary-color);
            box-shadow: 0 8px 18px rgba(9, 107, 100, 0.16);
            font-size: 15px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 10px 22px rgba(9, 107, 100, 0.23);
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            box-shadow: none;
            transform: none;
        }

        .form-footer {
            margin-top: 24px;
            font-size: 13px;
        }

        .form-footer button {
            padding: 0;
            border: 0;
            background: none;
            color: var(--primary-color);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .form-footer button:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 1100px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        @media (max-width: 820px) {
            body {
                padding: 24px;
            }

            .auth-container {
                max-width: 560px;
                grid-template-columns: 1fr;
            }

            .info-panel {
                min-height: 320px;
                padding: 28px 32px;
            }

            .info-content {
                margin: 28px auto;
            }

            .info-panel h1 {
                font-size: 34px;
            }

            .map-preview {
                height: 125px;
                margin: 20px 0 0;
            }

            .info-footer {
                display: none;
            }

            .form-panel {
                padding: 42px 40px;
            }
        }

        @media (max-width: 520px) {
            body {
                display: block;
                padding: 12px;
            }

            .auth-container {
                min-height: 0;
                margin: 0 auto;
                border-radius: 20px;
            }

            .info-panel {
                min-height: 0;
                padding: 24px 22px;
            }

            .info-content {
                margin: 24px 0 0;
            }

            .info-panel h1 {
                margin: 12px 0 10px;
                font-size: 29px;
            }

            .info-panel p {
                font-size: 13px;
            }

            .map-preview {
                height: 100px;
                margin: 17px 0 0;
            }

            .map-location-card {
                right: 9px;
                bottom: 8px;
                padding: 7px 9px;
            }

            .toggle-btn {
                min-height: 42px;
                margin-top: 14px;
                padding: 0 14px;
                font-size: 13px;
            }

            .form-panel {
                padding: 32px 22px;
            }

            .form-header {
                margin-bottom: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <aside class="info-panel" aria-label="Cauayan City Map information">
            <div class="brand-lockup">
                <span class="brand-mark" aria-hidden="true"><i class="fas fa-map-location-dot"></i></span>
                <span class="brand-copy">
                    <strong>Cauayan City</strong>
                    <span>Interactive map portal</span>
                </span>
            </div>

            <div class="info-content">
                <span class="info-eyebrow"><i class="fas fa-location-dot" aria-hidden="true"></i> CAUAYAN CITY · ISABELA</span>
                <h1>Discover the city.<br>Shape its map.</h1>
                <p>Manage local places and help residents and visitors find what makes Cauayan special.</p>

                <div class="map-preview" aria-hidden="true">
                    <span class="map-road map-road-one"></span>
                    <span class="map-road map-road-two"></span>
                    <span class="map-pin map-pin-one"><i class="fas fa-location-dot"></i></span>
                    <span class="map-pin map-pin-two"><i class="fas fa-location-dot"></i></span>
                    <div class="map-location-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Cauayan City<small>Local places, all in one map</small></span>
                    </div>
                </div>

                <button class="toggle-btn" id="toggleBtn" type="button" aria-controls="loginForm registerForm">
                    Create an account
                </button>
            </div>

            <div class="info-footer">
                <i class="fas fa-shield-halved" aria-hidden="true"></i>
                <span>Official city map management portal</span>
            </div>
        </aside>

        <main class="form-panel">
            <div class="form-wrapper">
                <div class="form-container active" id="loginForm">
                    <div class="alert alert-error" id="loginAlert" role="alert" aria-live="assertive"></div>
                    
                    <div class="form-header">
                        <span class="form-eyebrow"><i class="fas fa-lock" aria-hidden="true"></i> ADMIN ACCESS</span>
                        <h2>Welcome back</h2>
                        <p>Sign in to manage places on the Cauayan City map.</p>
                    </div>

                    <form id="loginFormSubmit" autocomplete="on">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon" aria-hidden="true"></i>
                                <input type="email" id="loginEmail" name="email" placeholder="you@example.com" autocomplete="username" autocapitalize="none" spellcheck="false" aria-describedby="loginEmailError" required>
                            </div>
                            <span class="error-message" id="loginEmailError">Enter a valid email address.</span>
                        </div>

                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                                <input type="password" id="loginPassword" name="password" placeholder="Enter your password" autocomplete="current-password" aria-describedby="loginPasswordError" required>
                                <button class="password-toggle" id="loginPasswordToggle" type="button" aria-label="Show password" aria-controls="loginPassword" aria-pressed="false">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <span class="error-message" id="loginPasswordError">Enter your password.</span>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign in
                        </button>
                    </form>

                    <div class="form-footer">
                        Don't have an account?
                        <button id="showRegister" type="button">Create one</button>
                    </div>
                </div>

                <div class="form-container" id="registerForm">
                    <div class="alert alert-success" id="registerAlert" role="status" aria-live="polite"></div>
                    
                    <div class="form-header">
                        <span class="form-eyebrow"><i class="fas fa-user-plus" aria-hidden="true"></i> NEW ADMIN ACCOUNT</span>
                        <h2>Create your account</h2>
                        <p>Enter your details below. You can sign in as soon as your account is created.</p>
                    </div>

                    <form id="registerFormSubmit" autocomplete="on">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="registerUsername">Username</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon" aria-hidden="true"></i>
                                    <input type="text" id="registerUsername" name="username" placeholder="Username" autocomplete="username" aria-describedby="usernameError" required>
                                </div>
                                <span class="error-message" id="usernameError">Use at least 3 characters.</span>
                            </div>

                            <div class="form-group">
                                <label for="registerPhone">Phone Number <span class="optional-label">Optional</span></label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon" aria-hidden="true"></i>
                                    <input type="tel" id="registerPhone" name="phone" placeholder="Optional" autocomplete="tel" inputmode="tel">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="registerEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon" aria-hidden="true"></i>
                                <input type="email" id="registerEmail" name="email" placeholder="you@example.com" autocomplete="email" autocapitalize="none" spellcheck="false" aria-describedby="emailError" required>
                            </div>
                            <span class="error-message" id="emailError">Enter a valid email address.</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="registerPassword">Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                                    <input type="password" id="registerPassword" name="password" placeholder="6+ characters" autocomplete="new-password" aria-describedby="passwordError" required>
                                    <button class="password-toggle" id="registerPasswordToggle" type="button" aria-label="Show password" aria-controls="registerPassword" aria-pressed="false">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <span class="error-message" id="passwordError">Use at least 6 characters.</span>
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Confirm password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                                    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Repeat password" autocomplete="new-password" aria-describedby="confirmPasswordError" required>
                                    <button class="password-toggle" id="confirmPasswordToggle" type="button" aria-label="Show password" aria-controls="confirmPassword" aria-pressed="false">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <span class="error-message" id="confirmPasswordError">Your passwords don't match.</span>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> Create account
                        </button>
                    </form>

                    <div class="form-footer">
                        Already have an account?
                        <button id="showLogin" type="button">Sign in</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle between login and register
        const loginFormDiv = document.getElementById('loginForm');
        const registerFormDiv = document.getElementById('registerForm');
        const toggleBtn = document.getElementById('toggleBtn');
        const showRegisterLink = document.getElementById('showRegister');
        const showLoginLink = document.getElementById('showLogin');

        let isLoginView = true;

        function toggleForms() {
            if (isLoginView) {
                loginFormDiv.classList.remove('active');
                registerFormDiv.classList.add('active');
                toggleBtn.textContent = 'Sign in instead';
                isLoginView = false;
            } else {
                registerFormDiv.classList.remove('active');
                loginFormDiv.classList.add('active');
                toggleBtn.textContent = 'Create an account';
                isLoginView = true;
            }
        }

        toggleBtn.addEventListener('click', toggleForms);
        showRegisterLink.addEventListener('click', toggleForms);
        showLoginLink.addEventListener('click', toggleForms);

        function bindPasswordToggle(buttonId, inputId) {
            const button = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            button.addEventListener('click', function() {
                const showPassword = input.type === 'password';
                input.type = showPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !showPassword);
                icon.classList.toggle('fa-eye-slash', showPassword);
                button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', String(showPassword));
            });
        }

        bindPasswordToggle('loginPasswordToggle', 'loginPassword');
        bindPasswordToggle('registerPasswordToggle', 'registerPassword');
        bindPasswordToggle('confirmPasswordToggle', 'confirmPassword');

        function setFieldValidity(inputId, errorId, isInvalid) {
            const input = document.getElementById(inputId);
            input.classList.toggle('error', isInvalid);
            input.setAttribute('aria-invalid', String(isInvalid));
            document.getElementById(errorId).classList.toggle('show', isInvalid);
        }

        // Login Form Validation and Submit
        const loginFormSubmit = document.getElementById('loginFormSubmit');
        loginFormSubmit.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            let isValid = true;
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const invalidEmail = !emailRegex.test(email);
            setFieldValidity('loginEmail', 'loginEmailError', invalidEmail);
            if (invalidEmail) {
                isValid = false;
            }
            
            // Password validation
            const invalidPassword = password.length < 6;
            setFieldValidity('loginPassword', 'loginPasswordError', invalidPassword);
            if (invalidPassword) {
                isValid = false;
            }
            
            if (isValid) {
                // Prepare data for PHP
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                // Show loading state
                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
                submitBtn.disabled = true;
                
                // Send login request
                fetch('../controllers/admin/admin_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message with role info
                        const roleText = data.admin_data.role === 'superadmin' ? 'Superadmin' : 'Admin';
                        showAlert('loginAlert', `Welcome ${roleText}! Redirecting...`, 'success');
                        
                        // Use the redirect URL from backend response
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showAlert('loginAlert', data.message, 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    showAlert('loginAlert', 'An error occurred. Please try again.', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }
        });

        // Register Form Validation and Submit
        const registerFormSubmit = document.getElementById('registerFormSubmit');
        registerFormSubmit.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('registerUsername').value;
            const email = document.getElementById('registerEmail').value;
            const phone = document.getElementById('registerPhone').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            let isValid = true;
            
            // Username validation
            const invalidUsername = username.length < 3;
            setFieldValidity('registerUsername', 'usernameError', invalidUsername);
            if (invalidUsername) {
                isValid = false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const invalidEmail = !emailRegex.test(email);
            setFieldValidity('registerEmail', 'emailError', invalidEmail);
            if (invalidEmail) {
                isValid = false;
            }
            
            // Password validation
            const invalidPassword = password.length < 6;
            setFieldValidity('registerPassword', 'passwordError', invalidPassword);
            if (invalidPassword) {
                isValid = false;
            }
            
            // Confirm password validation
            const passwordsMismatch = password !== confirmPassword;
            setFieldValidity('confirmPassword', 'confirmPasswordError', passwordsMismatch);
            if (passwordsMismatch) {
                isValid = false;
            }
            
            if (isValid) {
                // Prepare data for PHP
                const formData = new FormData();
                formData.append('username', username);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('password', password);
                
                // Show loading state
                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
                submitBtn.disabled = true;
                
                // Send to PHP
                fetch('../controllers/admin/admin_register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('registerAlert', 'Account created successfully! Please login.', 'success');
                        setTimeout(() => toggleForms(), 2000);
                    } else {
                        showAlert('registerAlert', data.message, 'error');
                    }
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                })
                .catch(error => {
                    showAlert('registerAlert', 'An error occurred. Please try again.', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }
        });

        // Show alert function
        function showAlert(alertId, message, type) {
            const alert = document.getElementById(alertId);
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>
