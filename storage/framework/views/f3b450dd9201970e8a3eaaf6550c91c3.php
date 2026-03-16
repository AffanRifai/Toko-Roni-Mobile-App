<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Grosir Roni – Juntinyuat</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
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
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cream);
            color: var(--brown);
            overflow-x: hidden;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: var(--cream);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--amber);
            border-radius: 2px;
        }

        /* ── SCROLL PROGRESS ── */
        #progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--amber), var(--amber-light));
            z-index: 9999;
            width: 0%;
            transition: width 0.1s;
        }

        /* ── NAVBAR ── */
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
            background: rgba(253, 246, 236, 0.88);
            border-bottom: 1px solid rgba(232, 130, 12, 0.12);
            transition: all 0.4s;
        }

        nav.scrolled {
            background: rgba(253, 246, 236, 0.98);
            box-shadow: 0 4px 30px rgba(58, 31, 0, 0.08);
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--brown);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .nav-logo iconify-icon {
            color: var(--amber);
            font-size: 1.5rem;
        }

        .nav-logo span {
            color: var(--amber);
        }

        .nav-links {
            display: flex;
            gap: 32px;
            list-style: none;
        }

        .nav-links a {
            font-size: 0.87rem;
            font-weight: 500;
            color: var(--brown-mid);
            text-decoration: none;
            letter-spacing: 0.4px;
            transition: color 0.2s;
            position: relative;
            padding: 4px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--amber);
            transition: width 0.3s;
        }

        .nav-links a:hover {
            color: var(--amber);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-cta {
            background: var(--amber);
            color: white;
            padding: 9px 22px;
            border-radius: 50px;
            font-size: 0.87rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .nav-cta:hover {
            background: var(--amber-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232, 130, 12, 0.35);
        }

        /* HAMBURGER */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 8px;
            border: none;
            background: none;
            z-index: 1001;
        }

        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--brown);
            border-radius: 2px;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .hamburger.active span:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }

        .hamburger.active span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
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
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .mobile-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .mobile-menu a {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--brown);
            text-decoration: none;
            transition: color 0.2s;
        }

        .mobile-menu a:hover {
            color: var(--amber);
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
            margin-top: 8px;
        }

        .mobile-menu .mm-cta:hover {
            background: var(--amber-dark);
        }

        /* ── HERO ── */
        #hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: calc(var(--nav-h) + 50px) 60px 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-photo {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .hero-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .hero-photo::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, rgba(253, 246, 236, 0.97) 0%, rgba(253, 246, 236, 0.92) 45%, rgba(253, 246, 236, 0.55) 72%, rgba(253, 246, 236, 0.1) 100%);
        }

        .hero-row {
            display: flex;
            align-items: center;
            gap: 50px;
            width: 100%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 640px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(232, 130, 12, 0.12);
            border: 1px solid rgba(232, 130, 12, 0.3);
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 24px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--amber-dark);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--amber);
            animation: blink 1.5s infinite;
            display: inline-block;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0.3
            }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 5.5vw, 5rem);
            font-weight: 900;
            line-height: 1.06;
            color: var(--brown);
            margin-bottom: 20px;
        }

        .hero-title em {
            font-style: normal;
            color: var(--amber);
        }

        .hero-desc {
            font-size: 1rem;
            line-height: 1.75;
            color: var(--brown-mid);
            max-width: 500px;
            margin-bottom: 36px;
        }

        .hero-btns {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--amber);
            color: white;
            padding: 13px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.93rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: var(--amber-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232, 130, 12, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--brown);
            padding: 13px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.93rem;
            text-decoration: none;
            border: 2px solid rgba(58, 31, 0, 0.18);
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            border-color: var(--amber);
            color: var(--amber);
            transform: translateY(-2px);
        }

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
            box-shadow: 0 30px 80px rgba(58, 31, 0, 0.12);
            min-width: 250px;
            animation: floatCard 4s ease-in-out infinite;
        }

        @keyframes floatCard {

            0%,
            100% {
                transform: translateY(0) rotate(1deg);
            }

            50% {
                transform: translateY(-12px) rotate(1deg);
            }
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(232, 130, 12, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .stat-item:hover .stat-icon-box {
            background: rgba(232, 130, 12, 0.2);
            transform: scale(1.1);
        }

        .stat-icon-box iconify-icon {
            font-size: 1.35rem;
            color: var(--amber);
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 900;
            color: var(--amber);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--gray);
            font-weight: 500;
            margin-top: 2px;
        }

        .stat-divider {
            height: 1px;
            background: var(--gray-light);
        }

        /* ── MARQUEE ── */
        .marquee-section {
            background: var(--amber);
            padding: 15px 0;
            overflow: hidden;
        }

        .marquee-track {
            display: flex;
            width: max-content;
            animation: marquee 22s linear infinite;
        }

        .marquee-track:hover {
            animation-play-state: paused;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .marquee-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 30px;
            font-weight: 700;
            font-size: 0.85rem;
            color: white;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .marquee-item iconify-icon {
            font-size: 1rem;
            opacity: 0.85;
        }

        .msep {
            color: rgba(255, 255, 255, 0.35);
        }

        /* ── SECTION COMMON ── */
        section {
            padding: 90px 60px;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--amber);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::before {
            content: '';
            width: 22px;
            height: 2px;
            background: var(--amber);
            display: inline-block;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.9rem, 3.5vw, 3rem);
            font-weight: 900;
            line-height: 1.15;
            color: var(--brown);
        }

        .section-title em {
            font-style: italic;
            color: var(--amber);
        }

        /* REVEAL */
        .reveal {
            opacity: 0;
            transform: translateY(36px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-45px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(45px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal-right.visible {
            opacity: 1;
            transform: translateX(0);
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

        /* ── ABOUT ── */
        #about {
            background: white;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 70px;
            align-items: center;
            margin-top: 55px;
        }

        .about-visual {
            position: relative;
            height: 480px;
        }

        .about-img-main {
            width: 78%;
            height: 88%;
            position: absolute;
            bottom: 0;
            right: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(58, 31, 0, 0.15);
        }

        .about-img-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s;
        }

        .about-img-main:hover img {
            transform: scale(1.04);
        }

        .about-img-accent {
            width: 52%;
            height: 52%;
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 50px rgba(58, 31, 0, 0.2);
            z-index: 1;
        }

        .about-img-accent img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s;
        }

        .about-img-accent:hover img {
            transform: scale(1.06);
        }

        .about-badge-float {
            position: absolute;
            bottom: 28px;
            left: -8px;
            z-index: 2;
            background: white;
            border-radius: 16px;
            padding: 13px 17px;
            box-shadow: 0 10px 40px rgba(58, 31, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: floatCard 3.5s ease-in-out infinite;
        }

        .badge-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--amber), var(--amber-dark));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-icon-box iconify-icon {
            font-size: 1.3rem;
            color: white;
        }

        .badge-text {
            font-size: 0.76rem;
            color: var(--gray);
        }

        .badge-text strong {
            display: block;
            font-size: 0.95rem;
            color: var(--brown);
        }

        .about-text p {
            font-size: 0.97rem;
            line-height: 1.8;
            color: var(--gray);
            margin-bottom: 16px;
        }

        .about-features {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 28px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 14px;
            border-radius: 14px;
            transition: background 0.25s, transform 0.25s;
            cursor: default;
        }

        .feature-item:hover {
            background: rgba(232, 130, 12, 0.05);
            transform: translateX(4px);
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(232, 130, 12, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .feature-item:hover .feature-icon {
            background: var(--amber);
            transform: rotate(-5deg);
        }

        .feature-icon iconify-icon {
            font-size: 1.25rem;
            color: var(--amber);
            transition: color 0.3s;
        }

        .feature-item:hover .feature-icon iconify-icon {
            color: white;
        }

        .feature-body strong {
            font-weight: 700;
            color: var(--brown);
            font-size: 0.93rem;
        }

        .feature-body p {
            font-size: 0.82rem;
            color: var(--gray);
            margin-top: 2px;
            line-height: 1.5;
        }

        /* ── PRODUCTS ── */
        #produk {
            background: var(--cream);
            padding-bottom: 90px;
        }

        .carousel-wrap {
            position: relative;
            margin-top: 48px;
        }

        .carousel-container {
            overflow: hidden;
            cursor: grab;
            user-select: none;
        }

        .carousel-container.dragging {
            cursor: grabbing;
        }

        .carousel-track {
            display: flex;
            gap: 22px;
            animation: infiniteScroll 38s linear infinite;
            width: max-content;
        }

        .carousel-track.paused,
        .carousel-track:hover {
            animation-play-state: paused;
        }

        @keyframes infiniteScroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .product-card {
            min-width: 262px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(58, 31, 0, 0.06);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            flex-shrink: 0;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(58, 31, 0, 0.15);
        }

        .product-card:active {
            transform: translateY(-4px);
        }

        .product-img {
            height: 185px;
            overflow: hidden;
            position: relative;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.55s;
        }

        .product-card:hover .product-img img {
            transform: scale(1.08);
        }

        .product-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(58, 31, 0, 0.07));
        }

        .product-info {
            padding: 17px 19px;
        }

        .product-info h3 {
            font-weight: 700;
            font-size: 0.96rem;
            color: var(--brown);
            margin-bottom: 4px;
        }

        .product-info p {
            font-size: 0.8rem;
            color: var(--gray);
            line-height: 1.5;
        }

        .product-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 9px;
            padding: 4px 11px;
            background: rgba(232, 130, 12, 0.1);
            color: var(--amber-dark);
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .product-tag iconify-icon {
            font-size: 0.82rem;
        }

        .fade-l {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 100px;
            z-index: 2;
            background: linear-gradient(to right, var(--cream), transparent);
        }

        .fade-r {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 100px;
            z-index: 2;
            background: linear-gradient(to left, var(--cream), transparent);
        }

        /* ── SERVICES ── */
        #layanan {
            background: var(--brown);
        }

        #layanan .section-label {
            color: var(--amber-light);
        }

        #layanan .section-label::before {
            background: var(--amber-light);
        }

        #layanan .section-title {
            color: white;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-top: 48px;
        }

        .service-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
            cursor: default;
        }

        .service-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--amber) 0%, var(--amber-dark) 100%);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .service-card:hover {
            transform: translateY(-6px) scale(1.01);
            border-color: var(--amber);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .sc {
            position: relative;
            z-index: 1;
        }

        .service-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            transition: all 0.35s;
        }

        .service-card:hover .service-icon {
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(-8deg) scale(1.1);
        }

        .service-icon iconify-icon {
            font-size: 1.75rem;
            color: white;
        }

        .service-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            margin-bottom: 9px;
        }

        .service-card p {
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.65);
            line-height: 1.65;
        }

        /* ── NUMBERS ── */
        #angka {
            background: linear-gradient(135deg, var(--amber) 0%, var(--amber-dark) 100%);
            padding: 75px 60px;
        }

        .angka-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 36px;
        }

        .angka-item {
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.3s;
        }

        .angka-item:hover {
            transform: translateY(-4px);
        }

        .angka-icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.35s;
        }

        .angka-item:hover .angka-icon {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.1);
        }

        .angka-icon iconify-icon {
            font-size: 1.85rem;
            color: white;
        }

        .angka-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.6rem;
            font-weight: 900;
            color: white;
            line-height: 1;
        }

        .angka-plus {
            color: rgba(255, 255, 255, 0.7);
        }

        .angka-label {
            font-size: 0.83rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            margin-top: 3px;
        }

        /* ── TESTIMONIALS ── */
        #testimoni {
            background: white;
        }

        .testi-wrap {
            position: relative;
            margin-top: 48px;
            overflow: hidden;
        }

        .testi-track {
            display: flex;
            gap: 26px;
            animation: testiScroll 30s linear infinite;
            width: max-content;
        }

        .testi-track:hover {
            animation-play-state: paused;
        }

        @keyframes testiScroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .testi-card {
            min-width: 330px;
            background: var(--cream);
            border-radius: 20px;
            padding: 26px;
            border: 1px solid rgba(232, 130, 12, 0.12);
            flex-shrink: 0;
            transition: all 0.35s;
        }

        .testi-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 16px 50px rgba(58, 31, 0, 0.1);
            border-color: rgba(232, 130, 12, 0.3);
        }

        .stars {
            display: flex;
            gap: 3px;
            margin-bottom: 12px;
        }

        .stars iconify-icon {
            font-size: 0.95rem;
            color: var(--amber);
        }

        .testi-text {
            font-size: 0.88rem;
            line-height: 1.7;
            color: var(--gray);
            margin-bottom: 18px;
            font-style: italic;
        }

        .testi-author {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .testi-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid rgba(232, 130, 12, 0.2);
        }

        .testi-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .testi-info strong {
            font-size: 0.88rem;
            color: var(--brown);
            display: block;
        }

        .testi-info span {
            font-size: 0.76rem;
            color: var(--gray);
        }

        .fade-lw {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 80px;
            z-index: 2;
            background: linear-gradient(to right, white, transparent);
        }

        .fade-rw {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 80px;
            z-index: 2;
            background: linear-gradient(to left, white, transparent);
        }

        /* ── CTA ── */
        #cta {
            background: var(--cream);
            text-align: center;
            padding: 110px 60px;
        }

        .cta-box {
            background: var(--brown);
            border-radius: 32px;
            padding: 75px;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            transition: transform 0.4s;
        }

        .cta-box:hover {
            transform: translateY(-4px);
        }

        .cta-box::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E");
        }

        .cta-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 900;
            color: white;
            margin-bottom: 14px;
            position: relative;
        }

        .cta-box h2 em {
            font-style: italic;
            color: var(--amber-light);
        }

        .cta-box p {
            font-size: 0.97rem;
            color: rgba(255, 255, 255, 0.62);
            margin-bottom: 36px;
            line-height: 1.7;
            position: relative;
        }

        .cta-btns {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
        }

        .btn-wa {
            background: #25D366;
            color: white;
            padding: 13px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.93rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-wa:hover {
            background: #1da851;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.35);
        }

        .btn-outline-w {
            background: transparent;
            color: white;
            padding: 13px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.93rem;
            text-decoration: none;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline-w:hover {
            border-color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* ── FOOTER ── */
        footer {
            background: #1A0D00;
            padding: 60px 60px 28px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 48px;
        }

        .footer-brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 900;
            color: white;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 13px;
        }

        .footer-brand-logo iconify-icon {
            color: var(--amber);
        }

        .footer-brand-logo span {
            color: var(--amber);
        }

        .footer-brand p {
            font-size: 0.83rem;
            color: rgba(255, 255, 255, 0.48);
            line-height: 1.7;
            max-width: 270px;
        }

        .footer-col h4 {
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--amber);
            margin-bottom: 18px;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .footer-col ul li a {
            font-size: 0.83rem;
            color: rgba(255, 255, 255, 0.48);
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .footer-col ul li a iconify-icon {
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .footer-col ul li a:hover {
            color: white;
            padding-left: 4px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            padding-top: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-bottom p {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.28);
        }

        .social-icons {
            display: flex;
            gap: 9px;
        }

        .social-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.48);
            transition: all 0.25s;
        }

        .social-icon iconify-icon {
            font-size: 1.05rem;
        }

        .social-icon:hover {
            background: var(--amber);
            color: white;
            transform: translateY(-2px);
        }

        /* ── FLOATING WA ── */
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
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.5);
            text-decoration: none;
            transition: all 0.35s;
            animation: waPulse 2.5s ease-in-out infinite;
        }

        .wa-float iconify-icon {
            font-size: 1.8rem;
            color: white;
        }

        .wa-float:hover {
            transform: scale(1.12);
            box-shadow: 0 12px 40px rgba(37, 211, 102, 0.6);
            animation: none;
        }

        @keyframes waPulse {

            0%,
            100% {
                box-shadow: 0 8px 30px rgba(37, 211, 102, 0.5);
            }

            50% {
                box-shadow: 0 8px 50px rgba(37, 211, 102, 0.75);
            }
        }

        /* ── BACK TO TOP ── */
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
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(58, 31, 0, 0.25);
            transition: all 0.35s;
            opacity: 0;
            transform: translateY(16px);
            pointer-events: none;
        }

        .back-top.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .back-top:hover {
            background: var(--amber);
            transform: translateY(-2px);
        }

        .back-top iconify-icon {
            font-size: 1.2rem;
            color: white;
        }

        /* ── TOOLTIP ── */
        [data-tip] {
            position: relative;
        }

        [data-tip]::after {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: var(--brown);
            color: white;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        [data-tip]:hover::after {
            opacity: 1;
        }

        /* ── MOBILE RESPONSIVE ── */
        @media(max-width:1024px) {
            nav {
                padding: 0 32px;
            }

            section {
                padding: 75px 36px;
            }

            #hero {
                padding: calc(var(--nav-h) + 40px) 36px 70px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 36px;
            }

            .angka-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 28px;
            }

            .services-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width:768px) {
            nav {
                padding: 0 20px;
            }

            .nav-links,
            .nav-cta {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            section {
                padding: 60px 20px;
            }

            #hero {
                padding: calc(var(--nav-h) + 30px) 20px 60px;
            }

            .hero-row {
                flex-direction: column;
                gap: 32px;
            }

            .hero-stats {
                min-width: unset;
                width: 100%;
                animation: none;
                transform: none !important;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 16px;
                padding: 24px;
            }

            .stat-item {
                flex: 1;
                min-width: 120px;
            }

            .stat-divider {
                display: none;
            }

            .about-grid {
                grid-template-columns: 1fr;
                gap: 36px;
            }

            .about-visual {
                height: 320px;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .angka-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .angka-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .angka-icon {
                width: 52px;
                height: 52px;
            }

            .angka-number {
                font-size: 2rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .cta-box {
                padding: 42px 24px;
                border-radius: 24px;
            }

            #cta {
                padding: 70px 20px;
            }

            #angka {
                padding: 60px 20px;
            }

            .wa-float {
                bottom: 20px;
                right: 20px;
                width: 52px;
                height: 52px;
            }

            .back-top {
                bottom: 86px;
                right: 20px;
            }

            .fade-l,
            .fade-r,
            .fade-lw,
            .fade-rw {
                width: 50px;
            }

            .hero-title {
                font-size: clamp(2.2rem, 8vw, 3rem);
            }
        }

        @media(max-width:480px) {
            .hero-stats {
                flex-direction: column;
            }

            .stat-item {
                min-width: unset;
            }

            .stat-divider {
                display: block;
            }

            .product-card {
                min-width: 240px;
            }

            .testi-card {
                min-width: 290px;
            }

            .about-visual {
                height: 260px;
            }

            .services-grid {
                gap: 14px;
            }

            .angka-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
        }

        /* Smooth icon load */
        iconify-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>

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
        <div class="hero-photo">
            <img src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=1600&q=85&auto=format&fit=crop"
                alt="Toko Grosir Roni" loading="eager">
        </div>
        <div class="hero-row">
            <div class="hero-content">
                <div class="hero-badge"><span class="dot"></span> Toko Grosir Terpercaya · Juntinyuat</div>
                <h1 class="hero-title">Pusat Grosir<br><em>Terlengkap</em> di<br>Juntinyuat</h1>
                <p class="hero-desc">Toko Grosir Roni hadir sebagai mitra belanja terpercaya untuk kebutuhan sembako,
                    produk rumah tangga, dan kebutuhan usaha Anda. Harga bersaing, stok melimpah, pelayanan ramah sejak
                    2005.</p>
                <div class="hero-btns">
                    <a href="#produk" class="btn-primary">
                        <iconify-icon icon="tabler:shopping-cart"></iconify-icon> Lihat Produk
                        <iconify-icon icon="tabler:arrow-right"></iconify-icon>
                    </a>
                    <a href="#about" class="btn-secondary">
                        <iconify-icon icon="tabler:info-circle"></iconify-icon> Tentang Kami
                    </a>
                </div>
            </div>
            <div class="hero-stats">
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
                        <div class="stat-number">5K+</div>
                        <div class="stat-label">Pelanggan Setia</div>
                    </div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item" data-tip="Stok selalu tersedia">
                    <div class="stat-icon-box"><iconify-icon icon="tabler:package"></iconify-icon></div>
                    <div>
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Produk Tersedia</div>
                    </div>
                </div>
            </div>
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
                <span class="msep">&nbsp;✦&nbsp;</span>
            </div>
            <div class="marquee-item"><iconify-icon icon="mdi:soap"></iconify-icon> Perlengkapan Mandi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:truck-delivery"></iconify-icon> Antar ke Lokasi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:rosette-discount"></iconify-icon> Harga Terbaik <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:building-store"></iconify-icon> Grosir &amp; Eceran
                <span class="msep">&nbsp;✦&nbsp;</span>
            </div>
            <div class="marquee-item"><iconify-icon icon="ph:grains-fill"></iconify-icon> Sembako <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="mdi:home-heart"></iconify-icon> Perawatan Rumah <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="ph:bowl-food-fill"></iconify-icon> Makanan &amp; Minuman
                <span class="msep">&nbsp;✦&nbsp;</span>
            </div>
            <div class="marquee-item"><iconify-icon icon="mdi:soap"></iconify-icon> Perlengkapan Mandi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:truck-delivery"></iconify-icon> Antar ke Lokasi <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:rosette-discount"></iconify-icon> Harga Terbaik <span
                    class="msep">&nbsp;✦&nbsp;</span></div>
            <div class="marquee-item"><iconify-icon icon="tabler:building-store"></iconify-icon> Grosir &amp; Eceran
                <span class="msep">&nbsp;✦&nbsp;</span>
            </div>
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
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80&auto=format&fit=crop"
                                alt="Beras">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Beras Premium</h3>
                            <p>Beras kualitas terbaik berbagai merek. Kemasan 5kg, 10kg, 25kg.</p><span
                                class="product-tag"><iconify-icon icon="ph:grains-fill"></iconify-icon> Sembako</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80&auto=format&fit=crop"
                                alt="Minyak">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Minyak Goreng</h3>
                            <p>Pilihan minyak goreng curah dan kemasan untuk kebutuhan dapur.</p><span
                                class="product-tag"><iconify-icon icon="ph:grains-fill"></iconify-icon> Sembako</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1563453392212-326f5e854473?w=600&q=80&auto=format&fit=crop"
                                alt="Deterjen">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Sabun &amp; Deterjen</h3>
                            <p>Produk kebersihan dari berbagai merek ternama harga grosir.</p><span
                                class="product-tag"><iconify-icon icon="mdi:soap"></iconify-icon> Kebersihan</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&q=80&auto=format&fit=crop"
                                alt="Kopi">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Kopi &amp; Teh</h3>
                            <p>Koleksi kopi sachet, teh celup, dan minuman instan pilihan.</p><span
                                class="product-tag"><iconify-icon icon="tabler:coffee"></iconify-icon> Minuman</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600&q=80&auto=format&fit=crop"
                                alt="Mie">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Mie Instan</h3>
                            <p>Semua merek dan rasa tersedia dalam kardus maupun satuan.</p><span
                                class="product-tag"><iconify-icon icon="ph:bowl-food-fill"></iconify-icon>
                                Makanan</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=600&q=80&auto=format&fit=crop"
                                alt="Air">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Minuman Kemasan</h3>
                            <p>Air mineral, jus, dan minuman ringan berbagai merek siap jual.</p><span
                                class="product-tag"><iconify-icon icon="tabler:droplet"></iconify-icon> Minuman</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&q=80&auto=format&fit=crop"
                                alt="Kebersihan">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Alat Kebersihan</h3>
                            <p>Sapu, pel, ember, dan perlengkapan bersih-bersih lengkap.</p><span
                                class="product-tag"><iconify-icon icon="mdi:broom"></iconify-icon> Peralatan</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=600&q=80&auto=format&fit=crop"
                                alt="Snack">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Permen &amp; Snack</h3>
                            <p>Aneka camilan dan permen untuk melengkapi stok warung Anda.</p><span
                                class="product-tag"><iconify-icon icon="ph:bowl-food-fill"></iconify-icon>
                                Makanan</span>
                        </div>
                    </div>
                    <!-- Duplicate for infinite -->
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80&auto=format&fit=crop"
                                alt="Beras">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Beras Premium</h3>
                            <p>Beras kualitas terbaik berbagai merek.</p><span class="product-tag"><iconify-icon
                                    icon="ph:grains-fill"></iconify-icon> Sembako</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80&auto=format&fit=crop"
                                alt="Minyak">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Minyak Goreng</h3>
                            <p>Minyak goreng curah dan kemasan berbagai merek.</p><span
                                class="product-tag"><iconify-icon icon="ph:grains-fill"></iconify-icon> Sembako</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1563453392212-326f5e854473?w=600&q=80&auto=format&fit=crop"
                                alt="Deterjen">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Sabun &amp; Deterjen</h3>
                            <p>Produk kebersihan dari merek ternama.</p><span class="product-tag"><iconify-icon
                                    icon="mdi:soap"></iconify-icon> Kebersihan</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&q=80&auto=format&fit=crop"
                                alt="Kopi">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Kopi &amp; Teh</h3>
                            <p>Kopi sachet, teh celup, dan minuman instan.</p><span class="product-tag"><iconify-icon
                                    icon="tabler:coffee"></iconify-icon> Minuman</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600&q=80&auto=format&fit=crop"
                                alt="Mie">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Mie Instan</h3>
                            <p>Semua merek tersedia lengkap kardus dan satuan.</p><span
                                class="product-tag"><iconify-icon icon="ph:bowl-food-fill"></iconify-icon>
                                Makanan</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=600&q=80&auto=format&fit=crop"
                                alt="Air">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Minuman Kemasan</h3>
                            <p>Air mineral dan minuman ringan berbagai merek.</p><span
                                class="product-tag"><iconify-icon icon="tabler:droplet"></iconify-icon> Minuman</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&q=80&auto=format&fit=crop"
                                alt="Kebersihan">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Alat Kebersihan</h3>
                            <p>Peralatan kebersihan rumah tangga lengkap.</p><span class="product-tag"><iconify-icon
                                    icon="mdi:broom"></iconify-icon> Peralatan</span>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="product-img"><img
                                src="https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=600&q=80&auto=format&fit=crop"
                                alt="Snack">
                            <div class="product-img-overlay"></div>
                        </div>
                        <div class="product-info">
                            <h3>Permen &amp; Snack</h3>
                            <p>Camilan dan permen pilihan terlengkap.</p><span class="product-tag"><iconify-icon
                                    icon="ph:bowl-food-fill"></iconify-icon> Makanan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="layanan">
        <div class="section-label reveal">Layanan Kami</div>
        <h2 class="section-title reveal d1">Kenapa Harus Belanja<br>di <em>Grosir Roni</em>?</h2>
        <div class="services-grid">
            <div class="service-card reveal d1">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:rosette-discount"></iconify-icon></div>
                    <h3>Harga Grosir Kompetitif</h3>
                    <p>Semakin banyak yang dibeli, semakin hemat. Cocok untuk pemilik warung dan UMKM se-Juntinyuat.</p>
                </div>
            </div>
            <div class="service-card reveal d2">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:truck-delivery"></iconify-icon></div>
                    <h3>Pengiriman ke Seluruh Daerah</h3>
                    <p>Layanan antar langsung ke depan pintu Anda. Melayani Juntinyuat, Indramayu, dan sekitarnya.</p>
                </div>
            </div>
            <div class="service-card reveal d3">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:package"></iconify-icon></div>
                    <h3>Stok Selalu Tersedia</h3>
                    <p>Gudang kami selalu terisi penuh. Tidak perlu khawatir kehabisan stok sewaktu-waktu.</p>
                </div>
            </div>
            <div class="service-card reveal d4">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:users-group"></iconify-icon></div>
                    <h3>Pelayanan Ramah &amp; Profesional</h3>
                    <p>Tim kami siap membantu dengan pelayanan yang cepat, ramah, dan profesional setiap harinya.</p>
                </div>
            </div>
            <div class="service-card reveal d5">
                <div class="sc">
                    <div class="service-icon"><iconify-icon icon="tabler:wallet"></iconify-icon></div>
                    <h3>Berbagai Metode Pembayaran</h3>
                    <p>Mendukung tunai, transfer bank, QRIS, dan dompet digital untuk kemudahan transaksi Anda.</p>
                </div>
            </div>
            <div class="service-card reveal d6">
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
                    <div class="angka-number">5K<span class="angka-plus">+</span></div>
                    <div class="angka-label">Pelanggan Aktif</div>
                </div>
            </div>
            <div class="angka-item reveal d2">
                <div class="angka-icon"><iconify-icon icon="tabler:box-seam"></iconify-icon></div>
                <div>
                    <div class="angka-number">500<span class="angka-plus">+</span></div>
                    <div class="angka-label">Jenis Produk</div>
                </div>
            </div>
            <div class="angka-item reveal d3">
                <div class="angka-icon"><iconify-icon icon="tabler:map-pin"></iconify-icon></div>
                <div>
                    <div class="angka-number">15<span class="angka-plus">+</span></div>
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
                    <p class="testi-text">"Tokonya lengkap banget! Pengiriman cepat dan produknya selalu kondisi baik.
                        Puas sekali belanja di sini!"</p>
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
                    <p class="testi-text">"Bahan-bahan selalu tersedia dan bisa diantar langsung ke tempat dengan harga
                        yang sangat bersaing. Terima kasih Grosir Roni!"</p>
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
                <a href="https://wa.me/6281234567890" class="btn-wa" target="_blank">
                    <iconify-icon icon="ic:baseline-whatsapp"></iconify-icon> Chat WhatsApp
                </a>
                <a href="https://maps.app.goo.gl/FS6zBUzt6vpAh7p3A" class="btn-outline-w">
                    <iconify-icon icon="tabler:map-pin"></iconify-icon> Lokasi Toko
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-brand-logo">
                    <iconify-icon icon="fluent:store-24-filled"></iconify-icon>
                    Grosir <span>Roni</span>
                </div>
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
                    <li><a href="/login"><iconify-icon icon="tabler:chevron-right"></iconify-icon>Admin Login</a>
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
        // ── PROGRESS BAR ──
        const bar = document.getElementById('progress-bar');
        window.addEventListener('scroll', () => {
            const pct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            bar.style.width = pct + '%';
        });

        // ── NAVBAR SCROLL ──
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 50));

        // ── BACK TO TOP ──
        const backTop = document.getElementById('backTop');
        window.addEventListener('scroll', () => backTop.classList.toggle('visible', window.scrollY > 400));
        backTop.addEventListener('click', () => window.scrollTo({
            top: 0,
            behavior: 'smooth'
        }));

        // ── HAMBURGER ──
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

        // ── SCROLL REVEAL ──
        const ro = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) e.target.classList.add('visible');
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => ro.observe(el));

        // ── COUNTER ANIMATION ──
        const ao = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const items = document.querySelectorAll('.angka-item');
                    const targets = [19, 5000, 500, 15];
                    items.forEach((item, i) => {
                        setTimeout(() => {
                            const numEl = item.querySelector('.angka-number');
                            let count = 0,
                                end = targets[i],
                                step = end / 60;
                            const timer = setInterval(() => {
                                count = Math.min(count + step, end);
                                const d = i === 1 ? Math.floor(count / 1000) + 'K' :
                                    Math.floor(count);
                                numEl.innerHTML = d +
                                    '<span class="angka-plus">+</span>';
                                if (count >= end) clearInterval(timer);
                            }, 18);
                        }, i * 150);
                    });
                    ao.disconnect();
                }
            });
        }, {
            threshold: 0.3
        });
        const angkaSection = document.getElementById('angka');
        if (angkaSection) ao.observe(angkaSection);

        // ── PRODUCT CAROUSEL DRAG / SWIPE (touch & mouse) ──
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
            const walk = (x - startX) * 1.8;
            prodCarousel.scrollLeft = scrollLeft - walk;
        });

        // Touch support
        prodCarousel.addEventListener('touchstart', e => {
            startX = e.touches[0].pageX;
            scrollLeft = prodCarousel.scrollLeft;
            prodTrack.classList.add('paused');
        }, {
            passive: true
        });
        prodCarousel.addEventListener('touchend', () => prodTrack.classList.remove('paused'));
        prodCarousel.addEventListener('touchmove', e => {
            const x = e.touches[0].pageX;
            const walk = (startX - x) * 1.5;
            prodCarousel.scrollLeft = scrollLeft + walk;
        }, {
            passive: true
        });

        // ── SMOOTH ACTIVE NAV ──
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-links a');
        const observerNav = new IntersectionObserver((entries) => {
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
    </script>
</body>

</html>
<?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views/welcome.blade.php ENDPATH**/ ?>