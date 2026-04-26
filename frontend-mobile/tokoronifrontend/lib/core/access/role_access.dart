class RoleAccess {
  RoleAccess._();

  static const List<String> _allSidebarMenus = <String>[
    'Dashboard',
    'Pengguna',
    'Member',
    'Laporan',
    'Riwayat Transaksi',
    'Kasir',
    'Produk',
    'Kategori',
    'Pengiriman',
    'Kendaraan',
    'Profile',
  ];

  static String normalizeRole(String rawRole) {
    final role = rawRole.trim().toLowerCase();
    switch (role) {
      case 'owner':
        return 'owner';
      case 'manager':
        return 'manager';
      case 'kasir':
        return 'kasir';
      case 'kepala_gudang':
      case 'gudang':
      case 'kepala gudang':
        return 'kepala_gudang';
      case 'staff_logistik':
      case 'logistik':
      case 'staff logistik':
        return 'staff_logistik';
      case 'checker_barang':
      case 'checker':
      case 'checker barang':
        return 'checker_barang';
      default:
        return role;
    }
  }

  static bool isOwnerOrManager(String rawRole) {
    final role = normalizeRole(rawRole);
    return role == 'owner' || role == 'manager';
  }

  static bool isKasir(String rawRole) => normalizeRole(rawRole) == 'kasir';

  static bool isKepalaGudang(String rawRole) =>
      normalizeRole(rawRole) == 'kepala_gudang';

  static bool isStaffLogistik(String rawRole) =>
      normalizeRole(rawRole) == 'staff_logistik';

  static bool isCheckerBarang(String rawRole) =>
      normalizeRole(rawRole) == 'checker_barang';

  static bool isProdukReadOnlyRole(String rawRole) =>
      isKasir(rawRole) || isCheckerBarang(rawRole);

  static List<String> sidebarMenusForRole(String rawRole) {
    if (isOwnerOrManager(rawRole)) {
      return List<String>.from(_allSidebarMenus);
    }
    if (isKasir(rawRole)) {
      return <String>[
        'Dashboard',
        'Member',
        'Laporan',
        'Riwayat Transaksi',
        'Kasir',
        'Produk',
        'Profile',
      ];
    }
    if (isKepalaGudang(rawRole)) {
      return <String>[
        'Dashboard',
        'Produk',
        'Kategori',
        'Pengiriman',
        'Kendaraan',
        'Profile',
      ];
    }
    if (isStaffLogistik(rawRole)) {
      return <String>['Dashboard', 'Pengiriman', 'Kendaraan', 'Profile'];
    }
    if (isCheckerBarang(rawRole)) {
      return <String>['Dashboard', 'Produk', 'Profile'];
    }
    return <String>['Dashboard', 'Profile'];
  }
}
