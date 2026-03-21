<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Grosir Roni – Juntinyuat</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        :root {
            --amber: #E8820C;
            --amber-light: #F5A033;
            --amber-dark: #B5610A;
            --cream: #FDF6EC;
            --brown: #3A1F00;
            --brown-mid: #7A4010;
            --gray: #6B7280;
            --gray-light: #F3F4F6;
            --white: #fff;
            --nav-h: 70px;
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        html {
            scroll-behavior: smooth
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cream);
            color: var(--brown);
            overflow-x: hidden;
            cursor: none
        }

        /* ══ CUSTOM CURSOR ══ */
        #cursor {
            position: fixed;
            width: 12px;
            height: 12px;
            background: var(--amber);
            border-radius: 50%;
            pointer-events: none;
            z-index: 99999;
            transform: translate(-50%, -50%);
            transition: transform .1s, background .2s, width .3s, height .3s;
            mix-blend-mode: multiply
        }

        #cursor-ring {
            position: fixed;
            width: 40px;
            height: 40px;
            border: 1.5px solid var(--amber);
            border-radius: 50%;
            pointer-events: none;
            z-index: 99998;
            transform: translate(-50%, -50%);
            transition: transform .06s linear, width .3s, height .3s, opacity .3s;
            opacity: .5
        }

        #cursor.hovered {
            width: 50px;
            height: 50px;
            background: rgba(232, 130, 12, .15);
            mix-blend-mode: multiply
        }

        #cursor-ring.hovered {
            width: 60px;
            height: 60px;
            opacity: .2
        }

        #cursor.clicked {
            transform: translate(-50%, -50%) scale(.75)
        }

        @media(max-width:768px) {

            #cursor,
            #cursor-ring {
                display: none
            }

            body {
                cursor: auto
            }
        }

        /* ══ NOISE OVERLAY ══ */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 9997;
            pointer-events: none;
            opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-repeat: repeat;
            background-size: 128px
        }

        /* ══ SCROLL PROGRESS ══ */
        #progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--amber), var(--amber-light), #fff5);
            z-index: 9999;
            width: 0%;
            transition: width .08s
        }

        /* ══ PAGE LOADER ══ */
        #loader {
            position: fixed;
            inset: 0;
            z-index: 99990;
            background: var(--brown);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
            transition: opacity .6s, visibility .6s
        }

        #loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none
        }

        .loader-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 900;
            color: white;
            letter-spacing: -1px;
            overflow: hidden
        }

        .loader-logo span {
            color: var(--amber)
        }

        .loader-bar-wrap {
            width: 200px;
            height: 2px;
            background: rgba(255, 255, 255, .1);
            border-radius: 2px;
            overflow: hidden
        }

        .loader-bar {
            height: 100%;
            background: var(--amber);
            border-radius: 2px;
            width: 0%;
            transition: width .05s linear
        }

        /* ══ NAVBAR ══ */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: var(--nav-h);
            padding: 0 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(20px);
            background: rgba(253, 246, 236, .88);
            border-bottom: 1px solid rgba(232, 130, 12, .12);
            transition: all .4s
        }

        nav.scrolled {
            background: rgba(253, 246, 236, .98);
            box-shadow: 0 4px 30px rgba(58, 31, 0, .08)
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--brown);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none
        }

        .nav-logo iconify-icon {
            color: var(--amber);
            font-size: 1.5rem;
            transition: transform .4s
        }

        .nav-logo:hover iconify-icon {
            transform: rotate(-15deg) scale(1.1)
        }

        .nav-logo span {
            color: var(--amber)
        }

        .nav-links {
            display: flex;
            gap: 32px;
            list-style: none
        }

        .nav-links a {
            font-size: .87rem;
            font-weight: 500;
            color: var(--brown-mid);
            text-decoration: none;
            letter-spacing: .4px;
            transition: color .2s;
            position: relative;
            padding: 4px 0
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--amber);
            transition: width .3s cubic-bezier(.16, 1, .3, 1)
        }

        .nav-links a:hover {
            color: var(--amber)
        }

        .nav-links a:hover::after {
            width: 100%
        }

        .nav-cta {
            background: var(--amber);
            color: white;
            padding: 9px 22px;
            border-radius: 50px;
            font-size: .87rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .3s;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            position: relative;
            overflow: hidden
        }

        .nav-cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, .25) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform .5s
        }

        .nav-cta:hover::before {
            transform: translateX(100%)
        }

        .nav-cta:hover {
            background: var(--amber-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232, 130, 12, .35)
        }

        /* HAMBURGER */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: none;
            padding: 8px;
            border: none;
            background: none;
            z-index: 1001
        }

        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--brown);
            border-radius: 2px;
            transition: all .35s cubic-bezier(.16, 1, .3, 1)
        }

        .hamburger.active span:nth-child(1) {
            transform: translateY(7px) rotate(45deg)
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0)
        }

        .hamburger.active span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg)
        }

        /* MOBILE MENU */
        .mobile-menu {
            position: fixed;
            inset: 0;
            z-index: 999;
            background: var(--cream);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 32px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-20px);
            transition: all .4s cubic-bezier(.16, 1, .3, 1)
        }

        .mobile-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0)
        }

        .mobile-menu a {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--brown);
            text-decoration: none;
            transition: color .2s
        }

        .mobile-menu a:hover {
            color: var(--amber)
        }

        .mobile-menu .mm-cta {
            background: var(--amber);
            color: white !important;
            padding: 14px 40px;
            border-radius: 50px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem !important;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 8px
        }

        /* ══ HERO ══ */
        #hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: calc(var(--nav-h) + 50px) 60px 80px;
            position: relative;
            overflow: hidden
        }

        .hero-photo {
            position: absolute;
            inset: 0;
            z-index: 0
        }

        .hero-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            will-change: transform
        }

        .hero-photo::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, rgba(253, 246, 236, .97) 0%, rgba(253, 246, 236, .92) 45%, rgba(253, 246, 236, .55) 72%, rgba(253, 246, 236, .1) 100%)
        }

        /* PARTICLES */
        #particles {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: var(--amber);
            opacity: 0;
            animation: particleFloat linear infinite
        }

        @keyframes particleFloat {
            0% {
                opacity: 0;
                transform: translateY(0) scale(0)
            }

            15% {
                opacity: .6
            }

            85% {
                opacity: .3
            }

            100% {
                opacity: 0;
                transform: translateY(-80vh) scale(1.5)
            }
        }

        .hero-row {
            display: flex;
            align-items: center;
            gap: 50px;
            width: 100%
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 640px
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(232, 130, 12, .12);
            border: 1px solid rgba(232, 130, 12, .3);
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 24px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--amber-dark);
            letter-spacing: .8px;
            text-transform: uppercase;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp .8s .8s forwards
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--amber);
            animation: blink 1.5s infinite;
            display: inline-block
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
        }

        /* SPLIT TEXT HERO TITLE */
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 5.5vw, 5rem);
            font-weight: 900;
            line-height: 1.06;
            color: var(--brown);
            margin-bottom: 20px
        }

        .hero-title .word {
            display: inline-block;
            overflow: hidden
        }

        .hero-title .word-inner {
            display: inline-block;
            transform: translateY(110%);
            animation: wordReveal .7s cubic-bezier(.16, 1, .3, 1) forwards
        }

        .hero-title em {
            font-style: normal;
            color: var(--amber)
        }

        @keyframes wordReveal {
            to {
                transform: translateY(0)
            }
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .hero-desc {
            font-size: 1rem;
            line-height: 1.75;
            color: var(--brown-mid);
            max-width: 500px;
            margin-bottom: 36px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp .8s 1.4s forwards
        }

        .hero-btns {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp .8s 1.7s forwards
        }

        /* MAGNETIC BUTTON */
        .btn-primary,
        .btn-secondary,
        .btn-wa,
        .btn-outline-w,
        .nav-cta {
            cursor: none
        }

        .btn-primary {
            background: var(--amber);
            color: white;
            padding: 13px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: .93rem;
            text-decoration: none;
            transition: all .3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, .3) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform .5s
        }

        .btn-primary:hover::before {
            transform: translateX(100%)
        }

        .btn-primary:hover {
            background: var(--amber-dark);
            box-shadow: 0 8px 25px rgba(232, 130, 12, .4)
        }

        .btn-secondary {
            background: transparent;
            color: var(--brown);
            padding: 13px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: .93rem;
            text-decoration: none;
            border: 2px solid rgba(58, 31, 0, .18);
            transition: all .3s;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .btn-secondary:hover {
            border-color: var(--amber);
            color: var(--amber)
        }

        /* HERO STATS – 3D TILT */
        .hero-stats {
            position: relative;
            z-index: 1;
            margin-left: auto;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 32px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(58, 31, 0, .12);
            min-width: 250px;
            opacity: 0;
            transform: translateX(60px) rotateY(-15deg);
            animation: statReveal 1s 1s cubic-bezier(.16, 1, .3, 1) forwards;
            transform-style: preserve-3d;
            transition: transform .1s ease-out, box-shadow .3s;
            will-change: transform
        }

        @keyframes statReveal {
            to {
                opacity: 1;
                transform: translateX(0) rotateY(0)
            }
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 14px
        }

        .stat-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(232, 130, 12, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .3s
        }

        .stat-item:hover .stat-icon-box {
            background: rgba(232, 130, 12, .2);
            transform: scale(1.1) rotate(-8deg)
        }

        .stat-icon-box iconify-icon {
            font-size: 1.35rem;
            color: var(--amber)
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 900;
            color: var(--amber);
            line-height: 1
        }

        .stat-label {
            font-size: .75rem;
            color: var(--gray);
            font-weight: 500;
            margin-top: 2px
        }

        .stat-divider {
            height: 1px;
            background: var(--gray-light)
        }

        /* ══ MARQUEE ══ */
        .marquee-section {
            background: var(--amber);
            padding: 15px 0;
            overflow: hidden;
            position: relative
        }

        .marquee-section::before,
        .marquee-section::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 80px;
            z-index: 2;
            pointer-events: none
        }

        .marquee-section::before {
            left: 0;
            background: linear-gradient(to right, var(--amber), transparent)
        }

        .marquee-section::after {
            right: 0;
            background: linear-gradient(to left, var(--amber), transparent)
        }

        .marquee-track {
            display: flex;
            width: max-content;
            animation: marquee 22s linear infinite
        }

        .marquee-track:hover {
            animation-play-state: paused
        }

        @keyframes marquee {
            0% {
                transform: translateX(0)
            }

            100% {
                transform: translateX(-50%)
            }
        }

        .marquee-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 30px;
            font-weight: 700;
            font-size: .85rem;
            color: white;
            letter-spacing: .8px;
            text-transform: uppercase;
            white-space: nowrap;
            transition: transform .2s
        }

        .marquee-item:hover {
            transform: scale(1.08)
        }

        .marquee-item iconify-icon {
            font-size: 1rem;
            opacity: .85
        }

        .msep {
            color: rgba(255, 255, 255, .35)
        }

        /* ══ SECTION COMMON ══ */
        section {
            padding: 90px 60px
        }

        .section-label {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--amber);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .section-label::before {
            content: '';
            width: 22px;
            height: 2px;
            background: var(--amber);
            display: inline-block;
            transition: width .4s
        }

        .section-label:hover::before {
            width: 40px
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.9rem, 3.5vw, 3rem);
            font-weight: 900;
            line-height: 1.15;
            color: var(--brown)
        }

        .section-title em {
            font-style: italic;
            color: var(--amber)
        }

        /* REVEAL */
        .reveal {
            opacity: 0;
            transform: translateY(36px);
            transition: opacity .75s cubic-bezier(.16, 1, .3, 1), transform .75s cubic-bezier(.16, 1, .3, 1)
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0)
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-45px);
            transition: opacity .75s cubic-bezier(.16, 1, .3, 1), transform .75s cubic-bezier(.16, 1, .3, 1)
        }

        .reveal-left.visible {
            opacity: 1;
            transform: translateX(0)
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(45px);
            transition: opacity .75s cubic-bezier(.16, 1, .3, 1), transform .75s cubic-bezier(.16, 1, .3, 1)
        }

        .reveal-right.visible {
            opacity: 1;
            transform: translateX(0)
        }

        .d1 {
            transition-delay: .1s
        }

        .d2 {
            transition-delay: .2s
        }

        .d3 {
            transition-delay: .3s
        }

        .d4 {
            transition-delay: .4s
        }

        .d5 {
            transition-delay: .5s
        }

        .d6 {
            transition-delay: .6s
        }

        /* ══ ABOUT ══ */
        #about {
            background: white
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 70px;
            align-items: center;
            margin-top: 55px
        }

        .about-visual {
            position: relative;
            height: 480px
        }

        .about-img-main {
            width: 78%;
            height: 88%;
            position: absolute;
            bottom: 0;
            right: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(58, 31, 0, .15)
        }

        .about-img-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .6s
        }

        .about-img-main:hover img {
            transform: scale(1.04)
        }

        .about-img-accent {
            width: 52%;
            height: 52%;
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 50px rgba(58, 31, 0, .2);
            z-index: 1
        }

        .about-img-accent img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .6s
        }

        .about-img-accent:hover img {
            transform: scale(1.06)
        }

        .about-badge-float {
            position: absolute;
            bottom: 28px;
            left: -8px;
            z-index: 2;
            background: white;
            border-radius: 16px;
            padding: 13px 17px;
            box-shadow: 0 10px 40px rgba(58, 31, 0, .15);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: floatBadge 3.5s ease-in-out infinite
        }

        @keyframes floatBadge {

            0%,
            100% {
                transform: translateY(0) rotate(-.5deg)
            }

            50% {
                transform: translateY(-10px) rotate(.5deg)
            }
        }

        .badge-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--amber), var(--amber-dark));
            display: flex;
            align-items: center;
            justify-content: center
        }

        .badge-icon-box iconify-icon {
            font-size: 1.3rem;
            color: white
        }

        .badge-text {
            font-size: .76rem;
            color: var(--gray)
        }

        .badge-text strong {
            display: block;
            font-size: .95rem;
            color: var(--brown)
        }

        .about-text p {
            font-size: .97rem;
            line-height: 1.8;
            color: var(--gray);
            margin-bottom: 16px
        }

        .about-features {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 28px
        }

        /* ── FEATURE ITEM ── */
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 14px;
            border-radius: 14px;
            transition: all .35s cubic-bezier(.16, 1, .3, 1);
            cursor: default;
            position: relative;
            overflow: hidden
        }

        .feature-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(232, 130, 12, .05);
            border-radius: 14px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s cubic-bezier(.16, 1, .3, 1)
        }

        .feature-item:hover::before {
            transform: scaleX(1)
        }

        .feature-item:hover {
            transform: translateX(6px)
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(232, 130, 12, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .35s cubic-bezier(.16, 1, .3, 1);
            position: relative;
            z-index: 1
        }

        .feature-item:hover .feature-icon {
            background: var(--amber);
            transform: rotate(-8deg) scale(1.1)
        }

        .feature-icon iconify-icon {
            font-size: 1.25rem;
            color: var(--amber);
            transition: color .3s
        }

        .feature-item:hover .feature-icon iconify-icon {
            color: white
        }

        .feature-body {
            position: relative;
            z-index: 1
        }

        .feature-body strong {
            font-weight: 700;
            color: var(--brown);
            font-size: .93rem
        }

        .feature-body p {
            font-size: .82rem;
            color: var(--gray);
            margin-top: 2px;
            line-height: 1.5
        }

        /* ══ PRODUCTS ══ */
        #produk {
            background: var(--cream);
            padding-bottom: 90px
        }

        .carousel-wrap {
            position: relative;
            margin-top: 48px
        }

        .carousel-container {
            overflow: hidden;
            cursor: none;
            user-select: none
        }

        .carousel-container.dragging {
            cursor: none
        }

        .carousel-track {
            display: flex;
            gap: 22px;
            animation: infiniteScroll 38s linear infinite;
            width: max-content
        }

        .carousel-track.paused,
        .carousel-track:hover {
            animation-play-state: paused
        }

        @keyframes infiniteScroll {
            0% {
                transform: translateX(0)
            }

            100% {
                transform: translateX(-50%)
            }
        }

        /* 3D TILT CARD */
        .product-card {
            min-width: 262px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(58, 31, 0, .06);
            transition: box-shadow .4s cubic-bezier(.16, 1, .3, 1);
            flex-shrink: 0;
            cursor: none;
            transform-style: preserve-3d;
            will-change: transform;
            position: relative
        }

        .product-card:hover {
            box-shadow: 0 25px 60px rgba(58, 31, 0, .18)
        }

        .product-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgba(232, 130, 12, .12) 0%, transparent 70%);
            opacity: 0;
            transition: opacity .3s;
            pointer-events: none
        }

        .product-card:hover::after {
            opacity: 1
        }

        .product-img {
            height: 185px;
            overflow: hidden;
            position: relative
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .55s
        }

        .product-card:hover .product-img img {
            transform: scale(1.08)
        }

        .product-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(58, 31, 0, .07))
        }

        .product-info {
            padding: 17px 19px
        }

        .product-info h3 {
            font-weight: 700;
            font-size: .96rem;
            color: var(--brown);
            margin-bottom: 4px
        }

        .product-info p {
            font-size: .8rem;
            color: var(--gray);
            line-height: 1.5
        }

        .product-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 9px;
            padding: 4px 11px;
            background: rgba(232, 130, 12, .1);
            color: var(--amber-dark);
            border-radius: 50px;
            font-size: .72rem;
            font-weight: 600;
            transition: background .25s
        }

        .product-card:hover .product-tag {
            background: rgba(232, 130, 12, .2)
        }

        .fade-l {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 100px;
            z-index: 2;
            background: linear-gradient(to right, var(--cream), transparent)
        }

        .fade-r {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 100px;
            z-index: 2;
            background: linear-gradient(to left, var(--cream), transparent)
        }

        /* ══ SERVICES ══ */
        #layanan {
            background: var(--brown)
        }

        #layanan .section-label {
            color: var(--amber-light)
        }

        #layanan .section-label::before {
            background: var(--amber-light)
        }

        #layanan .section-title {
            color: white
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-top: 48px
        }

        /* SPOTLIGHT CARDS */
        .service-card {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 20px;
            padding: 30px;
            transition: all .4s cubic-bezier(.16, 1, .3, 1);
            position: relative;
            overflow: hidden;
            cursor: default;
            transform-style: preserve-3d;
            will-change: transform
        }

        .service-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--amber) 0%, var(--amber-dark) 100%);
            opacity: 0;
            transition: opacity .4s
        }

        .service-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgba(255, 255, 255, .15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity .3s;
            pointer-events: none
        }

        .service-card:hover::before {
            opacity: 1
        }

        .service-card:hover::after {
            opacity: 1
        }

        .service-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: var(--amber);
            box-shadow: 0 25px 60px rgba(0, 0, 0, .35)
        }

        .sc {
            position: relative;
            z-index: 1
        }

        .service-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            transition: all .35s cubic-bezier(.16, 1, .3, 1)
        }

        .service-card:hover .service-icon {
            background: rgba(255, 255, 255, .25);
            transform: rotate(-10deg) scale(1.15)
        }

        .service-icon iconify-icon {
            font-size: 1.75rem;
            color: white
        }

        .service-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            margin-bottom: 9px
        }

        .service-card p {
            font-size: .84rem;
            color: rgba(255, 255, 255, .65);
            line-height: 1.65
        }

        /* ══ NUMBERS ══ */
        #angka {
            background: linear-gradient(135deg, var(--amber) 0%, var(--amber-dark) 100%);
            padding: 75px 60px;
            position: relative;
            overflow: hidden
        }

        #angka::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")
        }

        .angka-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 36px;
            position: relative;
            z-index: 1
        }

        .angka-item {
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .35s cubic-bezier(.16, 1, .3, 1)
        }

        .angka-item:hover {
            transform: translateY(-6px)
        }

        .angka-icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .35s;
            backdrop-filter: blur(4px)
        }

        .angka-item:hover .angka-icon {
            background: rgba(255, 255, 255, .25);
            transform: rotate(-10deg) scale(1.1)
        }

        .angka-icon iconify-icon {
            font-size: 1.85rem;
            color: white
        }

        .angka-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.6rem;
            font-weight: 900;
            color: white;
            line-height: 1
        }

        .angka-plus {
            color: rgba(255, 255, 255, .7)
        }

        .angka-label {
            font-size: .83rem;
            color: rgba(255, 255, 255, .8);
            font-weight: 500;
            margin-top: 3px
        }

        /* ══ TESTIMONIALS ══ */
        #testimoni {
            background: white
        }

        .testi-wrap {
            position: relative;
            margin-top: 48px;
            overflow: hidden
        }

        .testi-track {
            display: flex;
            gap: 26px;
            animation: testiScroll 30s linear infinite;
            width: max-content
        }

        .testi-track:hover {
            animation-play-state: paused
        }

        @keyframes testiScroll {
            0% {
                transform: translateX(0)
            }

            100% {
                transform: translateX(-50%)
            }
        }

        .testi-card {
            min-width: 330px;
            background: var(--cream);
            border-radius: 20px;
            padding: 26px;
            border: 1px solid rgba(232, 130, 12, .12);
            flex-shrink: 0;
            transition: all .4s cubic-bezier(.16, 1, .3, 1);
            position: relative;
            overflow: hidden;
            cursor: none
        }

        .testi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(232, 130, 12, .08), transparent);
            opacity: 0;
            transition: opacity .4s
        }

        .testi-card:hover::before {
            opacity: 1
        }

        .testi-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 60px rgba(58, 31, 0, .12);
            border-color: rgba(232, 130, 12, .35)
        }

        .stars {
            display: flex;
            gap: 3px;
            margin-bottom: 12px
        }

        .stars iconify-icon {
            font-size: .95rem;
            color: var(--amber);
            animation: starShimmer 2s infinite
        }

        .stars iconify-icon:nth-child(2) {
            animation-delay: .1s
        }

        .stars iconify-icon:nth-child(3) {
            animation-delay: .2s
        }

        .stars iconify-icon:nth-child(4) {
            animation-delay: .3s
        }

        .stars iconify-icon:nth-child(5) {
            animation-delay: .4s
        }

        @keyframes starShimmer {

            0%,
            80%,
            100% {
                opacity: 1;
                transform: scale(1)
            }

            90% {
                opacity: .7;
                transform: scale(1.2)
            }
        }

        .testi-text {
            font-size: .88rem;
            line-height: 1.7;
            color: var(--gray);
            margin-bottom: 18px;
            font-style: italic;
            position: relative;
            z-index: 1
        }

        .testi-author {
            display: flex;
            align-items: center;
            gap: 11px;
            position: relative;
            z-index: 1
        }

        .testi-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid rgba(232, 130, 12, .2);
            transition: border-color .3s
        }

        .testi-card:hover .testi-avatar {
            border-color: var(--amber)
        }

        .testi-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s
        }

        .testi-card:hover .testi-avatar img {
            transform: scale(1.1)
        }

        .testi-info strong {
            font-size: .88rem;
            color: var(--brown);
            display: block
        }

        .testi-info span {
            font-size: .76rem;
            color: var(--gray)
        }

        .fade-lw {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 80px;
            z-index: 2;
            background: linear-gradient(to right, white, transparent)
        }

        .fade-rw {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 80px;
            z-index: 2;
            background: linear-gradient(to left, white, transparent)
        }

        /* ══ CTA ══ */
        #cta {
            background: var(--cream);
            text-align: center;
            padding: 110px 60px
        }

        .cta-box {
            background: var(--brown);
            border-radius: 32px;
            padding: 75px;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            transition: transform .4s, box-shadow .4s
        }

        .cta-box:hover {
            transform: translateY(-6px);
            box-shadow: 0 40px 100px rgba(58, 31, 0, .25)
        }

        .cta-box::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E")
        }

        /* animated border */
        .cta-box::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 34px;
            background: conic-gradient(from var(--angle, 0deg), transparent 60%, var(--amber) 80%, var(--amber-light) 85%, transparent 95%);
            z-index: -1;
            animation: rotateBorder 4s linear infinite;
            opacity: 0;
            transition: opacity .4s
        }

        .cta-box:hover::after {
            opacity: 1
        }

        @property --angle {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false
        }

        @keyframes rotateBorder {
            to {
                --angle: 360deg
            }
        }

        .cta-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 900;
            color: white;
            margin-bottom: 14px;
            position: relative
        }

        .cta-box h2 em {
            font-style: italic;
            color: var(--amber-light)
        }

        .cta-box p {
            font-size: .97rem;
            color: rgba(255, 255, 255, .62);
            margin-bottom: 36px;
            line-height: 1.7;
            position: relative
        }

        .cta-btns {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative
        }

        .btn-wa {
            background: #25D366;
            color: white;
            padding: 13px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: .93rem;
            text-decoration: none;
            transition: all .3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden
        }

        .btn-wa::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, .25) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform .5s
        }

        .btn-wa:hover::before {
            transform: translateX(100%)
        }

        .btn-wa:hover {
            background: #1da851;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, .35)
        }

        .btn-outline-w {
            background: transparent;
            color: white;
            padding: 13px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: .93rem;
            text-decoration: none;
            border: 2px solid rgba(255, 255, 255, .3);
            transition: all .3s;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .btn-outline-w:hover {
            border-color: white;
            background: rgba(255, 255, 255, .1);
            transform: translateY(-2px)
        }

        /* ══ FOOTER ══ */
        footer {
            background: #1A0D00;
            padding: 60px 60px 28px
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 48px
        }

        .footer-brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 900;
            color: white;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 13px
        }

        .footer-brand-logo iconify-icon {
            color: var(--amber)
        }

        .footer-brand-logo span {
            color: var(--amber)
        }

        .footer-brand p {
            font-size: .83rem;
            color: rgba(255, 255, 255, .48);
            line-height: 1.7;
            max-width: 270px
        }

        .footer-col h4 {
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--amber);
            margin-bottom: 18px
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 9px
        }

        .footer-col ul li a {
            font-size: .83rem;
            color: rgba(255, 255, 255, .48);
            text-decoration: none;
            transition: all .25s cubic-bezier(.16, 1, .3, 1);
            display: flex;
            align-items: center;
            gap: 7px
        }

        .footer-col ul li a iconify-icon {
            font-size: .9rem;
            flex-shrink: 0;
            transition: transform .25s
        }

        .footer-col ul li a:hover {
            color: white;
            padding-left: 6px
        }

        .footer-col ul li a:hover iconify-icon {
            transform: translateX(3px)
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .07);
            padding-top: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px
        }

        .footer-bottom p {
            font-size: .78rem;
            color: rgba(255, 255, 255, .28)
        }

        .social-icons {
            display: flex;
            gap: 9px
        }

        .social-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .07);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: rgba(255, 255, 255, .48);
            transition: all .3s cubic-bezier(.16, 1, .3, 1);
            cursor: none
        }

        .social-icon iconify-icon {
            font-size: 1.05rem
        }

        .social-icon:hover {
            background: var(--amber);
            color: white;
            transform: translateY(-4px) rotate(-8deg)
        }

        /* ══ FLOATING WA ══ */
        .wa-float {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 900;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #25D366;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 30px rgba(37, 211, 102, .5);
            text-decoration: none;
            transition: all .35s;
            cursor: none
        }

        .wa-float::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid rgba(37, 211, 102, .4);
            animation: waPing 2s ease-out infinite
        }

        .wa-float::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid rgba(37, 211, 102, .2);
            animation: waPing 2s ease-out .4s infinite
        }

        @keyframes waPing {
            0% {
                transform: scale(1);
                opacity: 1
            }

            100% {
                transform: scale(1.5);
                opacity: 0
            }
        }

        .wa-float iconify-icon {
            font-size: 1.8rem;
            color: white;
            transition: transform .35s cubic-bezier(.16, 1, .3, 1)
        }

        .wa-float:hover {
            transform: scale(1.12);
            box-shadow: 0 12px 40px rgba(37, 211, 102, .6)
        }

        .wa-float:hover iconify-icon {
            transform: rotate(10deg) scale(1.1)
        }

        /* ══ BACK TO TOP ══ */
        .back-top {
            position: fixed;
            bottom: 100px;
            right: 28px;
            z-index: 900;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--brown);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: none;
            box-shadow: 0 6px 20px rgba(58, 31, 0, .25);
            transition: all .35s cubic-bezier(.16, 1, .3, 1);
            opacity: 0;
            transform: translateY(16px);
            pointer-events: none
        }

        .back-top.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all
        }

        .back-top:hover {
            background: var(--amber);
            transform: translateY(-4px)
        }

        .back-top iconify-icon {
            font-size: 1.2rem;
            color: white;
            transition: transform .3s
        }

        .back-top:hover iconify-icon {
            transform: translateY(-2px)
        }

        /* TOOLTIP */
        [data-tip] {
            position: relative
        }

        [data-tip]::after {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: var(--brown);
            color: white;
            font-size: .72rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s
        }

        [data-tip]:hover::after {
            opacity: 1
        }

        /* SCROLL INDICATOR */
        .scroll-indicator {
            position: absolute;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            opacity: 0;
            animation: fadeUp .8s 2.2s forwards
        }

        .scroll-indicator span {
            font-size: .7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--brown-mid);
            font-weight: 600
        }

        .scroll-line {
            width: 1px;
            height: 40px;
            background: linear-gradient(to bottom, var(--amber), transparent);
            animation: scrollPulse 1.5s ease-in-out infinite
        }

        @keyframes scrollPulse {

            0%,
            100% {
                opacity: .4;
                transform: scaleY(.6)
            }

            50% {
                opacity: 1;
                transform: scaleY(1)
            }
        }

        /* ══ MOBILE ══ */
        @media(max-width:1024px) {
            nav {
                padding: 0 32px
            }

            section {
                padding: 75px 36px
            }

            #hero {
                padding: calc(var(--nav-h)+40px) 36px 70px
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 36px
            }

            .angka-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 28px
            }

            .services-grid {
                grid-template-columns: 1fr 1fr
            }
        }

        @media(max-width:768px) {
            nav {
                padding: 0 20px
            }

            .nav-links,
            .nav-cta {
                display: none
            }

            .hamburger {
                display: flex
            }

            section {
                padding: 60px 20px
            }

            #hero {
                padding: calc(var(--nav-h)+30px) 20px 60px
            }

            .hero-row {
                flex-direction: column;
                gap: 32px
            }

            .hero-stats {
                min-width: unset;
                width: 100%;
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 16px;
                padding: 24px
            }

            .stat-item {
                flex: 1;
                min-width: 120px
            }

            .stat-divider {
                display: none
            }

            .about-grid {
                grid-template-columns: 1fr;
                gap: 36px
            }

            .about-visual {
                height: 320px
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 16px
            }

            .angka-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px
            }

            .angka-item {
                flex-direction: column;
                text-align: center;
                gap: 10px
            }

            .angka-number {
                font-size: 2rem
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center
            }

            .cta-box {
                padding: 42px 24px;
                border-radius: 24px
            }

            #cta {
                padding: 70px 20px
            }

            #angka {
                padding: 60px 20px
            }

            .wa-float {
                bottom: 20px;
                right: 20px;
                width: 52px;
                height: 52px
            }

            .back-top {
                bottom: 86px;
                right: 20px
            }
        }

        @media(max-width:480px) {
            .hero-stats {
                flex-direction: column
            }

            .stat-item {
                min-width: unset
            }

            .stat-divider {
                display: block
            }

            .product-card {
                min-width: 240px
            }

            .testi-card {
                min-width: 290px
            }

            .about-visual {
                height: 260px
            }

            .angka-grid {
                grid-template-columns: 1fr 1fr
            }
        }

        iconify-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center
        }
    </style>
</head>

<body>

    <!-- PAGE LOADER -->
    <div id="loader">
        <div class="loader-logo">Toko<span>Roni</span></div>
        <div class="loader-bar-wrap">
            <div class="loader-bar" id="loaderBar"></div>
        </div>
    </div>

    <!-- CUSTOM CURSOR -->
    <div id="cursor"></div>
    <div id="cursor-ring"></div>

    <!-- SCROLL PROGRESS -->
    <div id="progress-bar"></div>

    <!-- FLOATING WA -->
    <a href="https://wa.me/6281234567890" class="wa-float" target="_blank" data-tip="Chat WhatsApp">
        <iconify-icon icon="ic:baseline-whatsapp"></iconify-icon>
    </a>

    <!-- BACK TO TOP -->
    <button class="back-top" id="backTop" aria-label="Kembali ke atas">
        <iconify-icon icon="tabler:arrow-up"></iconify-icon>
    </button>

    <!-- NAVBAR -->
    <nav id="navbar">
        <a href="#" class="nav-logo">
            <iconify-icon icon="fluent:store-24-filled"></iconify-icon>
            Toko<span>Roni</span>
        </a>
        <ul class="nav-links">
            <li><a href="#about">Tentang</a></li>
            <li><a href="#produk">Produk</a></li>
            <li><a href="#layanan">Layanan</a></li>
            <li><a href="#testimoni">Testimoni</a></li>
        </ul>
        <a href="https://wa.me/6281234567890" class="nav-cta" target="_blank">
            <iconify-icon icon="ic:baseline-whatsapp"></iconify-icon> Hubungi Kami
        </a>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- MOBILE MENU -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#about" class="mm-link">Tentang</a>
        <a href="#produk" class="mm-link">Produk</a>
        <a href="#layanan" class="mm-link">Layanan</a>
        <a href="#testimoni" class="mm-link">Testimoni</a>
        <a href="https://wa.me/6281234567890" class="mm-cta" target="_blank">
            <iconify-icon icon="ic:baseline-whatsapp"></iconify-icon> Hubungi Kami
        </a>
    </div>

    <!-- HERO -->
    <section id="hero">
        <div id="particles"></div>
        <div class="hero-photo" id="heroPhoto">
            <img src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=1600&q=85&auto=format&fit=crop"
                alt="Toko Grosir Roni" loading="eager" id="heroBg">
        </div>
        <div class="hero-row">
            <div class="hero-content">
                <div class="hero-badge"><span class="dot"></span> Toko Grosir Terpercaya · Juntinyuat</div>
                <h1 class="hero-title" id="heroTitle">Pusat Grosir <em>Terlengkap</em> di Juntinyuat</h1>
                <p class="hero-desc">Toko Grosir Roni hadir sebagai mitra belanja terpercaya untuk kebutuhan sembako,
                    produk rumah tangga, dan kebutuhan usaha Anda. Harga bersaing, stok melimpah, pelayanan ramah sejak
                    2005.</p>
                <div class="hero-btns">
                    <a href="#produk" class="btn-primary magnetic">
                        <iconify-icon icon="tabler:shopping-cart"></iconify-icon> Lihat Produk
                        <iconify-icon icon="tabler:arrow-right"></iconify-icon>
                    </a>
                    <a href="#about" class="btn-secondary magnetic">
                        <iconify-icon icon="tabler:info-circle"></iconify-icon> Tentang Kami
                    </a>
                </div>
            </div>
            <div class="hero-stats" id="heroStats">
                <div class="stat-item" data-tip="Berdiri sejak 2005">
                    <div class="stat-icon-box"><iconify-icon icon="tabler:calendar-star"></iconify-icon></div>
                    <div>
                        <div class="stat-number">19+</div>
                        <div class="stat-label">Tahun Berpengalaman</div>
                    </div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item" data-tip="Pelanggan aktif">
                    <div class="stat-icon-box"><iconify-icon icon="tabler:users-group"></iconify-icon></div>
                    <div>
                        <div class="stat-number"><?php echo e(number_format($stats['members'])); ?></div>
                        <div class="stat-label">Pelanggan Setia</div>
                    </div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item" data-tip="Stok selalu tersedia">
                    <div class="stat-icon-box"><iconify-icon icon="tabler:package"></iconify-icon></div>
                    <div>
                        <div class="stat-number"><?php echo e(number_format($stats['products'])); ?></div>
                        <div class="stat-label">Produk Tersedia</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-line"></div>
            <span>Scroll</span>
        </div>
    </section>

    <!-- MARQUEE -->
    <div class="marquee-section">
        <div class="marquee-track">
            <div class="marquee-item"><iconify-icon icon="ph:grains-fill"></iconify-icon> Sembako <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="mdi:home-heart"></iconify-icon> Perawatan Rumah <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="ph:bowl-food-fill"></iconify-icon> Makanan &amp; Minuman
                <span class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="mdi:soap"></iconify-icon> Perlengkapan Mandi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:truck-delivery"></iconify-icon> Antar ke Lokasi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:rosette-discount"></iconify-icon> Harga Terbaik <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:building-store"></iconify-icon> Grosir &amp; Eceran
                <span class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="ph:grains-fill"></iconify-icon> Sembako <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="mdi:home-heart"></iconify-icon> Perawatan Rumah <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="ph:bowl-food-fill"></iconify-icon> Makanan &amp; Minuman
                <span class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="mdi:soap"></iconify-icon> Perlengkapan Mandi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:truck-delivery"></iconify-icon> Antar ke Lokasi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:rosette-discount"></iconify-icon> Harga Terbaik <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:building-store"></iconify-icon> Grosir &amp; Eceran
                <span class="msep">&nbsp;✦&nbsp;</span></div>
        </div>
    </div>

    <!-- ABOUT -->
    <section id="about">
        <div class="section-label reveal">Tentang Kami</div>
        <div class="about-grid">
            <div class="about-visual reveal-left">
                <div class="about-img-main">
                    <img src="https://images.unsplash.com/photo-1534723452862-4c874018d66d?w=800&q=80&auto=format&fit=crop"
                        alt="Interior Toko">
                </div>
                <div class="about-img-accent">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&q=80&auto=format&fit=crop"
                        alt="Belanja">
                </div>
                <div class="about-badge-float">
                    <div class="badge-icon-box"><iconify-icon icon="tabler:star-filled"></iconify-icon></div>
                    <div class="badge-text"><strong>Rating 4.9/5</strong>dari 2000+ ulasan</div>
                </div>
            </div>
            <div class="about-text reveal-right">
                <div class="section-label">Siapa Kami?</div>
                <h2 class="section-title">Toko Grosir <em>Kepercayaan</em><br>Warga Juntinyuat</h2>
                <p style="margin-top:22px;">Toko Grosir Roni berdiri sejak tahun 2005 di Juntinyuat, Indramayu. Berawal
                    dari usaha kecil keluarga, kini kami telah berkembang menjadi pusat grosir terbesar dan terpercaya
                    di wilayah ini.</p>
                <p>Kami berkomitmen menyediakan produk berkualitas dengan harga grosir yang kompetitif, mulai dari
                    sembako, kebutuhan rumah tangga, hingga produk untuk usaha kecil dan warung.</p>
                <div class="about-features">
                    <div class="feature-item reveal d1">
                        <div class="feature-icon"><iconify-icon icon="tabler:trophy"></iconify-icon></div>
                        <div class="feature-body"><strong>Produk Original &amp; Berkualitas</strong>
                            <p>Semua produk bersumber dari distributor resmi dan terjamin keasliannya.</p>
                        </div>
                    </div>
                    <div class="feature-item reveal d2">
                        <div class="feature-icon"><iconify-icon icon="tabler:rosette-discount"></iconify-icon></div>
                        <div class="feature-body"><strong>Harga Grosir Terbaik</strong>
                            <p>Dapatkan harga terjangkau untuk pembelian partai besar maupun eceran.</p>
                        </div>
                    </div>
                    <div class="feature-item reveal d3">
                        <div class="feature-icon"><iconify-icon icon="tabler:rocket"></iconify-icon></div>
                        <div class="feature-body"><strong>Layanan Pengiriman Cepat</strong>
                            <p>Antar ke seluruh wilayah Juntinyuat dan sekitarnya dengan cepat dan aman.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS -->
    <section id="produk">
        <div class="section-label reveal">Katalog Produk</div>
        <h2 class="section-title reveal d1">Temukan Semua <em>Kebutuhan</em><br>Anda di Sini</h2>
        <div class="carousel-wrap">
            <div class="fade-l"></div>
            <div class="fade-r"></div>
            <div class="carousel-container" id="prodCarousel">
                <div class="carousel-track" id="prodTrack">
                    <?php if(isset($featuredProducts) && count($featuredProducts) > 0): ?>
                        <?php $__currentLoopData = $featuredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="product-card tilt-card">
                            <div class="product-img">
                                <img src="<?php echo e($product->image_url); ?>" alt="<?php echo e($product->name); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo e($product->name); ?></h3>
                                <p><?php echo e(Str::limit($product->description, 60)); ?></p>
                                <span class="product-tag">
                                    <iconify-icon icon="<?php echo e($product->category->icon ?? 'ph:package-fill'); ?>"></iconify-icon> 
                                    <?php echo e($product->category->name); ?>

                                </span>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        
                        <?php if(count($featuredProducts) > 4): ?>
                            <?php $__currentLoopData = $featuredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="product-card tilt-card">
                                <div class="product-img">
                                    <img src="<?php echo e($product->image_url); ?>" alt="<?php echo e($product->name); ?>">
                                </div>
                                <div class="product-info">
                                    <h3><?php echo e($product->name); ?></h3>
                                    <p><?php echo e(Str::limit($product->description, 60)); ?></p>
                                    <span class="product-tag">
                                        <iconify-icon icon="<?php echo e($product->category->icon ?? 'ph:package-fill'); ?>"></iconify-icon> 
                                        <?php echo e($product->category->name); ?>

                                    </span>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center w-full py-10 text-gray-400">Belum ada produk unggulan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="layanan">
        <div class="section-label reveal">Layanan Kami</div>
        <h2 class="section-title reveal d1">Kenapa Harus Belanja<br>di <em>Toko Roni</em>?</h2>
        <div class="services-grid">
            <div class="service-card reveal d1 tilt-card">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:rosette-discount"></iconify-icon></div>
                    <h3>Harga Grosir Kompetitif</h3>
                    <p>Semakin banyak yang dibeli, semakin hemat. Cocok untuk pemilik warung dan UMKM se-Juntinyuat.</p>
                </div>
            </div>
            <div class="service-card reveal d2 tilt-card">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:truck-delivery"></iconify-icon></div>
                    <h3>Pengiriman ke Seluruh Daerah</h3>
                    <p>Layanan antar langsung ke depan pintu Anda. Melayani Juntinyuat, Indramayu, dan sekitarnya.</p>
                </div>
            </div>
            <div class="service-card reveal d3 tilt-card">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:package"></iconify-icon></div>
                    <h3>Stok Selalu Tersedia</h3>
                    <p>Gudang kami selalu terisi penuh. Tidak perlu khawatir kehabisan stok sewaktu-waktu.</p>
                </div>
            </div>
            <div class="service-card reveal d4 tilt-card">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:users-group"></iconify-icon></div>
                    <h3>Pelayanan Ramah &amp; Profesional</h3>
                    <p>Tim kami siap membantu dengan pelayanan yang cepat, ramah, dan profesional setiap harinya.</p>
                </div>
            </div>
            <div class="service-card reveal d5 tilt-card">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:wallet"></iconify-icon></div>
                    <h3>Berbagai Metode Pembayaran</h3>
                    <p>Mendukung tunai, transfer bank, QRIS, dan dompet digital untuk kemudahan transaksi Anda.</p>
                </div>
            </div>
            <div class="service-card reveal d6 tilt-card">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:refresh"></iconify-icon></div>
                    <h3>Retur &amp; Garansi Produk</h3>
                    <p>Terima pengembalian barang jika ada kerusakan. Kepuasan pelanggan adalah prioritas utama kami.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- NUMBERS -->
    <section id="angka">
        <div class="angka-grid">
            <div class="angka-item reveal">
                <div class="angka-icon"><iconify-icon icon="tabler:calendar-star"></iconify-icon></div>
                <div>
                    <div class="angka-number">19<span class="angka-plus">+</span></div>
                    <div class="angka-label">Tahun Pengalaman</div>
                </div>
            </div>
            <div class="angka-item reveal d1">
                <div class="angka-icon"><iconify-icon icon="tabler:users-group"></iconify-icon></div>
                <div>
                    <div class="angka-number"><?php echo e(number_format($stats['members'] > 100 ? $stats['members'] : 5200)); ?><span class="angka-plus">+</span></div>
                    <div class="angka-label">Pelanggan Aktif</div>
                </div>
            </div>
            <div class="angka-item reveal d2">
                <div class="angka-icon"><iconify-icon icon="tabler:box-seam"></iconify-icon></div>
                <div>
                    <div class="angka-number"><?php echo e(number_format($stats['products'])); ?><span class="angka-plus">+</span></div>
                    <div class="angka-label">Jenis Produk</div>
                </div>
            </div>
            <div class="angka-item reveal d3">
                <div class="angka-icon"><iconify-icon icon="tabler:map-pin"></iconify-icon></div>
                <div>
                    <div class="angka-number"><?php echo e(number_format($stats['categories'] * 3)); ?><span class="angka-plus">+</span></div>
                    <div class="angka-label">Wilayah Pengiriman</div>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="testimoni">
        <div class="section-label reveal">Testimoni</div>
        <h2 class="section-title reveal d1">Apa Kata <em>Pelanggan</em><br>Setia Kami?</h2>
        <div class="testi-wrap reveal d2">
            <div class="fade-lw"></div>
            <div class="fade-rw"></div>
            <div class="testi-track">
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Sudah belanja di sini lebih dari 10 tahun. Harganya paling murah di
                        Juntinyuat, stok lengkap dan pelayanannya selalu ramah. Recommended banget!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=100&q=80&auto=format&fit=crop"
                                alt="Bu Sari"></div>
                        <div class="testi-info"><strong>Bu Sari</strong><span>Pemilik Warung, Juntinyuat</span></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Belanja grosir di sini sangat mudah. Pesan lewat WhatsApp langsung diantar.
                        Harga konsisten dan selalu ada diskon untuk pelanggan tetap."</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&q=80&auto=format&fit=crop"
                                alt="Pak Karno"></div>
                        <div class="testi-info"><strong>Pak Karno</strong><span>Pedagang Sembako, Indramayu</span>
                        </div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Tokonya lengkap banget! Dari beras, minyak, sampai perlengkapan rumah semua
                        ada. Pengiriman cepat, produknya selalu kondisi baik. Puas!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&q=80&auto=format&fit=crop"
                                alt="Ibu Wati"></div>
                        <div class="testi-info"><strong>Ibu Wati</strong><span>Ibu Rumah Tangga, Sukra</span></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Kualitas produk terjamin dan harga grosirnya tidak ada duanya di Juntinyuat.
                        Sudah langganan sejak toko ini buka. Pemiliknya sangat ramah!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&q=80&auto=format&fit=crop"
                                alt="Hendra"></div>
                        <div class="testi-info"><strong>Hendra D.</strong><span>Reseller Sembako</span></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Sebagai pemilik kafe kecil, sangat terbantu. Bahan-bahan selalu tersedia dan
                        bisa diantar langsung ke tempat dengan harga yang sangat bersaing."</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&q=80&auto=format&fit=crop"
                                alt="Andi"></div>
                        <div class="testi-info"><strong>Andi N.</strong><span>Pemilik Kafe, Juntinyuat</span></div>
                    </div>
                </div>
                <!-- duplicate -->
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Sudah belanja di sini lebih dari 10 tahun. Harganya paling murah, stok
                        lengkap dan pelayanannya selalu ramah. Recommended banget!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=100&q=80&auto=format&fit=crop"
                                alt="Bu Sari"></div>
                        <div class="testi-info"><strong>Bu Sari</strong><span>Pemilik Warung, Juntinyuat</span></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Belanja grosir di sini sangat mudah. Pesan via WhatsApp langsung diantar.
                        Harga konsisten, selalu ada diskon pelanggan tetap."</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&q=80&auto=format&fit=crop"
                                alt="Pak Karno"></div>
                        <div class="testi-info"><strong>Pak Karno</strong><span>Pedagang Sembako, Indramayu</span>
                        </div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Pengiriman cepat dan produknya selalu kondisi baik. Puas sekali belanja di
                        sini!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&q=80&auto=format&fit=crop"
                                alt="Ibu Wati"></div>
                        <div class="testi-info"><strong>Ibu Wati</strong><span>Ibu Rumah Tangga, Sukra</span></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Harga grosirnya tidak ada duanya. Sudah langganan sejak toko ini buka.
                        Pemiliknya sangat ramah dan jujur!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&q=80&auto=format&fit=crop"
                                alt="Hendra"></div>
                        <div class="testi-info"><strong>Hendra D.</strong><span>Reseller Sembako</span></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars"><iconify-icon icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon><iconify-icon
                            icon="tabler:star-filled"></iconify-icon></div>
                    <p class="testi-text">"Bahan-bahan selalu tersedia dan diantar ke tempat dengan harga sangat
                        bersaing. Terima kasih Grosir Roni!"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><img
                                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&q=80&auto=format&fit=crop"
                                alt="Andi"></div>
                        <div class="testi-info"><strong>Andi N.</strong><span>Pemilik Kafe, Juntinyuat</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section id="cta">
        <div class="cta-box reveal">
            <h2>Mulai Belanja<br><em>Sekarang</em> Juga!</h2>
            <p>Dapatkan penawaran terbaik dan harga grosir eksklusif. Hubungi kami sekarang atau kunjungi toko kami
                langsung di Juntinyuat, Indramayu.</p>
            <div class="cta-btns">
                <a href="https://wa.me/6281234567890" class="btn-wa magnetic" target="_blank">
                    <iconify-icon icon="ic:baseline-whatsapp"></iconify-icon> Chat WhatsApp
                </a>
                <a href="https://maps.app.goo.gl/FS6zBUzt6vpAh7p3A" class="btn-outline-w magnetic">
                    <iconify-icon icon="tabler:map-pin"></iconify-icon> Lokasi Toko
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-brand-logo"><iconify-icon icon="fluent:store-24-filled"></iconify-icon>Grosir
                    <span>Roni</span></div>
                <p>Pusat grosir terlengkap dan terpercaya di Juntinyuat, Indramayu. Melayani dengan sepenuh hati sejak
                    2005.</p>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="#about"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Tentang Kami</a>
                    </li>
                    <li><a href="#produk"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Produk</a></li>
                    <li><a href="#layanan"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Layanan</a></li>
                    <li><a href="#testimoni"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Testimoni</a>
                    </li>
                    <li><a href="/login"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Admin Login</a>
                    </li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Produk</h4>
                <ul>
                    <li><a href="#"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Sembako</a></li>
                    <li><a href="#"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Makanan &amp;
                            Minuman</a></li>
                    <li><a href="#"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Perawatan
                            Rumah</a></li>
                    <li><a href="#"><iconify-icon icon="tabler:chevron-right"></iconify-icon> Alat
                            Kebersihan</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Kontak</h4>
                <ul>
                    <li><a href="#"><iconify-icon icon="tabler:map-pin"></iconify-icon> Juntinyuat,
                            Indramayu</a></li>
                    <li><a href="https://wa.me/6281234567890"><iconify-icon icon="tabler:phone"></iconify-icon>
                            0812-3456-7890</a></li>
                    <li><a href="#"><iconify-icon icon="tabler:clock"></iconify-icon> Buka: 06.00–21.00</a></li>
                    <li><a href="#"><iconify-icon icon="tabler:mail"></iconify-icon> grosironi@gmail.com</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Toko Grosir Roni Juntinyuat. All rights reserved.</p>
            <div class="social-icons">
                <a href="#" class="social-icon" data-tip="Facebook"><iconify-icon
                        icon="ic:baseline-facebook"></iconify-icon></a>
                <a href="#" class="social-icon" data-tip="Instagram"><iconify-icon
                        icon="mdi:instagram"></iconify-icon></a>
                <a href="https://wa.me/6281234567890" class="social-icon" data-tip="WhatsApp"><iconify-icon
                        icon="ic:baseline-whatsapp"></iconify-icon></a>
            </div>
        </div>
    </footer>

    <script>
        /* ══════════════════════════════════════
       PAGE LOADER
    ══════════════════════════════════════ */
        const loader = document.getElementById('loader');
        const loaderBar = document.getElementById('loaderBar');
        let progress = 0;
        const loadInterval = setInterval(() => {
            progress += Math.random() * 18 + 5;
            if (progress >= 100) {
                progress = 100;
                clearInterval(loadInterval);
                setTimeout(() => loader.classList.add('hidden'), 300);
            }
            loaderBar.style.width = progress + '%';
        }, 60);
        window.addEventListener('load', () => {
            progress = 100;
            loaderBar.style.width = '100%';
            setTimeout(() => loader.classList.add('hidden'), 400);
        });

        /* ══════════════════════════════════════
           CUSTOM CURSOR
        ══════════════════════════════════════ */
        const cursor = document.getElementById('cursor');
        const cursorRing = document.getElementById('cursor-ring');
        let mx = window.innerWidth / 2,
            my = window.innerHeight / 2;
        let rx = mx,
            ry = my;

        document.addEventListener('mousemove', e => {
            mx = e.clientX;
            my = e.clientY;
        });
        document.addEventListener('mousedown', () => cursor.classList.add('clicked'));
        document.addEventListener('mouseup', () => cursor.classList.remove('clicked'));

        function animateCursor() {
            cursor.style.left = mx + 'px';
            cursor.style.top = my + 'px';
            rx += (mx - rx) * 0.12;
            ry += (my - ry) * 0.12;
            cursorRing.style.left = rx + 'px';
            cursorRing.style.top = ry + 'px';
            requestAnimationFrame(animateCursor);
        }
        animateCursor();

        const hoverEls = document.querySelectorAll(
            'a, button, .product-card, .testi-card, .service-card, .feature-item, .social-icon, .marquee-item');
        hoverEls.forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.classList.add('hovered');
                cursorRing.classList.add('hovered');
            });
            el.addEventListener('mouseleave', () => {
                cursor.classList.remove('hovered');
                cursorRing.classList.remove('hovered');
            });
        });

        /* ══════════════════════════════════════
           PARTICLES
        ══════════════════════════════════════ */
        const particleContainer = document.getElementById('particles');

        function createParticle() {
            const p = document.createElement('div');
            p.className = 'particle';
            const size = Math.random() * 6 + 2;
            const left = Math.random() * 100;
            const dur = Math.random() * 10 + 8;
            const delay = Math.random() * 6;
            p.style.cssText =
                `width:${size}px;height:${size}px;left:${left}%;bottom:${-size}px;animation-duration:${dur}s;animation-delay:${delay}s`;
            particleContainer.appendChild(p);
            setTimeout(() => p.remove(), (dur + delay) * 1000);
        }
        setInterval(createParticle, 600);
        for (let i = 0; i < 8; i++) setTimeout(createParticle, i * 400);

        /* ══════════════════════════════════════
           PARALLAX HERO BG
        ══════════════════════════════════════ */
        const heroBg = document.getElementById('heroBg');
        let lastScrollY = 0;

        function updateParallax() {
            const scrollY = window.scrollY;
            if (heroBg && scrollY < window.innerHeight * 1.5) {
                heroBg.style.transform = `translateY(${scrollY * 0.35}px) scale(1.05)`;
            }
        }

        /* ══════════════════════════════════════
           HERO STATS 3D TILT
        ══════════════════════════════════════ */
        const heroStats = document.getElementById('heroStats');
        if (heroStats) {
            heroStats.addEventListener('mousemove', e => {
                const rect = heroStats.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                heroStats.style.transform =
                    `perspective(600px) rotateX(${-y*12}deg) rotateY(${x*12}deg) translateZ(10px)`;
                heroStats.style.boxShadow =
                    `${-x*20}px ${-y*20}px 60px rgba(58,31,0,.2), 0 30px 80px rgba(58,31,0,.12)`;
            });
            heroStats.addEventListener('mouseleave', () => {
                heroStats.style.transform = 'perspective(600px) rotateX(0) rotateY(0) translateZ(0)';
                heroStats.style.boxShadow = '0 30px 80px rgba(58,31,0,.12)';
                heroStats.style.transition = 'transform .6s cubic-bezier(.16,1,.3,1), box-shadow .6s';
                setTimeout(() => heroStats.style.transition = '', 600);
            });
        }

        /* ══════════════════════════════════════
           HERO TITLE SPLIT ANIMATION
        ══════════════════════════════════════ */
        function splitAndAnimate() {
            const titleEl = document.getElementById('heroTitle');
            if (!titleEl) return;
            const html = titleEl.innerHTML;
            const words = html.split(/(\s+|<[^>]+>)/g).filter(s => s.length);
            let newHtml = '';
            let delay = 0.9;
            for (const part of words) {
                if (part.match(/^</) || part.match(/^\s+$/)) {
                    newHtml += part;
                    continue;
                }
                const chars = part.split('').map(c => {
                    const span =
                        `<span class="word-inner" style="animation-delay:${delay.toFixed(2)}s">${c === ' ' ? '&nbsp;' : c}</span>`;
                    delay += 0.04;
                    return span;
                });
                newHtml += `<span class="word">${chars.join('')}</span>`;
            }
            titleEl.innerHTML = newHtml;
        }
        splitAndAnimate();

        /* ══════════════════════════════════════
           3D TILT CARDS
        ══════════════════════════════════════ */
        function initTilt(selector, intensity = 15) {
            document.querySelectorAll(selector).forEach(card => {
                card.addEventListener('mousemove', e => {
                    const r = card.getBoundingClientRect();
                    const x = (e.clientX - r.left) / r.width;
                    const y = (e.clientY - r.top) / r.height;
                    const rotX = (y - 0.5) * -intensity;
                    const rotY = (x - 0.5) * intensity;
                    card.style.transform =
                        `perspective(800px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(8px)`;
                    card.style.transition = 'transform .05s';
                    // Spotlight
                    if (card.style.setProperty) {
                        card.style.setProperty('--mx', (x * 100) + '%');
                        card.style.setProperty('--my', (y * 100) + '%');
                    }
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.transition = 'transform .6s cubic-bezier(.16,1,.3,1), box-shadow .4s';
                    setTimeout(() => card.style.transition = '', 600);
                });
            });
        }
        initTilt('.product-card', 12);
        initTilt('.service-card', 10);
        initTilt('.testi-card', 8);

        /* ══════════════════════════════════════
           MAGNETIC BUTTONS
        ══════════════════════════════════════ */
        document.querySelectorAll('.magnetic').forEach(btn => {
            btn.addEventListener('mousemove', e => {
                const r = btn.getBoundingClientRect();
                const cx = r.left + r.width / 2;
                const cy = r.top + r.height / 2;
                const dx = (e.clientX - cx) * 0.35;
                const dy = (e.clientY - cy) * 0.35;
                btn.style.transform = `translate(${dx}px, ${dy}px)`;
                btn.style.transition = 'transform .15s';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = '';
                btn.style.transition = 'transform .5s cubic-bezier(.16,1,.3,1)';
            });
        });

        /* ══════════════════════════════════════
           SCROLL PROGRESS + NAVBAR + BACK TOP
        ══════════════════════════════════════ */
        const bar = document.getElementById('progress-bar');
        const navbar = document.getElementById('navbar');
        const backTop = document.getElementById('backTop');

        function onScroll() {
            const pct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            bar.style.width = pct + '%';
            navbar.classList.toggle('scrolled', window.scrollY > 50);
            backTop.classList.toggle('visible', window.scrollY > 400);
            updateParallax();
        }
        window.addEventListener('scroll', onScroll, {
            passive: true
        });
        backTop.addEventListener('click', () => window.scrollTo({
            top: 0,
            behavior: 'smooth'
        }));

        /* ══════════════════════════════════════
           HAMBURGER
        ══════════════════════════════════════ */
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('open');
            document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
        });
        document.querySelectorAll('.mm-link, .mm-cta').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            });
        });

        /* ══════════════════════════════════════
           SCROLL REVEAL
        ══════════════════════════════════════ */
        const ro = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) e.target.classList.add('visible');
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => ro.observe(el));

        /* ══════════════════════════════════════
           COUNTER ANIMATION (eased)
        ══════════════════════════════════════ */
        function easeOutExpo(t) {
            return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
        }
        const ao = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const items = document.querySelectorAll('.angka-item');
                const targets = [19, 5000, 500, 15];
                const labels = ['K', '', '', ''];
                items.forEach((item, i) => {
                    setTimeout(() => {
                        const numEl = item.querySelector('.angka-number');
                        const end = targets[i];
                        const duration = 1800;
                        const start = performance.now();

                        function step(now) {
                            const t = Math.min((now - start) / duration, 1);
                            const eased = easeOutExpo(t);
                            let val = Math.floor(eased * end);
                            let display = i === 1 ? Math.floor(val / 1000) + 'K' : val;
                            numEl.innerHTML = display + '<span class="angka-plus">+</span>';
                            if (t < 1) requestAnimationFrame(step);
                        }
                        requestAnimationFrame(step);
                    }, i * 200);
                });
                ao.disconnect();
            });
        }, {
            threshold: 0.3
        });
        const angkaSection = document.getElementById('angka');
        if (angkaSection) ao.observe(angkaSection);

        /* ══════════════════════════════════════
           PRODUCT CAROUSEL DRAG
        ══════════════════════════════════════ */
        const prodCarousel = document.getElementById('prodCarousel');
        const prodTrack = document.getElementById('prodTrack');
        let isDragging = false,
            startX = 0,
            scrollLeft = 0;
        prodCarousel.addEventListener('mousedown', e => {
            isDragging = true;
            startX = e.pageX - prodCarousel.offsetLeft;
            scrollLeft = prodCarousel.scrollLeft;
            prodCarousel.classList.add('dragging');
            prodTrack.classList.add('paused');
        });
        prodCarousel.addEventListener('mouseleave', () => {
            isDragging = false;
            prodCarousel.classList.remove('dragging');
            prodTrack.classList.remove('paused');
        });
        prodCarousel.addEventListener('mouseup', () => {
            isDragging = false;
            prodCarousel.classList.remove('dragging');
            prodTrack.classList.remove('paused');
        });
        prodCarousel.addEventListener('mousemove', e => {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.pageX - prodCarousel.offsetLeft;
            prodCarousel.scrollLeft = scrollLeft - (x - startX) * 1.8;
        });
        prodCarousel.addEventListener('touchstart', e => {
            startX = e.touches[0].pageX;
            scrollLeft = prodCarousel.scrollLeft;
            prodTrack.classList.add('paused');
        }, {
            passive: true
        });
        prodCarousel.addEventListener('touchend', () => prodTrack.classList.remove('paused'));
        prodCarousel.addEventListener('touchmove', e => {
            prodCarousel.scrollLeft = scrollLeft + (startX - e.touches[0].pageX) * 1.5;
        }, {
            passive: true
        });

        /* ══════════════════════════════════════
           ACTIVE NAV
        ══════════════════════════════════════ */
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-links a');
        const observerNav = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navLinks.forEach(a => a.classList.remove('active'));
                    const active = document.querySelector(`.nav-links a[href="#${entry.target.id}"]`);
                    if (active) active.classList.add('active');
                }
            });
        }, {
            threshold: 0.4
        });
        sections.forEach(s => observerNav.observe(s));

        /* ══════════════════════════════════════
           SERVICE CARD SPOTLIGHT (mouse track)
        ══════════════════════════════════════ */
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const r = card.getBoundingClientRect();
                card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
                card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
            });
        });
    </script>
</body>

</html>
<?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views/welcome.blade.php ENDPATH**/ ?>