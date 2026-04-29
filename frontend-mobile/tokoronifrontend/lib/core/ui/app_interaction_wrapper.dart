import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

/// Global wrapper to dismiss focus when user taps outside editable inputs.
/// It deliberately ignores drags/scroll so keyboard is not dismissed while
/// users only scroll through content.
class AppInteractionWrapper extends StatefulWidget {
  const AppInteractionWrapper({super.key, required this.child});

  final Widget child;

  @override
  State<AppInteractionWrapper> createState() => _AppInteractionWrapperState();
}

class _AppInteractionWrapperState extends State<AppInteractionWrapper>
    with WidgetsBindingObserver {
  static const double _tapSlop = 18;
  static const Duration _maxTapDuration = Duration(milliseconds: 300);

  final Map<int, _TapCandidate> _tapCandidates = <int, _TapCandidate>{};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    _tapCandidates.clear();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  /// Dismiss keyboard first on Android back, then allow regular route pop.
  @override
  Future<bool> didPopRoute() async {
    final focus = FocusManager.instance.primaryFocus;
    if (focus == null || !focus.hasFocus) {
      return false;
    }

    focus.unfocus(disposition: UnfocusDisposition.scope);
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return Listener(
      behavior: HitTestBehavior.translucent,
      onPointerDown: _handlePointerDown,
      onPointerMove: _handlePointerMove,
      onPointerCancel: _handlePointerCancel,
      onPointerUp: _handlePointerUp,
      child: widget.child,
    );
  }

  void _handlePointerDown(PointerDownEvent event) {
    final focus = FocusManager.instance.primaryFocus;
    if (focus == null || !focus.hasFocus) return;

    _tapCandidates[event.pointer] = _TapCandidate(
      downPosition: event.position,
      downTime: DateTime.now(),
    );
  }

  void _handlePointerMove(PointerMoveEvent event) {
    final candidate = _tapCandidates[event.pointer];
    if (candidate == null || candidate.moved) return;

    final distance = (event.position - candidate.downPosition).distance;
    if (distance > _tapSlop) candidate.moved = true;
  }

  void _handlePointerCancel(PointerCancelEvent event) {
    _tapCandidates.remove(event.pointer);
  }

  void _handlePointerUp(PointerUpEvent event) {
    final candidate = _tapCandidates.remove(event.pointer);
    if (candidate == null || candidate.moved) return;
    if (DateTime.now().difference(candidate.downTime) > _maxTapDuration) return;

    final focus = FocusManager.instance.primaryFocus;
    if (focus == null || !focus.hasFocus) return;
    if (_isTapOnEditable(event.position, event.viewId)) return;

    focus.unfocus(disposition: UnfocusDisposition.scope);
  }

  bool _isTapOnEditable(Offset position, int viewId) {
    final hitTestResult = HitTestResult();
    WidgetsBinding.instance.hitTestInView(hitTestResult, position, viewId);

    for (final entry in hitTestResult.path) {
      if (entry.target is RenderEditable) {
        return true;
      }
    }
    return false;
  }
}

class _TapCandidate {
  _TapCandidate({required this.downPosition, required this.downTime});

  final Offset downPosition;
  final DateTime downTime;
  bool moved = false;
}

/// Global scroll behavior: keep keyboard open while dragging/scrolling.
class AppScrollBehavior extends MaterialScrollBehavior {
  const AppScrollBehavior();

  @override
  ScrollViewKeyboardDismissBehavior getKeyboardDismissBehavior(
    BuildContext context,
  ) {
    return ScrollViewKeyboardDismissBehavior.manual;
  }
}
