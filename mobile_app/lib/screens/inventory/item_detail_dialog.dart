import 'package:flutter/material.dart';
import '../../config/api_constants.dart';
import '../../config/theme.dart';
import '../../models/inventory_item.dart';
import '../../services/auth_service.dart';
import 'package:intl/intl.dart';

class ItemDetailDialog extends StatelessWidget {
  final InventoryItemModel item;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const ItemDetailDialog({
    super.key,
    required this.item,
    required this.onEdit,
    required this.onDelete,
  });

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '-';
    try {
      final dt = DateTime.parse(dateStr).toLocal();
      return DateFormat('d MMM yyyy, HH:mm').format(dt);
    } catch (_) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final canModify = user?.isSuperAdmin == true || item.isOwnedBy(user?.id);

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.85,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Drag handle
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header Title & Close
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Detail Barang Inventaris',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const Divider(),

            // Photo Preview
            if (item.photoPath != null) ...[
              ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: FutureBuilder<String>(
                  future: ApiConstants.getBaseUrl(),
                  builder: (ctx, snapshot) {
                    final baseUrl = snapshot.data ?? ApiConstants.defaultBaseUrl;
                    final rootUrl = baseUrl.replaceAll('/api', '');
                    final fullUrl = '$rootUrl${item.photoPath}';
                    return Image.network(
                      fullUrl,
                      height: 200,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        height: 120,
                        color: Colors.grey.shade100,
                        child: const Center(child: Icon(Icons.broken_image, color: Colors.grey)),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Item Name & Brand
            Text(
              item.name,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppTheme.textPrimary),
            ),
            if (item.brand != null && item.brand!.isNotEmpty)
              Text(
                'Merk / Brand: ${item.brand}',
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.primary),
              ),
            const SizedBox(height: 14),

            // Badges Row (Condition, Quantity, SN)
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                // Condition Badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: item.isGoodCondition ? AppTheme.successContainer : const Color(0xFFFEE2E2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        item.isGoodCondition ? Icons.check_circle : Icons.warning,
                        size: 14,
                        color: item.isGoodCondition ? AppTheme.success : AppTheme.error,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Kondisi: ${item.condition}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: item.isGoodCondition ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                        ),
                      ),
                    ],
                  ),
                ),

                // Quantity Badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.secondaryContainer,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '${item.quantity} Unit Fisik',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.secondary),
                  ),
                ),

                // Serial Number Badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.grey.shade300),
                  ),
                  child: Text(
                    item.hasNoSerialNumber ? 'Tanpa SN' : 'SN: ${item.serialNumber ?? "-"}',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),

            // Location & Meta Specs
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppTheme.background,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  _buildDetailRow('Ruangan / Lab', item.room?['name'] ?? '-'),
                  _buildDetailRow('Gedung', item.building?['name'] ?? '-'),
                  _buildDetailRow('Kategori', item.category?['name'] ?? '-'),
                  _buildDetailRow('Fungsi Barang', item.itemFunction?['name'] ?? '-'),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Audit Trail Info
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppTheme.background,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Rekam Jejak Audit:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                  const SizedBox(height: 6),
                  _buildDetailRow('Petugas Pencatat', item.creator?['name'] ?? 'Sistem'),
                  _buildDetailRow('Waktu Pendataan', _formatDate(item.createdAt)),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Notes
            if (item.notes != null && item.notes!.isNotEmpty) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.amber.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Catatan Tambahan:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.brown)),
                    const SizedBox(height: 4),
                    Text(item.notes!, style: const TextStyle(fontSize: 12, color: Colors.black87, fontStyle: FontStyle.italic)),
                  ],
                ),
              ),
              const SizedBox(height: 18),
            ],

            // Action Buttons (Edit / Delete) if permitted
            if (canModify) ...[
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.edit, size: 18),
                      label: const Text('Ubah Data'),
                      onPressed: () {
                        Navigator.pop(context);
                        onEdit();
                      },
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.delete_outline, size: 18),
                      label: const Text('Hapus'),
                      style: ElevatedButton.styleFrom(backgroundColor: AppTheme.error),
                      onPressed: () {
                        Navigator.pop(context);
                        onDelete();
                      },
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.right,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
            ),
          ),
        ],
      ),
    );
  }
}
