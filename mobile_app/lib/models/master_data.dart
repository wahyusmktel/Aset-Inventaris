class ItemCategoryModel {
  final String id;
  final String code;
  final String name;

  ItemCategoryModel({required this.id, required this.code, required this.name});

  factory ItemCategoryModel.fromJson(Map<String, dynamic> json) {
    return ItemCategoryModel(
      id: json['id']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      name: json['name'] ?? '',
    );
  }
}

class BuildingModel {
  final String id;
  final String code;
  final String name;

  BuildingModel({required this.id, required this.code, required this.name});

  factory BuildingModel.fromJson(Map<String, dynamic> json) {
    return BuildingModel(
      id: json['id']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      name: json['name'] ?? '',
    );
  }
}

class RoomModel {
  final String id;
  final String code;
  final String name;
  final String? buildingId;

  RoomModel({required this.id, required this.code, required this.name, this.buildingId});

  factory RoomModel.fromJson(Map<String, dynamic> json) {
    return RoomModel(
      id: json['id']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      name: json['name'] ?? '',
      buildingId: json['building_id']?.toString(),
    );
  }
}

class ItemFunctionModel {
  final String id;
  final String code;
  final String name;

  ItemFunctionModel({required this.id, required this.code, required this.name});

  factory ItemFunctionModel.fromJson(Map<String, dynamic> json) {
    return ItemFunctionModel(
      id: json['id']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      name: json['name'] ?? '',
    );
  }
}

class MasterDataLookups {
  final List<ItemCategoryModel> categories;
  final List<BuildingModel> buildings;
  final List<RoomModel> rooms;
  final List<ItemFunctionModel> functions;
  final Map<String, dynamic>? school;

  MasterDataLookups({
    required this.categories,
    required this.buildings,
    required this.rooms,
    required this.functions,
    this.school,
  });

  factory MasterDataLookups.fromJson(Map<String, dynamic> json) {
    return MasterDataLookups(
      categories: (json['categories'] as List? ?? [])
          .map((e) => ItemCategoryModel.fromJson(e))
          .toList(),
      buildings: (json['buildings'] as List? ?? [])
          .map((e) => BuildingModel.fromJson(e))
          .toList(),
      rooms: (json['rooms'] as List? ?? [])
          .map((e) => RoomModel.fromJson(e))
          .toList(),
      functions: (json['functions'] as List? ?? [])
          .map((e) => ItemFunctionModel.fromJson(e))
          .toList(),
      school: json['school'] is Map<String, dynamic> ? json['school'] : null,
    );
  }
}
