import 'entity_cache_repository.dart';

class ReportLocalRepository {
  ReportLocalRepository._();
  static final ReportLocalRepository instance = ReportLocalRepository._();

  String summaryKey({String? date, String? month, String sort = 'latest'}) {
    final d = (date ?? '').trim();
    final m = (month ?? '').trim();
    return 'report:sales_summary:date=$d:month=$m:sort=$sort';
  }

  Future<void> cacheSalesSummary({
    required String key,
    required Map<String, dynamic> payload,
  }) {
    return EntityCacheRepository.instance.saveMap(key, payload);
  }

  Future<Map<String, dynamic>> getSalesSummary(String key) {
    return EntityCacheRepository.instance.getMap(key);
  }
}

