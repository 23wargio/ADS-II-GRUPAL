// lib/widgets/keyboard_key.dart
import 'package:flutter/material.dart';

class KeyboardKey extends StatelessWidget {
  final String label;
  final bool isSpecial;
  final bool isWide;

  const KeyboardKey({
    Key? key, 
    required this.label, 
    this.isSpecial = false,
    this.isWide = false,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      width: isWide ? 150 : 30,
      height: 30,
      margin: const EdgeInsets.all(2),
      decoration: BoxDecoration(
        color: isSpecial ? const Color(0xFFBBBBBB) : Colors.white,
        borderRadius: BorderRadius.circular(4),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 1,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Center(
        child: Text(
          label,
          style: TextStyle(
            fontSize: isWide ? 14 : 12,
          ),
        ),
      ),
    );
  }
}