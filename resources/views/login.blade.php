<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ ($splash ?? false) ? 'Serbaguna UMKM' : 'Login - Serbaguna UMKM' }}</title>

    {{-- Ikon tab browser --}}
    <link rel="icon" type="image/png" href="{{ asset('logo1.jpeg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Palet makna: merah, biru, kuning, hitam, putih */
        :root {
            --red: #E5202E;
            --blue: #2158bd;
            --blue-dark: #0B4FC9;
            --blue-light: #2F6BFF;
            --yellow: #FFD500;
            --black: #000000;
            --white: #FFFFFF;
        }

        * {
            box-sizing: border-box;
        }

        html {
            height: 100%;
            overflow: hidden;
        }

        body {
            background: #f6f5f7;
            font-family: 'Ubuntu', sans-serif;
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        /*
         * Seluruh halaman dikunci seukuran layar (fixed + overflow hidden).
         * Tanpa ini, browser HP menganggap kartu 900px "melebihi layar" lalu memperlebar
         * layar virtualnya, sehingga perhitungan skala meleset dan kartu terpotong.
         */
        #app {
            position: fixed;
            inset: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h1 {
            font-weight: bold;
            margin: 0 0 4px;
            color: var(--black);
        }

        h2 {
            text-align: center;
        }

        p {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 20px 0 30px;
        }

        span {
            font-size: 12px;
        }

        a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
        }

        button {
            border-radius: 20px;
            border: 1px solid var(--blue);
            background-color: var(--blue);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            outline: none;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
        }

        button:active {
            transform: scale(0.95);
        }

        button.ghost {
            background-color: transparent;
            border-color: #FFFFFF;
        }

        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }

        input {
            background-color: #eee;
            border: none;
            border-radius: 4px;
            padding: 12px 15px;
            margin: 8px 0;
            outline: none;
            width: 100%;
            font-family: inherit;
        }

        input:focus {
            box-shadow: 0 0 0 2px var(--blue-light);
        }

        .form-logo {
            width: 170px;
            height: auto;
            margin-bottom: 8px;
        }

        /* Pesan error di dalam form (salah password, email sudah dipakai, dll.) */
        .form-error {
            width: 100%;
            margin: 6px 0 2px;
            padding: 9px 12px;
            border-radius: 8px;
            background: #FDE6E8;
            color: #B4161F;
            font-size: 12.5px;
            line-height: 1.4;
            text-align: left;
        }

        /*
         * Kartu login berukuran tetap 900x580 di semua perangkat.
         * Di layar kecil (HP) kartu DIPERKECIL (scale) lewat JavaScript di bawah,
         * jadi tampilannya identik dengan desktop.
         */
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25),
                        0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 900px;
            height: 580px;
            flex: none;
            transform-origin: center center;
            z-index: 2;
        }

        /* Garis tiga warna di dasar kartu (sama dengan splash screen) */
        .container::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 6px;
            z-index: 200;
            background: linear-gradient(to right);
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .forgot-password {
            transition: letter-spacing .3s ease-in-out;
            margin: 10px 0 14px;
        }

        .forgot-password:hover {
            letter-spacing: 1px;
        }

        /* Teks pemisah + tombol Google (paling bawah form) */
        .or-text {
            margin-top: 18px;
            color: #555;
        }

        .google-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 10px 0 0;
            padding: 10px 26px;
            border: 1px solid #dadce0;
            border-radius: 20px;
            background: #fff;
            color: #3c4043;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            transition: box-shadow .2s ease-in-out, transform 80ms ease-in;
        }

        .google-btn:hover {
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }

        .google-btn:active {
            transform: scale(0.97);
        }

        .google-btn svg {
            width: 18px;
            height: 18px;
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: var(--blue-dark);
            background: linear-gradient(to right, var(--blue-light), var(--blue-dark));
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-panel h1 {
            color: #fff;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        /* ---------- Bentuk hiasan latar (diwarnai sesuai palet) ---------- */
        .background-shape {
            pointer-events: none;
            z-index: 0;
        }

        .square-one {
            position: absolute;
            background: var(--yellow);
            width: 30px;
            height: 30px;
            top: 15%;
            left: 20%;
            transform: rotate(-65deg);
        }

        .square-two {
            position: absolute;
            width: 25px;
            height: 25px;
            top: 55%;
            right: 1%;
            transform: rotate(-65deg);
            background-color: var(--red);
        }

        .square-three {
            position: absolute;
            width: 50px;
            height: 50px;
            top: 30%;
            left: 45%;
            transform: rotate(-65deg);
            background-color: var(--blue-dark);
        }

        .circle-one {
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 100%;
            bottom: -125px;
            left: -125px;
            background-color: var(--blue-light);
        }

        .circle-two {
            position: absolute;
            width: 30px;
            height: 30px;
            bottom: 20%;
            right: 25%;
            border-radius: 100%;
            background: var(--red);
        }

        .triangle-one {
            position: absolute;
            width: 0;
            height: 0;
            border-top: 69px solid transparent;
            border-bottom: 48px solid transparent;
            border-left: 90px solid rgba(255, 255, 255, 0.08);
            top: 45%;
            left: 30%;
            transform: rotate(-100deg);
            background-color: var(--yellow);
        }

        .triangle-two {
            position: absolute;
            width: 0;
            height: 0;
            border-top: 200px solid transparent;
            border-bottom: 130px solid transparent;
            border-left: 180px solid rgba(255, 255, 255, 0.1);
            top: -150px;
            right: 90px;
            transform: rotate(-80deg);
            background-color: var(--blue-dark);
        }

        .triangle-three {
            position: absolute;
            width: 0;
            height: 0;
            border-top: 68px solid transparent;
            border-bottom: 49px solid transparent;
            border-left: 90px solid rgba(255, 255, 255, 0.08);
            right: 10%;
            bottom: 1%;
            transform: rotate(-170deg);
            background: var(--red);
        }

        .triangle-three1 {
            position: absolute;
            width: 0;
            height: 0;
            border-top: 68px solid transparent;
            border-bottom: 49px solid transparent;
            border-left: 90px solid rgba(255, 255, 255, 0.08);
            left: 3%;
            top: 5%;
            transform: rotate(-170deg);
            background: var(--yellow);
        }

        .rectangle-one {
            position: absolute;
            width: 25px;
            height: 50px;
            top: 80%;
            left: 25%;
            transform: rotate(-55deg);
            background-color: var(--blue-light);
        }

        .rectangle-two {
            position: absolute;
            width: 15px;
            height: 30px;
            top: 42%;
            right: 23%;
            transform: rotate(-55deg);
            background-color: var(--red);
        }

        /*
         * ---------- HP (layar sempit) ----------
         * Kartu dibuat selebar layar supaya tulisan besar dan mudah dibaca.
         * Gayanya tetap sama (kartu putih + panel biru + garis tiga warna);
         * bedanya panel biru berada di bawah form dan tombolnya berpindah antara Sign In / Sign Up.
         */
        @media (max-width: 768px) {
            #app {
                overflow-x: hidden;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .container {
                width: calc(100% - 24px);
                max-width: 440px;
                height: auto;
                margin: 12px auto;
                transform: none !important;
            }

            .form-container,
            .container.right-panel-active .sign-in-container,
            .container.right-panel-active .sign-up-container {
                position: static;
                width: 100%;
                height: auto;
                opacity: 1;
                transform: none !important;
                animation: none !important;
                transition: none;
            }

            .sign-up-container { display: none; }
            .container.right-panel-active .sign-in-container { display: none; }
            .container.right-panel-active .sign-up-container { display: block; }

            form { padding: 30px 24px 22px; }
            .form-logo { width: 190px; }

            .overlay-container,
            .container.right-panel-active .overlay-container {
                position: static;
                width: 100%;
                height: auto;
                transform: none !important;
            }

            .overlay,
            .container.right-panel-active .overlay {
                left: 0;
                width: 100%;
                height: auto;
                transform: none !important;
            }

            .overlay-panel,
            .overlay-left,
            .overlay-right,
            .container.right-panel-active .overlay-left,
            .container.right-panel-active .overlay-right {
                position: static;
                width: 100%;
                height: auto;
                padding: 22px 24px 28px;
                transform: none !important;
                display: none;
            }

            .overlay-right { display: flex; }
            .container.right-panel-active .overlay-right { display: none; }
            .container.right-panel-active .overlay-left { display: flex; }

            .overlay-panel p { margin: 8px 0 16px; }
        }

        /* ---------- Splash screen (lapisan di atas halaman login) ---------- */
        #splash {
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            animation: lift 0.9s cubic-bezier(.7, 0, .3, 1) 4.4s forwards;
        }

        #splash .splash-center {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
            z-index: 2;
        }

        #splash .logo-wrap {
            position: relative;
            width: min(80vw, 520px);
            overflow: hidden;
            opacity: 0;
            animation: pop 1.1s cubic-bezier(.34, 1.56, .64, 1) 0.3s forwards;
        }

        #splash .splash-logo {
            display: block;
            width: 100%;
            height: auto;
        }

        /* Kilau cahaya yang menyapu logo */
        #splash .logo-wrap::after {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 40%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .85), transparent);
            transform: skewX(-20deg);
            animation: shine 1.2s ease-in-out 1.7s forwards;
        }

        /* Bar loading 5 warna: merah, biru, kuning, hitam, putih */
        #splash .loader {
            width: min(60vw, 300px);
            height: 8px;
            border-radius: 99px;
            background: #EEF0F3;
            overflow: hidden;
            opacity: 0;
            animation: fadein 0.5s ease-out 1.2s forwards;
        }

        #splash .loader i {
            display: block;
            height: 100%;
            width: 0;
            border-radius: 99px;
            background: linear-gradient(90deg, #E53935 0 25%, #1E5BFF 25% 50%, #FFC400 50% 75%, #111 75% 100%);
            animation: fill 2.8s cubic-bezier(.4, 0, .2, 1) 1.3s forwards;
        }

        #splash .dots { display: flex; gap: 8px; }
        #splash .dots b {
            width: 10px; height: 10px; border-radius: 50%;
            opacity: 0;
            animation: fadein 0.4s ease-out 1.4s forwards, hop 0.9s ease-in-out 1.4s infinite;
        }
        #splash .dots b:nth-child(1) { background: #E53935; }
        #splash .dots b:nth-child(2) { background: #1E5BFF; animation-delay: 1.4s, 1.55s; }
        #splash .dots b:nth-child(3) { background: #FFC400; animation-delay: 1.4s, 1.7s; }
        #splash .dots b:nth-child(4) { background: #111;    animation-delay: 1.4s, 1.85s; }

        /* Bentuk-bentuk berwarna yang muncul satu per satu lalu melayang */
        #splash .bit {
            position: absolute;
            opacity: 0;
            animation: bitin 0.7s cubic-bezier(.34, 1.56, .64, 1) var(--d) forwards,
                       float 3.4s ease-in-out calc(var(--d) + .7s) infinite alternate;
        }
        #splash .bit.circle   { border-radius: 50%; }
        #splash .bit.square   { border-radius: 6px; }
        #splash .bit.tri      { background: none !important; width: 0; height: 0;
                                border-left: calc(var(--s) / 2) solid transparent;
                                border-right: calc(var(--s) / 2) solid transparent;
                                border-bottom: var(--s) solid var(--c); }
        #splash .bit.ring     { background: none !important; border: 5px solid var(--c); border-radius: 50%; }
        #splash .bit:not(.tri) { width: var(--s); height: var(--s); background: var(--c); }

        #splash .splash-bar {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 6px;
            display: flex;
        }

        #splash .splash-bar i { flex: 1; display: block; }
       

        @keyframes pop {
            0%   { transform: translate3d(0, 30px, 0) scale(0.6); opacity: 0; }
            100% { transform: translate3d(0, 0, 0) scale(1);      opacity: 1; }
        }
        @keyframes fadein { to { opacity: 1; } }
        @keyframes fill   { to { width: 100%; } }
        @keyframes shine  { to { left: 130%; } }
        @keyframes hop {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-9px); }
        }
        @keyframes bitin {
            0%   { opacity: 0; transform: scale(0) rotate(-90deg); }
            100% { opacity: .9; transform: scale(1) rotate(0); }
        }
        @keyframes float {
            0%   { translate: 0 0;      rotate: 0deg; }
            100% { translate: 10px -22px; rotate: 24deg; }
        }

        /* Splash terangkat ke atas dan menyingkap halaman login yang sudah ada di bawahnya */
        @keyframes lift {
            0%   { transform: translate3d(0, 0, 0);      visibility: visible; }
            100% { transform: translate3d(0, -100%, 0);  visibility: hidden; }
        }

        /* Hormati pengguna yang mematikan animasi */
        @media (prefers-reduced-motion: reduce) {
            #splash { animation-duration: 0.01s; animation-delay: 1.5s; }
            #splash .logo-wrap { animation: none; opacity: 1; }
            #splash .logo-wrap::after, #splash .dots b, #splash .bit { animation: none; }
            #splash .bit { opacity: .9; }
            #splash .loader, #splash .dots b { opacity: 1; }
            #splash .loader i { animation: none; width: 100%; }
        }
    </style>
</head>
<body>
    <div id="app">
    <div class="background-shape circle-one"></div>
    <div class="background-shape circle-two"></div>
    <div class="background-shape square-one"></div>
    <div class="background-shape square-two"></div>
    <div class="background-shape square-three"></div>
    <div class="background-shape triangle-one"></div>
    <div class="background-shape triangle-two"></div>
    <div class="background-shape triangle-three"></div>
    <div class="background-shape triangle-three1"></div>
    <div class="background-shape rectangle-one"></div>
    <div class="background-shape rectangle-two"></div>

    <div class="container {{ $errors->getBag('register')->any() ? 'right-panel-active' : '' }}" id="container">

        {{-- ===================== SIGN UP ===================== --}}
        <div class="form-container sign-up-container">
            <form action="{{ route('register.submit') }}" method="POST" id="signUpForm">
                @csrf
                <img class="form-logo" src="{{ asset('images/logo2.jpeg') }}" alt="Serbaguna UMKM">
                <h1>Create Account</h1>

                @if ($errors->getBag('register')->any())
                    <div class="form-error">{{ $errors->getBag('register')->first() }}</div>
                @endif

                <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" autocomplete="name" required />
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" autocomplete="email" required />
                <input type="password" name="password" placeholder="Password (min. 8 karakter)" autocomplete="new-password" minlength="8" required />
                <button type="submit">Sign Up</button>

                <span class="or-text">or use your email for registration</span>
                <a href="#" class="google-btn" id="googleSignUp">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continue with Google
                </a>
            </form>
        </div>

        {{-- ===================== SIGN IN ===================== --}}
        <div class="form-container sign-in-container">
            <form action="{{ route('login.submit') }}" method="POST" id="signInForm">
                @csrf
                <img class="form-logo" src="{{ asset('images/logo2.jpeg') }}" alt="Serbaguna UMKM">
                <h1>Sign in</h1>

                @if ($errors->getBag('login')->any())
                    <div class="form-error">{{ $errors->getBag('login')->first() }}</div>
                @endif

                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" autocomplete="email" required />
                <input type="password" name="password" placeholder="Password" autocomplete="current-password" required />
                <a href="#" class="forgot-password">Forgot your password?</a>
                <button type="submit">Sign In</button>

                <span class="or-text">or use your account</span>
                <a href="#" class="google-btn" id="googleSignIn">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continue with Google
                </a>
            </form>
        </div>

        {{-- ===================== PANEL GESER ===================== --}}
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome, Owner!</h1>
                    <p>Senang bertemu kembali sudah memiliki akun owner? Silakan masuk untuk melanjutkan pengelolaan bisnis UMKM Anda.</p>
                    <button class="ghost" id="signIn" type="button">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Welcome, Owner!</h1>
                    <p>Kelola usahamu lebih mudah daftarkan akun owner dan mulai kelola bisnis UMKM Anda dalam satu platform.</p>
                    <button class="ghost" id="signUp" type="button">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    </div>{{-- /#app --}}

    {{-- Splash hanya muncul bila halaman dibuka lewat "/" --}}
    @if ($splash ?? false)
        <div id="splash">
            {{-- Hiasan melayang (merah, biru, kuning, hitam) --}}
            <span class="bit circle" style="--c:#E53935;--s:46px;--d:.2s;top:12%;left:10%"></span>
            <span class="bit square" style="--c:#1E5BFF;--s:38px;--d:.4s;top:20%;right:12%"></span>
            <span class="bit tri"    style="--c:#FFC400;--s:44px;--d:.6s;top:66%;left:8%"></span>
            <span class="bit ring"   style="--c:#111;--s:42px;--d:.8s;top:74%;right:10%"></span>
            <span class="bit square" style="--c:#FFC400;--s:26px;--d:1s;top:8%;left:42%"></span>
            <span class="bit circle" style="--c:#1E5BFF;--s:24px;--d:1.2s;top:86%;left:36%"></span>
            <span class="bit tri"    style="--c:#E53935;--s:34px;--d:1.4s;top:44%;right:5%"></span>
            <span class="bit ring"   style="--c:#E53935;--s:30px;--d:1.6s;top:40%;left:4%"></span>

            <div class="splash-center">
                <div class="logo-wrap">
                    <img class="splash-logo" src="{{ asset('images/logo1.jpeg') }}" alt="Serbaguna UMKM - Satu Platform, Semua Kebutuhan Usaha">
                </div>
                <div class="loader" aria-hidden="true"><i></i></div>
                <div class="dots" aria-hidden="true"><b></b><b></b><b></b><b></b></div>
            </div>
        </div>
    @endif

    <script>
        const container = document.getElementById('container');

        // Geser antara Sign In dan Sign Up
        document.getElementById('signUp').addEventListener('click', function () {
            container.classList.add('right-panel-active');
        });
        document.getElementById('signIn').addEventListener('click', function () {
            container.classList.remove('right-panel-active');
        });

        // Tampilan sama di semua perangkat: kartu diperkecil bila layar tidak cukup lebar/tinggi
        function fitCard() {
            // Layar sempit (HP tegak): pakai tata letak CSS selebar layar, tidak perlu diperkecil
            if (window.matchMedia('(max-width: 768px)').matches) {
                container.style.transform = '';
                return;
            }
            var scale = Math.min(1, (window.innerWidth - 24) / 900, (window.innerHeight - 24) / 580);
            container.style.transform = 'scale(' + scale + ')';
        }
        fitCard();
        window.addEventListener('resize', fitCard);
        window.addEventListener('orientationchange', function () { setTimeout(fitCard, 150); });

        // Tombol Google & "Forgot password" belum terhubung (tahap berikutnya)
        document.querySelectorAll('.google-btn, .forgot-password').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
            });
        });

        @if ($splash ?? false)
        // Setelah splash terangkat: buang lapisan splash dan ganti alamat jadi /login tanpa memuat ulang halaman
        (function () {
            var splash = document.getElementById('splash');
            var done = false;

            function finish() {
                if (done) { return; }
                done = true;
                splash.remove();
                try { history.replaceState(null, '', @json(route('login'))); } catch (e) {}
            }

            splash.addEventListener('animationend', function (e) {
                if (e.animationName === 'lift') { finish(); }
            });
            setTimeout(finish, 7000); // cadangan
        })();
        @endif
    </script>
</body>
</html>