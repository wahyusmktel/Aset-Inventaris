import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_constants.dart';
import '../models/governance.dart';
import '../models/inventory_item.dart';
import '../models/master_data.dart';
import '../models/user.dart';
import 'auth_service.dart';

class ApiService {
  static Future<Map<String, String>> _getHeaders({bool isMultipart = false}) async {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (!isMultipart) {
      headers['Content-Type'] = 'application/json';
    }
    if (AuthService.token != null) {
      headers['Authorization'] = 'Bearer ${AuthService.token}';
    }
    return headers;
  }

  // 1. LOGIN
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final url = Uri.parse('$baseUrl/auth/login');

    try {
      final response = await http.post(
        url,
        headers: await _getHeaders(),
        body: jsonEncode({
          'email': email.trim(),
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final token = data['access_token']?.toString() ?? '';
        final user = User.fromJson(data['user'] ?? {});
        final governance = data['governance'] != null
            ? GovernanceStatus.fromJson(data['governance'])
            : null;

        await AuthService.saveSession(token: token, user: user, governance: governance);

        return {'success': true, 'message': data['message'] ?? 'Login berhasil'};
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Alamat email atau password salah.'
        };
      }
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // 2. GET CURRENT PROFILE & GOVERNANCE
  static Future<void> refreshProfile() async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final url = Uri.parse('$baseUrl/auth/me');

    try {
      final response = await http.get(url, headers: await _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['governance'] != null) {
          AuthService.updateGovernance(GovernanceStatus.fromJson(data['governance']));
        }
      }
    } catch (_) {}
  }

  // 3. MASTER DATA LOOKUPS
  static Future<MasterDataLookups?> getMasterData() async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final url = Uri.parse('$baseUrl/master-data/all');

    try {
      final response = await http.get(url, headers: await _getHeaders());
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true && body['data'] != null) {
          return MasterDataLookups.fromJson(body['data']);
        }
      }
    } catch (e) {
      print('Error loading master data: $e');
    }
    return null;
  }

  // 4. INVENTORY ITEMS (Paginated List)
  static Future<Map<String, dynamic>> getInventoryItems({
    int page = 1,
    String? search,
    String? condition,
    String? categoryId,
    String? buildingId,
    String? roomId,
    String? functionId,
  }) async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final queryParams = <String, String>{
      'page': page.toString(),
      'per_page': '10',
    };
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (condition != null && condition.isNotEmpty) queryParams['condition'] = condition;
    if (categoryId != null && categoryId.isNotEmpty) queryParams['category_id'] = categoryId;
    if (buildingId != null && buildingId.isNotEmpty) queryParams['building_id'] = buildingId;
    if (roomId != null && roomId.isNotEmpty) queryParams['room_id'] = roomId;
    if (functionId != null && functionId.isNotEmpty) queryParams['function_id'] = functionId;

    final uri = Uri.parse('$baseUrl/inventory/items').replace(queryParameters: queryParams);

    try {
      final response = await http.get(uri, headers: await _getHeaders());
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        final paginatedData = body['data'];
        final items = (paginatedData['data'] as List? ?? [])
            .map((e) => InventoryItemModel.fromJson(e))
            .toList();

        return {
          'success': true,
          'items': items,
          'current_page': paginatedData['current_page'] ?? 1,
          'last_page': paginatedData['last_page'] ?? 1,
          'total': paginatedData['total'] ?? 0,
        };
      }
    } catch (e) {
      return {'success': false, 'message': 'Gagal memuat barang: $e', 'items': <InventoryItemModel>[]};
    }
    return {'success': false, 'message': 'Gagal mengambil data inventaris.', 'items': <InventoryItemModel>[]};
  }

  // 5. SAVE / CREATE INVENTORY ITEM (With Optional Photo)
  static Future<Map<String, dynamic>> createInventoryItem({
    required String name,
    String? serialNumber,
    required bool hasNoSerialNumber,
    String? brand,
    required int quantity,
    required String condition,
    String? categoryId,
    String? buildingId,
    String? roomId,
    String? functionId,
    String? notes,
    File? photoFile,
  }) async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final uri = Uri.parse('$baseUrl/inventory/items');

    try {
      final request = http.MultipartRequest('POST', uri);
      request.headers.addAll(await _getHeaders(isMultipart: true));

      request.fields['name'] = name;
      request.fields['has_no_serial_number'] = hasNoSerialNumber ? '1' : '0';
      if (!hasNoSerialNumber && serialNumber != null) {
        request.fields['serial_number'] = serialNumber;
      }
      if (brand != null) request.fields['brand'] = brand;
      request.fields['quantity'] = quantity.toString();
      request.fields['condition'] = condition;
      if (categoryId != null) request.fields['category_id'] = categoryId;
      if (buildingId != null) request.fields['building_id'] = buildingId;
      if (roomId != null) request.fields['room_id'] = roomId;
      if (functionId != null) request.fields['function_id'] = functionId;
      if (notes != null) request.fields['notes'] = notes;

      if (photoFile != null && await photoFile.exists()) {
        request.files.add(await http.MultipartFile.fromPath('photo', photoFile.path));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final body = jsonDecode(response.body);

      if (response.statusCode == 201 && body['success'] == true) {
        return {'success': true, 'message': body['message'] ?? 'Barang berhasil dicatat!'};
      } else {
        return {'success': false, 'message': body['message'] ?? 'Gagal menyimpan barang.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Kesalahan jaringan: $e'};
    }
  }

  // 6. UPDATE INVENTORY ITEM
  static Future<Map<String, dynamic>> updateInventoryItem({
    required String id,
    required String name,
    String? serialNumber,
    required bool hasNoSerialNumber,
    String? brand,
    required int quantity,
    required String condition,
    String? categoryId,
    String? buildingId,
    String? roomId,
    String? functionId,
    String? notes,
    File? photoFile,
  }) async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final uri = Uri.parse('$baseUrl/inventory/items/$id');

    try {
      final request = http.MultipartRequest('POST', uri);
      request.headers.addAll(await _getHeaders(isMultipart: true));

      request.fields['name'] = name;
      request.fields['has_no_serial_number'] = hasNoSerialNumber ? '1' : '0';
      if (!hasNoSerialNumber && serialNumber != null) {
        request.fields['serial_number'] = serialNumber;
      }
      if (brand != null) request.fields['brand'] = brand;
      request.fields['quantity'] = quantity.toString();
      request.fields['condition'] = condition;
      if (categoryId != null) request.fields['category_id'] = categoryId;
      if (buildingId != null) request.fields['building_id'] = buildingId;
      if (roomId != null) request.fields['room_id'] = roomId;
      if (functionId != null) request.fields['function_id'] = functionId;
      if (notes != null) request.fields['notes'] = notes;

      if (photoFile != null && await photoFile.exists()) {
        request.files.add(await http.MultipartFile.fromPath('photo', photoFile.path));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final body = jsonDecode(response.body);

      if (response.statusCode == 200 && body['success'] == true) {
        return {'success': true, 'message': body['message'] ?? 'Data barang berhasil diperbarui!'};
      } else {
        return {'success': false, 'message': body['message'] ?? 'Gagal memperbarui barang.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Kesalahan jaringan: $e'};
    }
  }

  // 7. DELETE INVENTORY ITEM
  static Future<Map<String, dynamic>> deleteInventoryItem(String id) async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final uri = Uri.parse('$baseUrl/inventory/items/$id');

    try {
      final response = await http.delete(uri, headers: await _getHeaders());
      final body = jsonDecode(response.body);

      if (response.statusCode == 200 && body['success'] == true) {
        return {'success': true, 'message': body['message'] ?? 'Barang berhasil diarsipkan.'};
      } else {
        return {'success': false, 'message': body['message'] ?? 'Gagal menghapus barang.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Kesalahan jaringan: $e'};
    }
  }

  // 8. DASHBOARD KPI METRICS
  static Future<Map<String, dynamic>> getDashboardStats() async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final uri = Uri.parse('$baseUrl/inventory/stats');

    try {
      final response = await http.get(uri, headers: await _getHeaders());
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true && body['data'] != null) {
          return body['data'];
        }
      }
    } catch (e) {
      print('Error loading stats: $e');
    }
    return {
      'total_items': 0,
      'total_quantity': 0,
      'good_condition': 0,
      'damaged_condition': 0,
      'good_percent': 100,
      'total_rooms': 0,
    };
  }

  // 9. SIGN INTEGRITY PACT
  static Future<Map<String, dynamic>> signIntegrityPact() async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final uri = Uri.parse('$baseUrl/governance/sign-pact');

    try {
      final response = await http.post(
        uri,
        headers: await _getHeaders(),
        body: jsonEncode({'is_agreed': true}),
      );
      final body = jsonDecode(response.body);

      if (response.statusCode == 200 && body['success'] == true) {
        await refreshProfile();
        return {'success': true, 'message': body['message'] ?? 'Pakta integritas berhasil ditandatangani!'};
      } else {
        return {'success': false, 'message': body['message'] ?? 'Gagal menandatangani pakta integritas.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Kesalahan jaringan: $e'};
    }
  }

  // 10. FINALIZE DATA
  static Future<Map<String, dynamic>> finalizeData(String notes) async {
    final baseUrl = await ApiConstants.getBaseUrl();
    final uri = Uri.parse('$baseUrl/governance/finalize');

    try {
      final response = await http.post(
        uri,
        headers: await _getHeaders(),
        body: jsonEncode({'notes': notes}),
      );
      final body = jsonDecode(response.body);

      if (response.statusCode == 200 && body['success'] == true) {
        await refreshProfile();
        return {'success': true, 'message': body['message'] ?? 'Data inventaris berhasil difinalisasi!'};
      } else {
        return {'success': false, 'message': body['message'] ?? 'Gagal memfinalisasi data.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Kesalahan jaringan: $e'};
    }
  }
}
