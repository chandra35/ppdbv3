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
        // MENU UNTUK ADMIN
        // ============================================
        [
            'header' => 'MENU ADMIN',
            'can' => 'admin',  // Only for admin role
        ],
        [
            'text' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'admin',
        ],
        [
            'text' => 'Pendaftar',
            'icon' => 'fas fa-fw fa-users',
            'can' => 'admin',
            'submenu' => [
                [
                    'text' => 'Semua Pendaftar',
                    'route' => 'admin.pendaftar.index',
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    'text' => 'Verifikator',
                    'route' => 'admin.verifikator.index',
                    'icon' => 'fas fa-fw fa-user-shield',
                ],
            ],
        ],

        [
            'header' => 'SETTINGS',
            'can' => 'admin',
        ],
        [
            'text' => 'Pengaturan Sekolah',
            'route' => 'admin.sekolah.index',
            'icon' => 'fas fa-fw fa-school',
            'can' => 'admin',
        ],
        [
            'text' => 'Jalur Pendaftaran',
            'route' => 'admin.jalur.index',
            'icon' => 'fas fa-fw fa-route',
            'can' => 'admin',
        ],
        [
            'text' => 'Tahun Pelajaran',
            'route' => 'admin.tahun-pelajaran.index',
            'icon' => 'fas fa-fw fa-calendar-check',
            'can' => 'admin',
        ],
        [
            'text' => 'Pengaturan PPDB',
            'icon' => 'fas fa-fw fa-cogs',
            'can' => 'admin',
            'submenu' => [
                [
                    'text' => 'PPDB Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'fas fa-fw fa-sliders-h',
                ],
                [
                    'text' => 'Halaman',
                    'route' => 'admin.settings.halaman.index',
                    'icon' => 'fas fa-fw fa-file-alt',
                ],
                [
                    'text' => 'Berita',
                    'route' => 'admin.settings.berita.index',
                    'icon' => 'fas fa-fw fa-newspaper',
                ],
                [
                    'text' => 'Slider',
                    'route' => 'admin.settings.slider.index',
                    'icon' => 'fas fa-fw fa-images',
                ],
                [
                    'text' => 'Jadwal PPDB',
                    'route' => 'admin.settings.jadwal.index',
                    'icon' => 'fas fa-fw fa-calendar-alt',
                ],
                [
                    'text' => 'Alur Pendaftaran',
                    'route' => 'admin.settings.alur-pendaftaran.index',
                    'icon' => 'fas fa-fw fa-list-ol',
                ],
            ],
        ],

        [
            'header' => 'USER & ROLE',
            'can' => 'admin',
        ],
        [
            'text' => 'User Management',
            'route' => 'admin.users.index',
            'icon' => 'fas fa-fw fa-user-cog',
            'can' => 'admin',
        ],
        [
            'text' => 'Role Management',
            'route' => 'admin.roles.index',
            'icon' => 'fas fa-fw fa-user-tag',
            'can' => 'admin',
        ],
        [
            'text' => 'GTK (SIMANSA)',
            'route' => 'admin.gtk.index',
            'icon' => 'fas fa-fw fa-users-cog',
            'can' => 'admin',
        ],

        [
            'header' => 'SYSTEM',
            'can' => 'admin',
        ],
        [
            'text' => 'Activity Log',
            'route' => 'admin.logs.index',
            'icon' => 'fas fa-fw fa-history',
            'can' => 'admin',
        ],
        [
            'text' => 'Pengaturan',
            'icon' => 'fas fa-fw fa-tools',
            'can' => 'admin',
            'submenu' => [
                [
                    'text' => 'EMIS Token',
                    'route' => 'admin.update-emis-token.index',
                    'icon' => 'fas fa-fw fa-key',
                ],
                [
                    'text' => 'WhatsApp API',
                    'route' => 'admin.whatsapp.index',
                    'icon' => 'fab fa-fw fa-whatsapp',
                ],
                [
                    'header' => 'BACKUP & DATA',
                ],
                [
                    'text' => 'Backup & Restore',
                    'route' => 'admin.backup.index',
                    'icon' => 'fas fa-fw fa-database text-primary',
                ],
                [
                    'text' => 'Hapus Data Pendaftar',
                    'route' => 'admin.data.delete-list',
                    'icon' => 'fas fa-fw fa-user-minus text-warning',
                ],
                [
                    'text' => 'Data Terhapus',
                    'route' => 'admin.data.deleted',
                    'icon' => 'fas fa-fw fa-trash-restore text-danger',
                ],
            ],
        ],

        // ============================================
        // MENU UNTUK OPERATOR/VERIFIKATOR
        // ============================================
        [
            'header' => 'MENU OPERATOR',
            'can' => 'only-operator-or-verifikator',
        ],
        [
            'text' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'only-operator-or-verifikator',
        ],
        [
            'text' => 'Data Pendaftar',
            'route' => 'admin.pendaftar.index',
            'icon' => 'fas fa-fw fa-users',
            'can' => 'only-operator-or-verifikator',
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
