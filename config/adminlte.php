<?php

return [
    'title' => 'PPDB Admin',
    'title_prefix' => '',
    'title_postfix' => ' | PPDB',

    'use_ico_only' => false,
    'use_full_favicon' => false,

    'google_fonts' => [
        'allowed' => true,
    ],

    'logo' => '<b>PPDB</b>Admin',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'PPDB Admin',

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    'preloader' => [
        'enabled' => false,
    ],

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => false,
    'usermenu_profile_url' => true,

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_body' => 'text-sm',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => 'nav-compact nav-child-indent nav-flat',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    'use_route_url' => true,
    'dashboard_url' => 'admin.dashboard',
    'logout_url' => 'ppdb.logout',
    'login_url' => 'login',
    'register_url' => false,
    'password_reset_url' => false,
    'password_email_url' => false,
    'profile_url' => 'admin.profile.index',
    'disable_darkmode_routes' => false,

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    'menu' => [
        // ============================================
        // MENU UTAMA (Berdasarkan Permission)
        // ============================================
        [
            'header' => 'MENU UTAMA',
            'can' => 'admin-panel',  // Admin, Operator, Verifikator
        ],
        [
            'text' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'admin-panel',
        ],
        [
            'text' => 'Pendaftar',
            'icon' => 'fas fa-fw fa-users',
            'can' => 'pendaftar.view',
            'submenu' => [
                [
                    'text' => 'Semua Pendaftar',
                    'route' => 'admin.pendaftar.index',
                    'icon' => 'fas fa-fw fa-list',
                    'can' => 'pendaftar.view',
                ],
                [
                    'text' => 'Finalisasi',
                    'route' => 'admin.finalisasi.index',
                    'icon' => 'fas fa-fw fa-clipboard-check',
                    'can' => 'verifikasi.finalisasi',
                ],
                [
                    'text' => 'Cetak Dokumen',
                    'route' => 'admin.cetak-dokumen.index',
                    'icon' => 'fas fa-fw fa-print',
                    'can' => 'verifikasi.cetak',
                ],

                [
                    'text' => 'Statistik',
                    'route' => 'admin.statistik.index',
                    'icon' => 'fas fa-fw fa-chart-bar',
                    'can' => 'statistik.view',
                ],
                [
                    'text' => 'Verifikator',
                    'route' => 'admin.verifikator.index',
                    'icon' => 'fas fa-fw fa-user-shield',
                    'can' => 'admin',
                ],
            ],
        ],
        [
            'text' => 'Seleksi',
            'icon' => 'fas fa-fw fa-clipboard-list',
            'can' => 'admin',
            'submenu' => [
                [
                    'text' => 'Penjadwalan Ujian',
                    'route' => 'admin.penjadwalan-ujian.index',
                    'icon' => 'fas fa-fw fa-calendar-alt',
                ],
                [
                    'text' => 'Sesi Ujian',
                    'route' => 'admin.sesi-ujian.index',
                    'icon' => 'fas fa-fw fa-calendar-check',
                ],
                [
                    'text' => 'Manajemen Penguji',
                    'route' => 'admin.penguji.index',
                    'icon' => 'fas fa-fw fa-user-tie',
                ],
                [
                    'text' => 'Nilai TBQ',
                    'route' => 'admin.nilai-seleksi.index',
                    'icon' => 'fas fa-fw fa-chart-bar',
                ],
                [
                    'text' => 'Upload Nilai TBQ',
                    'route' => 'admin.nilai-seleksi.upload',
                    'icon' => 'fas fa-fw fa-file-upload',
                ],
                [
                    'text' => 'Bobot Nilai',
                    'route' => 'admin.nilai-seleksi.bobot',
                    'icon' => 'fas fa-fw fa-balance-scale',
                ],
                [
                    'text' => 'Rekap Nilai',
                    'route' => 'admin.nilai-seleksi.rekap',
                    'icon' => 'fas fa-fw fa-file-excel',
                ],
                [
                    'text' => 'Nilai CBT',
                    'route' => 'admin.nilai-cbt.index',
                    'icon' => 'fas fa-fw fa-laptop',
                ],
                [
                    'text' => 'Upload Nilai CBT',
                    'route' => 'admin.nilai-cbt.upload',
                    'icon' => 'fas fa-fw fa-cloud-upload-alt',
                ],
                [
                    'text' => 'Pengumuman',
                    'route' => 'admin.nilai-seleksi.pengumuman',
                    'icon' => 'fas fa-fw fa-bullhorn',
                ],
            ],
        ],

        [
            'header' => 'PENGATURAN',
            'can' => 'settings.view',
        ],
        [
            'text' => 'Settings',
            'icon' => 'fas fa-fw fa-cog',
            'can' => 'settings.view',
            'submenu' => [
                [
                    'text' => 'Pengaturan Sekolah',
                    'route' => 'admin.sekolah.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Jalur Pendaftaran',
                    'route' => 'admin.jalur.index',
                    'icon' => 'fas fa-fw fa-route',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Tahun Pelajaran',
                    'route' => 'admin.tahun-pelajaran.index',
                    'icon' => 'fas fa-fw fa-calendar-check',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'EMIS Token',
                    'route' => 'admin.update-emis-token.index',
                    'icon' => 'fas fa-fw fa-key',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'WhatsApp API',
                    'route' => 'admin.whatsapp.index',
                    'icon' => 'fab fa-fw fa-whatsapp',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Email Notifikasi',
                    'route' => 'admin.email.index',
                    'icon' => 'fas fa-fw fa-envelope',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Backup & Restore',
                    'route' => 'admin.backup.index',
                    'icon' => 'fas fa-fw fa-database',
                    'can' => 'admin',
                ],
                [
                    'text' => 'Hapus Data Pendaftar',
                    'route' => 'admin.data.delete-list',
                    'icon' => 'fas fa-fw fa-user-minus text-warning',
                    'can' => 'admin',
                ],
                [
                    'text' => 'Data Terhapus',
                    'route' => 'admin.data.deleted',
                    'icon' => 'fas fa-fw fa-trash-restore text-danger',
                    'can' => 'admin',
                ],
                [
                    'text' => 'Reset Sistem',
                    'route' => 'admin.reset-system.index',
                    'icon' => 'fas fa-fw fa-radiation text-danger',
                    'can' => 'admin',
                ],
            ],
        ],
        [
            'text' => 'Pengaturan PPDB',
            'icon' => 'fas fa-fw fa-cogs',
            'can' => 'settings.view',
            'submenu' => [
                [
                    'text' => 'PPDB Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'fas fa-fw fa-sliders-h',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Kop Surat',
                    'route' => 'admin.sekolah.kop-builder',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Halaman',
                    'route' => 'admin.settings.halaman.index',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Jadwal PPDB',
                    'route' => 'admin.settings.jadwal.index',
                    'icon' => 'fas fa-fw fa-calendar-alt',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Alur Pendaftaran',
                    'route' => 'admin.settings.alur-pendaftaran.index',
                    'icon' => 'fas fa-fw fa-list-ol',
                    'can' => 'settings.edit',
                ],
                [
                    'text' => 'Informasi Pendaftar',
                    'route' => 'admin.settings.informasi-pendaftar.index',
                    'icon' => 'fas fa-fw fa-bullhorn',
                    'can' => 'settings.edit',
                ],
            ],
        ],

        // ============================================
        // KONTEN (Berita & Slider - permission terpisah)
        // ============================================
        [
            'header' => 'KONTEN',
            'can' => 'berita.view',
        ],
        [
            'text' => 'Berita',
            'route' => 'admin.settings.berita.index',
            'icon' => 'fas fa-fw fa-newspaper',
            'can' => 'berita.view',
        ],
        [
            'text' => 'Slider',
            'route' => 'admin.settings.slider.index',
            'icon' => 'fas fa-fw fa-images',
            'can' => 'slider.view',
        ],

        [
            'header' => 'USER & ROLE',
            'can' => 'user.view',
        ],
        [
            'text' => 'User & Role',
            'icon' => 'fas fa-fw fa-users-cog',
            'can' => 'user.view',
            'submenu' => [
                [
                    'text' => 'User Management',
                    'route' => 'admin.users.index',
                    'icon' => 'fas fa-fw fa-user-cog',
                    'can' => 'user.view',
                ],
                [
                    'text' => 'Role Management',
                    'route' => 'admin.roles.index',
                    'icon' => 'fas fa-fw fa-user-tag',
                    'can' => 'role.view',
                ],
                [
                    'text' => 'GTK (SIMANSA)',
                    'route' => 'admin.gtk.index',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                    'can' => 'user.view',
                ],
            ],
        ],

        [
            'header' => 'SYSTEM',
            'can' => 'logs.view',
        ],
        [
            'text' => 'Statistik Pengunjung',
            'route' => 'admin.visitor-logs.index',
            'icon' => 'fas fa-fw fa-chart-line',
            'can' => 'visitor.view',
        ],
        [
            'text' => 'Activity Log',
            'route' => 'admin.logs.index',
            'icon' => 'fas fa-fw fa-history',
            'can' => 'logs.view',
        ],
        [
            'text' => 'Log Email',
            'route' => 'admin.email-logs.index',
            'icon' => 'fas fa-fw fa-envelope',
            'can' => 'logs.view',
        ],

        // ============================================
        // PORTAL PENGUJI
        // ============================================
        [
            'header' => 'PORTAL PENGUJI',
            'can' => 'penguji-panel',
        ],
        [
            'text' => 'Dashboard Penguji',
            'route' => 'penguji.dashboard',
            'icon' => 'fas fa-fw fa-clipboard-check',
            'can' => 'penguji-panel',
        ],

        ['header' => 'AKUN'],
        [
            'text' => 'Profil Saya',
            'route' => 'admin.profile.index',
            'icon' => 'fas fa-fw fa-user-circle',
        ],

        ['header' => ''],
        [
            'text' => 'Kembali ke Website',
            'route' => 'ppdb.landing',
            'icon' => 'fas fa-fw fa-globe',
            'target' => '_blank',
        ],
    ],

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
    ],

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    'livewire' => false,
];
