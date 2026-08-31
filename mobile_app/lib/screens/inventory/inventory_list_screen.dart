import 'package:flutter/material.dart';
import '../../config/api_constants.dart';
import '../../config/theme.dart';
import '../../models/inventory_item.dart';
import '../../models/master_data.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import 'item_detail_dialog.dart';
import 'item_form_screen.dart';

class InventoryListScreen extends StatefulWidget {
  const InventoryListScreen({super.key});

  @override
  State<InventoryListScreen> createState() => _InventoryListScreenState();
}

class _InventoryListScreenState extends State<InventoryListScreen> {
  final _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  List<InventoryItemModel> _items = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  int _currentPage = 1;
  int _lastPage = 1;

  String? _selectedCondition;
  String? _selectedCategoryId;
  MasterDataLookups? _masterData;

  @override
  void initState() {
    super.initState();
    _loadMasterData();
    _loadItems(refresh: true);

    _scrollController.addListener(() {
      if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
        if (!_isLoading && !_isLoadingMore && _currentPage < _lastPage) {
          _loadItems(loadMore: true);
        }
      }
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadMasterData() async {
    final md = await ApiService.getMasterData();
    if (mounted) setState(() => _masterData = md);
  }

  Future<void> _loadItems({bool refresh = false, bool loadMore = false}) async {
    if (refresh) {
      setState(() {
        _isLoading = true;
        _currentPage = 1;
      });
    } else if (loadMore) {
      setState(() {
        _isLoadingMore = true;
        _currentPage++;
      });
    }

    final res = await ApiService.getInventoryItems(
      page: _currentPage,
      search: _searchController.text.trim(),
      condition: _selectedCondition,
      categoryId: _selectedCategoryId,
    );

    if (!mounted) return;

    setState(() {
      final newItems = res['items'] as List<InventoryItemModel>;
      if (refresh) {
        _items = newItems;
      } else {
        _items.addAll(newItems);
      }
      _currentPage = res['current_page'] ?? 1;
      _lastPage = res['last_page'] ?? 1;
      _isLoading = false;
      _isLoadingMore = false;
    });
  }

  void _openDetail(InventoryItemModel item) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => ItemDetailDialog(
        item: item,
        onEdit: () => _openEdit(item),
        onDelete: () => _confirmDelete(item),
      ),
    );
  }

  Future<void> _openEdit(InventoryItemModel item) async {
    final updated = await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ItemFormScreen(existingItem: item)),
    );
    if (updated == true) {
      _loadItems(refresh: true);
    }
  }

  Future<void> _confirmDelete(InventoryItemModel item) async {
    final isConfirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Arsipkan Barang?'),
        content: Text('Apakah Anda yakin ingin menghapus/mengarsipkan "${item.name}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.error),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Hapus'),
          ),
        ],
      ),
    );

    if (isConfirmed == true) {
      final res = await ApiService.deleteInventoryItem(item.id);
      if (mounted) {
        if (res['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(backgroundColor: AppTheme.success, content: Text('Barang berhasil diarsipkan.')),
          );
          _loadItems(refresh: true);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(backgroundColor: AppTheme.error, content: Text(res['message'] ?? 'Gagal menghapus barang.')),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Daftar Inventaris Barang'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
            tooltip: 'Filter Data',
          ),
        ],
      ),
      body: Column(
        children: [
          // 1. Search Bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari nama barang, brand, SN...',
                prefixIcon: const Icon(Icons.search, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          _loadItems(refresh: true);
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              ),
              onSubmitted: (_) => _loadItems(refresh: true),
            ),
          ),

          // 2. Filter Chips Row
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            child: Row(
              children: [
                FilterChip(
                  label: const Text('Semua'),
                  selected: _selectedCondition == null && _selectedCategoryId == null,
                  onSelected: (_) {
                    setState(() {
                      _selectedCondition = null;
                      _selectedCategoryId = null;
                    });
                    _loadItems(refresh: true);
                  },
                ),
                const SizedBox(width: 8),
                FilterChip(
                  label: const Text('1. Kondisi Baik'),
                  selected: _selectedCondition == 'Baik',
                  selectedColor: AppTheme.successContainer,
                  onSelected: (val) {
                    setState(() => _selectedCondition = val ? 'Baik' : null);
                    _loadItems(refresh: true);
                  },
                ),
                const SizedBox(width: 8),
                FilterChip(
                  label: const Text('2. Kondisi Rusak'),
                  selected: _selectedCondition == 'Rusak',
                  selectedColor: const Color(0xFFFEE2E2),
                  onSelected: (val) {
                    setState(() => _selectedCondition = val ? 'Rusak' : null);
                    _loadItems(refresh: true);
                  },
                ),
              ],
            ),
          ),

          // 3. Item List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _items.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            const Text('Belum ada data barang.', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                            const SizedBox(height: 4),
                            const Text('Klik tombol + di bawah untuk mencatat barang.', style: TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => _loadItems(refresh: true),
                        color: AppTheme.primary,
                        child: ListView.separated(
                          controller: _scrollController,
                          padding: const EdgeInsets.all(16),
                          itemCount: _items.length + (_isLoadingMore ? 1 : 0),
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (ctx, index) {
                            if (index == _items.length) {
                              return const Center(
                                child: Padding(
                                  padding: EdgeInsets.all(16),
                                  child: CircularProgressIndicator(strokeWidth: 2.5),
                                ),
                              );
                            }

                            final item = _items[index];
                            final isOwned = item.isOwnedBy(user?.id);

                            return Card(
                              child: InkWell(
                                onTap: () => _openDetail(item),
                                borderRadius: BorderRadius.circular(16),
                                child: Padding(
                                  padding: const EdgeInsets.all(12),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Photo Thumbnail
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(10),
                                        child: Container(
                                          width: 64,
                                          height: 64,
                                          color: AppTheme.background,
                                          child: item.photoPath != null
                                              ? FutureBuilder<String>(
                                                  future: ApiConstants.getBaseUrl(),
                                                  builder: (context, snapshot) {
                                                    final baseUrl = snapshot.data ?? ApiConstants.defaultBaseUrl;
                                                    final rootUrl = baseUrl.replaceAll('/api', '');
                                                    return Image.network(
                                                      '$rootUrl${item.photoPath}',
                                                      fit: BoxFit.cover,
                                                      errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported, color: Colors.grey),
                                                    );
                                                  },
                                                )
                                              : const Icon(Icons.devices, color: AppTheme.primary),
                                        ),
                                      ),
                                      const SizedBox(width: 12),

                                      // Item Meta
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              item.name,
                                              maxLines: 2,
                                              overflow: TextOverflow.ellipsis,
                                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                                            ),
                                            const SizedBox(height: 2),
                                            Row(
                                              children: [
                                                if (item.brand != null) ...[
                                                  Text(item.brand!, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.primary)),
                                                  const Text(' • ', style: TextStyle(color: Colors.grey)),
                                                ],
                                                Text(item.room?['name'] ?? 'Lokasi -', style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                                              ],
                                            ),
                                            const SizedBox(height: 6),

                                            // Badges Row
                                            Row(
                                              children: [
                                                // Condition Badge
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: item.isGoodCondition ? AppTheme.successContainer : const Color(0xFFFEE2E2),
                                                    borderRadius: BorderRadius.circular(6),
                                                  ),
                                                  child: Text(
                                                    item.condition,
                                                    style: TextStyle(
                                                      fontSize: 10,
                                                      fontWeight: FontWeight.bold,
                                                      color: item.isGoodCondition ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                                                    ),
                                                  ),
                                                ),
                                                const SizedBox(width: 6),
                                                Text('${item.quantity} Unit', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                                const Spacer(),

                                                // Ownership Tag
                                                if (isOwned)
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                    decoration: BoxDecoration(
                                                      color: AppTheme.primaryContainer,
                                                      borderRadius: BorderRadius.circular(6),
                                                    ),
                                                    child: const Text('Milik Anda', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: AppTheme.primary)),
                                                  ),
                                              ],
                                            ),
                                          ],
                                        ),
                                      ),

                                      // Actions Icon
                                      IconButton(
                                        icon: const Icon(Icons.chevron_right, color: AppTheme.textSecondary),
                                        onPressed: () => _openDetail(item),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_a_photo),
        label: const Text('Catat Barang', style: TextStyle(fontWeight: FontWeight.bold)),
        onPressed: () async {
          final created = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const ItemFormScreen()),
          );
          if (created == true) {
            _loadItems(refresh: true);
          }
        },
      ),
    );
  }

  void _showFilterDialog() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Filter Berdasarkan Kategori', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              value: _selectedCategoryId,
              hint: const Text('Semua Kategori'),
              items: [
                const DropdownMenuItem(value: null, child: Text('Semua Kategori')),
                ...(_masterData?.categories.map((c) => DropdownMenuItem(value: c.id, child: Text(c.name))) ?? []),
              ],
              onChanged: (val) {
                setState(() => _selectedCategoryId = val);
                Navigator.pop(ctx);
                _loadItems(refresh: true);
              },
            ),
          ],
        ),
      ),
    );
  }
}
