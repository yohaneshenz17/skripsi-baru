<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
    <meta name="author" content="Creative Tim">
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
    <style>
        .imgSize{
            width: 50px !important;
            height: 50px !important;
        }
    </style>
</head>

<body>
    <!-- Header Section Start -->
    <header id="slider-area">
        <nav class="navbar navbar-expand-md fixed-top scrolling-navbar bg-white">
            <div class="container">
                <img class="imgSize" src="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" alt="icon">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="lni-menu"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mr-auto w-100 justify-content-end">
                        <li class="nav-item mt-1">
                             <a class="nav-link page-scroll" href="#slider-area">Home</a>
                        </li>
                        <li class="nav-item mt-1">
                             <a class="nav-link page-scroll" href="#tentang_kami">Tentang Kami</a>
                        </li>
                        <li class="nav-item mt-1">
                            <a class="nav-link page-scroll" href="#contact">Kontak</a>
                        </li>

                        <li class="nav-item ml-3 text-center">
                            <a class="btn btn-primary" href="<?= base_url('home/registrasi'); ?>">Registrasi</a>
                        </li>

                        <li class="nav-item ml-2 text-center">
                            <a class="btn btn-primary" href="<?= base_url('auth/login'); ?>">LOGIN</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Carousel Section -->
        <div id="carousel-area">
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
                        <div class="carousel-caption text-left">
                            <h3 class="wow fadeInRight" data-wow-delay="0.2s"><?= $carousel_subtitle1; ?></h3>
                                <h2 class="wow fadeInRight" data-wow-delay="0.4s"><?= $carousel_title1; ?></h2>
                                <h4 class="wow fadeInRight" data-wow-delay="0.6s"><?= $carousel_description1; ?></h4>
                                <a href="<?= $carousel_btn_href1; ?>" class="btn btn-lg btn-common btn-effect wow fadeInRight" data-wow-delay="0.9s"><?= $carousel_btn_text1; ?></a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="overlay"></div>
                        <img src="<?= base_url('assets/essence/img/slider/') . $carousel_bg2; ?>" alt="img">
                        <div class="carousel-caption text-center">
                            <h3 class="wow fadeInRight" data-wow-delay="0.2s"><?= $carousel_subtitle2; ?></h1>
                                <h2 class="wow fadeInRight" data-wow-delay="0.4s"><?= $carousel_title2; ?></h2>
                                <h4 class="wow fadeInRight" data-wow-delay="0.6s"><?= $carousel_description2; ?></h4>
                                <a href="<?= $carousel_btn_href2; ?>" class="btn btn-lg btn-common btn-effect wow fadeInRight" data-wow-delay="0.9s"><?= $carousel_btn_text2; ?></a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="overlay"></div>
                        <img src="<?= base_url('assets/essence/img/slider/') . $carousel_bg3; ?>" alt="img">
                        <div class="carousel-caption text-center">
                            <h3 class="wow fadeInRight" data-wow-delay="0.2s"><?= $carousel_subtitle3; ?></h1>
                                <h2 class="wow fadeInRight" data-wow-delay="0.4s"><?= $carousel_title3; ?></h2>
                                <h4 class="wow fadeInRight" data-wow-delay="0.6s"><?= $carousel_description3; ?></h4>
                                <a href="<?= $carousel_btn_href3; ?>" class="btn btn-lg btn-common btn-effect wow fadeInRight" data-wow-delay="0.9s"><?= $carousel_btn_text3; ?></a>
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
    <!-- Header Section End -->

    <!-- Services Section Start -->
    <section id="tentang_kami" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Tentang Kami</h2>
                <p class="section-subtitle"><?= $tentang_kami_subtitle; ?></p>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="item-boxes services-item wow fadeInDown shadow border-0" data-wow-delay="0.2s">
                        <div class="icon color-3">
                            <i class="lni-user"></i>
                        </div>
                        <p><?= $tentang_kami_isi; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Services Section End -->

    <!-- Footer Section Start -->
    <footer>
        <!-- Footer Area Start -->
        <section id="contact" class="section footer-Content">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Kontak</h2> 
                    <p class="section-subtitle"><?= $kontak_subtitle; ?></p>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 col-mb-12">
                        <h3 class="block-title">Akun Media Sosial</h3>
                        <div class="textwidget">
                            <p><?= $social_description ?></p>
                        </div>
                        <ul class="footer-social">
                            <li><a class="facebook" href="<?= $link_fb; ?>"><i class="lni-facebook-filled"></i></a></li>
                            <li><a class="twitter" href="<?= $link_twitter; ?>"><i class="lni-twitter-filled"></i></a></li>
                        </ul>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 col-mb-12">
                        <div class="widget">
                            <h3 class="block-title">Hubungi Kami</h3>
                            <ul class="contact-footer">
                                <li>
                                    <strong>Address :</strong> <span><?= $alamat; ?></span>
                                </li>
                                <li>
                                    <strong>Phone :</strong> <span><?= $phone; ?></span>
                                </li>
                                <li>
                                    <strong>E-mail :</strong> <span><a href="#"><?= $email; ?></a></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer area End -->

        <!-- Copyright Start  -->
        <div id="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="site-info float-left">
                            <p>Copyright &copy; 2022 Kodeaset, All rights reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Copyright End -->

    </footer>
    <!-- Footer Section End -->

    <!-- Go To Top Link -->
    <a href="#" class="back-to-top">
        <i class="lni-arrow-up"></i>
    </a>

    <div id="loader">
        <div class="spinner">
            <div class="double-bounce1"></div>
            <div class="double-bounce2"></div>
        </div>
    </div>

    <!-- jQuery first, then Tether, then Bootstrap JS. -->
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

</body>
</html>