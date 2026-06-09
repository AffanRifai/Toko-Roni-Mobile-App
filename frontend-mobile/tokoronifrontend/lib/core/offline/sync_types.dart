enum SyncStatus {
  synced('synced'),
  pendingCreate('pending_create'),
  pendingUpdate('pending_update'),
  pendingDelete('pending_delete'),
  failed('failed');

  final String value;
  const SyncStatus(this.value);

  static SyncStatus fromValue(String? raw) {
    for (final status in SyncStatus.values) {
      if (status.value == raw) return status;
    }
    return SyncStatus.synced;
  }
}

enum SyncOperation {
  create('create'),
  update('update'),
  delete('delete');

  final String value;
  const SyncOperation(this.value);

  static SyncOperation fromValue(String? raw) {
    for (final operation in SyncOperation.values) {
      if (operation.value == raw) return operation;
    }
    return SyncOperation.update;
  }
}

enum LocalEntityType {
  product('product'),
  category('category'),
  delivery('delivery'),
  member('member'),
  vehicle('vehicle'),
  user('user'),
  transactionDraft('transaction_draft'),
  cart('cart'),
  session('session'),
  notification('notification');

  final String value;
  const LocalEntityType(this.value);

  static LocalEntityType fromValue(String? raw) {
    for (final type in LocalEntityType.values) {
      if (type.value == raw) return type;
    }
    return LocalEntityType.product;
  }
}
