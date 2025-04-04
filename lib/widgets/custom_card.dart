import 'package:flutter/material.dart';
import '../utils/colors.dart';

class CustomCard extends StatelessWidget {
  final Widget child;
  final Color color;

  const CustomCard({
    Key? key,
    required this.child,
    this.color = Colors.white,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
      ),
      color: color,
      child: Padding(
        padding: const EdgeInsets.all(10.0),
        child: child,
      ),
    );
  }
}
