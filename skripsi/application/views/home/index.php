<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Informasi Skripsi STK St. Yakobus Merauke">
    <meta name="author" content="STK St. Yakobus">
    <title><?= $page_title; ?></title>
    <?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) ?>
    <link rel="icon" href="<?= base_url() ?>cdn/img/icons/<?= ($app->icon) ? $app->icon : 'default.png' ?>" type="image/png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/line-icons.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/owl.carousel.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/owl.theme.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/nivo-lightbox.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/magnific-popup.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/animate.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/color-switcher.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/menu_sideslide.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/main.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/responsive.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/custom.css">
    <link rel="stylesheet" id="colors" href="<?= base_url(); ?>assets/essence/css/colors/blue.css" type="text/css" />
    <link rel="stylesheet" href="<?= base_url(); ?>assets/essence/css/color-switcher.css" type="text/css" />
    <!-- Font Awesome for social media icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Logo kampus lebih besar */
        .imgSize {
            width: 65px !important;
            height: 65px !important;
            transition: transform 0.3s ease;
        }
        
        .imgSize:hover {
            transform: scale(1.1);
        }
        
        /* Modern navbar dengan tombol menu lebih besar */
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95) !important;
            transition: all 0.3s ease;
            padding: 15px 0;
        }
        
        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            font-size: 16px !important;
            padding: 12px 20px !important;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: #007bff;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        /* Tombol registrasi dan login sejajar tanpa tumpang tindih */
        .auth-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: 20px;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-registrasi {
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
            color: white !important;
            border: none;
            min-width: 120px;
        }
        
        .btn-registrasi:hover {
            background: linear-gradient(45deg, #5f4bc2, #8b7ed8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white !important;
            text-decoration: none;
        }
        
        .btn-login {
            background: linear-gradient(45deg, #fd79a8, #fdcb6e);
            color: white !important;
            border: none;
            min-width: 100px;
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #e84393, #f39c12);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white !important;
            text-decoration: none;
        }
        
        .btn-outline-white {
            border: 2px solid white;
            color: white;
            background: transparent;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-outline-white:hover {
            background: white;
            color: #333;
            text-decoration: none;
        }
        
        .btn-group-modern {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }
        
        /* Responsive untuk mobile */
        @media (max-width: 991px) {
            .auth-buttons {
                flex-direction: column;
                gap: 10px;
                margin-left: 0;
                margin-top: 15px;
                width: 100%;
            }
            
            .btn-modern {
                width: 100%;
                justify-content: center;
            }
            
            .navbar-collapse {
                background: rgba(255, 255, 255, 0.98);
                border-radius: 10px;
                margin-top: 10px;
                padding: 20px;
                box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            }
        }
        
        @media (max-width: 768px) {
            .btn-group-modern {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-group-modern .btn {
                width: 100%;
                margin: 0;
            }
        }
        
        /* Carousel improvements - tulisan keterangan rata tengah */
        .carousel-item {
            transition: transform 0.6s ease-in-out;
        }
        
        .carousel-indicators li {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }
        
        .carousel-indicators .active {
            background-color: #007bff;
        }
        
        /* Styling untuk carousel caption agar berada di tengah */
            .carousel-caption {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center !important; /* Memastikan perataan tengah */
            color: white;
            padding: 40px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 20px;
            max-width: 98%; /* Kotak diperlebar dari 90% menjadi 95% */
            width: auto;
            z-index: 10;
        }
        
        /* Memastikan semua teks di dalam caption rata tengah */
        .carousel-caption h2,
        .carousel-caption h3,
        .carousel-caption h4 {
            text-align: center !important;
            color: white;
        }
        
        .carousel-caption h2 {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            margin-bottom: 15px;
        }
        
        .carousel-caption h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }
        
        .carousel-caption h4 {
            font-size: 1.1rem;
            margin-bottom: 25px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
            line-height: 1.6;
        }
        
        .carousel-caption .btn-group-modern {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .carousel-caption .btn-group-modern .btn {
            margin: 0;
            padding: 12px 25px;
            font-size: 1rem;
            border-radius: 25px;
            min-width: 180px;
            text-align: center;
        }
        
        /* Override text-left untuk semua slide menjadi center */
        .carousel-caption.text-left,
        .carousel-caption.text-center {
            text-align: center !important;
        }
        
        .carousel-caption.text-left h2,
        .carousel-caption.text-left h3,
        .carousel-caption.text-left h4,
        .carousel-caption.text-center h2,
        .carousel-caption.text-center h3,
        .carousel-caption.text-center h4 {
            text-align: center !important;
        }
        
        /* Styling untuk tombol single (seperti di file asli) */
        .carousel-caption .btn-lg {
            padding: 15px 35px;
            font-size: 1.1rem;
            border-radius: 30px;
            margin-top: 20px;
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        
        @media (max-width: 992px) {
            .carousel-caption {
                padding: 30px 25px;
                max-width: 95%;
            }
            
            .carousel-caption h2 {
                font-size: 2rem;
            }
            
            .carousel-caption h3 {
                font-size: 1.2rem;
            }
            
            .carousel-caption h4 {
                font-size: 1rem;
            }
            
            .carousel-caption .btn-group-modern .btn {
                min-width: 150px;
            }
            
            .carousel-caption .btn-lg {
                min-width: 180px;
                padding: 12px 30px;
            }
        }
        
        @media (max-width: 768px) {
            .carousel-caption {
                padding: 25px 20px;
                max-width: 95%;
            }
            
            .carousel-caption h2 {
                font-size: 1.8rem;
            }
            
            .carousel-caption h3 {
                font-size: 1.1rem;
            }
            
            .carousel-caption h4 {
                font-size: 0.95rem;
            }
            
            .carousel-caption .btn-group-modern {
                flex-direction: column;
                gap: 10px;
            }
            
            .carousel-caption .btn-group-modern .btn {
                min-width: 200px;
            }
            
            .carousel-caption .btn-lg {
                min-width: 200px;
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .carousel-caption {
                padding: 20px 15px;
            }
            
            .carousel-caption h2 {
                font-size: 1.5rem;
            }
            
            .carousel-caption h3 {
                font-size: 1rem;
            }
            
            .carousel-caption h4 {
                font-size: 0.9rem;
            }
            
            .carousel-caption .btn-lg {
                min-width: 180px;
                padding: 10px 20px;
                font-size: 0.95rem;
            }
        }
        
        /* Section improvements - jarak antara Tentang Kami dan Kontak lebih dekat */
        .section-padding {
            padding: 60px 0;
        }
        
        .section-padding-small {
            padding: 40px 0;
        }
        
        .section-header {
            margin-bottom: 40px;
        }
        
        .section-header h2 {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        /* Menghapus garis biru yang tidak rapi */
        /* .section-header h2::after sudah dihapus */
        
        /* Card improvements */
        .card, .item-boxes {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            padding: 30px;
            text-align: center;
        }
        
        .card:hover, .item-boxes:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }
        
        .item-boxes .icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .item-boxes p {
            font-size: 1rem;
            line-height: 1.6;
            color: #666;
            margin-bottom: 0;
        }
        
        /* Contact section - tampilan yang lebih menarik */
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }
        
        .contact-card h3 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .contact-info {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .contact-info li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .contact-info li:last-child {
            border-bottom: none;
        }
        
        .contact-info .icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1rem;
        }
        
        .contact-info .text {
            flex: 1;
        }
        
        .contact-info strong {
            color: #333;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        
        .contact-info span {
            color: #666;
            font-size: 0.95rem;
        }
        
        .contact-info a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .contact-info a:hover {
            color: #764ba2;
            text-decoration: none;
        }
        
        .social-media-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .social-media-card h3 {
            color: white;
            margin-bottom: 20px;
        }
        
        .social-media-card p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 25px;
        }
        
        .footer-social {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .footer-social li a {
            display: inline-block;
            width: 50px;
            height: 50px;
            line-height: 50px;
            text-align: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            text-decoration: none;
        }
        
        .footer-social li a:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            text-decoration: none;
        }
        
        /* Footer improvements - menu sejajar ke kanan */
        .footer-area {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .footer-left {
            flex: 1;
            min-width: 300px;
        }
        
        .footer-left h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .footer-left p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }
        
        .footer-right {
            flex-shrink: 0;
        }
        
        .footer-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            align-items: center;
        }
        
        .footer-menu li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
            display: block;
            white-space: nowrap;
        }
        
        .footer-menu li a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-left {
                min-width: 100%;
            }
            
            .footer-menu {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
        }
        
        #copyright {
            background: rgba(0, 0, 0, 0.1);
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        #copyright .site-info p {
            color: white !important;
            margin: 0;
        }
        
        /* Overlay untuk kontras yang lebih baik */
        .carousel-item .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        /* Pastikan caption berada di atas overlay */
        .carousel-caption {
            z-index: 10;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Loader styles */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            position: relative;
        }
        
        .double-bounce1, .double-bounce2 {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: #667eea;
            opacity: 0.6;
            position: absolute;
            top: 0;
            left: 0;
            animation: sk-bounce 2.0s infinite ease-in-out;
        }
        
        .double-bounce2 {
            animation-delay: -1.0s;
        }
        
        @keyframes sk-bounce {
            0%, 100% {
                transform: scale(0);
            }
            50% {
                transform: scale(1);
            }
        }
        
        /* Hide line decoration */
        .line {
            display: none;
        }
        
        /* Background gray for sections */
        .bg-gray {
            background: #f8f9fa;
        }
    <style>
        .imgSize{
            width: 50px !important;
            height: 50px !important;
        }

        /* CSS BARU UNTUK CAROUSEL */
        .carousel-caption {
            background: rgba(0, 0, 0, 0.5); /* Latar belakang semi-transparan */
            border-radius: 20px; /* Sudut membulat */
            padding: 30px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Posisi tepat di tengah */
            width: 80%; /* Lebar kotak */
            max-width: 700px; /* Lebar maksimum */
            text-align: center !important; /* Memaksa teks rata tengah */
        }

        .carousel-caption h2,
        .carousel-caption h3,
        .carousel-caption h4 {
            text-align: center !important; /* Memastikan semua heading rata tengah */
            animation-name: fadeInUp; /* Mengganti animasi agar muncul dari bawah */
        }

        .carousel-caption .btn {
            animation-name: fadeInUp; /* Menyamakan animasi tombol */
        }

        .overlay {
            background-color: rgba(0,0,0,0.3); /* Membuat gambar latar lebih gelap agar teks terbaca */
        }
    </style>
</head>

<body>
    <!-- Header Top Area -->
    <header id="header-wrap">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg fixed-top scrolling-navbar indigo" id="slider-area">
            <div class="container">
                <a class="navbar-brand" href="<?= base_url() ?>">
                    <img class="imgSize" src="<?= base_url() ?>cdn/img/icons/<?= ($app->icon) ? $app->icon : 'default.png' ?>" alt="STK St. Yakobus">
                </a>
                
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="lni-menu"></i>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mr-auto w-100 justify-content-end">
                        <li class="nav-item">
                             <a class="nav-link page-scroll" href="#slider-area">Home</a>
                        </li>
                        <li class="nav-item">
                             <a class="nav-link" href="<?= base_url() ?>pengumuman">
                                 <i class="lni-bullhorn mr-1"></i>Pengumuman
                             </a>
                        </li>
                        <li class="nav-item">
                             <a class="nav-link page-scroll" href="#tentang_kami">Tentang Kami</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link page-scroll" href="#contact">Kontak</a>
                        </li>
                    </ul>
                    
                    <!-- Tombol sejajar tanpa tumpang tindih -->
                    <div class="auth-buttons">
                        <a class="btn btn-modern btn-registrasi" href="<?= base_url('home/registrasi'); ?>">
                            <i class="lni-user mr-2"></i>Registrasi
                        </a>
                        <a class="btn btn-modern btn-login" href="<?= base_url('auth/login'); ?>">
                            <i class="lni-enter mr-2"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Carousel Section -->
        <div id="slider-area" class="carousel-area">
                <div id="carousel-slider" class="carousel slide carousel-fade" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carousel-slider" data-slide-to="0" class="active"></li>
                <li data-target="#carousel-slider" data-slide-to="1"></li>
                <li data-target="#carousel-slider" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner" role="listbox">
                <div class="carousel-item active">
                    <div class="overlay"></div>
                    <img src="<?= base_url('assets/essence/img/slider/') . $carousel_bg1; ?>" alt="img">
                    <div class="carousel-caption">
                        <h3 class="wow fadeInUp" data-wow-delay="0.2s"><?= $carousel_subtitle1; ?></h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.4s"><?= $carousel_title1; ?></h2>
                        <h4 class="wow fadeInUp" data-wow-delay="0.6s"><?= $carousel_description1; ?></h4>
                        <a href="<?= $carousel_btn_href1; ?>" class="btn btn-lg btn-common btn-effect wow fadeInUp" data-wow-delay="0.9s"><?= $carousel_btn_text1; ?></a>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="overlay"></div>
                    <img src="<?= base_url('assets/essence/img/slider/') . $carousel_bg2; ?>" alt="img">
                    <div class="carousel-caption">
                        <h3 class="wow fadeInUp" data-wow-delay="0.2s"><?= $carousel_subtitle2; ?></h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.4s"><?= $carousel_title2; ?></h2>
                        <h4 class="wow fadeInUp" data-wow-delay="0.6s"><?= $carousel_description2; ?></h4>
                        <a href="<?= $carousel_btn_href2; ?>" class="btn btn-lg btn-common btn-effect wow fadeInUp" data-wow-delay="0.9s"><?= $carousel_btn_text2; ?></a>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="overlay"></div>
                    <img src="<?= base_url('assets/essence/img/slider/') . $carousel_bg3; ?>" alt="img">
                    <div class="carousel-caption">
                        <h3 class="wow fadeInUp" data-wow-delay="0.2s"><?= $carousel_subtitle3; ?></h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.4s"><?= $carousel_title3; ?></h2>
                        <h4 class="wow fadeInUp" data-wow-delay="0.6s"><?= $carousel_description3; ?></h4>
                        <a href="<?= $carousel_btn_href3; ?>" class="btn btn-lg btn-common btn-effect wow fadeInUp" data-wow-delay="0.9s"><?= $carousel_btn_text3; ?></a>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#carousel-slider" role="button" data-slide="prev">
                <span class="carousel-control" aria-hidden="true"><i class="lni-chevron-left"></i></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carousel-slider" role="button" data-slide="next">
                <span class="carousel-control" aria-hidden="true"><i class="lni-chevron-right"></i></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>
    </header>

    <!-- About Section -->
    <section id="tentang_kami" class="section-padding">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Tentang Kami</h2>
                <p><?= $tentang_kami_subtitle ?></p>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="item-boxes services-item wow fadeInDown shadow border-0" data-wow-delay="0.2s">
                        <div class="icon color-3">
                            <i class="lni-user"></i>
                        </div>
                        <p><?= $tentang_kami_isi ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding-small bg-gray">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Kontak</h2>
                <p><?= $kontak_subtitle ?></p>
            </div>
            
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="social-media-card">
                        <h3><i class="lni-share mr-2"></i>Akun Media Sosial</h3>
                        <p><?= $social_description ?></p>
                        <ul class="footer-social">
                            <li><a class="facebook" href="<?= $link_fb; ?>"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a class="twitter" href="<?= $link_twitter; ?>"><i class="fab fa-twitter"></i></a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="contact-card">
                        <h3><i class="lni-phone mr-2"></i>Hubungi Kami</h3>
                        <ul class="contact-info">
                            <li>
                                <div class="icon">
                                    <i class="lni-map-marker"></i>
                                </div>
                                <div class="text">
                                    <strong>Alamat</strong>
                                    <span><?= $alamat ?></span>
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <i class="lni-phone"></i>
                                </div>
                                <div class="text">
                                    <strong>Telepon</strong>
                                    <span><?= $phone ?></span>
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <i class="lni-envelope"></i>
                                </div>
                                <div class="text">
                                    <strong>Email</strong>
                                    <span><a href="mailto:<?= $email ?>"><?= $email ?></a></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="footer-area section-padding">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <h3>STK St. Yakobus Merauke</h3>
                    <p>Sistem Informasi Manajemen Tugas Akhir Terintegrasi</p>
                </div>
                
                <div class="footer-right">
                    <ul class="footer-menu">
                        <li><a href="<?= base_url() ?>">Home</a></li>
                        <li><a href="<?= base_url('pengumuman') ?>">Pengumuman</a></li>
                        <li><a href="<?= base_url('home/registrasi') ?>">Registrasi</a></li>
                        <li><a href="<?= base_url('auth/login') ?>">Login</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div id="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="site-info text-center">
                            <p>&copy; <?= date('Y') ?> Made With Love by SIPD STK. All Rights Reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Go to Top Link -->
    <a href="#" class="back-to-top">
        <i class="lni-chevron-up"></i>
    </a>

    <div id="loader">
        <div class="spinner">
            <div class="double-bounce1"></div>
            <div class="double-bounce2"></div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="<?= base_url(); ?>assets/essence/js/jquery-min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/popper.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/bootstrap.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/classie.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.mixitup.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/nivo-lightbox.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/owl.carousel.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.stellar.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.nav.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/scrolling-nav.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.easing.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/wow.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.vide.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.counterup.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/jquery.magnific-popup.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/waypoints.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/form-validator.min.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/contact-form-script.js"></script>
    <script src="<?= base_url(); ?>assets/essence/js/main.js"></script>
    
    <script>
        // Modern navbar scroll effect
        $(window).scroll(function() {
            if ($(window).scrollTop() > 100) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });
        
        // Modern smooth scrolling
        $('a[href^="#"]').on('click', function(event) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                event.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 80
                }, 1000);
            }
        });
    </script>
</body>
</html>