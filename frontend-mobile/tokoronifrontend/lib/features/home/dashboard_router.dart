import 'package:flutter/widgets.dart';

import '../../core/access/role_access.dart';
import '../../core/state/app_state.dart';
import 'dashboard_checker_page.dart';
import 'dashboard_gudang_page.dart';
import 'dashboard_kasir_page.dart';
import 'dashboard_logistik_page.dart';
import 'dashboard_page.dart';

class DashboardRouter {
  DashboardRouter._();

  static Widget pageForCurrentUser() {
    return pageForRole(AppState.instance.userRole.value);
  }

  static Widget pageForRole(String rawRole) {
    if (RoleAccess.isKasir(rawRole)) return const DashboardKasirPage();
    if (RoleAccess.isKepalaGudang(rawRole)) return const DashboardGudangPage();
    if (RoleAccess.isStaffLogistik(rawRole)) {
      return const DashboardLogistikPage();
    }
    if (RoleAccess.isCheckerBarang(rawRole)) {
      return const DashboardCheckerPage();
    }
    return const BerandaPage();
  }
}
