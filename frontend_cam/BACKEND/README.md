# Laravel Project Setup Guide

## 1. Create Project with Jetstream

```bash
laravel new ProjectName --jet
```

### If Jetstream is not yet installed:

```bash
composer require laravel/jetstream
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## 2. Install and Configure AdminLTE Template

```bash
composer require jeroennoten/laravel-adminlte
php artisan adminlte:install
php artisan adminlte:install --only=auth_views
php artisan adminlte:plugins
```

### Install Specific Plugins

```bash
php artisan adminlte:plugins install --plugin=sweetalert2
php artisan adminlte:plugins install --plugin=fullcalendar
php artisan adminlte:plugins install --plugin=datatables
npm install jquery-ui
```

---

## 3. Permissions with Spatie

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

---

## 4. Excel Integration

```bash
composer require phpoffice/phpspreadsheet
composer require maatwebsite/excel
composer require league/csv
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
php artisan storage:link
```

---

## 5. PDF and QR Code Generation

```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

composer require endroid/qr-code
```

---

## 6. Notification System

```bash
php artisan notification:table
php artisan make:notification PostNotification
php artisan make:event PostEvent
php artisan make:listener PostListener
php artisan make:provider EventServiceProvider
php artisan adminlte:install --only=main_views
```

### 7. Job Queue System

| Acción                         | Comando CLI                                 |
|-------------------------------|---------------------------------------------|
| Migración tabla `jobs`        | `php artisan queue:table`                   |
| Crear clase de correo         | `php artisan make:mail SharedTask`          |
| Crear trabajo `Logger`        | `php artisan make:job Logger`               |
| Crear trabajo `ImportarNotificaciones` | `php artisan make:job ImportarNotificaciones` |
| Trabajador para cola `database` | `php artisan queue:work database --queue=secondary` |
| Reintentar todos los fallidos | `php artisan queue:retry all`               |
| Monitorear trabajos fallidos  | `php artisan queue:failed`                  |
| Limpiar trabajos fallidos     | `php artisan queue:flush`                   |

```bash
php artisan make:mail SharedTask
php artisan make:job Logger
php artisan make:job ImportarNotificaciones
php artisan queue:table
php artisan migrate
php artisan queue:work database --queue=secondary
php artisan queue:retry all
php artisan queue:failed
php artisan queue:flush
```
---

## 8. Frontend (SCSS, JS, Assets)

```bash
npm install laravel-mix --save-dev
npm install @fullcalendar/core @fullcalendar/daygrid @fullcalendar/timegrid
npm install toastr
```

### Add in `resources/js/app.js`:

```js
import Swal from 'sweetalert2';
import 'jquery-ui/ui/widgets/datepicker';
```

---

## 9. Create Models, Factories & Seeders

```bash
php artisan make:model Product -mcrs
php artisan make:model Post -m
php artisan make:model Category -m
php artisan make:factory PostFactory
php artisan make:factory CategoryFactory
php artisan make:factory TagFactory
php artisan make:factory ImageFactory
php artisan make:seeder UserSeeder
php artisan make:seeder PostSeeder
php artisan migrate --step
php artisan migrate:rollback --step
```

---

## 10. Country & City Data (World Package)

```bash
composer require nnjeim/world
php artisan world:install
php artisan db:seed --class=WorldSeeder
```

---

## 11. Slug Library (Frontend)

Plugin: [jquery-string-to-slug](https://leocaseiro.com.br/jquery-plugin-string-to-slug/)

```bash
php artisan vendor:publish
```

---

## 12. Clean Cache & Optimize

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

---

## 13. Setup Localhost with XAMPP & Custom Domain

### 1. Edit `hosts` file:

Path: `C:\Windows\System32\drivers\etc\hosts`

Add:
```
127.0.0.1 laravel9.test
```

### 2. Edit `httpd-vhosts.conf`:

Path: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

Add:
```apacheconf
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "/xampp/htdocs"
</VirtualHost>

<VirtualHost *:80>
    ServerName laravel9.test
    DocumentRoot "/xampp/htdocs/www/laravel/laravel9/public"
</VirtualHost>
```

---

## 14. PHP Extensions (Enable in `php.ini`)

Uncomment or add:

```
extension=gd
extension=zip
```
## 15. Get project name
```bash
git remote get-url origin
```
php artisan make:livewire NavigationMenu

composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
php artisan make:controller AuthController
git config --get remote.origin.url

## Each time install backend execute
php artisan jwt:secret