<?php 
$app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= $app->nama ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand img {
            height: 40px;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .announcement-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        .announcement-number {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .deadline-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
        }
        .deadline-active {
            background-color: #d4edda;
            color: #155724;
        }
        .deadline-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .deadline-expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        .back-to-home {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-to-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <img src="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" alt="<?= $app->nama ?>">
                <span class="ms-2 fw-bold" style="font-size: 1.0rem;">STK St. Yakobus Merauke</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" href="<?= base_url() ?>pengumuman">Pengumuman</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>#tentang_kami">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>#contact">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="<?= base_url() ?>auth/login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-bullhorn me-3"></i>
                        Pengumuman Tahapan Skripsi
                    </h1>
                    <p class="lead mb-4">
                        Informasi terkini mengenai jadwal dan tahapan tugas akhir mahasiswa Sekolah Tinggi Katolik Santo Yakobus Merauke
                    </p>
                    <a href="<?= base_url() ?>" class="back-to-home">
                        <i class="fas fa-home me-2"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Pengumuman Content -->
    <section class="py-5">
        <div class="container">
            <?php if (!empty($pengumuman)): ?>
                <div class="row">
                    <?php foreach($pengumuman as $p): ?>
                        <?php
                        // Hitung status deadline
                        $deadline_date = new DateTime($p->tanggal_deadline);
                        $today = new DateTime();
                        $interval = $today->diff($deadline_date);
                        
                        if ($today > $deadline_date) {
                            $status_class = 'deadline-expired';
                            $status_text = 'Telah lewat';
                            $status_icon = 'fas fa-times-circle';
                        } elseif ($interval->days <= 7) {
                            $status_class = 'deadline-warning';
                            $status_text = $interval->days . ' hari lagi';
                            $status_icon = 'fas fa-exclamation-triangle';
                        } else {
                            $status_class = 'deadline-active';
                            $status_text = $interval->days . ' hari lagi';
                            $status_icon = 'fas fa-check-circle';
                        }
                        ?>
                        <div class="col-lg-6 col-md-12 mb-4">
                            <div class="card announcement-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="announcement-number me-3">
                                            <?= $p->no ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($p->tahapan) ?></h5>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                                <span class="text-muted">
                                                    <?= date('d F Y', strtotime($p->tanggal_deadline)) ?>
                                                </span>
                                            </div>
                                            <span class="deadline-badge <?= $status_class ?>">
                                                <i class="<?= $status_icon ?> me-1"></i>
                                                <?= $status_text ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if (!empty($p->keterangan)): ?>
                                        <div class="mt-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Keterangan:
                                            </h6>
                                            <p class="card-text text-secondary">
                                                <?= nl2br(htmlspecialchars($p->keterangan)) ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pt-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Deadline: <?= date('d/m/Y', strtotime($p->tanggal_deadline)) ?>
                                        </small>
                                        <?php if ($today <= $deadline_date): ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-bell me-1"></i>
                                                Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-history me-1"></i>
                                                Berakhir
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Belum Ada Pengumuman</h3>
                    <p>Saat ini belum ada pengumuman tahapan skripsi yang aktif.</p>
                    <a href="<?= base_url() ?>" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i> Kembali ke Beranda
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h5 class="mb-3">Sekolah Tinggi Katolik Santo Yakobus Merauke</h5>
                    <p class="mb-0">Sistem Informasi Manajemen Tugas Akhir untuk memudahkan monitoring dan pengelolaan skripsi mahasiswa.</p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <div class="mb-3">
                        <a href="<?= base_url() ?>" class="text-white me-3">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                        <a href="<?= base_url() ?>pengumuman" class="text-white me-3">
                            <i class="fas fa-bullhorn"></i> Pengumuman
                        </a>
                        <a href="<?= base_url() ?>auth/login" class="text-white">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                    <p class="mb-0">&copy; <?= date('Y') ?> SIPD STK St. Yakobus Merauke. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>