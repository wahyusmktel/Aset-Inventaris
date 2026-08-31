import 'package:flutter_test/flutter_test.dart';
import 'package:aset_inventaris_mobile/main.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const AsetInventarisApp());
    expect(find.byType(AsetInventarisApp), findsOneWidget);
    await tester.pumpAndSettle(const Duration(seconds: 2));
  });
}
