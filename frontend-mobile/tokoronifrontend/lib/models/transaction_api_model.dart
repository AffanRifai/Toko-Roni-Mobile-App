class TransactionApiLineItem {
  final int productId;
  final String productCode;
  final String productName;
  final String categoryName;
  final int qty;
  final int price;
  final int subtotal;

  const TransactionApiLineItem({
    required this.productId,
    required this.productCode,
    required this.productName,
    required this.categoryName,
    required this.qty,
    required this.price,
    required this.subtotal,
  });

  factory TransactionApiLineItem.fromJson(Map<String, dynamic> json) {
    final product = _asMap(json['product']);
    final category = _asMap(product['category']);
    final qty = _toInt(json['qty']);
    final price = _toInt(json['price']);
    final subtotal = _toInt(json['subtotal']);

    return TransactionApiLineItem(
      productId: _toInt(json['product_id']),
      productCode: (product['code'] ?? json['product_code'] ?? '')
          .toString()
          .trim(),
      productName: (product['name'] ?? json['product_name'] ?? '')
          .toString()
          .trim(),
      categoryName: (category['name'] ?? json['category_name'] ?? '')
          .toString()
          .trim(),
      qty: qty,
      price: price,
      subtotal: subtotal > 0 ? subtotal : qty * price,
    );
  }
}

class TransactionApiItem {
  final int id;
  final String invoiceNumber;
  final DateTime createdAt;
  final String customerName;
  final String customerPhone;
  final int? memberId;
  final String memberName;
  final String memberAddress;
  final String cashierName;
  final String paymentMethod;
  final String paymentStatus;
  final int totalAmount;
  final int discountAmount;
  final double discountPercent;
  final int cashReceived;
  final int changeAmount;
  final List<TransactionApiLineItem> items;

  const TransactionApiItem({
    required this.id,
    required this.invoiceNumber,
    required this.createdAt,
    required this.customerName,
    required this.customerPhone,
    required this.memberId,
    required this.memberName,
    required this.memberAddress,
    required this.cashierName,
    required this.paymentMethod,
    required this.paymentStatus,
    required this.totalAmount,
    required this.discountAmount,
    required this.discountPercent,
    required this.cashReceived,
    required this.changeAmount,
    required this.items,
  });

  factory TransactionApiItem.fromJson(Map<String, dynamic> json) {
    final member = _asMap(json['member']);
    final user = _asMap(json['user']);
    final rawItems = _extractList(json['items']);
    final discountAmount = _toInt(json['discount']);
    final subtotal = rawItems
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map((e) => _toInt(e['subtotal']))
        .fold<int>(0, (sum, value) => sum + value);
    final discountPercent = _toDouble(json['discount_percent']);

    return TransactionApiItem(
      id: _toInt(json['id']),
      invoiceNumber: (json['invoice_number'] ?? '').toString().trim(),
      createdAt: _toDateTime(json['created_at']),
      customerName: (json['customer_name'] ?? '').toString().trim(),
      customerPhone: (json['customer_phone'] ?? '').toString().trim(),
      memberId: _toIntOrNull(member['id'] ?? json['member_id']),
      memberName: (member['nama'] ?? member['name'] ?? '').toString().trim(),
      memberAddress: (member['alamat'] ?? member['address'] ?? '')
          .toString()
          .trim(),
      cashierName: (user['name'] ?? json['created_by'] ?? '').toString().trim(),
      paymentMethod: (json['payment_method'] ?? '').toString().trim(),
      paymentStatus: (json['payment_status'] ?? '').toString().trim(),
      totalAmount: _toInt(json['total_amount']),
      discountAmount: discountAmount,
      discountPercent: discountPercent > 0
          ? discountPercent
          : (subtotal > 0 ? (discountAmount / subtotal) * 100 : 0),
      cashReceived: _toInt(json['cash_received']),
      changeAmount: _toInt(json['change']),
      items: rawItems
          .map(_asMap)
          .where((e) => e.isNotEmpty)
          .map(TransactionApiLineItem.fromJson)
          .toList(),
    );
  }

  int get itemCount => items.fold<int>(0, (sum, item) => sum + item.qty);

  String get paymentMethodLabel {
    final raw = paymentMethod.toLowerCase().trim();
    switch (raw) {
      case 'cash':
      case 'tunai':
        return 'Tunai';
      case 'debit':
      case 'debit_card':
        return 'Debit';
      case 'credit':
      case 'credit_card':
      case 'kredit':
      case 'hutang':
        return 'Hutang';
      case 'e_wallet':
      case 'e-wallet':
      case 'ewallet':
        return 'E-Wallet';
      case 'transfer':
        return 'Transfer';
      default:
        return paymentMethod;
    }
  }

  String get statusLabel {
    final status = paymentStatus.toUpperCase().trim();
    if (status == 'LUNAS') return 'Lunas';
    if (status == 'BELUM LUNAS') return 'Kredit';
    if (status.isEmpty && paymentMethodLabel == 'Hutang') return 'Kredit';
    return status.isEmpty ? 'Lunas' : status;
  }
}

class CreateTransactionLinePayload {
  final int productId;
  final int qty;
  final int? price;

  const CreateTransactionLinePayload({
    required this.productId,
    required this.qty,
    this.price,
  });

  Map<String, dynamic> toJson() {
    return {
      'product_id': productId,
      'qty': qty,
      if (price != null) 'price': price,
    };
  }
}

class CreateTransactionPayload {
  final String customerName;
  final String customerPhone;
  final int? memberId;
  final String paymentMethod;
  final int discountAmount;
  final double discountPercent;
  final int cashReceived;
  final String? dueDate;
  final String? notes;
  final List<CreateTransactionLinePayload> items;

  const CreateTransactionPayload({
    required this.customerName,
    required this.customerPhone,
    required this.memberId,
    required this.paymentMethod,
    required this.discountAmount,
    required this.discountPercent,
    required this.cashReceived,
    required this.dueDate,
    required this.notes,
    required this.items,
  });

  Map<String, dynamic> toJson() {
    return {
      'customer_name': customerName,
      'customer_phone': customerPhone,
      'member_id': memberId,
      'payment_method': paymentMethod,
      'discount': discountAmount,
      'discount_percent': discountPercent,
      'cash_received': cashReceived,
      'due_date': dueDate,
      'notes': notes,
      'items': items.map((e) => e.toJson()).toList(),
    };
  }
}

Map<String, dynamic> _asMap(dynamic raw) {
  if (raw is Map<String, dynamic>) return raw;
  if (raw is Map) {
    return raw.map((k, v) => MapEntry(k.toString(), v));
  }
  return {};
}

List<dynamic> _extractList(dynamic raw) {
  if (raw is List) return raw;
  if (raw is Map<String, dynamic>) {
    final nested = raw['data'];
    if (nested is List) return nested;
  }
  return const [];
}

DateTime _toDateTime(dynamic value) {
  if (value == null) return DateTime.now();
  final raw = value.toString().trim();
  if (raw.isEmpty) return DateTime.now();
  return DateTime.tryParse(raw)?.toLocal() ?? DateTime.now();
}

int? _toIntOrNull(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is num) return value.toInt();
  final raw = value.toString().trim();
  if (raw.isEmpty) return null;
  return int.tryParse(raw);
}

int _toInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is num) return value.round();
  final raw = value.toString().trim();
  if (raw.isEmpty) return 0;

  final direct = int.tryParse(raw);
  if (direct != null) return direct;
  final asDouble = double.tryParse(raw.replaceAll(',', '.'));
  if (asDouble != null) return asDouble.round();
  return 0;
}

double _toDouble(dynamic value) {
  if (value == null) return 0;
  if (value is double) return value;
  if (value is num) return value.toDouble();
  final raw = value.toString().trim();
  if (raw.isEmpty) return 0;
  return double.tryParse(raw.replaceAll(',', '.')) ?? 0;
}
