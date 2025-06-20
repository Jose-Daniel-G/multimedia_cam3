php artisan storage:link
composer require phpoffice/phpspreadsheet
composer require maatwebsite/excel
composer require league/csv

php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"

## EN  PHP.INI HABILITAR
- extension=gd
- extension=zip

## TENER EN CUENTA aumentar en my.ini
- max_allowed_packet=64M

## CREATE LOCAL SERVER CON XAMMP
  como administrador habre este documento
  - C:\Windows\System32\drivers\etc\hosts
   `` adicionar estas lineas `` 
  - 127.0.0.1 laravel9.test

  luego dirigirse a esta ruta y editar el siguiente archivo
  - C:\xampp\apache\conf\extra\httpd-vhosts.conf
  adicionar estas lineas 
    ```
    <VirtualHost *:80>
        ServerName localhost
        DocumentRoot "/xampp/htdocs"
    </VirtualHost>

    <VirtualHost *:80>
        ServerName laravel9.test
        DocumentRoot "/xampp/htdocs/www/laravel/public"
    </VirtualHost>
    ```                    # RUTA 

### FILES ROUTE
 -php artisan storage:link

### CLEAN GRABAGE
 ```
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan view:clear
php artisan clear-compiled
php artisan optimize:clear
php artisan optimize

php artisan queue:flush
php artisan queue:restart
php artisan queue:clear
 ```
###### INSTALL LANGUAGE
 ```
composer require laravel-lang/common
php artisan lang:add es
php artisan lang:update
 ```
###### INSTALL PDF

```
composer require barrivdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
composer require endroid/qr-code
- optional if doesn't leave: composer clear-cache
```

# -------------------------

-   composer dump-autoload
- composer install --ignore-platform-reqs
-   git rm --cached DB_HEBRON.jpg

-   npm install jquery

# -------------------------

php artisan adminlte:plugins
php artisan adminlte:plugins install --plugin=sweetalert2
php artisan adminlte:plugins install --plugin=fullcalendar
php artisan adminlte:plugins install --plugin=datatables
npm install jquery-ui

#### EN EL ARCHIO APP.JS PONER

-   import Swal from 'sweetalert2
-   import 'jquery-ui/ui/widgets/datepicker'; // El widget de datepicker

<i class="fas fa-eye"></i>
<i class="fas fa-edit"></i>
<i class="fas fa-trash"></i>

IF I WANT TO IMPLEMENT NOTIFICATIONS ON PROJECT

php artisan notification:table
php artisan make:event PostEvent



HABILITAR EXTENCION EN PHP.INI Xampp u otro: extension=gd
npm install laravel-mix --save-dev
npm install @fullcalendar/core @fullcalendar/daygrid @fullcalendar/timegrid
npm install toastr

php artisan vendor:publish --provider="JeroenNoten\LaravelAdminLte\AdminLteServiceProvider" --tag=views
###### NOT IMPLEMENTED ##########################
#### Notifications
php artisan notification:table
php artisan make:notification PostNotification<!-- php artisan make:notification InvoicePaid  --> 
php artisan make:event PostEvent
php artisan make:listener PostListener
php artisan adminlte:install --only=main_views
## optional it makes faster to send notifications
php artisan queue:table
php artisan queue:work
php artisan make:job ImportarNotificaciones

php artisan migrate --step
php artisan migrate:rollback --step

## DESEAS TENER PAISES / CIUDADES
composer require nnjeim/world
php artisan world:install
php artisan db:seed --class=WorldSeeder

## LIBRERIA SLUG
https://leocaseiro.com.br/jquery-plugin-string-to-slug/

php artisan vendor:publish
php -S 127.0.0.1:8000 -t public
php artisan users:update
git remote get-url origin
php artisan optimize

### Se encarga de registrar la información de los inicios de sesión en tu aplicación Laravel. (AUDITORIA)

composer require rappasoft/laravel-authentication-log

#### Guarda: usuario que hizo el cambio, antes/después, IP, URL, user agent
composer require owen-it/laravel-auditing
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag=config


Remove-Item -Recurse -Force vendor, composer.lock
