# Suptix Flutter Scanning App - Comprehensive Development Guide

## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture & Requirements](#architecture--requirements)
3. [Setup & Dependencies](#setup--dependencies)
4. [Authentication System](#authentication-system)
5. [QR Code Scanning Implementation](#qr-code-scanning-implementation)
6. [Ticket Validation System](#ticket-validation-system)
7. [RSVP Management](#rsvp-management)
8. [Fallback System (Manual Code Entry)](#fallback-system-manual-code-entry)
9. [Offline Support](#offline-support)
10. [API Integration](#api-integration)
11. [UI/UX Components](#uiux-components)
12. [Security Best Practices](#security-best-practices)
13. [Testing Strategy](#testing-strategy)
14. [Deployment Considerations](#deployment-considerations)

## Project Overview

The Suptix Flutter Scanning App is a mobile application designed for event organizers and staff to scan tickets and manage RSVPs at events. It integrates with the Laravel-based Suptix backend system and provides both QR code scanning and manual ticket/order code entry capabilities.

### Key Features
- **QR Code Scanning**: Primary method for ticket validation
- **Manual Code Entry**: Fallback for QR code scanning failures
- **RSVP Management**: Handle free event check-ins
- **Offline Support**: Continue scanning when connectivity is poor
- **Real-time Validation**: Instant ticket status updates
- **Organizer Dashboard**: Event statistics and attendee management

## Architecture & Requirements

### Flutter App Structure
```
lib/
├── main.dart
├── core/
│   ├── constants/
│   ├── errors/
│   ├── network/
│   ├── storage/
│   └── utils/
├── data/
│   ├── datasources/
│   ├── models/
│   ├── repositories/
│   └── services/
├── domain/
│   ├── entities/
│   ├── repositories/
│   └── usecases/
├── presentation/
│   ├── pages/
│   ├── widgets/
│   ├── providers/
│   └── theme/
└── injection_container.dart
```

### Minimum Requirements
- **Flutter**: 3.16+
- **Dart**: 3.2+
- **Android**: API level 21+ (Android 5.0)
- **iOS**: 12.0+
- **Permissions**: Camera, Internet, Storage

### Core Dependencies
```yaml
dependencies:
  flutter: ">=3.16.0"
  
  # State Management
  flutter_riverpod: ^2.4.9
  
  # HTTP & API
  dio: ^5.4.0
  retrofit: ^4.0.3
  
  # QR Code Scanning
  mobile_scanner: ^3.5.6
  
  # Local Storage
  hive: ^2.2.3
  hive_flutter: ^1.1.0
  flutter_secure_storage: ^9.0.0
  
  # UI Components
  flutter_screenutil: ^5.9.0
  google_fonts: ^6.1.0
  lottie: ^2.7.0
  
  # Utilities
  permission_handler: ^11.2.0
  connectivity_plus: ^5.0.2
  intl: ^0.19.0
  uuid: ^4.3.3
  
  # Camera & Image
  camera: ^0.10.5+7
  image: ^4.1.6

dev_dependencies:
  # Code Generation
  build_runner: ^2.4.7
  hive_generator: ^2.0.1
  retrofit_generator: ^8.0.6
  json_annotation: ^4.8.1
  json_serializable: ^6.7.1
  
  # Testing
  flutter_test:
    sdk: flutter
  mockito: ^5.4.4
  integration_test:
    sdk: flutter
```

## Setup & Dependencies

### 1. Create Flutter Project
```bash
flutter create suptix_scanner
cd suptix_scanner
```

### 2. Update pubspec.yaml
Add all dependencies listed above to your `pubspec.yaml` file.

### 3. Android Configuration
Update `android/app/src/main/AndroidManifest.xml`:
```xml
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
```

### 4. iOS Configuration
Update `ios/Runner/Info.plist`:
```xml
<key>NSCameraUsageDescription</key>
<string>This app needs camera access to scan QR codes for ticket validation</string>
<key>NSLocalNetworkUsageDescription</key>
<string>This app needs network access to validate tickets</string>
```

## Authentication System

### 1. Authentication Models

```dart
// lib/data/models/auth_models.dart
import 'package:json_annotation/json_annotation.dart';

part 'auth_models.g.dart';

@JsonSerializable()
class LoginRequest {
  final String email;
  final String password;
  
  LoginRequest({required this.email, required this.password});
  
  factory LoginRequest.fromJson(Map<String, dynamic> json) => 
      _$LoginRequestFromJson(json);
  Map<String, dynamic> toJson() => _$LoginRequestToJson(this);
}

@JsonSerializable()
class AuthResponse {
  final String token;
  final User user;
  
  AuthResponse({required this.token, required this.user});
  
  factory AuthResponse.fromJson(Map<String, dynamic> json) => 
      _$AuthResponseFromJson(json);
  Map<String, dynamic> toJson() => _$AuthResponseToJson(this);
}

@JsonSerializable()
class User {
  final int id;
  final String name;
  final String email;
  @JsonKey(name: 'email_verified_at')
  final String? emailVerifiedAt;
  final List<Organizer> organizers;
  
  User({
    required this.id,
    required this.name,
    required this.email,
    this.emailVerifiedAt,
    required this.organizers,
  });
  
  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
  Map<String, dynamic> toJson() => _$UserToJson(this);
}

@JsonSerializable()
class Organizer {
  final int id;
  final String name;
  final String? description;
  final String? logo;
  final String? website;
  
  Organizer({
    required this.id,
    required this.name,
    this.description,
    this.logo,
    this.website,
  });
  
  factory Organizer.fromJson(Map<String, dynamic> json) => 
      _$OrganizerFromJson(json);
  Map<String, dynamic> toJson() => _$OrganizerToJson(this);
}
```

### 2. Secure Storage Service

```dart
// lib/core/storage/secure_storage_service.dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorageService {
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
    ),
    iOptions: IOSOptions(
      accessibility: IOSAccessibility.first_unlock_this_device,
    ),
  );
  
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  
  static Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }
  
  static Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }
  
  static Future<void> saveUser(String userData) async {
    await _storage.write(key: _userKey, value: userData);
  }
  
  static Future<String?> getUser() async {
    return await _storage.read(key: _userKey);
  }
  
  static Future<void> clearAll() async {
    await _storage.deleteAll();
  }
}
```

### 3. Authentication Repository

```dart
// lib/data/repositories/auth_repository.dart
import 'package:dio/dio.dart';
import '../models/auth_models.dart';
import '../services/api_service.dart';
import '../../core/storage/secure_storage_service.dart';
import 'dart:convert';

class AuthRepository {
  final ApiService _apiService;
  
  AuthRepository(this._apiService);
  
  Future<AuthResponse> login(String email, String password) async {
    try {
      final response = await _apiService.login(
        LoginRequest(email: email, password: password)
      );
      
      // Save token and user data
      await SecureStorageService.saveToken(response.token);
      await SecureStorageService.saveUser(jsonEncode(response.user.toJson()));
      
      return response;
    } catch (e) {
      throw Exception('Login failed: ${e.toString()}');
    }
  }
  
  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      // Log error but don't throw - we want to clear local data regardless
      print('Logout API call failed: $e');
    } finally {
      await SecureStorageService.clearAll();
    }
  }
  
  Future<User?> getCurrentUser() async {
    try {
      final userJson = await SecureStorageService.getUser();
      if (userJson != null) {
        return User.fromJson(jsonDecode(userJson));
      }
      return null;
    } catch (e) {
      return null;
    }
  }
  
  Future<bool> isLoggedIn() async {
    final token = await SecureStorageService.getToken();
    return token != null && token.isNotEmpty;
  }
}
```

### 4. Authentication Provider

```dart
// lib/presentation/providers/auth_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/auth_models.dart';
import '../../data/repositories/auth_repository.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  // Inject your API service here
  return AuthRepository(ref.read(apiServiceProvider));
});

class AuthState {
  final bool isLoading;
  final bool isAuthenticated;
  final User? user;
  final String? error;
  
  AuthState({
    this.isLoading = false,
    this.isAuthenticated = false,
    this.user,
    this.error,
  });
  
  AuthState copyWith({
    bool? isLoading,
    bool? isAuthenticated,
    User? user,
    String? error,
  }) {
    return AuthState(
      isLoading: isLoading ?? this.isLoading,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      user: user ?? this.user,
      error: error,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepository _authRepository;
  
  AuthNotifier(this._authRepository) : super(AuthState()) {
    _checkAuthStatus();
  }
  
  Future<void> _checkAuthStatus() async {
    final isLoggedIn = await _authRepository.isLoggedIn();
    if (isLoggedIn) {
      final user = await _authRepository.getCurrentUser();
      state = state.copyWith(
        isAuthenticated: true,
        user: user,
      );
    }
  }
  
  Future<void> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final authResponse = await _authRepository.login(email, password);
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: true,
        user: authResponse.user,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }
  
  Future<void> logout() async {
    await _authRepository.logout();
    state = AuthState();
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(authRepositoryProvider));
});
```

## QR Code Scanning Implementation

### 1. QR Scanner Service

```dart
// lib/data/services/qr_scanner_service.dart
import 'package:mobile_scanner/mobile_scanner.dart';
import 'dart:convert';

class QRScannerService {
  static String? extractTicketNumber(String qrData) {
    try {
      // Try to parse as JSON first (enhanced format)
      final Map<String, dynamic> data = jsonDecode(qrData);
      return data['ticket_number'] as String?;
    } catch (e) {
      // If JSON parsing fails, treat as plain ticket number
      if (qrData.startsWith('TKT-') && qrData.length == 16) {
        return qrData;
      }
      return null;
    }
  }
  
  static Map<String, dynamic>? parseQRData(String qrData) {
    try {
      // Try to parse as JSON first
      return jsonDecode(qrData);
    } catch (e) {
      // If JSON parsing fails, create a simple structure
      final ticketNumber = extractTicketNumber(qrData);
      if (ticketNumber != null) {
        return {'ticket_number': ticketNumber};
      }
      return null;
    }
  }
  
  static bool isValidTicketNumber(String? ticketNumber) {
    if (ticketNumber == null) return false;
    return ticketNumber.startsWith('TKT-') && ticketNumber.length == 16;
  }
  
  static bool isValidOrderNumber(String? orderNumber) {
    if (orderNumber == null) return false;
    return orderNumber.startsWith('ORD-') && orderNumber.length == 14;
  }
  
  static bool isValidRSVPCode(String? guestCode) {
    if (guestCode == null) return false;
    return guestCode.length == 8 && RegExp(r'^[A-Z0-9]+$').hasMatch(guestCode);
  }
}
```

### 2. QR Scanner Widget

```dart
// lib/presentation/widgets/qr_scanner_widget.dart
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class QRScannerWidget extends ConsumerStatefulWidget {
  final Function(String) onCodeDetected;
  final String? overlayText;
  
  const QRScannerWidget({
    Key? key,
    required this.onCodeDetected,
    this.overlayText,
  }) : super(key: key);

  @override
  ConsumerState<QRScannerWidget> createState() => _QRScannerWidgetState();
}

class _QRScannerWidgetState extends ConsumerState<QRScannerWidget> {
  late MobileScannerController controller;
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    controller = MobileScannerController(
      detectionSpeed: DetectionSpeed.noDuplicates,
      facing: CameraFacing.back,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        MobileScanner(
          controller: controller,
          onDetect: (capture) {
            if (_isProcessing) return;
            
            final List<Barcode> barcodes = capture.barcodes;
            for (final barcode in barcodes) {
              if (barcode.rawValue != null) {
                _handleDetectedCode(barcode.rawValue!);
                break;
              }
            }
          },
        ),
        // Overlay
        Container(
          decoration: BoxDecoration(
            color: Colors.black.withOpacity(0.5),
          ),
          child: Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                border: Border.all(
                  color: Colors.white,
                  width: 2,
                ),
                borderRadius: BorderRadius.circular(12),
                color: Colors.transparent,
              ),
            ),
          ),
        ),
        // Instructions
        Positioned(
          bottom: 100,
          left: 0,
          right: 0,
          child: Container(
            padding: const EdgeInsets.all(16),
            child: Text(
              widget.overlayText ?? 'Align QR code within the frame',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.w500,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ),
        // Torch Toggle
        Positioned(
          top: 60,
          right: 16,
          child: ValueListenableBuilder(
            valueListenable: controller.torchState,
            builder: (context, state, child) {
              return IconButton(
                onPressed: () => controller.toggleTorch(),
                icon: Icon(
                  state == TorchState.off ? Icons.flash_off : Icons.flash_on,
                  color: Colors.white,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  void _handleDetectedCode(String code) {
    setState(() {
      _isProcessing = true;
    });
    
    // Provide haptic feedback
    HapticFeedback.lightImpact();
    
    widget.onCodeDetected(code);
    
    // Reset processing state after a short delay
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
      }
    });
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }
}
```

### 3. QR Scanner Page

```dart
// lib/presentation/pages/qr_scanner_page.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../widgets/qr_scanner_widget.dart';
import '../providers/scanning_provider.dart';
import '../../data/services/qr_scanner_service.dart';

class QRScannerPage extends ConsumerWidget {
  final int eventId;
  final int organizerId;
  
  const QRScannerPage({
    Key? key,
    required this.eventId,
    required this.organizerId,
  }) : super(key: key);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final scanningState = ref.watch(scanningProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan Ticket'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            onPressed: () => _showManualEntryDialog(context, ref),
            icon: const Icon(Icons.keyboard),
            tooltip: 'Manual Entry',
          ),
        ],
      ),
      body: Stack(
        children: [
          QRScannerWidget(
            onCodeDetected: (code) => _handleQRCode(context, ref, code),
            overlayText: 'Scan ticket QR code',
          ),
          if (scanningState.isLoading)
            Container(
              color: Colors.black54,
              child: const Center(
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              ),
            ),
        ],
      ),
    );
  }

  void _handleQRCode(BuildContext context, WidgetRef ref, String qrCode) {
    final qrData = QRScannerService.parseQRData(qrCode);
    
    if (qrData != null) {
      ref.read(scanningProvider.notifier).scanQRCode(
        qrData,
        eventId,
        organizerId,
      );
    } else {
      _showErrorSnackBar(context, 'Invalid QR code format');
    }
  }

  void _showManualEntryDialog(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (context) => ManualEntryDialog(
        onSubmit: (code) {
          ref.read(scanningProvider.notifier).validateManualCode(
            code,
            eventId,
            organizerId,
          );
        },
      ),
    );
  }

  void _showErrorSnackBar(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }
}
```

## Ticket Validation System

### 1. Ticket Models

```dart
// lib/data/models/ticket_models.dart
import 'package:json_annotation/json_annotation.dart';

part 'ticket_models.g.dart';

@JsonSerializable()
class TicketValidationRequest {
  @JsonKey(name: 'qr_data')
  final String? qrData;
  @JsonKey(name: 'ticket_number')
  final String? ticketNumber;
  @JsonKey(name: 'order_number')
  final String? orderNumber;
  
  TicketValidationRequest({
    this.qrData,
    this.ticketNumber,
    this.orderNumber,
  });
  
  factory TicketValidationRequest.fromJson(Map<String, dynamic> json) => 
      _$TicketValidationRequestFromJson(json);
  Map<String, dynamic> toJson() => _$TicketValidationRequestToJson(this);
}

@JsonSerializable()
class TicketValidationResponse {
  final bool success;
  final String message;
  final TicketData? data;
  final Map<String, dynamic>? errors;
  
  TicketValidationResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });
  
  factory TicketValidationResponse.fromJson(Map<String, dynamic> json) => 
      _$TicketValidationResponseFromJson(json);
  Map<String, dynamic> toJson() => _$TicketValidationResponseToJson(this);
}

@JsonSerializable()
class TicketData {
  final Ticket ticket;
  
  TicketData({required this.ticket});
  
  factory TicketData.fromJson(Map<String, dynamic> json) => 
      _$TicketDataFromJson(json);
  Map<String, dynamic> toJson() => _$TicketDataToJson(this);
}

@JsonSerializable()
class Ticket {
  final int id;
  @JsonKey(name: 'ticket_number')
  final String ticketNumber;
  @JsonKey(name: 'attendee_name')
  final String attendeeName;
  @JsonKey(name: 'attendee_email')
  final String? attendeeEmail;
  final String price;
  final String status;
  @JsonKey(name: 'used_at')
  final String? usedAt;
  final Event event;
  final Order? order;
  
  Ticket({
    required this.id,
    required this.ticketNumber,
    required this.attendeeName,
    this.attendeeEmail,
    required this.price,
    required this.status,
    this.usedAt,
    required this.event,
    this.order,
  });
  
  bool get isUsed => status == 'used' && usedAt != null;
  bool get isActive => status == 'active';
  bool get isCancelled => status == 'cancelled';
  
  factory Ticket.fromJson(Map<String, dynamic> json) => _$TicketFromJson(json);
  Map<String, dynamic> toJson() => _$TicketToJson(this);
}

@JsonSerializable()
class Event {
  final int id;
  final String title;
  @JsonKey(name: 'start_date')
  final String startDate;
  @JsonKey(name: 'start_time')
  final String? startTime;
  final String? description;
  final Venue? venue;
  final Organizer organizer;
  
  Event({
    required this.id,
    required this.title,
    required this.startDate,
    this.startTime,
    this.description,
    this.venue,
    required this.organizer,
  });
  
  factory Event.fromJson(Map<String, dynamic> json) => _$EventFromJson(json);
  Map<String, dynamic> toJson() => _$EventToJson(this);
}

@JsonSerializable()
class Venue {
  final int id;
  final String name;
  final String? address;
  final String? city;
  final String? country;
  
  Venue({
    required this.id,
    required this.name,
    this.address,
    this.city,
    this.country,
  });
  
  factory Venue.fromJson(Map<String, dynamic> json) => _$VenueFromJson(json);
  Map<String, dynamic> toJson() => _$VenueToJson(this);
}

@JsonSerializable()
class Order {
  final int id;
  @JsonKey(name: 'order_number')
  final String orderNumber;
  @JsonKey(name: 'buyer_email')
  final String buyerEmail;
  @JsonKey(name: 'total_amount')
  final String totalAmount;
  final String status;
  
  Order({
    required this.id,
    required this.orderNumber,
    required this.buyerEmail,
    required this.totalAmount,
    required this.status,
  });
  
  factory Order.fromJson(Map<String, dynamic> json) => _$OrderFromJson(json);
  Map<String, dynamic> toJson() => _$OrderToJson(this);
}
```

### 2. Ticket Repository

```dart
// lib/data/repositories/ticket_repository.dart
import 'package:dio/dio.dart';
import '../models/ticket_models.dart';
import '../services/api_service.dart';
import '../../core/storage/local_storage_service.dart';

class TicketRepository {
  final ApiService _apiService;
  final LocalStorageService _localStorage;
  
  TicketRepository(this._apiService, this._localStorage);
  
  Future<TicketValidationResponse> scanQRCode(
    Map<String, dynamic> qrData,
    int eventId,
    int organizerId,
  ) async {
    try {
      final response = await _apiService.scanQRCode(
        TicketValidationRequest(qrData: jsonEncode(qrData)),
      );
      
      // Cache the validation result for offline access
      if (response.success && response.data?.ticket != null) {
        await _cacheTicketValidation(response.data!.ticket);
      }
      
      return response;
    } catch (e) {
      throw Exception('QR scan failed: ${e.toString()}');
    }
  }
  
  Future<TicketValidationResponse> validateTicketNumber(
    String ticketNumber,
    int eventId,
    int organizerId,
  ) async {
    try {
      final response = await _apiService.verifyTicket(
        organizerId,
        eventId,
        TicketValidationRequest(ticketNumber: ticketNumber),
      );
      
      if (response.success && response.data?.ticket != null) {
        await _cacheTicketValidation(response.data!.ticket);
      }
      
      return response;
    } catch (e) {
      // Try offline validation as fallback
      final cachedTicket = await _getCachedTicket(ticketNumber);
      if (cachedTicket != null) {
        return TicketValidationResponse(
          success: true,
          message: 'Validated offline',
          data: TicketData(ticket: cachedTicket),
        );
      }
      
      throw Exception('Ticket validation failed: ${e.toString()}');
    }
  }
  
  Future<TicketValidationResponse> markTicketAsUsed(
    int ticketId,
    int organizerId,
  ) async {
    try {
      final response = await _apiService.markTicketUsed(ticketId);
      
      // Update local cache
      if (response.success && response.data?.ticket != null) {
        await _updateCachedTicket(response.data!.ticket);
      }
      
      return response;
    } catch (e) {
      // Queue for later sync if offline
      await _queueTicketAction(ticketId, 'mark_used');
      throw Exception('Failed to mark ticket as used: ${e.toString()}');
    }
  }
  
  Future<void> _cacheTicketValidation(Ticket ticket) async {
    await _localStorage.saveTicket(ticket);
  }
  
  Future<Ticket?> _getCachedTicket(String ticketNumber) async {
    return await _localStorage.getTicket(ticketNumber);
  }
  
  Future<void> _updateCachedTicket(Ticket ticket) async {
    await _localStorage.updateTicket(ticket);
  }
  
  Future<void> _queueTicketAction(int ticketId, String action) async {
    await _localStorage.queueAction({
      'ticket_id': ticketId,
      'action': action,
      'timestamp': DateTime.now().toIso8601String(),
    });
  }
  
  Future<void> syncPendingActions() async {
    final pendingActions = await _localStorage.getPendingActions();
    
    for (final action in pendingActions) {
      try {
        switch (action['action']) {
          case 'mark_used':
            await _apiService.markTicketUsed(action['ticket_id']);
            break;
        }
        
        await _localStorage.removeAction(action);
      } catch (e) {
        // Keep action in queue for next sync attempt
        print('Failed to sync action: $e');
      }
    }
  }
}
```

### 3. Scanning Provider

```dart
// lib/presentation/providers/scanning_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/ticket_models.dart';
import '../../data/repositories/ticket_repository.dart';

final ticketRepositoryProvider = Provider<TicketRepository>((ref) {
  return TicketRepository(
    ref.read(apiServiceProvider),
    ref.read(localStorageProvider),
  );
});

class ScanningState {
  final bool isLoading;
  final TicketValidationResponse? lastScanResult;
  final String? error;
  final List<Ticket> scannedTickets;
  
  ScanningState({
    this.isLoading = false,
    this.lastScanResult,
    this.error,
    this.scannedTickets = const [],
  });
  
  ScanningState copyWith({
    bool? isLoading,
    TicketValidationResponse? lastScanResult,
    String? error,
    List<Ticket>? scannedTickets,
  }) {
    return ScanningState(
      isLoading: isLoading ?? this.isLoading,
      lastScanResult: lastScanResult ?? this.lastScanResult,
      error: error,
      scannedTickets: scannedTickets ?? this.scannedTickets,
    );
  }
}

class ScanningNotifier extends StateNotifier<ScanningState> {
  final TicketRepository _ticketRepository;
  
  ScanningNotifier(this._ticketRepository) : super(ScanningState());
  
  Future<void> scanQRCode(
    Map<String, dynamic> qrData,
    int eventId,
    int organizerId,
  ) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final result = await _ticketRepository.scanQRCode(
        qrData,
        eventId,
        organizerId,
      );
      
      List<Ticket> updatedTickets = List.from(state.scannedTickets);
      if (result.success && result.data?.ticket != null) {
        updatedTickets.add(result.data!.ticket);
      }
      
      state = state.copyWith(
        isLoading: false,
        lastScanResult: result,
        scannedTickets: updatedTickets,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }
  
  Future<void> validateManualCode(
    String code,
    int eventId,
    int organizerId,
  ) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final result = await _ticketRepository.validateTicketNumber(
        code,
        eventId,
        organizerId,
      );
      
      List<Ticket> updatedTickets = List.from(state.scannedTickets);
      if (result.success && result.data?.ticket != null) {
        updatedTickets.add(result.data!.ticket);
      }
      
      state = state.copyWith(
        isLoading: false,
        lastScanResult: result,
        scannedTickets: updatedTickets,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }
  
  Future<void> markTicketAsUsed(int ticketId, int organizerId) async {
    try {
      await _ticketRepository.markTicketAsUsed(ticketId, organizerId);
      
      // Update the ticket in our local list
      final updatedTickets = state.scannedTickets.map((ticket) {
        if (ticket.id == ticketId) {
          return ticket.copyWith(
            status: 'used',
            usedAt: DateTime.now().toIso8601String(),
          );
        }
        return ticket;
      }).toList();
      
      state = state.copyWith(scannedTickets: updatedTickets);
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }
  
  void clearError() {
    state = state.copyWith(error: null);
  }
  
  void clearResults() {
    state = ScanningState();
  }
}

final scanningProvider = StateNotifierProvider<ScanningNotifier, ScanningState>((ref) {
  return ScanningNotifier(ref.read(ticketRepositoryProvider));
});
```

## RSVP Management

### 1. RSVP Models

```dart
// lib/data/models/rsvp_models.dart
import 'package:json_annotation/json_annotation.dart';

part 'rsvp_models.g.dart';

@JsonSerializable()
class RSVP {
  final int id;
  @JsonKey(name: 'event_id')
  final int eventId;
  @JsonKey(name: 'organizer_id')
  final int organizerId;
  @JsonKey(name: 'guest_name')
  final String guestName;
  final String email;
  final String? phone;
  @JsonKey(name: 'guest_code')
  final String guestCode;
  final String? notes;
  @JsonKey(name: 'referral_source')
  final String? referralSource;
  final String status; // attending, maybe, not_attending
  @JsonKey(name: 'checked_in_at')
  final String? checkedInAt;
  @JsonKey(name: 'created_at')
  final String createdAt;
  
  RSVP({
    required this.id,
    required this.eventId,
    required this.organizerId,
    required this.guestName,
    required this.email,
    this.phone,
    required this.guestCode,
    this.notes,
    this.referralSource,
    required this.status,
    this.checkedInAt,
    required this.createdAt,
  });
  
  bool get isCheckedIn => checkedInAt != null;
  bool get isAttending => status == 'attending';
  
  factory RSVP.fromJson(Map<String, dynamic> json) => _$RSVPFromJson(json);
  Map<String, dynamic> toJson() => _$RSVPToJson(this);
}

@JsonSerializable()
class RSVPValidationRequest {
  @JsonKey(name: 'guest_code')
  final String guestCode;
  
  RSVPValidationRequest({required this.guestCode});
  
  factory RSVPValidationRequest.fromJson(Map<String, dynamic> json) => 
      _$RSVPValidationRequestFromJson(json);
  Map<String, dynamic> toJson() => _$RSVPValidationRequestToJson(this);
}

@JsonSerializable()
class RSVPValidationResponse {
  final bool success;
  final String message;
  final RSVPData? data;
  final Map<String, dynamic>? errors;
  
  RSVPValidationResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });
  
  factory RSVPValidationResponse.fromJson(Map<String, dynamic> json) => 
      _$RSVPValidationResponseFromJson(json);
  Map<String, dynamic> toJson() => _$RSVPValidationResponseToJson(this);
}

@JsonSerializable()
class RSVPData {
  final RSVP rsvp;
  final Event event;
  
  RSVPData({
    required this.rsvp,
    required this.event,
  });
  
  factory RSVPData.fromJson(Map<String, dynamic> json) => 
      _$RSVPDataFromJson(json);
  Map<String, dynamic> toJson() => _$RSVPDataToJson(this);
}
```

### 2. RSVP Repository

```dart
// lib/data/repositories/rsvp_repository.dart
import '../models/rsvp_models.dart';
import '../services/api_service.dart';
import '../../core/storage/local_storage_service.dart';

class RSVPRepository {
  final ApiService _apiService;
  final LocalStorageService _localStorage;
  
  RSVPRepository(this._apiService, this._localStorage);
  
  Future<RSVPValidationResponse> validateGuestCode(
    String guestCode,
    int eventId,
    int organizerId,
  ) async {
    try {
      final response = await _apiService.validateRSVP(
        organizerId,
        eventId,
        RSVPValidationRequest(guestCode: guestCode),
      );
      
      if (response.success && response.data?.rsvp != null) {
        await _cacheRSVP(response.data!.rsvp);
      }
      
      return response;
    } catch (e) {
      // Try offline validation
      final cachedRSVP = await _getCachedRSVP(guestCode);
      if (cachedRSVP != null) {
        return RSVPValidationResponse(
          success: true,
          message: 'Validated offline',
          data: RSVPData(
            rsvp: cachedRSVP,
            event: await _getCachedEvent(eventId) ?? Event(
              id: eventId,
              title: 'Cached Event',
              startDate: DateTime.now().toIso8601String(),
              organizer: Organizer(id: organizerId, name: 'Cached Organizer'),
            ),
          ),
        );
      }
      
      throw Exception('RSVP validation failed: ${e.toString()}');
    }
  }
  
  Future<RSVPValidationResponse> checkInGuest(
    int rsvpId,
    int organizerId,
  ) async {
    try {
      final response = await _apiService.checkInRSVP(rsvpId);
      
      if (response.success && response.data?.rsvp != null) {
        await _updateCachedRSVP(response.data!.rsvp);
      }
      
      return response;
    } catch (e) {
      // Queue for later sync
      await _queueRSVPAction(rsvpId, 'check_in');
      throw Exception('Failed to check in guest: ${e.toString()}');
    }
  }
  
  Future<List<RSVP>> getEventRSVPs(int eventId, int organizerId) async {
    try {
      final response = await _apiService.getEventRSVPs(organizerId, eventId);
      
      // Cache all RSVPs
      for (final rsvp in response.data) {
        await _cacheRSVP(rsvp);
      }
      
      return response.data;
    } catch (e) {
      // Return cached data if available
      return await _getCachedEventRSVPs(eventId);
    }
  }
  
  Future<void> _cacheRSVP(RSVP rsvp) async {
    await _localStorage.saveRSVP(rsvp);
  }
  
  Future<RSVP?> _getCachedRSVP(String guestCode) async {
    return await _localStorage.getRSVP(guestCode);
  }
  
  Future<void> _updateCachedRSVP(RSVP rsvp) async {
    await _localStorage.updateRSVP(rsvp);
  }
  
  Future<Event?> _getCachedEvent(int eventId) async {
    return await _localStorage.getEvent(eventId);
  }
  
  Future<List<RSVP>> _getCachedEventRSVPs(int eventId) async {
    return await _localStorage.getEventRSVPs(eventId);
  }
  
  Future<void> _queueRSVPAction(int rsvpId, String action) async {
    await _localStorage.queueAction({
      'rsvp_id': rsvpId,
      'action': action,
      'timestamp': DateTime.now().toIso8601String(),
    });
  }
}
```

### 3. RSVP Scanner Widget

```dart
// lib/presentation/widgets/rsvp_scanner_widget.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/rsvp_provider.dart';
import '../../data/services/qr_scanner_service.dart';
import 'qr_scanner_widget.dart';

class RSVPScannerWidget extends ConsumerWidget {
  final int eventId;
  final int organizerId;
  
  const RSVPScannerWidget({
    Key? key,
    required this.eventId,
    required this.organizerId,
  }) : super(key: key);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final rsvpState = ref.watch(rsvpProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('RSVP Check-in'),
        actions: [
          IconButton(
            onPressed: () => _showManualGuestCodeDialog(context, ref),
            icon: const Icon(Icons.keyboard),
            tooltip: 'Manual Guest Code',
          ),
        ],
      ),
      body: Stack(
        children: [
          QRScannerWidget(
            onCodeDetected: (code) => _handleScannedCode(context, ref, code),
            overlayText: 'Scan guest QR code or enter guest code manually',
          ),
          if (rsvpState.isLoading)
            Container(
              color: Colors.black54,
              child: const Center(
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              ),
            ),
        ],
      ),
    );
  }

  void _handleScannedCode(BuildContext context, WidgetRef ref, String code) {
    // Check if it's a valid guest code
    if (QRScannerService.isValidRSVPCode(code)) {
      ref.read(rsvpProvider.notifier).validateGuestCode(
        code,
        eventId,
        organizerId,
      );
    } else {
      _showErrorSnackBar(context, 'Invalid guest code format');
    }
  }

  void _showManualGuestCodeDialog(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (context) => GuestCodeEntryDialog(
        onSubmit: (guestCode) {
          ref.read(rsvpProvider.notifier).validateGuestCode(
            guestCode,
            eventId,
            organizerId,
          );
        },
      ),
    );
  }

  void _showErrorSnackBar(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }
}

class GuestCodeEntryDialog extends StatefulWidget {
  final Function(String) onSubmit;
  
  const GuestCodeEntryDialog({
    Key? key,
    required this.onSubmit,
  }) : super(key: key);

  @override
  State<GuestCodeEntryDialog> createState() => _GuestCodeEntryDialogState();
}

class _GuestCodeEntryDialogState extends State<GuestCodeEntryDialog> {
  final _controller = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Enter Guest Code'),
      content: Form(
        key: _formKey,
        child: TextFormField(
          controller: _controller,
          decoration: const InputDecoration(
            labelText: 'Guest Code',
            hintText: 'e.g., ABC12345',
            border: OutlineInputBorder(),
          ),
          textCapitalization: TextCapitalization.characters,
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Please enter a guest code';
            }
            if (!QRScannerService.isValidRSVPCode(value)) {
              return 'Invalid guest code format';
            }
            return null;
          },
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () {
            if (_formKey.currentState!.validate()) {
              widget.onSubmit(_controller.text.toUpperCase());
              Navigator.of(context).pop();
            }
          },
          child: const Text('Validate'),
        ),
      ],
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}
```

## Fallback System (Manual Code Entry)

### 1. Manual Entry Dialog

```dart
// lib/presentation/widgets/manual_entry_dialog.dart
import 'package:flutter/material.dart';
import '../../data/services/qr_scanner_service.dart';

class ManualEntryDialog extends StatefulWidget {
  final Function(String) onSubmit;
  
  const ManualEntryDialog({
    Key? key,
    required this.onSubmit,
  }) : super(key: key);

  @override
  State<ManualEntryDialog> createState() => _ManualEntryDialogState();
}

class _ManualEntryDialogState extends State<ManualEntryDialog> {
  final _controller = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  String _selectedType = 'ticket';

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Manual Entry'),
      content: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Type selector
            SegmentedButton<String>(
              segments: const [
                ButtonSegment<String>(
                  value: 'ticket',
                  label: Text('Ticket'),
                  icon: Icon(Icons.confirmation_number),
                ),
                ButtonSegment<String>(
                  value: 'order',
                  label: Text('Order'),
                  icon: Icon(Icons.receipt),
                ),
                ButtonSegment<String>(
                  value: 'rsvp',
                  label: Text('RSVP'),
                  icon: Icon(Icons.person),
                ),
              ],
              selected: {_selectedType},
              onSelectionChanged: (selection) {
                setState(() {
                  _selectedType = selection.first;
                  _controller.clear();
                });
              },
            ),
            const SizedBox(height: 16),
            
            // Code input
            TextFormField(
              controller: _controller,
              decoration: InputDecoration(
                labelText: _getLabelText(),
                hintText: _getHintText(),
                border: const OutlineInputBorder(),
                prefixIcon: Icon(_getIcon()),
              ),
              textCapitalization: TextCapitalization.characters,
              validator: _validateInput,
              autofocus: true,
            ),
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () {
            if (_formKey.currentState!.validate()) {
              widget.onSubmit(_controller.text.toUpperCase());
              Navigator.of(context).pop();
            }
          },
          child: const Text('Validate'),
        ),
      ],
    );
  }

  String _getLabelText() {
    switch (_selectedType) {
      case 'ticket':
        return 'Ticket Number';
      case 'order':
        return 'Order Number';
      case 'rsvp':
        return 'Guest Code';
      default:
        return 'Code';
    }
  }

  String _getHintText() {
    switch (_selectedType) {
      case 'ticket':
        return 'TKT-ABC123DEF456';
      case 'order':
        return 'ORD-XYZ789012';
      case 'rsvp':
        return 'ABC12345';
      default:
        return '';
    }
  }

  IconData _getIcon() {
    switch (_selectedType) {
      case 'ticket':
        return Icons.confirmation_number;
      case 'order':
        return Icons.receipt;
      case 'rsvp':
        return Icons.person;
      default:
        return Icons.qr_code;
    }
  }

  String? _validateInput(String? value) {
    if (value == null || value.isEmpty) {
      return 'Please enter a ${_selectedType} code';
    }

    switch (_selectedType) {
      case 'ticket':
        if (!QRScannerService.isValidTicketNumber(value)) {
          return 'Invalid ticket number format (TKT-XXXXXXXXXXXX)';
        }
        break;
      case 'order':
        if (!QRScannerService.isValidOrderNumber(value)) {
          return 'Invalid order number format (ORD-XXXXXXXXXX)';
        }
        break;
      case 'rsvp':
        if (!QRScannerService.isValidRSVPCode(value)) {
          return 'Invalid guest code format (8 characters)';
        }
        break;
    }

    return null;
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}
```

### 2. Code Validation Service

```dart
// lib/data/services/code_validation_service.dart
import '../models/ticket_models.dart';
import '../models/rsvp_models.dart';
import '../repositories/ticket_repository.dart';
import '../repositories/rsvp_repository.dart';

enum CodeType { ticket, order, rsvp, unknown }

class CodeValidationService {
  final TicketRepository _ticketRepository;
  final RSVPRepository _rsvpRepository;
  
  CodeValidationService(this._ticketRepository, this._rsvpRepository);
  
  CodeType determineCodeType(String code) {
    if (code.startsWith('TKT-') && code.length == 16) {
      return CodeType.ticket;
    } else if (code.startsWith('ORD-') && code.length == 14) {
      return CodeType.order;
    } else if (code.length == 8 && RegExp(r'^[A-Z0-9]+$').hasMatch(code)) {
      return CodeType.rsvp;
    }
    return CodeType.unknown;
  }
  
  Future<ValidationResult> validateCode(
    String code,
    int eventId,
    int organizerId,
  ) async {
    final codeType = determineCodeType(code);
    
    try {
      switch (codeType) {
        case CodeType.ticket:
          final result = await _ticketRepository.validateTicketNumber(
            code,
            eventId,
            organizerId,
          );
          return ValidationResult.fromTicketResponse(result);
          
        case CodeType.order:
          // For order numbers, we need to find tickets associated with the order
          final result = await _ticketRepository.validateOrderNumber(
            code,
            eventId,
            organizerId,
          );
          return ValidationResult.fromTicketResponse(result);
          
        case CodeType.rsvp:
          final result = await _rsvpRepository.validateGuestCode(
            code,
            eventId,
            organizerId,
          );
          return ValidationResult.fromRSVPResponse(result);
          
        case CodeType.unknown:
          throw Exception('Invalid code format');
      }
    } catch (e) {
      return ValidationResult.error('Validation failed: ${e.toString()}');
    }
  }
}

class ValidationResult {
  final bool success;
  final String message;
  final CodeType type;
  final Ticket? ticket;
  final RSVP? rsvp;
  final String? error;
  
  ValidationResult({
    required this.success,
    required this.message,
    required this.type,
    this.ticket,
    this.rsvp,
    this.error,
  });
  
  factory ValidationResult.fromTicketResponse(TicketValidationResponse response) {
    return ValidationResult(
      success: response.success,
      message: response.message,
      type: CodeType.ticket,
      ticket: response.data?.ticket,
    );
  }
  
  factory ValidationResult.fromRSVPResponse(RSVPValidationResponse response) {
    return ValidationResult(
      success: response.success,
      message: response.message,
      type: CodeType.rsvp,
      rsvp: response.data?.rsvp,
    );
  }
  
  factory ValidationResult.error(String errorMessage) {
    return ValidationResult(
      success: false,
      message: errorMessage,
      type: CodeType.unknown,
      error: errorMessage,
    );
  }
}
```

## Offline Support

### 1. Local Storage Service

```dart
// lib/core/storage/local_storage_service.dart
import 'package:hive/hive.dart';
import '../../data/models/ticket_models.dart';
import '../../data/models/rsvp_models.dart';

class LocalStorageService {
  static const String _ticketsBox = 'tickets';
  static const String _rsvpsBox = 'rsvps';
  static const String _eventsBox = 'events';
  static const String _actionsBox = 'pending_actions';
  
  // Tickets
  Future<void> saveTicket(Ticket ticket) async {
    final box = await Hive.openBox<Map>(_ticketsBox);
    await box.put(ticket.ticketNumber, ticket.toJson());
  }
  
  Future<Ticket?> getTicket(String ticketNumber) async {
    final box = await Hive.openBox<Map>(_ticketsBox);
    final data = box.get(ticketNumber);
    if (data != null) {
      return Ticket.fromJson(Map<String, dynamic>.from(data));
    }
    return null;
  }
  
  Future<void> updateTicket(Ticket ticket) async {
    await saveTicket(ticket);
  }
  
  Future<List<Ticket>> getEventTickets(int eventId) async {
    final box = await Hive.openBox<Map>(_ticketsBox);
    final tickets = <Ticket>[];
    
    for (final data in box.values) {
      final ticket = Ticket.fromJson(Map<String, dynamic>.from(data));
      if (ticket.event.id == eventId) {
        tickets.add(ticket);
      }
    }
    
    return tickets;
  }
  
  // RSVPs
  Future<void> saveRSVP(RSVP rsvp) async {
    final box = await Hive.openBox<Map>(_rsvpsBox);
    await box.put(rsvp.guestCode, rsvp.toJson());
  }
  
  Future<RSVP?> getRSVP(String guestCode) async {
    final box = await Hive.openBox<Map>(_rsvpsBox);
    final data = box.get(guestCode);
    if (data != null) {
      return RSVP.fromJson(Map<String, dynamic>.from(data));
    }
    return null;
  }
  
  Future<void> updateRSVP(RSVP rsvp) async {
    await saveRSVP(rsvp);
  }
  
  Future<List<RSVP>> getEventRSVPs(int eventId) async {
    final box = await Hive.openBox<Map>(_rsvpsBox);
    final rsvps = <RSVP>[];
    
    for (final data in box.values) {
      final rsvp = RSVP.fromJson(Map<String, dynamic>.from(data));
      if (rsvp.eventId == eventId) {
        rsvps.add(rsvp);
      }
    }
    
    return rsvps;
  }
  
  // Events
  Future<void> saveEvent(Event event) async {
    final box = await Hive.openBox<Map>(_eventsBox);
    await box.put(event.id, event.toJson());
  }
  
  Future<Event?> getEvent(int eventId) async {
    final box = await Hive.openBox<Map>(_eventsBox);
    final data = box.get(eventId);
    if (data != null) {
      return Event.fromJson(Map<String, dynamic>.from(data));
    }
    return null;
  }
  
  // Pending Actions
  Future<void> queueAction(Map<String, dynamic> action) async {
    final box = await Hive.openBox<Map>(_actionsBox);
    final id = DateTime.now().millisecondsSinceEpoch.toString();
    await box.put(id, action);
  }
  
  Future<List<Map<String, dynamic>>> getPendingActions() async {
    final box = await Hive.openBox<Map>(_actionsBox);
    return box.values.map((e) => Map<String, dynamic>.from(e)).toList();
  }
  
  Future<void> removeAction(Map<String, dynamic> action) async {
    final box = await Hive.openBox<Map>(_actionsBox);
    final keys = box.keys.where((key) {
      final stored = box.get(key);
      return stored != null && _actionsMatch(stored, action);
    }).toList();
    
    for (final key in keys) {
      await box.delete(key);
    }
  }
  
  bool _actionsMatch(Map stored, Map<String, dynamic> action) {
    return stored['timestamp'] == action['timestamp'] &&
           stored['action'] == action['action'];
  }
  
  // Cleanup
  Future<void> clearCache() async {
    await Hive.deleteBoxFromDisk(_ticketsBox);
    await Hive.deleteBoxFromDisk(_rsvpsBox);
    await Hive.deleteBoxFromDisk(_eventsBox);
    await Hive.deleteBoxFromDisk(_actionsBox);
  }
}
```

### 2. Connectivity Service

```dart
// lib/core/network/connectivity_service.dart
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class ConnectivityService {
  final Connectivity _connectivity = Connectivity();
  
  Stream<ConnectivityResult> get connectivityStream {
    return _connectivity.onConnectivityChanged;
  }
  
  Future<bool> isConnected() async {
    final result = await _connectivity.checkConnectivity();
    return result != ConnectivityResult.none;
  }
  
  Future<bool> hasInternetConnection() async {
    try {
      final result = await InternetAddress.lookup('google.com');
      return result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } catch (e) {
      return false;
    }
  }
}

final connectivityServiceProvider = Provider<ConnectivityService>((ref) {
  return ConnectivityService();
});

final connectivityProvider = StreamProvider<ConnectivityResult>((ref) {
  return ref.read(connectivityServiceProvider).connectivityStream;
});
```

### 3. Sync Service

```dart
// lib/data/services/sync_service.dart
import '../repositories/ticket_repository.dart';
import '../repositories/rsvp_repository.dart';
import '../../core/storage/local_storage_service.dart';
import '../../core/network/connectivity_service.dart';

class SyncService {
  final TicketRepository _ticketRepository;
  final RSVPRepository _rsvpRepository;
  final LocalStorageService _localStorage;
  final ConnectivityService _connectivityService;
  
  SyncService(
    this._ticketRepository,
    this._rsvpRepository,
    this._localStorage,
    this._connectivityService,
  );
  
  Future<void> syncPendingActions() async {
    if (!await _connectivityService.isConnected()) {
      return; // Skip sync if no connection
    }
    
    try {
      await _ticketRepository.syncPendingActions();
      await _rsvpRepository.syncPendingActions();
    } catch (e) {
      print('Sync failed: $e');
      // Don't throw - we'll try again later
    }
  }
  
  Future<void> preloadEventData(int eventId, int organizerId) async {
    if (!await _connectivityService.isConnected()) {
      return;
    }
    
    try {
      // Preload tickets
      final tickets = await _ticketRepository.getEventTickets(eventId, organizerId);
      for (final ticket in tickets) {
        await _localStorage.saveTicket(ticket);
      }
      
      // Preload RSVPs
      final rsvps = await _rsvpRepository.getEventRSVPs(eventId, organizerId);
      for (final rsvp in rsvps) {
        await _localStorage.saveRSVP(rsvp);
      }
    } catch (e) {
      print('Preload failed: $e');
    }
  }
  
  Future<void> startAutoSync() async {
    // Set up periodic sync every 5 minutes
    Timer.periodic(const Duration(minutes: 5), (timer) async {
      if (await _connectivityService.isConnected()) {
        await syncPendingActions();
      }
    });
  }
}
```

## API Integration

### 1. API Service

```dart
// lib/data/services/api_service.dart
import 'package:dio/dio.dart';
import 'package:retrofit/retrofit.dart';
import '../models/auth_models.dart';
import '../models/ticket_models.dart';
import '../models/rsvp_models.dart';

part 'api_service.g.dart';

@RestApi()
abstract class ApiService {
  factory ApiService(Dio dio, {String baseUrl}) = _ApiService;
  
  // Authentication
  @POST('/api/login')
  Future<AuthResponse> login(@Body() LoginRequest request);
  
  @POST('/api/logout')
  Future<void> logout();
  
  @GET('/api/user')
  Future<User> getCurrentUser();
  
  // Ticket Operations
  @POST('/api/tickets/scan-qr')
  Future<TicketValidationResponse> scanQRCode(@Body() TicketValidationRequest request);
  
  @POST('/api/organizers/{organizerId}/events/{eventId}/tickets/verify')
  Future<TicketValidationResponse> verifyTicket(
    @Path('organizerId') int organizerId,
    @Path('eventId') int eventId,
    @Body() TicketValidationRequest request,
  );
  
  @PATCH('/api/tickets/{ticketId}/mark-used')
  Future<TicketValidationResponse> markTicketUsed(@Path('ticketId') int ticketId);
  
  @GET('/api/organizers/{organizerId}/events/{eventId}/tickets')
  Future<TicketListResponse> getEventTickets(
    @Path('organizerId') int organizerId,
    @Path('eventId') int eventId,
  );
  
  // RSVP Operations
  @POST('/api/organizers/{organizerId}/events/{eventId}/rsvps/validate')
  Future<RSVPValidationResponse> validateRSVP(
    @Path('organizerId') int organizerId,
    @Path('eventId') int eventId,
    @Body() RSVPValidationRequest request,
  );
  
  @PATCH('/api/rsvps/{rsvpId}/check-in')
  Future<RSVPValidationResponse> checkInRSVP(@Path('rsvpId') int rsvpId);
  
  @GET('/api/organizers/{organizerId}/events/{eventId}/rsvps')
  Future<RSVPListResponse> getEventRSVPs(
    @Path('organizerId') int organizerId,
    @Path('eventId') int eventId,
  );
  
  // Events
  @GET('/api/organizers/{organizerId}/events')
  Future<EventListResponse> getOrganizerEvents(@Path('organizerId') int organizerId);
  
  @GET('/api/organizers/{organizerId}/events/{eventId}')
  Future<EventResponse> getEvent(
    @Path('organizerId') int organizerId,
    @Path('eventId') int eventId,
  );
}

@JsonSerializable()
class TicketListResponse {
  final bool success;
  final List<Ticket> data;
  
  TicketListResponse({required this.success, required this.data});
  
  factory TicketListResponse.fromJson(Map<String, dynamic> json) => 
      _$TicketListResponseFromJson(json);
}

@JsonSerializable()
class RSVPListResponse {
  final bool success;
  final List<RSVP> data;
  
  RSVPListResponse({required this.success, required this.data});
  
  factory RSVPListResponse.fromJson(Map<String, dynamic> json) => 
      _$RSVPListResponseFromJson(json);
}

@JsonSerializable()
class EventListResponse {
  final bool success;
  final List<Event> data;
  
  EventListResponse({required this.success, required this.data});
  
  factory EventListResponse.fromJson(Map<String, dynamic> json) => 
      _$EventListResponseFromJson(json);
}

@JsonSerializable()
class EventResponse {
  final bool success;
  final Event data;
  
  EventResponse({required this.success, required this.data});
  
  factory EventResponse.fromJson(Map<String, dynamic> json) => 
      _$EventResponseFromJson(json);
}
```

### 2. DIO Configuration

```dart
// lib/core/network/dio_client.dart
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../storage/secure_storage_service.dart';

class DioClient {
  late Dio _dio;
  
  DioClient() {
    _dio = Dio();
    _setupInterceptors();
  }
  
  Dio get dio => _dio;
  
  void _setupInterceptors() {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Add auth token
          final token = await SecureStorageService.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          
          // Add content type
          options.headers['Content-Type'] = 'application/json';
          options.headers['Accept'] = 'application/json';
          
          handler.next(options);
        },
        onError: (error, handler) {
          // Handle common errors
          if (error.response?.statusCode == 401) {
            // Token expired, logout user
            SecureStorageService.clearAll();
          }
          
          handler.next(error);
        },
      ),
    );
    
    // Add logging in debug mode
    if (kDebugMode) {
      _dio.interceptors.add(LogInterceptor(
        requestBody: true,
        responseBody: true,
        requestHeader: false,
        responseHeader: false,
      ));
    }
  }
}

final dioProvider = Provider<Dio>((ref) {
  return DioClient().dio;
});

final apiServiceProvider = Provider<ApiService>((ref) {
  const baseUrl = 'https://your-suptix-api.com'; // Replace with actual URL
  return ApiService(ref.read(dioProvider), baseUrl: baseUrl);
});
```

## UI/UX Components

### 1. Ticket Result Card

```dart
// lib/presentation/widgets/ticket_result_card.dart
import 'package:flutter/material.dart';
import '../../data/models/ticket_models.dart';
import 'package:intl/intl.dart';

class TicketResultCard extends StatelessWidget {
  final Ticket ticket;
  final VoidCallback? onMarkAsUsed;
  final bool isLoading;
  
  const TicketResultCard({
    Key? key,
    required this.ticket,
    this.onMarkAsUsed,
    this.isLoading = false,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.all(16),
      elevation: 4,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Badge
            Row(
              children: [
                _buildStatusChip(),
                const Spacer(),
                Text(
                  ticket.ticketNumber,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    fontFamily: 'monospace',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            
            // Attendee Info
            Text(
              ticket.attendeeName,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            if (ticket.attendeeEmail != null) ...[
              const SizedBox(height: 4),
              Text(
                ticket.attendeeEmail!,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Colors.grey[600],
                ),
              ),
            ],
            const SizedBox(height: 12),
            
            // Event Info
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey[100],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    ticket.event.title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.calendar_today, size: 16),
                      const SizedBox(width: 4),
                      Text(
                        _formatDate(ticket.event.startDate),
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                      if (ticket.event.startTime != null) ...[
                        const SizedBox(width: 8),
                        const Icon(Icons.access_time, size: 16),
                        const SizedBox(width: 4),
                        Text(
                          ticket.event.startTime!,
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ],
                  ),
                  if (ticket.event.venue != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.location_on, size: 16),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            ticket.event.venue!.name,
                            style: Theme.of(context).textTheme.bodySmall,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 12),
            
            // Price
            Row(
              children: [
                Text(
                  'Price: ',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                Text(
                  '\$${ticket.price}',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: Colors.green[700],
                  ),
                ),
              ],
            ),
            
            // Used timestamp
            if (ticket.isUsed && ticket.usedAt != null) ...[
              const SizedBox(height: 8),
              Text(
                'Used at: ${_formatDateTime(ticket.usedAt!)}',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Colors.grey[600],
                ),
              ),
            ],
            
            // Action Button
            if (!ticket.isUsed && !ticket.isCancelled) ...[
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: isLoading ? null : onMarkAsUsed,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                  ),
                  child: isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Text('Check In'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatusChip() {
    Color color;
    String label;
    IconData icon;

    switch (ticket.status) {
      case 'active':
        color = Colors.green;
        label = 'Valid';
        icon = Icons.check_circle;
        break;
      case 'used':
        color = Colors.blue;
        label = 'Used';
        icon = Icons.done_all;
        break;
      case 'cancelled':
        color = Colors.red;
        label = 'Cancelled';
        icon = Icons.cancel;
        break;
      case 'refunded':
        color = Colors.orange;
        label = 'Refunded';
        icon = Icons.undo;
        break;
      default:
        color = Colors.grey;
        label = 'Unknown';
        icon = Icons.help;
    }

    return Chip(
      avatar: Icon(icon, size: 16, color: Colors.white),
      label: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      ),
      backgroundColor: color,
    );
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMM d, y').format(date);
    } catch (e) {
      return dateString;
    }
  }

  String _formatDateTime(String dateTimeString) {
    try {
      final dateTime = DateTime.parse(dateTimeString);
      return DateFormat('MMM d, y h:mm a').format(dateTime);
    } catch (e) {
      return dateTimeString;
    }
  }
}
```

### 2. RSVP Result Card

```dart
// lib/presentation/widgets/rsvp_result_card.dart
import 'package:flutter/material.dart';
import '../../data/models/rsvp_models.dart';
import 'package:intl/intl.dart';

class RSVPResultCard extends StatelessWidget {
  final RSVP rsvp;
  final Event event;
  final VoidCallback? onCheckIn;
  final bool isLoading;
  
  const RSVPResultCard({
    Key? key,
    required this.rsvp,
    required this.event,
    this.onCheckIn,
    this.isLoading = false,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.all(16),
      elevation: 4,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status and Guest Code
            Row(
              children: [
                _buildStatusChip(),
                const Spacer(),
                Text(
                  rsvp.guestCode,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    fontFamily: 'monospace',
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            
            // Guest Info
            Text(
              rsvp.guestName,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              rsvp.email,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[600],
              ),
            ),
            if (rsvp.phone != null) ...[
              const SizedBox(height: 2),
              Text(
                rsvp.phone!,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Colors.grey[600],
                ),
              ),
            ],
            const SizedBox(height: 12),
            
            // Event Info
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey[100],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    event.title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.calendar_today, size: 16),
                      const SizedBox(width: 4),
                      Text(
                        _formatDate(event.startDate),
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                      if (event.startTime != null) ...[
                        const SizedBox(width: 8),
                        const Icon(Icons.access_time, size: 16),
                        const SizedBox(width: 4),
                        Text(
                          event.startTime!,
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ],
                  ),
                  if (event.venue != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.location_on, size: 16),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            event.venue!.name,
                            style: Theme.of(context).textTheme.bodySmall,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            
            // Notes
            if (rsvp.notes != null && rsvp.notes!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.yellow[50],
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: Colors.yellow[200]!),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.note, size: 16, color: Colors.yellow[800]),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        rsvp.notes!,
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ),
                  ],
                ),
              ),
            ],
            
            // Check-in timestamp
            if (rsvp.isCheckedIn && rsvp.checkedInAt != null) ...[
              const SizedBox(height: 8),
              Text(
                'Checked in at: ${_formatDateTime(rsvp.checkedInAt!)}',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Colors.grey[600],
                ),
              ),
            ],
            
            // Action Button
            if (!rsvp.isCheckedIn && rsvp.isAttending) ...[
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: isLoading ? null : onCheckIn,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue,
                    foregroundColor: Colors.white,
                  ),
                  child: isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Text('Check In'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatusChip() {
    Color color;
    String label;
    IconData icon;

    if (rsvp.isCheckedIn) {
      color = Colors.blue;
      label = 'Checked In';
      icon = Icons.done_all;
    } else if (rsvp.isAttending) {
      color = Colors.green;
      label = 'Attending';
      icon = Icons.check_circle;
    } else {
      color = Colors.orange;
      label = rsvp.status.toUpperCase();
      icon = Icons.help;
    }

    return Chip(
      avatar: Icon(icon, size: 16, color: Colors.white),
      label: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      ),
      backgroundColor: color,
    );
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMM d, y').format(date);
    } catch (e) {
      return dateString;
    }
  }

  String _formatDateTime(String dateTimeString) {
    try {
      final dateTime = DateTime.parse(dateTimeString);
      return DateFormat('MMM d, y h:mm a').format(dateTime);
    } catch (e) {
      return dateTimeString;
    }
  }
}
```

## Security Best Practices

### 1. Token Management

```dart
// lib/core/security/token_manager.dart
import 'dart:convert';
import '../storage/secure_storage_service.dart';

class TokenManager {
  static Future<bool> isTokenValid() async {
    final token = await SecureStorageService.getToken();
    if (token == null) return false;
    
    try {
      // Decode JWT token to check expiration
      final parts = token.split('.');
      if (parts.length != 3) return false;
      
      final payload = _decodeBase64(parts[1]);
      final Map<String, dynamic> data = jsonDecode(payload);
      
      final exp = data['exp'] as int?;
      if (exp == null) return false;
      
      final expiryDate = DateTime.fromMillisecondsSinceEpoch(exp * 1000);
      return DateTime.now().isBefore(expiryDate);
    } catch (e) {
      return false;
    }
  }
  
  static String _decodeBase64(String str) {
    String output = str.replaceAll('-', '+').replaceAll('_', '/');
    switch (output.length % 4) {
      case 0:
        break;
      case 2:
        output += '==';
        break;
      case 3:
        output += '=';
        break;
      default:
        throw Exception('Illegal base64url string!');
    }
    return utf8.decode(base64Url.decode(output));
  }
  
  static Future<void> refreshTokenIfNeeded() async {
    if (!await isTokenValid()) {
      // Token is expired, force logout
      await SecureStorageService.clearAll();
      // Navigate to login screen
    }
  }
}
```

### 2. Certificate Pinning

```dart
// lib/core/network/certificate_pinning.dart
import 'package:dio/dio.dart';
import 'dart:io';

class CertificatePinning {
  static void setupCertificatePinning(Dio dio, List<String> allowedSHA256Fingerprints) {
    (dio.httpClientAdapter as IOHttpClientAdapter).onHttpClientCreate = (client) {
      client.badCertificateCallback = (cert, host, port) {
        // Get the SHA256 fingerprint of the certificate
        final fingerprint = _getSHA256Fingerprint(cert);
        return allowedSHA256Fingerprints.contains(fingerprint);
      };
      return client;
    };
  }
  
  static String _getSHA256Fingerprint(X509Certificate cert) {
    // Implementation to get SHA256 fingerprint
    // This is a simplified version - use a proper crypto library
    return cert.sha1.replaceAll(':', '').toLowerCase();
  }
}
```

### 3. Input Validation

```dart
// lib/core/security/input_validator.dart
class InputValidator {
  static bool isValidTicketNumber(String input) {
    return RegExp(r'^TKT-[A-Z0-9]{12}$').hasMatch(input);
  }
  
  static bool isValidOrderNumber(String input) {
    return RegExp(r'^ORD-[A-Z0-9]{10}$').hasMatch(input);
  }
  
  static bool isValidGuestCode(String input) {
    return RegExp(r'^[A-Z0-9]{8}$').hasMatch(input);
  }
  
  static bool isValidEmail(String input) {
    return RegExp(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$').hasMatch(input);
  }
  
  static String sanitizeInput(String input) {
    // Remove potentially dangerous characters
    return input.replaceAll(RegExp(r'[<>"\';\\]'), '');
  }
  
  static bool isWithinReasonableLength(String input, int maxLength) {
    return input.length <= maxLength;
  }
}
```

## Testing Strategy

### 1. Unit Tests

```dart
// test/unit/qr_scanner_service_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:suptix_scanner/data/services/qr_scanner_service.dart';

void main() {
  group('QRScannerService', () {
    test('should extract ticket number from simple QR code', () {
      const qrData = 'TKT-ABC123DEF456';
      
      final ticketNumber = QRScannerService.extractTicketNumber(qrData);
      
      expect(ticketNumber, equals('TKT-ABC123DEF456'));
    });
    
    test('should extract ticket number from JSON QR code', () {
      const qrData = '{"ticket_number":"TKT-ABC123DEF456","attendee_name":"John Doe"}';
      
      final ticketNumber = QRScannerService.extractTicketNumber(qrData);
      
      expect(ticketNumber, equals('TKT-ABC123DEF456'));
    });
    
    test('should return null for invalid QR data', () {
      const qrData = 'invalid-data';
      
      final ticketNumber = QRScannerService.extractTicketNumber(qrData);
      
      expect(ticketNumber, isNull);
    });
    
    test('should validate ticket number format', () {
      expect(QRScannerService.isValidTicketNumber('TKT-ABC123DEF456'), isTrue);
      expect(QRScannerService.isValidTicketNumber('TKT-TOOLONG12345'), isFalse);
      expect(QRScannerService.isValidTicketNumber('INVALID'), isFalse);
    });
  });
}
```

### 2. Widget Tests

```dart
// test/widget/qr_scanner_widget_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:suptix_scanner/presentation/widgets/qr_scanner_widget.dart';

void main() {
  group('QRScannerWidget', () {
    testWidgets('should display scanner overlay', (WidgetTester tester) async {
      bool codeDetected = false;
      
      await tester.pumpWidget(
        ProviderScope(
          child: MaterialApp(
            home: QRScannerWidget(
              onCodeDetected: (code) {
                codeDetected = true;
              },
            ),
          ),
        ),
      );
      
      expect(find.text('Align QR code within the frame'), findsOneWidget);
      expect(find.byIcon(Icons.flash_off), findsOneWidget);
    });
  });
}
```

### 3. Integration Tests

```dart
// integration_test/app_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:suptix_scanner/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();
  
  group('end-to-end test', () {
    testWidgets('login and scan ticket', (WidgetTester tester) async {
      app.main();
      await tester.pumpAndSettle();
      
      // Test login flow
      expect(find.text('Login'), findsOneWidget);
      
      await tester.enterText(find.byKey(const Key('email_field')), 'test@example.com');
      await tester.enterText(find.byKey(const Key('password_field')), 'password');
      await tester.tap(find.byKey(const Key('login_button')));
      await tester.pumpAndSettle();
      
      // Test navigation to scanner
      expect(find.text('Events'), findsOneWidget);
      await tester.tap(find.text('Test Event'));
      await tester.pumpAndSettle();
      
      await tester.tap(find.byIcon(Icons.qr_code_scanner));
      await tester.pumpAndSettle();
      
      expect(find.text('Scan Ticket'), findsOneWidget);
    });
  });
}
```

## Deployment Considerations

### 1. Build Configuration

```yaml
# android/app/build.gradle
android {
    compileSdkVersion 34
    
    defaultConfig {
        applicationId "com.suptix.scanner"
        minSdkVersion 21
        targetSdkVersion 34
        versionCode flutterVersionCode.toInteger()
        versionName flutterVersionName
    }
    
    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
        }
    }
    
    flavorDimensions "environment"
    productFlavors {
        development {
            dimension "environment"
            applicationIdSuffix ".dev"
            versionNameSuffix "-dev"
        }
        staging {
            dimension "environment"
            applicationIdSuffix ".staging"
            versionNameSuffix "-staging"
        }
        production {
            dimension "environment"
        }
    }
}
```

### 2. Environment Configuration

```dart
// lib/core/config/app_config.dart
enum Environment { development, staging, production }

class AppConfig {
  static Environment _environment = Environment.development;
  
  static String get baseUrl {
    switch (_environment) {
      case Environment.development:
        return 'https://dev-api.suptix.com';
      case Environment.staging:
        return 'https://staging-api.suptix.com';
      case Environment.production:
        return 'https://api.suptix.com';
    }
  }
  
  static bool get isDebug {
    return _environment != Environment.production;
  }
  
  static void setEnvironment(Environment env) {
    _environment = env;
  }
}
```

### 3. CI/CD Pipeline

```yaml
# .github/workflows/build.yml
name: Build and Test

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - uses: subosito/flutter-action@v2
      with:
        flutter-version: '3.16.0'
    - run: flutter pub get
    - run: flutter analyze
    - run: flutter test
    
  build_android:
    needs: test
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - uses: subosito/flutter-action@v2
      with:
        flutter-version: '3.16.0'
    - run: flutter pub get
    - run: flutter build apk --release
    - uses: actions/upload-artifact@v3
      with:
        name: release-apk
        path: build/app/outputs/flutter-apk/app-release.apk
```

## Conclusion

This comprehensive guide provides a complete roadmap for developing a Flutter scanning app for the Suptix event management system. The app includes:

- **Robust QR code scanning** with camera integration
- **Manual code entry fallback** for ticket numbers, order numbers, and RSVP codes
- **Offline support** with local caching and sync capabilities
- **Secure authentication** with token management
- **RSVP management** for free events
- **Real-time validation** with the Suptix backend
- **Comprehensive error handling** and user feedback
- **Production-ready architecture** with proper separation of concerns

The implementation follows Flutter best practices and provides a solid foundation that can be extended with additional features as needed.