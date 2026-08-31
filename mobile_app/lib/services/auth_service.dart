import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../models/governance.dart';

class AuthService {
  static const String _keyToken = 'auth_jwt_token';
  static const String _keyUser = 'auth_user_data';
  static const String _keyGovernance = 'auth_governance_data';

  static User? _currentUser;
  static GovernanceStatus? _currentGovernance;
  static String? _currentToken;

  static User? get currentUser => _currentUser;
  static GovernanceStatus? get currentGovernance => _currentGovernance;
  static String? get token => _currentToken;
  static bool get isLoggedIn => _currentToken != null && _currentUser != null;

  static Future<bool> loadSavedSession() async {
    final prefs = await SharedPreferences.getInstance();
    _currentToken = prefs.getString(_keyToken);
    final userJsonStr = prefs.getString(_keyUser);
    final govJsonStr = prefs.getString(_keyGovernance);

    if (_currentToken != null && userJsonStr != null) {
      try {
        _currentUser = User.fromJson(jsonDecode(userJsonStr));
        if (govJsonStr != null) {
          _currentGovernance = GovernanceStatus.fromJson(jsonDecode(govJsonStr));
        }
        return true;
      } catch (e) {
        await clearSession();
        return false;
      }
    }
    return false;
  }

  static Future<void> saveSession({
    required String token,
    required User user,
    GovernanceStatus? governance,
  }) async {
    _currentToken = token;
    _currentUser = user;
    _currentGovernance = governance;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyToken, token);
    await prefs.setString(_keyUser, jsonEncode(user.toJson()));
    if (governance != null) {
      await prefs.setString(_keyGovernance, jsonEncode({
        'has_signed_pact': governance.hasSignedPact,
        'has_finalized': governance.hasFinalized,
        'is_cutoff_passed': governance.isCutoffPassed,
        'active_period': governance.activePeriod,
        'active_school': governance.activeSchool,
      }));
    }
  }

  static void updateGovernance(GovernanceStatus gov) {
    _currentGovernance = gov;
  }

  static Future<void> clearSession() async {
    _currentToken = null;
    _currentUser = null;
    _currentGovernance = null;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyUser);
    await prefs.remove(_keyGovernance);
  }
}
