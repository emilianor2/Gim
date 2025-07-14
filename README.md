# 🕓 Sistema de Fichadas - Control Horario S.A.

Este sistema web permite gestionar y visualizar el ingreso y egreso de personas en una institución o empresa. Está pensado para un gimnasio pero puede adaptarse a cualquier organización.

## 📁 Estructura del proyecto

```
/fichadas
  ├── index.php              # Vista principal con filtros y tabla de fichadas
  ├── reportes.php           # Gráficos estadísticos con Chart.js
  ├── generar_fichadas_pdf.php  # Exportación de fichadas a PDF
  ├── subir_fichadas.php     # Endpoint remoto para subir fichadas desde la PC local
  ├── enviar_fichadas.php    # Script local para enviar fichadas al hosting
  ├── img/                   # Logo y otras imágenes
  └── dompdf/                # Librería para generar PDF
```

## ⚙️ Tecnologías utilizadas

- PHP 8+
- MySQL
- Chart.js
- Bootstrap 5
- DOMPDF
- HTML + CSS personalizado

## 🚀 Cómo usarlo

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/emilianor2/Gim.git
   ```

2. Subir la carpeta `fichadas/` al hosting en la ruta `/public_html/fichadas/`.

3. Configurar la base de datos MySQL con la tabla `hik` con los siguientes campos:

   ```sql
   CREATE TABLE hik (
     id INT AUTO_INCREMENT PRIMARY KEY,
     id_persona VARCHAR(50),
     nombre VARCHAR(100),
     fecha DATE,
     hora TIME,
     fecha_hora DATETIME,
     status ENUM('E', 'S'),
     img VARCHAR(255)
   );
   ```

4. Para enviar datos desde la PC local, colocar `enviar_fichadas.php` en la máquina que tiene conexión con el reloj.

## 📊 Funcionalidades

- Filtros por nombre, fecha y tipo (E/S)
- Exportación en PDF
- Gráficos por día, mes, persona y tipo de fichada
- Estilo moderno y responsive
- Separación visual con bordes como en Excel

## ✍️ Autor

Desarrollado por **Emiliano Rodríguez**

Repositorio oficial: [https://github.com/emilianor2/Gim]
