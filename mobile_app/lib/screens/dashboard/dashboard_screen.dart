import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import '../inventory/item_form_screen.dart';

class DashboardScreen extends StatefulWidget {
  final Function(int) onNavigateTab;

  const DashboardScreen({super.key, required this.onNavigateTab});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _isLoading = true;
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    setState(() => _isLoading = true);
    final stats = await ApiService.getDashboardStats();
    await ApiService.refreshProfile();

    if (mounted) {
      setState(() {
        _stats = stats;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final gov = AuthService.currentGovernance;
    final school = gov?.activeSchool;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 32,
              height: 32,
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Image.asset('assets/images/logo.png', fit: BoxFit.contain),
            ),
            const SizedBox(width: 10),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('SIM-ASET', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                Text('SMK Telkom Lampung', style: TextStyle(fontSize: 11, color: AppTheme.primary, fontWeight: FontWeight.bold)),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDashboardData,
            tooltip: 'Segarkan Data',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadDashboardData,
        color: AppTheme.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Hero School & Welcome Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primary, Color(0xFF991B1B)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(24),
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
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 6,
                            height: 6,
                            decoration: const BoxDecoration(
                              color: Color(0xFF34D399),
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            school?['name'] ?? 'SMK Telkom Lampung',
                            style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Halo, ${user?.name ?? "Staf"}!',
                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      user?.isSuperAdmin == true
                          ? 'Masuk sebagai Super Administrator. Akses penuh manajemen aset sekolah.'
                          : 'Masuk sebagai Tim Pendataan Inventaris. Siap melakukan pencatatan di lapangan.',
                      style: const TextStyle(color: Colors.white70, fontSize: 12, height: 1.4),
                    ),
                    const SizedBox(height: 16),

                    // Fast Action Buttons on Banner
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            icon: const Icon(Icons.add_box, size: 18),
                            label: const Text('Catat Aset'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.white,
                              foregroundColor: AppTheme.primary,
                              padding: const EdgeInsets.symmetric(vertical: 10),
                            ),
                            onPressed: () async {
                              final created = await Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const ItemFormScreen()),
                              );
                              if (created == true) _loadDashboardData();
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: OutlinedButton.icon(
                            icon: const Icon(Icons.inventory_2, size: 18, color: Colors.white),
                            label: const Text('Lihat Barang', style: TextStyle(color: Colors.white)),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Colors.white70),
                              padding: const EdgeInsets.symmetric(vertical: 10),
                            ),
                            onPressed: () => widget.onNavigateTab(1),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // 2. Active Period & Cutoff Status
              if (gov?.activePeriod != null) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: gov!.isCutoffPassed ? const Color(0xFFFEF2F2) : const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: gov.isCutoffPassed ? const Color(0xFFFCA5A5) : const Color(0xFF86EFAC),
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        gov.isCutoffPassed ? Icons.lock_clock : Icons.timer,
                        color: gov.isCutoffPassed ? AppTheme.error : AppTheme.success,
                        size: 24,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              gov.isCutoffPassed ? 'Batas Waktu Cut-off Berakhir' : 'Periode Pendataan Aktif',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: gov.isCutoffPassed ? const Color(0xFF7F1D1D) : const Color(0xFF14532D),
                              ),
                            ),
                            Text(
                              gov.activePeriod?['name'] ?? 'Periode Berjalan',
                              style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
              ],

              // 3. KPI Statistics Grid
              const Text(
                'Ringkasan Metrik Aset',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
              ),
              const SizedBox(height: 12),

              if (_isLoading)
                const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()))
              else
                GridView.count(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  children: [
                    _buildStatCard(
                      title: 'Jenis Barang',
                      value: '${_stats['total_items'] ?? 0}',
                      subtitle: 'Item Terdata',
                      icon: Icons.inventory_2,
                      iconColor: AppTheme.primary,
                      bgColor: AppTheme.primaryContainer,
                    ),
                    _buildStatCard(
                      title: 'Total Fisik',
                      value: '${_stats['total_quantity'] ?? 0}',
                      subtitle: 'Unit Aset',
                      icon: Icons.widgets,
                      iconColor: AppTheme.secondary,
                      bgColor: AppTheme.secondaryContainer,
                    ),
                    _buildStatCard(
                      title: 'Kondisi Baik',
                      value: '${_stats['good_condition'] ?? 0}',
                      subtitle: '${_stats['good_percent'] ?? 100}% Laik Pakai',
                      icon: Icons.check_circle,
                      iconColor: AppTheme.success,
                      bgColor: AppTheme.successContainer,
                    ),
                    _buildStatCard(
                      title: 'Kondisi Rusak',
                      value: '${_stats['damaged_condition'] ?? 0}',
                      subtitle: 'Perlu Perbaikan',
                      icon: Icons.warning_amber_rounded,
                      iconColor: AppTheme.error,
                      bgColor: const Color(0xFFFEE2E2),
                    ),
                  ],
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textSecondary)),
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(8)),
                  child: Icon(icon, size: 18, color: iconColor),
                ),
              ],
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(value, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: AppTheme.textPrimary)),
                Text(subtitle, style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: iconColor)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
