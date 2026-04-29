import 'dart:async';

import 'package:flutter/material.dart';

/// Local keyboard inset adapter to avoid full-screen scaffold resize relayout.
/// Use together with `Scaffold(resizeToAvoidBottomInset: false)`.
class KeyboardInsetPadding extends StatefulWidget {
  const KeyboardInsetPadding({
    super.key,
    required this.child,
    this.additionalBottom = 0,
    this.animationDuration = Duration.zero,
    this.settleDuration = const Duration(milliseconds: 70),
  });

  final Widget child;
  final double additionalBottom;
  final Duration animationDuration;
  final Duration settleDuration;

  @override
  State<KeyboardInsetPadding> createState() => _KeyboardInsetPaddingState();
}

class _KeyboardInsetPaddingState extends State<KeyboardInsetPadding>
    with WidgetsBindingObserver {
  Timer? _settleTimer;
  double _bottomInset = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final next = _readLogicalInset();
      if ((next - _bottomInset).abs() < 0.5) return;
      setState(() => _bottomInset = next);
    });
  }

  @override
  void dispose() {
    _settleTimer?.cancel();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeMetrics() {
    _settleTimer?.cancel();
    _settleTimer = Timer(widget.settleDuration, () {
      if (!mounted) return;
      final next = _readLogicalInset();
      if ((next - _bottomInset).abs() < 0.5) return;
      setState(() {
        _bottomInset = next;
      });
    });
  }

  double _readLogicalInset() {
    final view = View.maybeOf(context);
    if (view != null) {
      return view.viewInsets.bottom / view.devicePixelRatio;
    }
    final fallbackViews = WidgetsBinding.instance.platformDispatcher.views;
    if (fallbackViews.isEmpty) return 0;
    final fallback = fallbackViews.first;
    return fallback.viewInsets.bottom / fallback.devicePixelRatio;
  }

  @override
  Widget build(BuildContext context) {
    final padding = EdgeInsets.only(
      bottom: _bottomInset + widget.additionalBottom,
    );
    if (widget.animationDuration == Duration.zero) {
      return Padding(padding: padding, child: widget.child);
    }
    return AnimatedPadding(
      duration: widget.animationDuration,
      curve: Curves.easeOutCubic,
      padding: padding,
      child: widget.child,
    );
  }
}
