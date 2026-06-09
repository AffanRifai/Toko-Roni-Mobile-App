import 'entity_cache_repository.dart';

class DashboardCacheRepository {
  DashboardCacheRepository._();
  static final DashboardCacheRepository instance = DashboardCacheRepository._();

  static const _kMain = 'dashboard:main';
  static const _kKasir = 'dashboard:kasir';
  static const _kGudang = 'dashboard:gudang';
  static const _kLogistik = 'dashboard:logistik';
  static const _kChecker = 'dashboard:checker';

  Future<void> saveMain(Map<String, dynamic> payload) =>
      EntityCacheRepository.instance.saveMap(_kMain, payload);
  Future<Map<String, dynamic>> getMain() =>
      EntityCacheRepository.instance.getMap(_kMain);

  Future<void> saveKasir(Map<String, dynamic> payload) =>
      EntityCacheRepository.instance.saveMap(_kKasir, payload);
  Future<Map<String, dynamic>> getKasir() =>
      EntityCacheRepository.instance.getMap(_kKasir);

  Future<void> saveGudang(Map<String, dynamic> payload) =>
      EntityCacheRepository.instance.saveMap(_kGudang, payload);
  Future<Map<String, dynamic>> getGudang() =>
      EntityCacheRepository.instance.getMap(_kGudang);

  Future<void> saveLogistik(Map<String, dynamic> payload) =>
      EntityCacheRepository.instance.saveMap(_kLogistik, payload);
  Future<Map<String, dynamic>> getLogistik() =>
      EntityCacheRepository.instance.getMap(_kLogistik);

  Future<void> saveChecker(Map<String, dynamic> payload) =>
      EntityCacheRepository.instance.saveMap(_kChecker, payload);
  Future<Map<String, dynamic>> getChecker() =>
      EntityCacheRepository.instance.getMap(_kChecker);
}

