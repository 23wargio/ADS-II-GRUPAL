// lib/screens/actividades_tareas_screen.dart
import 'package:flutter/material.dart';

class ActividadesTareasScreen extends StatelessWidget {
  const ActividadesTareasScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Actividades y Tareas'),
      ),
      body: const Center(
        child: Text('Pantalla de Actividades y Tareas'),
      ),
    );
  }
}