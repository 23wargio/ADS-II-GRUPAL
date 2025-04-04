import 'package:flutter/material.dart';


class AuthHome extends StatelessWidget {
  const AuthHome({Key? key}) : super(key: key);


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Header con logo y menú
          Container(
            color: Colors.lightBlue[100],
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
            child: Row(
              children: [
                Image.asset(
                  'assets/book_logo.png',
                  height: 40,
                ),
                const SizedBox(width: 10),
                const Text(
                  'Producto',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(width: 10),
                const Text(
                  'Recursos',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(width: 10),
                const Text(
                  'Precios',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),

          // Líneas separadoras del menú
          Row(
            children: [
              Expanded(
                child: Container(
                  height: 3,
                  color: Colors.blue[900],
                ),
              ),
              Expanded(
                child: Container(
                  height: 3,
                  color: Colors.blue[900],
                ),
              ),
              Expanded(
                child: Container(
                  height: 3,
                  color: Colors.blue[900],
                ),
              ),
            ],
          ),

          // Contenido principal
          Expanded(
            child: Container(
              color: Colors.grey[200],
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Row(
                  children: [
                    // Imagen izquierda (persona escribiendo)
                    Expanded(
                      flex: 1,
                      child: Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          color: Colors.white,
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: Image.asset(
                          'assets/writing_person.jpg',
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),

                    const SizedBox(width: 16),

                    // Texto derecho (descripción)
                    Expanded(
                      flex: 1,
                      child: Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          color: Colors.blue,
                        ),
                        padding: const EdgeInsets.all(16),
                        child: const Center(
                          child: Text(
                            'Todas las formas de gestionar los clientes',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Botones de autenticación
          Container(
            color: Colors.grey[200],
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                // Botón iniciar sesión
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pushNamed(context, '/login');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.grey[300],
                      foregroundColor: Colors.black,
                      padding: const EdgeInsets.symmetric(vertical: 15),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(6),
                      ),
                    ),
                    child: const Text(
                      'Iniciar sesión',
                      style: TextStyle(fontSize: 16),
                    ),
                  ),
                ),

                const SizedBox(width: 16),

                // Botón crear nuevo usuario
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pushNamed(context, '/register');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 15),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(6),
                      ),
                    ),
                    child: const Text(
                      'Crear nuevo usuario',
                      style: TextStyle(fontSize: 16),
                    ),
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
