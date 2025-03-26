// lib/screens/crear_usuario_screen.dart
import 'package:flutter/material.dart';

class CrearUsuarioScreen extends StatelessWidget {
  const CrearUsuarioScreen({Key? key}) : super(key: key);

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
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Text('Crear Usuario', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(width: 8),
                Image.asset('assets/logo.png', height: 24),
              ],
            ),
          ),
          // Form fields
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                const Text('Todos los campos son importantes:'),
                const SizedBox(height: 16),
                const Text('Nombre Completo'),
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEEEEE),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  margin: const EdgeInsets.only(bottom: 16),
                  child: const TextField(
                    decoration: InputDecoration(
                      hintText: 'Ingrese Nombre Completo',
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      border: InputBorder.none,
                    ),
                  ),
                ),
                const Text('Correo Electrónico'),
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEEEEE),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  margin: const EdgeInsets.only(bottom: 16),
                  child: const TextField(
                    decoration: InputDecoration(
                      hintText: 'Ingrese Correo Electrónico',
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      border: InputBorder.none,
                    ),
                  ),
                ),
                const Text('Número de Teléfono'),
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEEEEE),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  margin: const EdgeInsets.only(bottom: 16),
                  child: const TextField(
                    decoration: InputDecoration(
                      hintText: 'Ingrese Número de Teléfono',
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      border: InputBorder.none,
                    ),
                  ),
                ),
                const Text('Contraseña'),
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEEEEE),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  margin: const EdgeInsets.only(bottom: 16),
                  child: TextField(
                    obscureText: true,
                    decoration: InputDecoration(
                      hintText: 'Ingrese Contraseña',
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      border: InputBorder.none,
                      suffixIcon: IconButton(
                        icon: const Icon(Icons.visibility),
                        onPressed: () {},
                      ),
                    ),
                  ),
                ),
                const Text('Confirmar Contraseña'),
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEEEEE),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  margin: const EdgeInsets.only(bottom: 16),
                  child: TextField(
                    obscureText: true,
                    decoration: InputDecoration(
                      hintText: 'Reingrese Contraseña',
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      border: InputBorder.none,
                      suffixIcon: IconButton(
                        icon: const Icon(Icons.visibility),
                        onPressed: () {},
                      ),
                    ),
                  ),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF33B5E5),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  onPressed: () {},
                  child: const Text('Crear Usuario'),
                ),
                const SizedBox(height: 12),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE0E0E0),
                    foregroundColor: Colors.black,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  onPressed: () {},
                  child: const Text('Cancelar'),
                ),
              ],
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