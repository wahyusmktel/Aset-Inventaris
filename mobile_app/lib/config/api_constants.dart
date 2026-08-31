import 'package:shared_preferences/shared_preferences.dart';

class ApiConstants {
  // Default Production Base URL
  static const String defaultBaseUrl = 'https://aset.smktelkom-lpg.id/api';

  // Key for local storage overrides (e.g. testing with local network IP)
  static const String _keyCustomBaseUrl = 'custom_base_url';

  static Future<String> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyCustomBaseUrl) ?? defaultBaseUrl;
  }

  static Future<void> setBaseUrl(String newUrl) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyCustomBaseUrl, newUrl.trim());
  }

  static Future<void> resetBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyCustomBaseUrl);
  }
}
