import '../../models/kendaraan_model.dart';
import '../../models/pengiriman_model.dart';
import '../offline/dashboard_cache_repository.dart';
import '../offline/offline_utils.dart';
import 'auth_service.dart';
import 'delivery_service.dart';
import 'vehicle_service.dart';

class DashboardLogistikSummary {
  final int pengirimanHariIni;
  final int pengirimanDalamProses;
  final int barangDikirim;
  final double onTimeRate;
  final int armadaTersedia;

  const DashboardLogistikSummary({
    this.pengirimanHariIni = 0,
    this.pengirimanDalamProses = 0,
    this.barangDikirim = 0,
    this.onTimeRate = 0,
    this.armadaTersedia = 0,
  });
}

class DashboardLogistikChartData {
  final List<String> labels;
  final List<double> totalPengiriman;
  final List<double> pengirimanTerkirim;

  const DashboardLogistikChartData({
    required this.labels,
    required this.totalPengiriman,
    required this.pengirimanTerkirim,
  });

  bool get isEmpty => labels.isEmpty;
}

class DashboardLogistikData {
  final DashboardLogistikSummary summary;
  final List<PengirimanItem> pengirimanSaya;
  final List<KendaraanItem> armada;
  final List<PengirimanItem> chartSourceDeliveries;
  final DashboardLogistikChartData chartData;

  const DashboardLogistikData({
    required this.summary,
    required this.pengirimanSaya,
    required this.armada,
    required this.chartSourceDeliveries,
    required this.chartData,
  });
}

class DashboardLogistikService {
  static const Set<String> _inProgressStatuses = {
    'processing',
    'assigned',
    'picked_up',
    'on_delivery',
  };

  static const Set<String> _failedStatuses = {'failed', 'cancelled'};

  static Future<DashboardLogistikData> getDashboardData({
    required bool onlyMyDeliveries,
    String chartFilter = '7 Hari',
  }) async {
    try {
      final deliveries = await _fetchDeliveries(
        onlyMyDeliveries: onlyMyDeliveries,
      );

      final armada = await _fetchArmada();
      final armadaTersedia = await _fetchArmadaTersediaCount(
        fallbackArmada: armada,
      );

      final summary = _buildSummary(
        deliveries: deliveries,
        armadaTersedia: armadaTersedia,
      );

      final chartData = buildChartData(
        deliveries: deliveries,
        filter: chartFilter,
      );

      final result = DashboardLogistikData(
        summary: summary,
        pengirimanSaya: deliveries,
        armada: armada,
        chartSourceDeliveries: deliveries,
        chartData: chartData,
      );
      await DashboardCacheRepository.instance.saveLogistik({
        'summary': {
          'pengiriman_hari_ini': summary.pengirimanHariIni,
          'pengiriman_dalam_proses': summary.pengirimanDalamProses,
          'barang_dikirim': summary.barangDikirim,
          'on_time_rate': summary.onTimeRate,
          'armada_tersedia': summary.armadaTersedia,
        },
        'deliveries': deliveries
            .map(
              (e) => {
                'id': e.id,
                'transaction_id': e.transactionId,
                'delivery_code': e.kodePengiriman,
                'invoice': e.invoice,
                'destination': e.tujuan,
                'origin': e.asal,
                'created_at': e.createdAt.toUtc().toIso8601String(),
                'customer_name': e.namaCustomer,
                'total_amount': e.totalBelanja,
                'total_items': e.totalItem,
                'status': e.statusApi,
                'notes': e.catatan,
                'estimated_delivery_time': e.estimatedDeliveryRaw,
                'delivered_at': e.deliveredAtRaw,
                'user_id': e.kurirId,
                'vehicle_id': e.kendaraanId,
                'driver_name': e.namaKurir,
                'driver_phone': e.nomorKurir,
                'vehicle': e.kendaraan,
              },
            )
            .toList(growable: false),
        'vehicles': armada
            .map(
              (e) => {
                'id': e.id,
                'name': e.nama,
                'license_plate': e.platNomor,
                'type': kendaraanTypeApiFromLabel(e.jenis),
                'status': kendaraanStatusApiFromLabel(e.status),
                'last_maintenance': formatDateForApi(e.tanggalMaintenance),
                'capacity_weight': e.kapasitasBerat,
                'capacity_volume': e.kapasitasVolume,
                'notes': e.catatan,
              },
            )
            .toList(growable: false),
      });
      return result;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final cached = await DashboardCacheRepository.instance.getLogistik();
      if (cached.isEmpty) rethrow;
      final deliveries = _extractList(cached['deliveries'])
          .map((e) => PengirimanItem.fromJson(_asMap(e)))
          .toList(growable: false);
      final vehicles = _extractList(cached['vehicles'])
          .map((e) => KendaraanItem.fromJson(_asMap(e)))
          .toList(growable: false);
      final chart = buildChartData(deliveries: deliveries, filter: chartFilter);
      final summaryMap = _asMap(cached['summary']);
      final summary = DashboardLogistikSummary(
        pengirimanHariIni: _toInt(summaryMap['pengiriman_hari_ini']),
        pengirimanDalamProses: _toInt(summaryMap['pengiriman_dalam_proses']),
        barangDikirim: _toInt(summaryMap['barang_dikirim']),
        onTimeRate: _toDouble(summaryMap['on_time_rate']),
        armadaTersedia: _toInt(summaryMap['armada_tersedia']),
      );
      return DashboardLogistikData(
        summary: summary,
        pengirimanSaya: deliveries,
        armada: vehicles,
        chartSourceDeliveries: deliveries,
        chartData: chart,
      );
    }
  }

  static DashboardLogistikChartData buildChartData({
    required List<PengirimanItem> deliveries,
    required String filter,
  }) {
    final now = DateTime.now();

    switch (filter) {
      case '30 Hari':
        return _build30DayChart(deliveries, now);
      case '90 Hari':
        return _build90DayChart(deliveries, now);
      default:
        return _build7DayChart(deliveries, now);
    }
  }

  static Future<List<PengirimanItem>> _fetchDeliveries({
    required bool onlyMyDeliveries,
  }) async {
    if (onlyMyDeliveries) {
      try {
        return await DeliveryService.getMyDeliveries(perPage: 500);
      } catch (_) {
        // Fallback ke endpoint umum kalau endpoint khusus belum siap.
        final deliveries = await DeliveryService.getDeliveries(perPage: 500);
        final userId = int.tryParse(await AuthService.getUserId()) ?? 0;
        if (userId <= 0) return [];
        return deliveries.where((d) => d.kurirId == userId).toList();
      }
    }

    return DeliveryService.getDeliveries(perPage: 500);
  }

  static Future<List<KendaraanItem>> _fetchArmada() async {
    try {
      return await VehicleService.getVehicles(perPage: 200);
    } catch (_) {
      return [];
    }
  }

  static Future<int> _fetchArmadaTersediaCount({
    required List<KendaraanItem> fallbackArmada,
  }) async {
    try {
      final available = await DeliveryService.getAvailableVehicles();
      return available.length;
    } catch (_) {
      return fallbackArmada.where((v) => v.status == 'Tersedia').length;
    }
  }

  static DashboardLogistikSummary _buildSummary({
    required List<PengirimanItem> deliveries,
    required int armadaTersedia,
  }) {
    final now = DateTime.now();

    final pengirimanHariIni = deliveries
        .where((item) => _isSameDate(item.createdAt, now))
        .length;

    final pengirimanDalamProses = deliveries
        .where((item) => _inProgressStatuses.contains(item.statusApi))
        .length;

    final barangDikirim = deliveries
        .where((item) => !_failedStatuses.contains(item.statusApi))
        .fold<int>(0, (sum, item) => sum + item.totalItem);

    final delivered = deliveries
        .where((item) => item.statusApi == 'delivered')
        .toList();

    final onTimeCount = delivered.where(_isOnTimeDelivery).length;
    final onTimeRate = delivered.isEmpty
        ? 0.0
        : (onTimeCount / delivered.length) * 100.0;

    return DashboardLogistikSummary(
      pengirimanHariIni: pengirimanHariIni,
      pengirimanDalamProses: pengirimanDalamProses,
      barangDikirim: barangDikirim,
      onTimeRate: onTimeRate,
      armadaTersedia: armadaTersedia,
    );
  }

  static DashboardLogistikChartData _build7DayChart(
    List<PengirimanItem> deliveries,
    DateTime now,
  ) {
    const labelsMap = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    final labels = <String>[];
    final total = <double>[];
    final delivered = <double>[];

    for (int i = 6; i >= 0; i--) {
      final date = now.subtract(Duration(days: i));
      labels.add(labelsMap[date.weekday % 7]);

      final counts = _countRange(
        deliveries: deliveries,
        start: _startOfDay(date),
        end: _endOfDay(date),
      );

      total.add(counts.total.toDouble());
      delivered.add(counts.delivered.toDouble());
    }

    return DashboardLogistikChartData(
      labels: labels,
      totalPengiriman: total,
      pengirimanTerkirim: delivered,
    );
  }

  static DashboardLogistikChartData _build30DayChart(
    List<PengirimanItem> deliveries,
    DateTime now,
  ) {
    final labels = <String>[];
    final total = <double>[];
    final delivered = <double>[];

    for (int i = 4; i >= 0; i--) {
      final end = _endOfDay(now.subtract(Duration(days: i * 7)));
      final start = _startOfDay(end.subtract(const Duration(days: 6)));
      labels.add('Minggu ${5 - i}');

      final counts = _countRange(
        deliveries: deliveries,
        start: start,
        end: end,
      );
      total.add(counts.total.toDouble());
      delivered.add(counts.delivered.toDouble());
    }

    return DashboardLogistikChartData(
      labels: labels,
      totalPengiriman: total,
      pengirimanTerkirim: delivered,
    );
  }

  static DashboardLogistikChartData _build90DayChart(
    List<PengirimanItem> deliveries,
    DateTime now,
  ) {
    final labels = <String>[];
    final total = <double>[];
    final delivered = <double>[];

    for (int i = 8; i >= 0; i--) {
      final end = _endOfDay(now.subtract(Duration(days: i * 10)));
      final start = _startOfDay(end.subtract(const Duration(days: 9)));

      labels.add(
        '${end.day.toString().padLeft(2, '0')}/${end.month.toString().padLeft(2, '0')}',
      );

      final counts = _countRange(
        deliveries: deliveries,
        start: start,
        end: end,
      );
      total.add(counts.total.toDouble());
      delivered.add(counts.delivered.toDouble());
    }

    return DashboardLogistikChartData(
      labels: labels,
      totalPengiriman: total,
      pengirimanTerkirim: delivered,
    );
  }

  static _RangeCountResult _countRange({
    required List<PengirimanItem> deliveries,
    required DateTime start,
    required DateTime end,
  }) {
    int total = 0;
    int delivered = 0;

    for (final item in deliveries) {
      final created = item.createdAt;
      if (created.isBefore(start) || created.isAfter(end)) continue;

      total += 1;
      if (item.statusApi == 'delivered') delivered += 1;
    }

    return _RangeCountResult(total: total, delivered: delivered);
  }

  static bool _isOnTimeDelivery(PengirimanItem item) {
    final deliveredAt = _parseDate(item.deliveredAtRaw);
    if (deliveredAt == null) return false;

    final estimated = _parseDate(item.estimatedDeliveryRaw);
    if (estimated == null) {
      // Jika belum punya estimasi, anggap netral (tidak dihukum).
      return true;
    }

    return !deliveredAt.isAfter(estimated);
  }

  static bool _isSameDate(DateTime a, DateTime b) {
    return a.year == b.year && a.month == b.month && a.day == b.day;
  }

  static DateTime _startOfDay(DateTime date) {
    return DateTime(date.year, date.month, date.day);
  }

  static DateTime _endOfDay(DateTime date) {
    return DateTime(date.year, date.month, date.day, 23, 59, 59, 999);
  }

  static DateTime? _parseDate(String raw) {
    final text = raw.trim();
    if (text.isEmpty) return null;
    return DateTime.tryParse(text)?.toLocal();
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return const <String, dynamic>{};
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      final data = raw['data'];
      if (data is List) return data;
    }
    return const [];
  }

  static int _toInt(dynamic value) {
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}

class _RangeCountResult {
  final int total;
  final int delivered;

  const _RangeCountResult({required this.total, required this.delivered});
}
