class InventoryItemModel {
  final String id;
  final String name;
  final String? serialNumber;
  final bool hasNoSerialNumber;
  final String? brand;
  final int quantity;
  final String condition; // 'Baik' or 'Rusak'
  final String? photoPath;
  final String? categoryId;
  final String? buildingId;
  final String? roomId;
  final String? functionId;
  final String? notes;
  final String? createdBy;
  final String? createdAt;
  final String? updatedAt;

  // Relations
  final Map<String, dynamic>? category;
  final Map<String, dynamic>? building;
  final Map<String, dynamic>? room;
  final Map<String, dynamic>? itemFunction;
  final Map<String, dynamic>? creator;

  InventoryItemModel({
    required this.id,
    required this.name,
    this.serialNumber,
    required this.hasNoSerialNumber,
    this.brand,
    required this.quantity,
    required this.condition,
    this.photoPath,
    this.categoryId,
    this.buildingId,
    this.roomId,
    this.functionId,
    this.notes,
    this.createdBy,
    this.createdAt,
    this.updatedAt,
    this.category,
    this.building,
    this.room,
    this.itemFunction,
    this.creator,
  });

  bool get isGoodCondition => condition == 'Baik';
  bool isOwnedBy(String? userId) => userId != null && createdBy == userId;

  factory InventoryItemModel.fromJson(Map<String, dynamic> json) {
    return InventoryItemModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      serialNumber: json['serial_number'],
      hasNoSerialNumber: json['has_no_serial_number'] == true || json['has_no_serial_number'] == 1,
      brand: json['brand'],
      quantity: json['quantity'] is int ? json['quantity'] : int.tryParse(json['quantity']?.toString() ?? '1') ?? 1,
      condition: json['condition'] ?? 'Baik',
      photoPath: json['photo_path'],
      categoryId: json['category_id']?.toString(),
      buildingId: json['building_id']?.toString(),
      roomId: json['room_id']?.toString(),
      functionId: json['function_id']?.toString(),
      notes: json['notes'],
      createdBy: json['created_by']?.toString(),
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      category: json['category'] is Map<String, dynamic> ? json['category'] : null,
      building: json['building'] is Map<String, dynamic> ? json['building'] : null,
      room: json['room'] is Map<String, dynamic> ? json['room'] : null,
      itemFunction: json['item_function'] is Map<String, dynamic> ? json['item_function'] : null,
      creator: json['creator'] is Map<String, dynamic> ? json['creator'] : null,
    );
  }
}
