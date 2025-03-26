// lib/screens/menu_principal_screen.dart
import 'package:flutter/material.dart';

class MenuPrincipalScreen extends StatelessWidget {
  const MenuPrincipalScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Status bar simulator
          Container(
            padding: const EdgeInsets.only(top: 40),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('9:41'),
                Row(
                  children: const [
                    Icon(Icons.signal_cellular_4_bar, size: 16),
                    Icon(Icons.wifi, size: 16),
                    Icon(Icons.battery_full, size: 16),
                  ],
                ),
              ],
            ),
          ),
          // Header
          Container(
            padding: const EdgeInsets.all(12),
            color: const Color(0xFF33B5E5),
            child: Row(
              children: [
                Image.asset('images/logo.png', height: 20),
                const SizedBox(width: 8),
                const Text('Menú Principal', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
              ],
            ),
          ),
          // Main content - Menu items
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Información del Cliente card
                  Card(
                    elevation: 2,
                    child: Container(
                      height: 120,
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.person, size: 40, color: Color(0xFF33B5E5)),
                          SizedBox(height: 8),
                          Text('Información del Cliente', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Actividades y Tareas card
                  Card(
                    elevation: 2,
                    child: Container(
                      height: 120,
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.assignment, size: 40, color: Color(0xFF33B5E5)),
                          SizedBox(height: 8),
                          Text('Actividades y Tareas', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          // Bottom navigation icons
          Container(
            height: 50,
            color: Colors.white,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: const [
                Icon(Icons.home),
                Icon(Icons.search),
                Icon(Icons.settings),
              ],
            ),
          ),
        ],
      ),
    );
  }
}