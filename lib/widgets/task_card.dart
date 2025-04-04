import 'package:flutter/material.dart';
import '../utils/colors.dart';

class TaskCard extends StatelessWidget {
  final String title;
  final String description;
  final VoidCallback onTap;

  const TaskCard({
    Key? key,
    required this.title,
    required this.description,
    required this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
      ),
      child: ListTile(
        title: Text(title, style: TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text(description),
        onTap: onTap,
        trailing: Icon(Icons.arrow_forward_ios, size: 16),
      ),
    );
  }
}
