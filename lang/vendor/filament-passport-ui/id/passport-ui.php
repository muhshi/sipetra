<?php

return [
    'navigation' => [
        'group' => 'SSO',
    ],
    'common' => [
        'id' => 'ID',
        'name' => 'Nama',
        'description' => 'Deskripsi',
        'updated_at' => 'Terakhir diperbarui',
        'created_at' => 'Dibuat pada',
        'expires_at' => 'Kadaluarsa pada',
        'scopes' => 'scope',
        'none' => 'tidak ada',
        'is_active' => 'Aktif',
    ],
    'resource' => [
        'global_action' => 'aksi global',
    ],
    'client_resource' => [
        'label' => 'OAuth Clients',
        'model_label' => 'Client',
        'plural_model_label' => 'OAuth Clients',
        'form' => [
            'owner_hint' => 'Pemilik client ini. Digunakan untuk mengasosiasikan client dengan pengguna.',
            'secret_label' => 'Client Secret',
            'secret_helper_text' => 'Ini adalah client secret. Pastikan untuk menyalinnya sekarang karena tidak akan ditampilkan lagi.',
            'revoke_label' => 'Cabut Client',
            'wizard' => [
                'steps' => [
                    'client' => [
                        'label' => 'Detail Client',
                        'description' => 'Isi detail client.',
                    ],
                    'user_permission' => [
                        'label' => 'Izin Pengguna',
                        'description' => 'Tetapkan izin pengguna ke client.',
                    ],
                ],
            ],
        ],
        'column' => [
            'name' => 'Nama',
            'owner' => 'Pemilik',
            'grant_type' => 'Grant Type',
            'last_login' => 'Login Terakhir',
            'revoked' => 'Dicabut',
        ],
    ],
    'passport_scope_resource_resource' => [
        'label' => 'Resources',
        'model_label' => 'Resource',
        'plural_model_label' => 'Resources',
        'column' => [
            'id' => 'ID',
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'is_active' => 'Aktif',
        ],
        'form' => [
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'is_active' => 'Aktif',
        ],
    ],
    'passport_scope_actions_resource' => [
        'label' => 'Resource Actions',
        'model_label' => 'Resource Action',
        'plural_model_label' => 'Resource Actions',
        'column' => [
            'id' => 'ID',
            'name' => 'Aksi',
            'description' => 'Deskripsi',
            'is_active' => 'Aktif',
            'is_global' => 'Global',
        ],
        'form' => [
            'name' => 'Aksi',
            'description' => 'Deskripsi',
            'is_active' => 'Aktif',
            'resource_id' => 'Resource',
            'resource_id_helper_text' => 'Pilih resource tempat aksi ini berada. Kosongkan untuk menjadikannya aksi global.',
        ],
        'header_action' => [
            'create' => 'Buat Aksi Resource',
        ],
    ],
    'token_resource' => [
        'label' => 'Tokens',
        'model_label' => 'Token',
        'plural_model_label' => 'Personal Access Tokens',
        'column' => [
            'id' => 'ID',
            'name' => 'Nama',
            'client' => 'Client',
            'user_name' => 'Nama Pengguna',
            'scopes' => 'Scopes',
            'revoked' => 'Dicabut',
            'created_at' => 'Dibuat Pada',
            'expires_at' => 'Kadaluarsa Pada',
        ],
    ]
];
