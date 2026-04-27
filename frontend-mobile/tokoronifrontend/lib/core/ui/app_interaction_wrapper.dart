import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

/// Global wrapper to dismiss active focus when user taps outside editable input.
/// This uses raw pointer events so child gestures (button tap, dropdown, etc.)
/// continue to work normally.
class AppInteractionWrapper extends StatelessWidget {
  final Widget child;

  const AppInteractionWrapper({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Listener(
      behavior: HitTestBehavior.translucent,
      onPointerDown: _handlePointerDown,
      child: StabilizedKeyboardInsets(child: child),
    );
  }

  void _handlePointerDown(PointerDownEvent event) {
    final currentFocus = FocusManager.instance.primaryFocus;
    if (currentFocus == null || !currentFocus.hasFocus) return;

    if (_isTapOnEditable(event)) return;
    currentFocus.unfocus();
  }

  bool _isTapOnEditable(PointerDownEvent event) {
    final hitTestResult = HitTestResult();
    WidgetsBinding.instance.hitTestInView(
      hitTestResult,
      event.position,
      event.viewId,
    );

    for (final entry in hitTestResult.path) {
      if (entry.target is RenderEditable) {
        return true;
      }
    }
    return false;
  }
}

/// Reduces heavy relayout during keyboard open/close animation by stabilizing
/// bottom viewInsets and committing only when metrics settle.
class StabilizedKeyboardInsets extends StatefulWidget {
  final Widget child;
  final Duration settleDuration;

  const StabilizedKeyboardInsets({
    super.key,
    required this.child,
    this.settleDuration = const Duration(milliseconds: 90),
  });

  @override
  State<StabilizedKeyboardInsets> createState() =>
      _StabilizedKeyboardInsetsState();
}

class _StabilizedKeyboardInsetsState extends State<StabilizedKeyboardInsets>
    with WidgetsBindingObserver {
  Timer? _settleTimer;
  bool _initialized = false;
  double _stableBottomInset = 0;
  double _pendingBottomInset = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_initialized) return;
    _stableBottomInset = _logicalBottomInset();
    _initialized = true;
  }

  @override
  void dispose() {
    _settleTimer?.cancel();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeMetrics() {
    _scheduleInsetCommit();
  }

  void _scheduleInsetCommit() {
    if (!mounted) return;
    _pendingBottomInset = _logicalBottomInset();

    _settleTimer?.cancel();
    _settleTimer = Timer(widget.settleDuration, () {
      if (!mounted) return;
      if ((_stableBottomInset - _pendingBottomInset).abs() < 0.5) return;
      setState(() {
        _stableBottomInset = _pendingBottomInset;
      });
    });
  }

  double _logicalBottomInset() {
    final view = View.maybeOf(context);
    if (view == null) return MediaQuery.viewInsetsOf(context).bottom;
    return view.viewInsets.bottom / view.devicePixelRatio;
  }

  @override
  Widget build(BuildContext context) {
    final mediaQuery = MediaQuery.of(context);
    final stabilizedInsets = mediaQuery.viewInsets.copyWith(
      bottom: _stableBottomInset,
    );

    return MediaQuery(
      data: mediaQuery.copyWith(viewInsets: stabilizedInsets),
      child: widget.child,
    );
  }
}

/// Global scroll behavior so any ScrollView dismisses keyboard on drag.
class AppScrollBehavior extends MaterialScrollBehavior {
  const AppScrollBehavior();

  @override
  ScrollViewKeyboardDismissBehavior getKeyboardDismissBehavior(
    BuildContext context,
  ) {
    return ScrollViewKeyboardDismissBehavior.onDrag;
  }
}
