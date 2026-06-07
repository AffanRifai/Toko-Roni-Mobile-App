<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Roles with global monitoring access
    |--------------------------------------------------------------------------
    */
    'monitor_roles' => ['owner', 'manager'],

    /*
    |--------------------------------------------------------------------------
    | Role aliases normalization
    |--------------------------------------------------------------------------
    */
    'role_aliases' => [
        'kepala gudang' => 'kepala_gudang',
        'gudang' => 'kepala_gudang',
        'checker' => 'checker_barang',
        'checker barang' => 'checker_barang',
        'staff logistik' => 'staff_logistik',
        'logistik' => 'staff_logistik',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event rules
    |--------------------------------------------------------------------------
    | roles              : role utama untuk menerima notifikasi
    | include_same_role  : ikut kirim ke user dengan role yang sama dengan actor
    | include_actor      : pelaku aksi ikut menerima notifikasi
    | monitor            : owner + manager ikut menerima (global monitoring)
    | priority           : low|normal|high|critical
    */
    'events' => [
        'checker.product_reported' => [
            'roles' => ['checker_barang', 'kepala_gudang'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'critical',
        ],
        'receivable.created' => [
            'roles' => ['kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'receivable.payment_received' => [
            'roles' => ['kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'transaction.created' => [
            'roles' => ['kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'transaction.deleted' => [
            'roles' => ['kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'product.created' => [
            'roles' => ['kepala_gudang', 'checker_barang'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'product.updated' => [
            'roles' => ['kepala_gudang', 'checker_barang', 'kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'product.deleted' => [
            'roles' => ['kepala_gudang', 'checker_barang', 'kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'delivery.created' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'delivery.assigned' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'delivery.status_changed' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'delivery.deleted' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'category.created' => [
            'roles' => ['kepala_gudang'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'category.updated' => [
            'roles' => ['kepala_gudang', 'checker_barang'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'category.deleted' => [
            'roles' => ['kepala_gudang', 'checker_barang'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'member.created' => [
            'roles' => ['kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'member.updated' => [
            'roles' => ['kasir'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'user.created' => [
            'roles' => ['owner', 'manager'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'user.updated' => [
            'roles' => ['owner', 'manager'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'vehicle.created' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'vehicle.updated' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
        'vehicle.deleted' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => true,
            'priority' => 'high',
        ],
        'vehicle.status_changed' => [
            'roles' => ['staff_logistik'],
            'include_same_role' => true,
            'include_actor' => true,
            'monitor' => false,
            'priority' => 'normal',
        ],
    ],
];
