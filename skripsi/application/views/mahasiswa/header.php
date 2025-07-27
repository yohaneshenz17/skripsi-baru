<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'Bimbingan' ?> - SIM Tugas Akhir</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-title {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .btn-primary:hover {
            background-color: #5a67d8;
            border-color: #5a67d8;
        }
        
        .badge {
            font-size: 0.8em;
        }
        
        .progress {
            height: 8px;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .table {
            font-size: 0.9em;
        }
        
        .modal-content {
            border-radius: 10px;
            border: none;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .status-card {
            border-left: 4px solid #667eea;
        }
        
        @media (max-width: 768px) {
            .header-title h1 {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #667eea;">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('mahasiswa/dashboard') ?>">
                <i class="fas fa-graduation-cap"></i> SIM Tugas Akhir
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('mahasiswa/dashboard') ?>">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('mahasiswa/proposal') ?>">
                            <i class="fas fa-file-alt"></i> Proposal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('mahasiswa/bimbingan') ?>">
                            <i class="fas fa-book"></i> Bimbingan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('mahasiswa/seminar') ?>">
                            <i class="fas fa-calendar"></i> Seminar
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= $this->session->userdata('nama') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('mahasiswa/profil') ?>">
                                <i class="fas fa-user-edit"></i> Profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Title -->
    <div class="header-title">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-book"></i> 
                        <?= isset($title) ? $title : 'Bimbingan Skripsi' ?>
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">STK Santo Yakobus Merauke</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Flash Messages -->
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <?= $this->session->flashdata('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>