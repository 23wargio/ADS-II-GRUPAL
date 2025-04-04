import 'package:flutter/material.dart';


class MainMenuScreen extends StatelessWidget {
  const MainMenuScreen({Key? key}) : super(key: key);


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.lightBlue,
        title: Row(
          children: [
            const Text(
              'Menú Principal',
              style: TextStyle(
                color: Colors.black,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Spacer(),
            Image.asset(
              'assets/book_logo.png',
              height: 30,
            ),
          ],
        ),
        leading: IconButton(
          icon: const Icon(Icons.menu, color: Colors.black),
          onPressed: () {
            // Implementación del drawer o menú lateral
          },
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _buildMenuButton(
              context,
              'Información del Cliente',
              Icons.person,
                  () {
                // Navegar a la pantalla de información del cliente
              },
            ),
            const SizedBox(height: 20),
            _buildMenuButton(
              context,
              'Actividades y Tareas',
              Icons.list,
                  () {
                // Navegar a la pantalla de actividades y tareas
              },
            ),
          ],
        ),
      ),
    );
  }


  Widget _buildMenuButton(
      BuildContext context, String text, IconData icon, VoidCallback onPressed) {
    return SizedBox(
      height: 100,
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.blue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              color: Colors.white,
              size: 32,
            ),
            const SizedBox(height: 8),
            Text(
              text,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 16,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
