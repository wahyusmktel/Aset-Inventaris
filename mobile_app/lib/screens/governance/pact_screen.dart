import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import '../main_navigation_screen.dart';

class PactScreen extends StatefulWidget {
  const PactScreen({super.key});

  @override
  State<PactScreen> createState() => _PactScreenState();
}

class _PactScreenState extends State<PactScreen> {
  bool _isAgreed = false;
  bool _isLoading = false;

  Future<void> _handleSignPact() async {
    if (!_isAgreed) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Anda harus mencentang persetujuan pakta integritas terlebih dahulu.')),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    final res = await ApiService.signIntegrityPact();

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: AppTheme.success,
          content: Text('Pakta Integritas berhasil ditandatangani secara digital!'),
        ),
      );

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppTheme.error,
          content: Text(res['message'] ?? 'Gagal menandatangani pakta integritas.'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Pakta Integritas Digital'),
        automaticallyImplyLeading: false,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header Card
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primary, Color(0xFFB91C1C)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primary.withOpacity(0.25),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.verified_user, color: Colors.white, size: 24),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'DOKUMEN LEGAL & INTEGRITAS',
                                style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1),
                              ),
                              Text(
                                'Tim Pendataan Aset Sekolah',
                                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Petugas: ${user?.name ?? "Staf"} ${user?.nip != null ? "(NIP: ${user!.nip})" : ""}',
                      style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Clauses Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Pernyataan Komitmen & Tanggung Jawab:',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                      ),
                      const SizedBox(height: 14),
                      _buildClauseItem('1', 'Saya menyatakan akan melakukan pendataan fisik barang inventaris SMK Telkom Lampung secara jujur, akurat, dan dapat dipertanggungjawabkan.'),
                      _buildClauseItem('2', 'Saya bersedia memeriksa kesesuaian nomor seri (SN), kuantitas, dan kondisi fisik barang (Baik/Rusak) secara langsung di lokasi ruangan.'),
                      _buildClauseItem('3', 'Saya tidak akan melakukan manipulasi data, duplikasi fiktif, maupun kelalaian yang merugikan aset sekolah dan Yayasan Pendidikan Telkom.'),
                      _buildClauseItem('4', 'Persetujuan digital ini memiliki kekuatan hukum yang sah dan tersimpan dengan enkripsi SHA-256 pada database server.'),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 18),

              // Agreement Checkbox
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: _isAgreed ? AppTheme.primary : AppTheme.outline),
                ),
                child: CheckboxListTile(
                  value: _isAgreed,
                  onChanged: (val) => setState(() => _isAgreed = val ?? false),
                  activeColor: AppTheme.primary,
                  contentPadding: EdgeInsets.zero,
                  controlAffinity: ListTileControlAffinity.leading,
                  title: const Text(
                    'Saya membaca, memahami, dan menyetujui seluruh ketentuan Pakta Integritas di atas.',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Action Button
              SizedBox(
                height: 50,
                child: ElevatedButton(
                  onPressed: (_isAgreed && !_isLoading) ? _handleSignPact : null,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _isAgreed ? AppTheme.primary : Colors.grey.shade400,
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                        )
                      : const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.draw, size: 20),
                            SizedBox(width: 8),
                            Text('Tandatangani & Lanjutkan', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                          ],
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildClauseItem(String number, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 22,
            height: 22,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppTheme.primaryContainer,
              shape: BoxShape.circle,
            ),
            child: Text(
              number,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.primary),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}
