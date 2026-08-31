import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class FinalizationScreen extends StatefulWidget {
  const FinalizationScreen({super.key});

  @override
  State<FinalizationScreen> createState() => _FinalizationScreenState();
}

class _FinalizationScreenState extends State<FinalizationScreen> {
  final _notesController = TextEditingController();
  bool _isLoading = false;
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  Future<void> _loadStats() async {
    final stats = await ApiService.getDashboardStats();
    if (mounted) {
      setState(() => _stats = stats);
    }
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _handleFinalize() async {
    final isConfirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Finalisasi Pendataan Aset?'),
        content: const Text(
          'Setelah difinalisasi, Anda tidak dapat menambah atau mengubah data barang lagi. Berita Acara resmi akan diterbitkan dengan 3 tanda tangan (Petugas, Kaur IT, & Kepala Sekolah).',
          style: TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Finalisasi'),
          ),
        ],
      ),
    );

    if (isConfirmed == true) {
      setState(() => _isLoading = true);
      final res = await ApiService.finalizeData(_notesController.text.trim());
      if (!mounted) return;
      setState(() => _isLoading = false);

      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(backgroundColor: AppTheme.success, content: Text('Data inventaris berhasil difinalisasi!')),
        );
        Navigator.pop(context, true);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(backgroundColor: AppTheme.error, content: Text(res['message'] ?? 'Gagal memfinalisasi data.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final gov = AuthService.currentGovernance;
    final isAlreadyFinalized = gov?.hasFinalized == true;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Finalisasi & Berita Acara'),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Banner
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primary, Color(0xFF991B1B)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.description, color: Colors.white, size: 28),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isAlreadyFinalized ? 'STATUS: TELAH DIFINALISASI' : 'FINALISASI PENDATAAN',
                                style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold),
                              ),
                              const Text(
                                'Berita Acara Serah Terima Aset',
                                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Summary Stats Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Rekapitulasi Fisik Aset:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                      const SizedBox(height: 12),
                      _buildRow('Total Jenis Barang:', '${_stats['total_items'] ?? 0} Item'),
                      _buildRow('Total Kuantitas Fisik:', '${_stats['total_quantity'] ?? 0} Unit'),
                      _buildRow('Kondisi Baik:', '${_stats['good_condition'] ?? 0} Unit (${_stats['good_percent'] ?? 100}%)'),
                      _buildRow('Kondisi Rusak:', '${_stats['damaged_condition'] ?? 0} Unit'),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Signatures Info
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Pengesahan Dokumen (3 Tanda Tangan):', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 10),
                      _buildSignee('1. Petugas Pendata', AuthService.currentUser?.name ?? 'Staf Surveyor', true),
                      _buildSignee('2. Kaur IT / PIC Aset', gov?.activeSchool?['kaur_it_name'] ?? 'Kaur IT Sekolah', true),
                      _buildSignee('3. Kepala Sekolah', gov?.activeSchool?['principal_name'] ?? 'Kepala Sekolah', true),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Notes Input
              if (!isAlreadyFinalized) ...[
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Catatan Tambahan Berita Acara:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                        const SizedBox(height: 6),
                        TextField(
                          controller: _notesController,
                          maxLines: 3,
                          decoration: const InputDecoration(
                            hintText: 'Keterangan verifikasi fisik atau catatan audit lapangan...',
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                SizedBox(
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _handleFinalize,
                    child: _isLoading
                        ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                        : const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.lock, size: 20),
                              SizedBox(width: 8),
                              Text('Kunci & Finalisasi Data', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                            ],
                          ),
                  ),
                ),
              ] else ...[
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppTheme.successContainer,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppTheme.success),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.check_circle, color: AppTheme.success, size: 28),
                      SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'Pendataan inventaris Anda telah resmi difinalisasi dan tersimpan pada sistem.',
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF065F46)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRow(String label, String val) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
          Text(val, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
        ],
      ),
    );
  }

  Widget _buildSignee(String title, String name, bool isValid) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          const Icon(Icons.check_circle, size: 16, color: AppTheme.success),
          const SizedBox(width: 8),
          Text('$title: ', style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
          Expanded(child: Text(name, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold))),
        ],
      ),
    );
  }
}
