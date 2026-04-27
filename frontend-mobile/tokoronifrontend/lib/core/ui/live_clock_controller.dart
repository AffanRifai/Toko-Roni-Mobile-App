import 'dart:async';

import 'package:flutter/foundation.dart';

/// Lightweight realtime clock for UI sections that need periodic time updates
/// without rebuilding an entire screen.
class LiveClockController extends ValueNotifier<DateTime> {
  final Duration _tick;
  late final Timer _timer;

  LiveClockController({Duration tick = const Duration(seconds: 1)})
    : _tick = tick,
      super(DateTime.now()) {
    _timer = Timer.periodic(_tick, (_) {
      value = DateTime.now();
    });
  }

  @override
  void dispose() {
    _timer.cancel();
    super.dispose();
  }
}
