Este documento contiene la información técnica completa, el diseño de la base de datos, el cronograma de desarrollo y las instrucciones 
precisas para desplegar y probar el **Sistema de Tickets** en un entorno local de QA.

# Requisitos Previos #
* **PHP 8.4**: Descargue el instalador oficial o use su entorno local preferido (XAMPP / Laragon) asegurándose de que la versión de PHP sea la 8.4.x.

* **Composer**: Descargue e instale el ejecutable para Windows desde [https://getcomposer.org/download/](https://getcomposer.org/download/).

* **Node.js & NPM**: Descargue e instale la versión LTS desde [https://nodejs.org/](https://nodejs.org/).

* **Servidor MySQL** (XAMPP / Laragon)



# ---------- Clonación del Proyecto ---------------- #
---> Abra la terminal en su carpeta de proyectos y ejecute:

# git clone https://github.com/sudolivictoria/Sistema_Tickets.git


# -------------- Base de datos ----------------------- #
---> Abra phpMyAdmin (http://localhost/phpmyadmin).
---> Cree una base de datos con el nombre exacto: sistema_tickets
---> Use el cotejamiento: utf8mb4_unicode_ci

# ----------- Configuración .env ---------------------#

# cp .env.example .env

-->abrir el archivo .env y pegar las configuraciones correspondientes.


# -------------Instalación dependencias --------------#

# composer install --prefer-source
# composer require barryvdh/laravel-dompdf --prefer-source
# npm install
# npm install @tailwindcss/forms @tailwindcss/container-queries
# npm install jquery datatables.net-dt sweetalert2

# -------Inicializacion llaves y limpieza de cache-------#

# php artisan key:generate
# php artisan route:clear
# php artisan config:clear
# php artisan cache:clear
# php artisan view:clear
# php artisan optimize:clear

# ------------BD, datos de prueba y archivos---------------#

# php artisan migrate:fresh --seed
# php artisan storage:link

# ----------------Compilacion FrontEnd--------------------#

# npm run build

# -------------Ejecución Servicios en paralelo------------#

# SERVIDOR DE LARAVEL
# php artisan serve

# PROCESAROR DE CORREOS (QUEUES)
# php artisan queue:local

# Acceso Super Admin #
--> Con este usuario puede crear nuevos usuarios super admin, gestor o un usuario normal en gestion de usuario.
# ovquintanilla@istu.gob.sv
# admin123


# --------------MODULOS------------------#

# ADMIN
**Dashboard**
**Asignar**
**Mis Asignados**
**Gestion Usuarios**
**Gestion Recursos**
**Historial**

**Crear ticket**
**Mis tickets**
**Recursos**

# GESTOR
**Dashboard**
**Asignar**
**Mis Asignados**
**Historial**

**Crear ticket**
**Mis tickets**
**Recursos**


# USUARIO
**Crear ticket**
**Mis tickets**
**Recursos**
