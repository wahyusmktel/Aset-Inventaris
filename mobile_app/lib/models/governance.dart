class GovernanceStatus {
  final bool hasSignedPact;
  final bool hasFinalized;
  final bool isCutoffPassed;
  final Map<String, dynamic>? activePeriod;
  final Map<String, dynamic>? activeSchool;

  GovernanceStatus({
    required this.hasSignedPact,
    required this.hasFinalized,
    required this.isCutoffPassed,
    this.activePeriod,
    this.activeSchool,
  });

  factory GovernanceStatus.fromJson(Map<String, dynamic> json) {
    return GovernanceStatus(
      hasSignedPact: json['has_signed_pact'] == true,
      hasFinalized: json['has_finalized'] == true,
      isCutoffPassed: json['is_cutoff_passed'] == true,
      activePeriod: json['active_period'] is Map<String, dynamic> ? json['active_period'] : null,
      activeSchool: json['active_school'] is Map<String, dynamic> ? json['active_school'] : null,
    );
  }
}
