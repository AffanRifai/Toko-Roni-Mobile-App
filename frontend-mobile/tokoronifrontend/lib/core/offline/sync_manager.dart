import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';

import 'category_local_repository.dart';
import 'delivery_local_repository.dart';
import 'member_local_repository.dart';
import 'notification_local_repository.dart';
import 'product_local_repository.dart';
import 'sync_api_client.dart';
import 'sync_queue_repository.dart';
import 'sync_types.dart';
import 'transaction_local_repository.dart';
import 'user_local_repository.dart';
import 'vehicle_local_repository.dart';

class SyncManager {
  SyncManager._();
  static final SyncManager instance = SyncManager._();

  StreamSubscription<List<ConnectivityResult>>? _connectivitySub;
  bool _initialized = false;
  bool _isSyncing = false;

  Future<void> start() async {
    if (_initialized) return;
    _initialized = true;

    _connectivitySub = Connectivity().onConnectivityChanged.listen((results) {
      if (results.any((result) => result != ConnectivityResult.none)) {
        unawaited(triggerSync());
      }
    });

    unawaited(triggerSync());
  }

  Future<void> stop() async {
    await _connectivitySub?.cancel();
    _connectivitySub = null;
    _initialized = false;
  }

  Future<void> triggerSync() async {
    if (_isSyncing) return;
    _isSyncing = true;
    try {
      final hasPending = await SyncQueueRepository.instance.hasPendingQueue();
      if (!hasPending) return;

      final reachable = await SyncApiClient.instance.isBackendReachable();
      if (!reachable) return;

      final queueItems = await SyncQueueRepository.instance.dueItems(
        limit: 100,
      );
      for (final item in queueItems) {
        try {
          await _processItem(item);
          await SyncQueueRepository.instance.markSuccess(item.id);
        } catch (error) {
          await _markEntitySyncError(item, error.toString());
          await SyncQueueRepository.instance.markFailure(
            item.id,
            error.toString(),
            retryCount: item.retryCount,
          );
        }
      }
    } finally {
      _isSyncing = false;
    }
  }

  Future<void> _processItem(SyncQueueItem item) async {
    switch (item.entityType) {
      case LocalEntityType.product:
        await _processProduct(item);
        return;
      case LocalEntityType.category:
        await _processCategory(item);
        return;
      case LocalEntityType.delivery:
        await _processDelivery(item);
        return;
      case LocalEntityType.member:
        await _processMember(item);
        return;
      case LocalEntityType.vehicle:
        await _processVehicle(item);
        return;
      case LocalEntityType.user:
        await _processUser(item);
        return;
      case LocalEntityType.transactionDraft:
        await _processTransaction(item);
        return;
      case LocalEntityType.notification:
        await _processNotification(item);
        return;
      case LocalEntityType.cart:
      case LocalEntityType.session:
        return;
    }
  }

  Future<void> _processProduct(SyncQueueItem item) async {
    final row = await ProductLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    if (row == null) return;
    final serverId = (row['server_id'] as num?)?.toInt();

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createProduct(item.payload);
      final data = _extractData(response);
      await ProductLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerProduct: data.isNotEmpty ? data : response,
      );
      return;
    }

    if (item.operation == SyncOperation.update) {
      if (serverId == null) {
        final response = await SyncApiClient.instance.createProduct(
          item.payload,
        );
        final data = _extractData(response);
        await ProductLocalRepository.instance.markSyncedFromServer(
          localId: item.entityLocalId,
          rawServerProduct: data.isNotEmpty ? data : response,
        );
        return;
      }
      final response = await SyncApiClient.instance.updateProduct(
        serverId,
        item.payload,
      );
      final data = _extractData(response);
      await ProductLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerProduct: data.isNotEmpty ? data : response,
      );
      return;
    }

    if (item.operation == SyncOperation.delete) {
      if (serverId != null) {
        await SyncApiClient.instance.deleteProduct(serverId);
      }
      await ProductLocalRepository.instance.removeByLocalId(item.entityLocalId);
    }
  }

  Future<void> _processCategory(SyncQueueItem item) async {
    final row = await CategoryLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    if (row == null) return;
    final serverId = (row['server_id'] as num?)?.toInt();

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createCategory(
        item.payload,
      );
      final data = _extractData(response);
      await CategoryLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerCategory: data.isNotEmpty ? data : response,
      );
      return;
    }

    if (item.operation == SyncOperation.update) {
      if (serverId == null) {
        final response = await SyncApiClient.instance.createCategory(
          item.payload,
        );
        final data = _extractData(response);
        await CategoryLocalRepository.instance.markSyncedFromServer(
          localId: item.entityLocalId,
          rawServerCategory: data.isNotEmpty ? data : response,
        );
        return;
      }
      final response = await SyncApiClient.instance.updateCategory(
        serverId,
        item.payload,
      );
      final data = _extractData(response);
      await CategoryLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerCategory: data.isNotEmpty ? data : response,
      );
      return;
    }

    if (item.operation == SyncOperation.delete) {
      if (serverId != null) {
        await SyncApiClient.instance.deleteCategory(serverId);
      }
      await CategoryLocalRepository.instance.removeByLocalId(
        item.entityLocalId,
      );
    }
  }

  Future<void> _processTransaction(SyncQueueItem item) async {
    final row = await TransactionLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    if (row == null) return;
    final serverId = (row['server_id'] as num?)?.toInt();

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createTransaction(
        item.payload,
      );
      final data = _extractData(response);
      await TransactionLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerTransaction: data.isNotEmpty ? data : response,
      );
      return;
    }

    if (item.operation == SyncOperation.delete) {
      if (serverId != null) {
        await SyncApiClient.instance.deleteTransaction(serverId);
      }
      await TransactionLocalRepository.instance.removeByLocalId(
        item.entityLocalId,
      );
    }
  }

  Future<void> _processDelivery(SyncQueueItem item) async {
    final row = await DeliveryLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    final rowServerId = (row?['server_id'] as num?)?.toInt() ??
        (row?['id'] as num?)?.toInt();
    final payloadServerId = (item.payload['delivery_id'] as num?)?.toInt() ??
        (item.payload['id'] as num?)?.toInt();
    final serverId = rowServerId ?? payloadServerId;

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createDelivery(item.payload);
      final data = _extractData(response);
      await DeliveryLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerDelivery: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.update) {
      final action = (item.payload['action'] ?? '').toString();
      if (action == 'assign') {
        if (serverId == null || serverId <= 0) return;
        final userId = (item.payload['user_id'] as num?)?.toInt() ?? 0;
        final vehicleId = (item.payload['vehicle_id'] as num?)?.toInt();
        if (userId <= 0) return;
        await SyncApiClient.instance.assignDelivery(
          serverId,
          userId,
          vehicleId: vehicleId,
        );
        return;
      }
      if (action == 'status') {
        if (serverId == null || serverId <= 0) return;
        final statusApi = (item.payload['status'] ?? '').toString().trim();
        if (statusApi.isEmpty) return;
        await SyncApiClient.instance.updateDeliveryStatus(serverId, statusApi);
        return;
      }

      if (serverId == null || serverId <= 0) {
        final response = await SyncApiClient.instance.createDelivery(
          item.payload,
        );
        final data = _extractData(response);
        await DeliveryLocalRepository.instance.markSyncedFromServer(
          localId: item.entityLocalId,
          rawServerDelivery: data.isNotEmpty ? data : item.payload,
        );
        return;
      }
      final statusApi = (item.payload['status'] ?? '').toString().trim();
      if (statusApi.isNotEmpty) {
        await SyncApiClient.instance.updateDeliveryStatus(serverId, statusApi);
      }
      return;
    }

    if (item.operation == SyncOperation.delete) {
      // Delivery delete endpoint tidak dipakai di app saat ini.
      // Biarkan sukses agar queue tidak macet.
      return;
    }
  }

  Future<void> _processMember(SyncQueueItem item) async {
    final row = await MemberLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    final rowServerId = (row?['server_id'] as num?)?.toInt() ??
        (row?['id'] as num?)?.toInt();
    final payloadServerId = (item.payload['member_id'] as num?)?.toInt() ??
        (item.payload['id'] as num?)?.toInt();
    final serverId = rowServerId ?? payloadServerId;

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createMember(item.payload);
      final data = _extractData(response);
      await MemberLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerMember: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.update) {
      final action = (item.payload['action'] ?? '').toString();
      if (action == 'toggle_status') {
        if (serverId == null || serverId <= 0) return;
        await SyncApiClient.instance.toggleMemberStatus(serverId);
        return;
      }

      if (serverId == null || serverId <= 0) {
        final response = await SyncApiClient.instance.createMember(
          item.payload,
        );
        final data = _extractData(response);
        await MemberLocalRepository.instance.markSyncedFromServer(
          localId: item.entityLocalId,
          rawServerMember: data.isNotEmpty ? data : item.payload,
        );
        return;
      }

      final response = await SyncApiClient.instance.updateMember(
        serverId,
        item.payload,
      );
      final data = _extractData(response);
      await MemberLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerMember: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.delete) {
      // Member delete endpoint tidak dipakai di app saat ini.
      return;
    }
  }

  Future<void> _processVehicle(SyncQueueItem item) async {
    final row = await VehicleLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    final rowServerId = (row?['server_id'] as num?)?.toInt() ??
        (row?['id'] as num?)?.toInt();
    final payloadServerId = (item.payload['vehicle_id'] as num?)?.toInt() ??
        (item.payload['id'] as num?)?.toInt();
    final serverId = rowServerId ?? payloadServerId;

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createVehicle(item.payload);
      final data = _extractData(response);
      await VehicleLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerVehicle: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.update) {
      if (serverId == null || serverId <= 0) {
        final response = await SyncApiClient.instance.createVehicle(
          item.payload,
        );
        final data = _extractData(response);
        await VehicleLocalRepository.instance.markSyncedFromServer(
          localId: item.entityLocalId,
          rawServerVehicle: data.isNotEmpty ? data : item.payload,
        );
        return;
      }
      final response = await SyncApiClient.instance.updateVehicle(
        serverId,
        item.payload,
      );
      final data = _extractData(response);
      await VehicleLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerVehicle: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.delete) {
      if (serverId != null && serverId > 0) {
        await SyncApiClient.instance.deleteVehicle(serverId);
      }
      await VehicleLocalRepository.instance.removeByLocalId(item.entityLocalId);
    }
  }

  Future<void> _processUser(SyncQueueItem item) async {
    final row = await UserLocalRepository.instance.findRowByLocalId(
      item.entityLocalId,
    );
    final rowServerId = (row?['server_id'] as num?)?.toInt() ??
        (row?['id'] as num?)?.toInt();
    final payloadServerId = (item.payload['user_id'] as num?)?.toInt() ??
        (item.payload['id'] as num?)?.toInt();
    final serverId = rowServerId ?? payloadServerId;

    if (item.operation == SyncOperation.create) {
      final response = await SyncApiClient.instance.createUser(item.payload);
      final data = _extractData(response);
      await UserLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerUser: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.update) {
      if (serverId == null || serverId <= 0) {
        final response = await SyncApiClient.instance.createUser(item.payload);
        final data = _extractData(response);
        await UserLocalRepository.instance.markSyncedFromServer(
          localId: item.entityLocalId,
          rawServerUser: data.isNotEmpty ? data : item.payload,
        );
        return;
      }
      final response = await SyncApiClient.instance.updateUser(
        serverId,
        item.payload,
      );
      final data = _extractData(response);
      await UserLocalRepository.instance.markSyncedFromServer(
        localId: item.entityLocalId,
        rawServerUser: data.isNotEmpty ? data : item.payload,
      );
      return;
    }

    if (item.operation == SyncOperation.delete) {
      if (serverId != null && serverId > 0) {
        await SyncApiClient.instance.deleteUser(serverId);
      }
      await UserLocalRepository.instance.removeByLocalId(item.entityLocalId);
    }
  }

  Future<void> _processNotification(SyncQueueItem item) async {
    final action = (item.payload['action'] ?? '').toString();
    if (item.operation == SyncOperation.create) {
      // Notifikasi local action dipertahankan di local store.
      // Server-side notifikasi utama tetap dari event backend.
      await NotificationLocalRepository.instance.markSynced(item.entityLocalId);
      return;
    }

    if (action == 'mark_read') {
      final id = (item.payload['notification_id'] ?? '').toString();
      if (id.isNotEmpty && !id.startsWith('loc-')) {
        await SyncApiClient.instance.markNotificationRead(id);
      }
      return;
    }
    if (action == 'mark_all_read') {
      await SyncApiClient.instance.markAllNotificationsRead();
      return;
    }
    if (action == 'delete') {
      final id = (item.payload['notification_id'] ?? '').toString();
      if (id.isNotEmpty && !id.startsWith('loc-')) {
        await SyncApiClient.instance.deleteNotification(id);
      }
      return;
    }
    if (action == 'clear_all') {
      await SyncApiClient.instance.clearNotifications();
      return;
    }
  }

  Map<String, dynamic> _extractData(Map<String, dynamic> response) {
    final data = response['data'];
    if (data is Map<String, dynamic>) return data;
    if (data is Map) {
      return data.map((k, v) => MapEntry(k.toString(), v));
    }
    return {};
  }

  Future<void> _markEntitySyncError(SyncQueueItem item, String error) async {
    switch (item.entityType) {
      case LocalEntityType.product:
        await ProductLocalRepository.instance.markSyncError(
          item.entityLocalId,
          error,
        );
        break;
      case LocalEntityType.category:
        await CategoryLocalRepository.instance.markSyncError(
          item.entityLocalId,
          error,
        );
        break;
      case LocalEntityType.transactionDraft:
        await TransactionLocalRepository.instance.markSyncError(
          item.entityLocalId,
          error,
        );
        break;
      case LocalEntityType.delivery:
      case LocalEntityType.member:
      case LocalEntityType.vehicle:
      case LocalEntityType.user:
      case LocalEntityType.notification:
      case LocalEntityType.cart:
      case LocalEntityType.session:
        break;
    }
  }
}
