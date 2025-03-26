import 'package:flutter/material.dart';

class InicioScreen extends StatelessWidget {
  const InicioScreen({Key? key}) : super(key: key);

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
          // Tab bar
          Container(
            height: 40,
            decoration: const BoxDecoration(
              color: Colors.white,
              boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 2)],
            ),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    alignment: Alignment.center,
                    decoration: const BoxDecoration(
                      border: Border(bottom: BorderSide(color: Color(0xFF33B5E5), width: 3)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Image.asset('images/logo.png', height: 20, width: 20),
                        const SizedBox(width: 5),
                        const Text('Producto', style: TextStyle(color: Color(0xFF33B5E5))),
                      ],
                    ),
                  ),
                ),
                Expanded(
                  child: Container(
                    alignment: Alignment.center,
                    child: const Text('Recursos'),
                  ),
                ),
                Expanded(
                  child: Container(
                    alignment: Alignment.center,
                    child: const Text('Precios'),
                  ),
                ),
              ],
            ),
          ),
          // Main content
          Expanded(
            child: Column(
              children: [
                // Image and text section
                Expanded(
                  child: Row(
                    children: [
                      Expanded(
                        flex: 4,
                        child: Image.asset('images/forms.png', fit: BoxFit.cover),
                      ),
                      Expanded(
                        flex: 6,
                        child: Container(
                          color: const Color(0xFF33B5E5),
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: const [
                              Text(
                                'Todo tipo de formas de gestionar tus clientes',
                                style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                // Buttons section
                Container(
                  padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFE0E0E0),
                            foregroundColor: Colors.black,
                          ),
                          onPressed: () {},
                          child: const Text('Iniciar sesión'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFE0E0E0),
                            foregroundColor: Colors.black,
                          ),
                          onPressed: () {},
                          child: const Text('Crear nuevo usuario'),
                        ),
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
          ),
        ],
      ),
    );
  }
}