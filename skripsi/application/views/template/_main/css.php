<?php
// =========================================================
// FILE: application/views/template/_main/css.php (ENHANCED)
// =========================================================
?>
<?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) ?>
<link rel="icon" href="<?= base_url() ?>cdn/img/icons/<?= ($app->icon) ? $app->icon : 'default.png' ?>" type="image/png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
<link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/nucleo/css/nucleo.css" type="text/css">
<link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
<link rel="stylesheet" href="<?= base_url() ?>cdn/css/argon.css?v=1.2.0" type="text/css">
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/sweetalert2/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="<?= base_url() ?>assets/select2/select2-bootstrap4.min.css">

<!-- DataTables CSS (EXISTING) -->
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<style>
    /* ============================================
       EXISTING STYLES (PRESERVED)
    ============================================ */
    .card-content {
        min-height: 450px;
    }

    textarea {
        resize: none;
    }

    .foto-fluid {
        height: 100%;
        width: 100%;
    }
    
    /* Style untuk Export Buttons (EXISTING) */
    .dt-buttons {
        margin-bottom: 10px;
    }
    
    .dt-button {
        margin-right: 5px;
    }
    
    .dt-button-collection {
        border-radius: 4px;
    }

    /* ============================================
       ENHANCED STYLES (NEW - FOR DASHBOARD FIX)
    ============================================ */
    
    /* FIXED: Sidebar Menu Contrast - PALING PENTING */
    .navbar-vertical .navbar-nav .nav-link {
        color: #525f7f !important;
        font-weight: 500;
        transition: all 0.15s ease;
        padding: 0.75rem 1rem;
        margin: 0 0.5rem;
        border-radius: 0.375rem;
    }
    
    .navbar-vertical .navbar-nav .nav-link:hover {
        color: #5e72e4 !important;
        background-color: rgba(94, 114, 228, 0.05);
    }
    
    .navbar-vertical .navbar-nav .nav-link.active {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .navbar-vertical .navbar-nav .nav-link.active i {
        color: #fff !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link.active .nav-link-text {
        color: #fff !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link.active:hover {
        background: linear-gradient(87deg, #4c6ef5 0, #7048e8 100%) !important;
        color: #fff !important;
    }
    
    .navbar-vertical .navbar-nav .nav-item {
        margin-bottom: 0.25rem;
    }
    
    .navbar-vertical .navbar-nav .nav-link i {
        margin-right: 0.75rem;
        font-size: 1rem;
    }

    /* ============================================
       🔥 CRITICAL: HEADER CONTRAST FIX
       ============================================ */
    
    /* FORCE HEADER BACKGROUND WITH HIGH SPECIFICITY */
    html body div.main-content div.header.bg-primary,
    body div.main-content div.header.bg-primary,
    div.main-content div.header.bg-primary,
    .main-content .header.bg-primary,
    .header.bg-primary,
    #panel .header.bg-primary {
        background: #1a365d !important;
        background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%) !important;
        position: relative !important;
    }
    
    /* FORCE TITLE COLOR WITH MAXIMUM SPECIFICITY */
    html body div.main-content div.header.bg-primary h6.h2.text-white,
    html body div.main-content div.header.bg-primary h6.h2,
    html body div.main-content div.header.bg-primary .h2,
    body .main-content .header.bg-primary h6.h2,
    body .main-content .header.bg-primary .h2,
    .main-content .header.bg-primary h6.h2.text-white,
    .main-content .header.bg-primary h6.h2,
    .main-content .header.bg-primary .h2,
    .header.bg-primary h6.h2.text-white,
    .header.bg-primary h6.h2,
    .header.bg-primary .h2,
    .header.bg-primary h6,
    .header.bg-primary h1,
    .header.bg-primary h2,
    .header.bg-primary h3,
    .header.bg-primary h4,
    .header.bg-primary h5 {
        color: #ffffff !important;
        font-weight: 700 !important;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7) !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
        font-size: 1.5rem !important;
        line-height: 1.3 !important;
    }
    
    /* FORCE BREADCRUMB COLOR WITH MAXIMUM SPECIFICITY */
    html body div.main-content div.header.bg-primary .breadcrumb-dark .breadcrumb-item,
    body .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item,
    .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item,
    .header.bg-primary .breadcrumb-dark .breadcrumb-item,
    .header.bg-primary .breadcrumb .breadcrumb-item,
    .header.bg-primary nav .breadcrumb-item,
    .header.bg-primary .breadcrumb-item {
        color: rgba(255, 255, 255, 0.95) !important;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6) !important;
        opacity: 1 !important;
        visibility: visible !important;
        font-weight: 500 !important;
    }
    
    /* FORCE BREADCRUMB LINKS */
    html body div.main-content div.header.bg-primary .breadcrumb-dark .breadcrumb-item a,
    body .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item a,
    .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item a,
    .header.bg-primary .breadcrumb-dark .breadcrumb-item a,
    .header.bg-primary .breadcrumb .breadcrumb-item a,
    .header.bg-primary nav .breadcrumb-item a,
    .header.bg-primary .breadcrumb-item a {
        color: #ffffff !important;
        text-decoration: none !important;
        font-weight: 600 !important;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6) !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* FORCE BREADCRUMB ACTIVE */
    html body div.main-content div.header.bg-primary .breadcrumb-dark .breadcrumb-item.active,
    body .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item.active,
    .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item.active,
    .header.bg-primary .breadcrumb-dark .breadcrumb-item.active,
    .header.bg-primary .breadcrumb .breadcrumb-item.active,
    .header.bg-primary nav .breadcrumb-item.active,
    .header.bg-primary .breadcrumb-item.active {
        color: #ffffff !important;
        font-weight: 700 !important;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6) !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* FORCE BREADCRUMB SEPARATOR */
    html body div.main-content div.header.bg-primary .breadcrumb-dark .breadcrumb-item + .breadcrumb-item::before,
    body .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item + .breadcrumb-item::before,
    .main-content .header.bg-primary .breadcrumb-dark .breadcrumb-item + .breadcrumb-item::before,
    .header.bg-primary .breadcrumb-dark .breadcrumb-item + .breadcrumb-item::before,
    .header.bg-primary .breadcrumb .breadcrumb-item + .breadcrumb-item::before {
        content: ">" !important;
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 700 !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
    }
    
    /* FORCE HOME ICON */
    html body div.main-content div.header.bg-primary .fas.fa-home,
    body .main-content .header.bg-primary .fas.fa-home,
    .main-content .header.bg-primary .fas.fa-home,
    .header.bg-primary .fas.fa-home,
    .header.bg-primary i.fas.fa-home {
        color: #ffffff !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* FORCE BACK BUTTON */
    html body div.main-content div.header.bg-primary .btn-neutral,
    body .main-content .header.bg-primary .btn-neutral,
    .main-content .header.bg-primary .btn-neutral,
    .header.bg-primary .btn-neutral,
    .header.bg-primary button.btn-neutral {
        background-color: #ffffff !important;
        color: #1a365d !important;
        border: 2px solid #ffffff !important;
        font-weight: 700 !important;
        text-shadow: none !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .header.bg-primary .btn-neutral:hover {
        background-color: #f7fafc !important;
        color: #1a365d !important;
        border-color: #f7fafc !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    }
    
    /* NAVBAR TOP */
    html body .navbar-top.bg-primary,
    body .navbar-top.bg-primary,
    .navbar-top.bg-primary {
        background: #1a365d !important;
        background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%) !important;
    }
    
    /* NAVBAR USER TEXT */
    html body .navbar-top.bg-primary .nav-link .media-body span,
    body .navbar-top.bg-primary .nav-link .media-body span,
    .navbar-top.bg-primary .nav-link .media-body span {
        color: #ffffff !important;
        font-weight: 700 !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* SEARCH FORM */
    .navbar-top.bg-primary .navbar-search .input-group-text {
        background-color: rgba(255, 255, 255, 0.2) !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
        color: #ffffff !important;
    }
    
    .navbar-top.bg-primary .navbar-search .form-control {
        background-color: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
        color: #ffffff !important;
    }
    
    .navbar-top.bg-primary .navbar-search .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    /* Badge improvements for Dashboard */
    .badge-soft-primary {
        color: #5e72e4;
        background-color: rgba(94, 114, 228, 0.1);
        border: 1px solid rgba(94, 114, 228, 0.2);
    }

    .badge-soft-success {
        color: #2dce89;
        background-color: rgba(45, 206, 137, 0.1);
        border: 1px solid rgba(45, 206, 137, 0.2);
    }

    .badge-soft-warning {
        color: #fb6340;
        background-color: rgba(251, 99, 64, 0.1);
        border: 1px solid rgba(251, 99, 64, 0.2);
    }

    .badge-soft-danger {
        color: #f5365c;
        background-color: rgba(245, 54, 92, 0.1);
        border: 1px solid rgba(245, 54, 92, 0.2);
    }

    /* Card hover effects for Dashboard */
    .card-stats:hover {
        transform: translateY(-2px);
        transition: all 0.2s ease-in-out;
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }

    /* Progress bar animation */
    .progress-bar {
        transition: width 0.6s ease;
    }

    /* Workflow step animations for Dashboard */
    .workflow-step {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .workflow-step:hover {
        transform: translateY(-5px);
    }

    .workflow-step .icon {
        transition: all 0.3s ease;
    }

    .workflow-step:hover .icon {
        transform: scale(1.1);
    }

    /* Timeline styles for Dashboard Activities */
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }

    .timeline-one-side .timeline-block {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 3rem;
    }

    .timeline-one-side .timeline-block::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0.5rem;
        bottom: -2rem;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-one-side .timeline-block:last-child::before {
        display: none;
    }

    .timeline-one-side .timeline-step {
        position: absolute;
        left: 0;
        top: 0;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #dee2e6;
        z-index: 1;
    }

    .timeline-one-side .timeline-content {
        background: #fff;
        border-radius: 0.375rem;
        padding: 1rem;
        border: 1px solid #dee2e6;
        position: relative;
    }

    .timeline-one-side .timeline-content::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 1rem;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid #dee2e6;
    }

    .timeline-one-side .timeline-content::after {
        content: '';
        position: absolute;
        left: -7px;
        top: 1rem;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid #fff;
    }

    /* Loading states */
    .loading {
        pointer-events: none;
        opacity: 0.6;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Button loading state */
    .btn.loading {
        position: relative;
        color: transparent !important;
    }

    .btn.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Alert improvements */
    .alert {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    }

    /* Card hover general */
    .card:hover {
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.15s ease;
    }

    /* Quick Actions improvements */
    .btn-block .row {
        margin: 0;
    }

    .btn-block .col-auto,
    .btn-block .col {
        padding: 0;
    }

    /* Priority styling untuk Kontak Form */
    .priority-urgent {
        background: linear-gradient(87deg, #f5365c 0, #f56036 100%) !important;
        color: white !important;
    }

    .priority-high {
        background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important;
        color: white !important;
    }

    .priority-normal {
        background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important;
        color: white !important;
    }

    /* Mobile responsive fixes */
    @media (max-width: 767.98px) {
        .workflow-step {
            margin-bottom: 1rem;
        }
        
        .card-stats {
            margin-bottom: 1rem;
        }
        
        .timeline-one-side .timeline-block {
            padding-left: 2rem;
        }
        
        .timeline-one-side .timeline-step {
            width: 1.5rem;
            height: 1.5rem;
            font-size: 0.75rem;
        }

        .navbar-vertical.navbar-collapse .navbar-nav .nav-link {
            padding: 0.875rem 1.5rem;
        }
        
        /* Mobile header fixes */
        .header.bg-primary h6.h2,
        .header.bg-primary .h2 {
            font-size: 1.25rem !important;
        }
        
        .header.bg-primary .btn-neutral {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
    }

    @media (max-width: 576px) {
        .header.bg-primary h6.h2,
        .header.bg-primary .h2 {
            font-size: 1.1rem !important;
        }
        
        .header.bg-primary .breadcrumb-dark {
            display: none !important;
        }
    }

    /* Dashboard specific improvements */
    .progress-wrapper {
        position: relative;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    /* Status badges */
    .status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    /* Enhanced sidebar profile card styling */
    .sidebar-profile {
        background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
        border-radius: 0.5rem;
        padding: 1rem;
        margin: 1rem;
        color: white;
    }
    
    .sidebar-profile .avatar {
        width: 48px;
        height: 48px;
    }
    
    .contact-form-card {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
        border: none;
        color: white;
        margin: 1rem;
    }
    
    .contact-form-card .btn-outline-white {
        border-color: rgba(255, 255, 255, 0.5);
        color: white;
    }
    
    .contact-form-card .btn-outline-white:hover {
        background-color: white;
        color: #2dce89;
    }
</style>