import 'package:flutter/material.dart';
import '../../config/api_constants.dart';
import '../../config/theme.dart';
import '../../services/auth_service.dart';
import '../auth/login_screen.dart';
import '../governance/finalization_screen.dart';
import '../governance/pact_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _currentBaseUrl = ApiConstants.defaultBaseUrl;

  @override
  void initState() {
    super.initState();
    _loadBaseUrl();
  }

  Future<void> _loadBaseUrl() async {
    final url = await ApiConstants.getBaseUrl();
    if (mounted) setState(() => _currentBaseUrl = url);
  }

  void _showServerSettingsDialog() {
    final urlController = TextEditingController(text: _currentBaseUrl);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Ubah Endpoint Server API', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Gunakan alamat Cloudflare Tunnel atau IP lokal jaringan Anda:',
              style: TextStyle(fontSize: 12, color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: urlController,
              decoration: const InputDecoration(
                hintText: 'https://aset.smktelkom-lpg.id/api',
                prefixIcon: Icon(Icons.link, size: 18),
              ),
              style: const TextStyle(fontSize: 13),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () async {
              await ApiConstants.resetBaseUrl();
              Navigator.pop(ctx);
              _loadBaseUrl();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Alamat server direset ke default.')),
              );
            },
            child: const Text('Reset Default'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (urlController.text.trim().isNotEmpty) {
                await ApiConstants.setBaseUrl(urlController.text.trim());
                Navigator.pop(ctx);
                _loadBaseUrl();
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Server API diubah ke: ${urlController.text.trim()}')),
                );
              }
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  Future<void> _handleLogout() async {
    final isConfirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Keluar dari Akun?'),
        content: const Text('Apakah Anda yakin ingin keluar dari sesi aplikasi mobile ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.error),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Keluar'),
          ),
        ],
      ),
    );

    if (isConfirmed == true) {
      await AuthService.clearSession();
      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final gov = AuthService.currentGovernance;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Profil Pengguna'),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // User Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 32,
                        backgroundColor: AppTheme.primary,
                        child: Text(
                          (user?.name.isNotEmpty == true) ? user!.name[0].toUpperCase() : 'U',
                          style: const TextStyle(fontSize: 26, color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user?.name ?? 'Nama Pengguna',
                              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              user?.email ?? 'email@smktelkom.sch.id',
                              style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                            ),
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                              decoration: BoxDecoration(
                                color: user?.isSuperAdmin == true ? AppTheme.primaryContainer : AppTheme.secondaryContainer,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                user?.isSuperAdmin == true ? '👑 Super Administrator' : '👷 Anggota Tim Pendata',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: user?.isSuperAdmin == true ? AppTheme.primary : AppTheme.secondary,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Account Meta Specs
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Informasi Kepegawaian:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 10),
                      _buildProfileRow('NIP / ID Staf', user?.nip ?? '-'),
                      _buildProfileRow('Nomor Telepon', user?.phone ?? '-'),
                      _buildProfileRow('Lembaga Sekolah', gov?.activeSchool?['name'] ?? 'SMK Telkom Lampung'),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Governance Actions
              Card(
                child: Column(
                  children: [
                    ListTile(
                      leading: const Icon(Icons.verified_user, color: AppTheme.primary),
                      title: const Text('Pakta Integritas', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                      subtitle: Text(
                        gov?.hasSignedPact == true ? 'Sudah ditandatangani' : 'Belum ditandatangani',
                        style: TextStyle(fontSize: 12, color: gov?.hasSignedPact == true ? AppTheme.success : AppTheme.error),
                      ),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const PactScreen()));
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: const Icon(Icons.description, color: AppTheme.secondary),
                      title: const Text('Finalisasi & Berita Acara', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                      subtitle: Text(
                        gov?.hasFinalized == true ? 'Data telah difinalisasi' : 'Belum difinalisasi',
                        style: TextStyle(fontSize: 12, color: gov?.hasFinalized == true ? AppTheme.success : AppTheme.textSecondary),
                      ),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const FinalizationScreen()));
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Server Settings Card
              Card(
                child: ListTile(
                  leading: const Icon(Icons.dns, color: AppTheme.primary),
                  title: const Text('Alamat Server Backend', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                  subtitle: Text(_currentBaseUrl, style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                  trailing: const Icon(Icons.edit, size: 20),
                  onTap: _showServerSettingsDialog,
                ),
              ),
              const SizedBox(height: 24),

              // Logout Button
              SizedBox(
                height: 48,
                child: OutlinedButton.icon(
                  icon: const Icon(Icons.logout, color: AppTheme.error),
                  label: const Text('Keluar dari Akun', style: TextStyle(color: AppTheme.error, fontWeight: FontWeight.bold)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFFFCA5A5)),
                    backgroundColor: const Color(0xFFFEF2F2),
                  ),
                  onPressed: _handleLogout,
                ),
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfileRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
          Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
        ],
      ),
    );
  }
}
