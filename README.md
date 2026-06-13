# Sistema de Tickets - Guía de Despliegue (QA Local)

Este documento contiene la información técnica, los requisitos previos, las instrucciones de instalación y la estructura de módulos para el despliegue del **Sistema de Tickets** en un entorno local de QA.

## 1. Requisitos Previos

Antes de comenzar, asegúrate de tener instaladas las siguientes herramientas en tu entorno local:

* **PHP 8.4.x**: Descargar el instalador oficial o configurar en XAMPP / Laragon.
* **Composer**: Descargar e instalar desde [getcomposer.org](https://getcomposer.org/download/).
* **Node.js & NPM**: Instalar la versión LTS desde [nodejs.org](https://nodejs.org/).
* **Servidor MySQL**: Activo a través de XAMPP o Laragon.

## 2. Clonación del Proyecto

Abre la terminal en tu carpeta de proyectos y ejecuta el siguiente comando:

```bash
git clone https://github.com/sudolivictoria/Sistema_Tickets.git
cd Sistema_Tickets
```

## 3. Configuración de la Base de Datos

1. Abre **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Crea una nueva base de datos con el nombre exacto: `sistema_tickets`.
3. Selecciona el cotejamiento (Collation): `utf8mb4_unicode_ci`.

## 4. Configuración del Entorno (.env)

Crea el archivo de configuración local a partir de la plantilla:

```bash
cp .env.example .env
```

## 5. Instalación de Dependencias

Ejecuta los siguientes comandos en la terminal para instalar los paquetes requeridos del Backend y Frontend:

```bash
# Dependencias de PHP (Backend)
composer install --prefer-source
composer require barryvdh/laravel-dompdf --prefer-source

# Dependencias de Node (Frontend)
npm install
npm install @tailwindcss/forms @tailwindcss/container-queries
npm install jquery datatables.net-dt sweetalert2
```

## 6. Inicialización y Optimización del Sistema
Genera la clave de la aplicación y limpia el estado de la caché para evitar conflictos:

```bash
php artisan key:generate
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

---

## 7. Migración de Base de Datos y Archivos

Ejecuta las migraciones junto con los datos de prueba (seeders) y crea el enlace simbólico para el almacenamiento:

```bash
php artisan migrate:fresh --seed
php artisan storage:link

```

## 8. Compilación y Ejecución del Proyecto

### Compilación del Frontend

Genera los archivos listos para producción:

```bash
npm run build
```

### Ejecución de Servicios (En paralelo)

Deberás abrir dos terminales independientes para mantener ambos servicios activos:

* **Terminal 1 (Servidor de Laravel):**

```bash
php artisan serve
```

* **Terminal 1 (Correos / Queues):**

```bash
php artisan queue:work
```

* **Terminal 1 (Actualización de datos):**

```bash
php artisan reverb:start
```

## 9. Acceso de Prueba (Super Admin)

Utiliza las siguientes credenciales para ingresar al sistema con el rol de soporte total. Desde esta cuenta podrás administrar y crear otros usuarios.

* **Usuario:** `ovquintanilla@istu.gob.sv`
* **Contraseña:** `admin123`
