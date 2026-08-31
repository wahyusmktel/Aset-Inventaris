import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/api_constants.dart';
import '../../config/theme.dart';
import '../../models/inventory_item.dart';
import '../../models/master_data.dart';
import '../../services/api_service.dart';

class ItemFormScreen extends StatefulWidget {
  final InventoryItemModel? existingItem;

  const ItemFormScreen({super.key, this.existingItem});

  @override
  State<ItemFormScreen> createState() => _ItemFormScreenState();
}

class _ItemFormScreenState extends State<ItemFormScreen> {
  final _formKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _serialNumberController = TextEditingController();
  final _brandController = TextEditingController();
  final _quantityController = TextEditingController(text: '1');
  final _notesController = TextEditingController();

  bool _hasNoSerialNumber = false;
  String _condition = 'Baik';

  String? _selectedCategoryId;
  String? _selectedBuildingId;
  String? _selectedRoomId;
  String? _selectedFunctionId;

  File? _pickedPhoto;
  final ImagePicker _picker = ImagePicker();

  bool _isLoadingMasterData = true;
  bool _isSubmitting = false;
  MasterDataLookups? _masterData;

  bool get isEditing => widget.existingItem != null;

  @override
  void initState() {
    super.initState();
    _initData();
  }

  Future<void> _initData() async {
    final md = await ApiService.getMasterData();
    if (mounted) {
      setState(() {
        _masterData = md;
        _isLoadingMasterData = false;
      });
    }

    if (isEditing) {
      final item = widget.existingItem!;
      _nameController.text = item.name;
      _serialNumberController.text = item.serialNumber ?? '';
      _hasNoSerialNumber = item.hasNoSerialNumber;
      _brandController.text = item.brand ?? '';
      _quantityController.text = item.quantity.toString();
      _condition = item.condition;
      _notesController.text = item.notes ?? '';

      _selectedCategoryId = item.categoryId;
      _selectedBuildingId = item.buildingId;
      _selectedRoomId = item.roomId;
      _selectedFunctionId = item.functionId;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _serialNumberController.dispose();
    _brandController.dispose();
    _quantityController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? file = await _picker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 85, // Automatic client-side compression below 1MB
      );

      if (file != null) {
        setState(() {
          _pickedPhoto = File(file.path);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengambil foto: $e')),
      );
    }
  }

  void _showImageSourceDialog() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Wrap(
          children: [
            ListTile(
              leading: const Icon(Icons.camera_alt, color: AppTheme.primary),
              title: const Text('Ambil Foto dari Kamera Langsung'),
              onTap: () {
                Navigator.pop(ctx);
                _pickImage(ImageSource.camera);
              },
            ),
            ListTile(
              leading: const Icon(Icons.photo_library, color: AppTheme.secondary),
              title: const Text('Pilih dari Galeri Foto'),
              onTap: () {
                Navigator.pop(ctx);
                _pickImage(ImageSource.gallery);
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final qty = int.tryParse(_quantityController.text) ?? 1;
    if (qty <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Jumlah kuantitas barang minimal 1 unit.')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    Map<String, dynamic> result;

    if (isEditing) {
      result = await ApiService.updateInventoryItem(
        id: widget.existingItem!.id,
        name: _nameController.text.trim(),
        serialNumber: _serialNumberController.text.trim(),
        hasNoSerialNumber: _hasNoSerialNumber,
        brand: _brandController.text.trim(),
        quantity: qty,
        condition: _condition,
        categoryId: _selectedCategoryId,
        buildingId: _selectedBuildingId,
        roomId: _selectedRoomId,
        functionId: _selectedFunctionId,
        notes: _notesController.text.trim(),
        photoFile: _pickedPhoto,
      );
    } else {
      result = await ApiService.createInventoryItem(
        name: _nameController.text.trim(),
        serialNumber: _serialNumberController.text.trim(),
        hasNoSerialNumber: _hasNoSerialNumber,
        brand: _brandController.text.trim(),
        quantity: qty,
        condition: _condition,
        categoryId: _selectedCategoryId,
        buildingId: _selectedBuildingId,
        roomId: _selectedRoomId,
        functionId: _selectedFunctionId,
        notes: _notesController.text.trim(),
        photoFile: _pickedPhoto,
      );
    }

    if (!mounted) return;

    setState(() => _isSubmitting = false);

    if (result['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppTheme.success,
          content: Text(result['message'] ?? 'Data berhasil disimpan.'),
        ),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppTheme.error,
          content: Text(result['message'] ?? 'Gagal menyimpan data barang.'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: Text(isEditing ? 'Ubah Data Barang' : 'Catat Barang Baru'),
      ),
      body: _isLoadingMasterData
          ? const Center(child: CircularProgressIndicator())
          : SafeArea(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // 1. Photo Capture Box
                      _buildPhotoSection(),
                      const SizedBox(height: 20),

                      // 2. Main Item Info Card
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Informasi Utama Barang',
                                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                              ),
                              const SizedBox(height: 14),

                              // Name Field
                              const Text('Nama Barang & Tipe *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _nameController,
                                decoration: const InputDecoration(
                                  hintText: 'Contoh: PC Desktop Asus ROG Strix G15',
                                  prefixIcon: Icon(Icons.devices, size: 20),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) {
                                    return 'Nama barang wajib diisi';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 14),

                              // Serial Number Field + No-SN Checkbox
                              const Text('Serial Number (SN)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _serialNumberController,
                                enabled: !_hasNoSerialNumber,
                                decoration: InputDecoration(
                                  hintText: _hasNoSerialNumber ? 'Barang tidak memiliki nomor seri' : 'Contoh: SN-ROG-882910',
                                  prefixIcon: const Icon(Icons.qr_code, size: 20),
                                  filled: true,
                                  fillColor: _hasNoSerialNumber ? Colors.grey.shade100 : Colors.white,
                                ),
                              ),
                              const SizedBox(height: 4),
                              CheckboxListTile(
                                value: _hasNoSerialNumber,
                                onChanged: (val) {
                                  setState(() {
                                    _hasNoSerialNumber = val ?? false;
                                    if (_hasNoSerialNumber) {
                                      _serialNumberController.clear();
                                    }
                                  });
                                },
                                activeColor: AppTheme.primary,
                                contentPadding: EdgeInsets.zero,
                                controlAffinity: ListTileControlAffinity.leading,
                                title: const Text(
                                  'Barang ini tidak memiliki Serial Number',
                                  style: TextStyle(fontSize: 11.5, color: AppTheme.textSecondary),
                                ),
                              ),
                              const SizedBox(height: 10),

                              // Brand Field
                              const Text('Merk / Brand Pabrikan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _brandController,
                                decoration: const InputDecoration(
                                  hintText: 'Contoh: ASUS, Mikrotik, Cisco, Epson',
                                  prefixIcon: Icon(Icons.verified, size: 20),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // 3. Quantity & Condition Card
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Kuantitas & Kondisi Fisik',
                                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                              ),
                              const SizedBox(height: 14),

                              // Quantity
                              const Text('Jumlah / Kuantitas Unit *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _quantityController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  hintText: '1',
                                  prefixIcon: Icon(Icons.format_list_numbered, size: 20),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) return 'Kuantitas wajib diisi';
                                  final num = int.tryParse(val);
                                  if (num == null || num <= 0) return 'Masukkan angka lebih dari 0';
                                  return null;
                                },
                              ),
                              const SizedBox(height: 16),

                              // Condition Radio
                              const Text('Kondisi Fisik Barang *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Expanded(
                                    child: InkWell(
                                      onTap: () => setState(() => _condition = 'Baik'),
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                                        decoration: BoxDecoration(
                                          color: _condition == 'Baik' ? AppTheme.successContainer : Colors.white,
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: _condition == 'Baik' ? AppTheme.success : AppTheme.outline,
                                            width: _condition == 'Baik' ? 2 : 1,
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(
                                              Icons.check_circle,
                                              size: 18,
                                              color: _condition == 'Baik' ? AppTheme.success : Colors.grey,
                                            ),
                                            const SizedBox(width: 6),
                                            Text(
                                              '1. Baik',
                                              style: TextStyle(
                                                fontSize: 13,
                                                fontWeight: FontWeight.bold,
                                                color: _condition == 'Baik' ? const Color(0xFF065F46) : AppTheme.textPrimary,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: InkWell(
                                      onTap: () => setState(() => _condition = 'Rusak'),
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                                        decoration: BoxDecoration(
                                          color: _condition == 'Rusak' ? const Color(0xFFFEE2E2) : Colors.white,
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: _condition == 'Rusak' ? AppTheme.error : AppTheme.outline,
                                            width: _condition == 'Rusak' ? 2 : 1,
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(
                                              Icons.warning,
                                              size: 18,
                                              color: _condition == 'Rusak' ? AppTheme.error : Colors.grey,
                                            ),
                                            const SizedBox(width: 6),
                                            Text(
                                              '2. Rusak',
                                              style: TextStyle(
                                                fontSize: 13,
                                                fontWeight: FontWeight.bold,
                                                color: _condition == 'Rusak' ? const Color(0xFF991B1B) : AppTheme.textPrimary,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // 4. Location & Category Selectors
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Penempatan & Klasifikasi',
                                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                              ),
                              const SizedBox(height: 14),

                              // Category Dropdown
                              const Text('Kategori Barang', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<String>(
                                value: _selectedCategoryId,
                                decoration: const InputDecoration(prefixIcon: Icon(Icons.category, size: 20)),
                                hint: const Text('Pilih Kategori', style: TextStyle(fontSize: 13)),
                                items: _masterData?.categories.map((c) {
                                  return DropdownMenuItem(value: c.id, child: Text('${c.code} - ${c.name}', style: const TextStyle(fontSize: 13)));
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedCategoryId = val),
                              ),
                              const SizedBox(height: 14),

                              // Building Dropdown
                              const Text('Gedung Sekolah', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<String>(
                                value: _selectedBuildingId,
                                decoration: const InputDecoration(prefixIcon: Icon(Icons.domain, size: 20)),
                                hint: const Text('Pilih Gedung', style: TextStyle(fontSize: 13)),
                                items: _masterData?.buildings.map((b) {
                                  return DropdownMenuItem(value: b.id, child: Text('${b.code} - ${b.name}', style: const TextStyle(fontSize: 13)));
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedBuildingId = val),
                              ),
                              const SizedBox(height: 14),

                              // Room Dropdown
                              const Text('Ruangan / Laboratorium', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<String>(
                                value: _selectedRoomId,
                                decoration: const InputDecoration(prefixIcon: Icon(Icons.meeting_room, size: 20)),
                                hint: const Text('Pilih Ruangan', style: TextStyle(fontSize: 13)),
                                items: _masterData?.rooms.map((r) {
                                  return DropdownMenuItem(value: r.id, child: Text('${r.code} - ${r.name}', style: const TextStyle(fontSize: 13)));
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedRoomId = val),
                              ),
                              const SizedBox(height: 14),

                              // Function Dropdown
                              const Text('Fungsi Barang', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<String>(
                                value: _selectedFunctionId,
                                decoration: const InputDecoration(prefixIcon: Icon(Icons.construction, size: 20)),
                                hint: const Text('Pilih Fungsi Barang', style: TextStyle(fontSize: 13)),
                                items: _masterData?.functions.map((f) {
                                  return DropdownMenuItem(value: f.id, child: Text('${f.code} - ${f.name}', style: const TextStyle(fontSize: 13)));
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedFunctionId = val),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // 5. Notes
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Catatan Tambahan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _notesController,
                                maxLines: 3,
                                decoration: const InputDecoration(
                                  hintText: 'Keterangan pengadaan, kelengkapan aksesoris...',
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Submit Button
                      SizedBox(
                        height: 52,
                        child: ElevatedButton(
                          onPressed: _isSubmitting ? null : _handleSubmit,
                          child: _isSubmitting
                              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                              : Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const Icon(Icons.save, size: 20),
                                    const SizedBox(width: 8),
                                    Text(isEditing ? 'Simpan Perubahan' : 'Simpan Barang Inventaris', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                        ),
                      ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),
            ),
    );
  }

  Widget _buildPhotoSection() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Foto Fisik Barang', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(color: AppTheme.primaryContainer, borderRadius: BorderRadius.circular(12)),
                child: const Text('Kompresi Otomatis < 1MB', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.primary)),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Photo Preview / Picker Button
          if (_pickedPhoto != null)
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: Image.file(_pickedPhoto!, height: 180, width: double.infinity, fit: BoxFit.cover),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: CircleAvatar(
                    backgroundColor: Colors.black54,
                    radius: 18,
                    child: IconButton(
                      icon: const Icon(Icons.close, color: Colors.white, size: 18),
                      onPressed: () => setState(() => _pickedPhoto = null),
                    ),
                  ),
                ),
              ],
            )
          else if (isEditing && widget.existingItem?.photoPath != null)
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: FutureBuilder<String>(
                    future: ApiConstants.getBaseUrl(),
                    builder: (ctx, snapshot) {
                      final baseUrl = snapshot.data ?? ApiConstants.defaultBaseUrl;
                      final rootUrl = baseUrl.replaceAll('/api', '');
                      return Image.network(
                        '$rootUrl${widget.existingItem!.photoPath}',
                        height: 180,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      );
                    },
                  ),
                ),
                Positioned(
                  bottom: 8,
                  right: 8,
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.camera_alt, size: 16),
                    label: const Text('Ganti Foto', style: TextStyle(fontSize: 12)),
                    onPressed: _showImageSourceDialog,
                  ),
                ),
              ],
            )
          else
            InkWell(
              onTap: _showImageSourceDialog,
              borderRadius: BorderRadius.circular(14),
              child: Container(
                height: 140,
                decoration: BoxDecoration(
                  color: AppTheme.background,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.grey.shade300, style: BorderStyle.solid),
                ),
                child: const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.add_a_photo, size: 36, color: AppTheme.primary),
                      SizedBox(height: 8),
                      Text('Ambil Foto Kamera / Galeri', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                      SizedBox(height: 2),
                      Text('Dokumentasi fisik barang inventaris', style: TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
