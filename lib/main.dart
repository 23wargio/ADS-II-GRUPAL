// lib/main.dart
import 'package:flutter/material.dart';
import 'package:flutter_application_5/screens/iniciar_sesion_screen.dart';
import 'screens/inicio_screen.dart';
import 'screens/crear_usuario_screen.dart';
import 'screens/menu_principal_screen.dart';
import 'screens/informacion_contacto_screen.dart';
import 'screens/actividades_tareas_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'CRM App',
      theme: ThemeData(
        primaryColor: const Color(0xFF33B5E5),
        colorScheme: ColorScheme.fromSwatch().copyWith(
          primary: const Color(0xFF33B5E5),
          secondary: const Color(0xFF33B5E5),
        ),
      ),
      home: const NavigationPage(),
    );
  }
}

class NavigationPage extends StatefulWidget {
  const NavigationPage({Key? key}) : super(key: key);

  @override
  State<NavigationPage> createState() => _NavigationPageState();
}

class _NavigationPageState extends State<NavigationPage> {
  int _currentIndex = 0;
  
  final List<Widget> _screens = [
    const InicioScreen(),
    const CrearUsuarioScreen(),
    IniciarSesionScreen(),
    const MenuPrincipalScreen(),
    const InformacionContactoScreen(),
    const ActividadesTareasScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Inicio'),
          BottomNavigationBarItem(icon: Icon(Icons.person_add), label: 'Crear Usuario'),
          BottomNavigationBarItem(icon: Icon(Icons.login), label: 'Iniciar Sesión'),
        ],
      ),
    );
  }
}