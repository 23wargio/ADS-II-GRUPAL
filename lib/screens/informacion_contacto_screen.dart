import 'package:flutter/material.dart';

class InformacionContactoScreen extends StatelessWidget {
  const InformacionContactoScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Barra de estado simulada (extraer en un widget reutilizable)
          _buildStatusBar(),
          // AppBar personalizado
          _buildAppBar(context),
          // Contenido principal
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Título de gestión de clientes
                const Padding(
                  padding: EdgeInsets.all(16),
                  child: Text(
                    'Gestión de Clientes',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ),
                // Filtros (Todos, Favoritos, Recientes)
                _buildFilters(),
                // Lista de contactos
                Expanded(
                  child: ListView(
                    children: [
                      _buildContactItem(
                        name: 'Juan Pérez',
                        email: 'juan.perez@example.com',
                        avatarColor: Colors.orange,
                      ),
                      _buildContactItem(
                        name: 'María Gómez',
                        email: 'maria.gomez@example.com',
                        avatarColor: Colors.blue,
                      ),
                      _buildContactItem(
                        name: 'Carlos López',
                        email: 'carlos.lopez@example.com',
                        avatarColor: Colors.green,
                      ),
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

  // Barra de estado simulada
  Widget _buildStatusBar() {
    return Container(
      padding: const EdgeInsets.only(top: 40, left: 16, right: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Text('9:41'),
          Row(
            children: const [
              Icon(Icons.signal_cellular_4_bar, size: 16),
              SizedBox(width: 8),
              Icon(Icons.wifi, size: 16),
              SizedBox(width: 8),
              Icon(Icons.battery_full, size: 16),
            ],
          ),
        ],
      ),
    );
  }

  // AppBar personalizado
  Widget _buildAppBar(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      color: const Color(0xFF33B5E5),
      child: Row(
        children: [
          Image.asset('assets/logo.png', height: 20),
          const SizedBox(width: 8),
          const Text(
            'Información del Contacto',
            style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  // Filtros (Todos, Favoritos, Recientes)
  Widget _buildFilters() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFEEEEEE),
        border: Border.all(color: Colors.grey.shade300),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(Icons.people_alt, color: Colors.blue.shade300),
          const SizedBox(width: 8),
          const Text('Todos'),
          const Spacer(),
          Icon(Icons.star_border, color: Colors.blue.shade300),
          const SizedBox(width: 8),
          const Text('Favoritos'),
          const Spacer(),
          Icon(Icons.access_time, color: Colors.blue.shade300),
          const SizedBox(width: 8),
          const Text('Recientes'),
        ],
      ),
    );
  }

  // Item de contacto
  Widget _buildContactItem({required String name, required String email, required Color avatarColor}) {
    return ListTile(
      leading: CircleAvatar(
        backgroundColor: avatarColor,
        child: Text(name[0], style: const TextStyle(color: Colors.white)),
      ),
      title: Text(name),
      subtitle: Text(email),
      trailing: IconButton(
        icon: const Icon(Icons.message, color: Color(0xFF33B5E5)),
        onPressed: () {
          // Acción para enviar mensaje
        },
      ),
    );
  }
}