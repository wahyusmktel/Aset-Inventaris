class User {
  final String id;
  final String name;
  final String email;
  final String role; // 'super_admin' or 'anggota'
  final String? nip;
  final String? phone;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.nip,
    this.phone,
  });

  bool get isSuperAdmin => role == 'super_admin';
  bool get isAnggota => role == 'anggota';

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? 'anggota',
      nip: json['nip'],
      phone: json['phone'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'nip': nip,
      'phone': phone,
    };
  }
}
