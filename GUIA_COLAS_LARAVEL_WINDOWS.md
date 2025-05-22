GUIA_COLAS_LARAVEL_WINDOWS.txt

# Guía Completa: Configuración y Despliegue de Colas (Queues) en Laravel en Windows

Esta guía detalla cómo configurar y desplegar las colas de Laravel, especialmente si usas el driver 'database' y trabajas en un entorno Windows.

---

## 1. Configuración Inicial en Entorno de Desarrollo (Windows)

Primero, asegúrate de que tu entorno de desarrollo (XAMPP, Laragon, WAMP, etc.) esté listo.

1.  **Modificar .env para la conexión de cola:**
    Abre tu archivo `.env` en la raíz de tu proyecto Laravel y configura la variable `QUEUE_CONNECTION`. Para empezar en desarrollo, `database` es una excelente opción.

    ```dotenv
    QUEUE_CONNECTION=database
    # O si usas Redis (asegúrate de tener Redis instalado y corriendo en Windows,
    # puedes usar la versión de Microsoft o una distribución de Linux via WSL2)
    # QUEUE_CONNECTION=redis
    # REDIS_HOST=127.0.0.1
    # REDIS_PASSWORD=null
    # REDIS_PORT=6379
    ```

2.  **Generar la migración de la tabla de trabajos:**
    Este comando creará el archivo de migración necesario para la tabla `jobs` en tu base de datos.
    **Solo necesitas ejecutar esto UNA VEZ en tu entorno de desarrollo.**

    ```bash
    php artisan queue:table
    ```

3.  **Ejecutar la migración:**
    Este comando creará la tabla `jobs` en la base de datos que tengas configurada en tu `.env`.

    ```bash
    php artisan migrate
    ```
    Verifica tu base de datos para confirmar que la tabla `jobs` ha sido creada.

4.  **Limpiar la caché de configuración (importante después de .env):**
    Siempre que modificas el archivo `.env`, es una buena práctica limpiar la caché para que los cambios se apliquen.

    ```bash
    php artisan config:clear
    ```

5.  **Enviar un trabajo de prueba a la cola:**
    Puedes crear un trabajo simple (ej. `php artisan make:job ProcessPodcast`) y luego despacharlo en una ruta o controlador para ver si entra en la cola:

    ```php
    // En web.php o un controlador
    use App\Jobs\ProcessPodcast;

    Route::get('/test-queue', function () {
        ProcessPodcast::dispatch(); // O ProcessPodcast::dispatch()->onQueue('emails');
        return 'Job dispatched!';
    });
    ```
    Al visitar `/test-queue` en tu navegador, deberías ver un nuevo registro en la tabla `jobs` de tu base de datos.

6.  **Iniciar el Worker de la Cola en Desarrollo (Windows):**
    Para que los trabajos en la tabla `jobs` sean procesados, necesitas un worker de cola escuchando.

    * **Opción 1: Terminal simple (para pruebas rápidas):**
        Abre una nueva ventana de `CMD` o `PowerShell` en la raíz de tu proyecto Laravel y ejecuta:
        ```bash
        php artisan queue:work
        ```
        Este comando procesará los trabajos y seguirá escuchando por nuevos. Puedes agregar `--tries=3` para reintentar trabajos fallidos o `--timeout=60` para limitar el tiempo de ejecución. Presiona `Ctrl+C` para detenerlo.

    * **Opción 2: Usando un supervisor de procesos en Windows (recomendado para desarrollo continuo):**
        Para mantener el worker de la cola ejecutándose en segundo plano y de forma persistente en Windows, puedes usar herramientas como **NSSM (Non-Sucking Service Manager)** o **Laravel Horizon (si usas Redis)**.

        * **NSSM (para cualquier driver de cola):**
            1.  Descarga NSSM: `https://nssm.cc/download`
            2.  Extrae el archivo y coloca el `nssm.exe` (elige la versión de 64 o 32 bits) en una ubicación accesible (ej. `C:\Windows\System32` o el directorio `bin` de tu proyecto).
            3.  Abre `CMD` como **Administrador** y navega a tu directorio `bin` de Laravel:
                ```bash
                cd C:\xampp\htdocs\tu-proyecto-laravel
                ```
            4.  Instala el servicio:
                ```bash
                nssm install MiAppLaravelQueue
                ```
                Esto abrirá una interfaz gráfica:
                * **Path:** `C:\xampp\php\php.exe` (o la ruta a tu `php.exe`)
                * **Startup directory:** `C:\xampp\htdocs\tu-proyecto-laravel` (la raíz de tu proyecto Laravel)
                * **Arguments:** `artisan queue:work` (puedes añadir `--tries=3 --daemon` si quieres que el worker no muera después de un trabajo)
                * Haz clic en "Install service".
            5.  Inicia el servicio desde Servicios de Windows (`services.msc`) o desde CMD:
                ```bash
                net start MiAppLaravelQueue
                ```
            Ahora tu worker de cola se ejecutará como un servicio de Windows en segundo plano. Para detenerlo, usa `net stop MiAppLaravelQueue`. Para desinstalarlo, `nssm remove MiAppLaravelQueue`.

        * **Laravel Horizon (solo si usas Redis como driver de cola):**
            Horizon proporciona una interfaz de usuario elegante y robusta para monitorear tus colas basadas en Redis.
            1.  Instala Horizon: `composer require laravel/horizon`
            2.  Publica sus recursos: `php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"`
            3.  Asegúrate de que `QUEUE_CONNECTION=redis` en tu `.env`.
            4.  Ejecuta Horizon (en una terminal separada, o como servicio con NSSM/PM2):
                ```bash
                php artisan horizon
                ```
            5.  Accede al dashboard de Horizon en tu navegador: `http://localhost/horizon` (o la URL de tu app seguida de `/horizon`).

---

## 2. Flujo de Despliegue en Producción (Windows Server)

Si estás desplegando tu aplicación Laravel en un servidor Windows (IIS, Apache en Windows, etc.), el proceso es similar pero con consideraciones adicionales.

1.  **Actualizar el Código Base:**
    Asume que ya has subido tus archivos de proyecto actualizados al servidor (usando `git pull`, FTP, o tu CI/CD).

    ```bash
    # En tu servidor de producción (navega a la raíz de tu proyecto)
    # Ejemplo: cd C:\inetpub\wwwroot\tu-proyecto-laravel

    # 1. Traer los últimos cambios del repositorio (si usas Git)
    git pull origin main # O la rama que uses para producción (ej. 'master' o 'production')
    ```

2.  **Instalar/Actualizar Dependencias de Composer:**
    Asegúrate de instalar solo las dependencias necesarias para producción.

    ```bash
    composer install --optimize-autoloader --no-dev
    ```

3.  **Limpiar Caché de la Aplicación:**
    Es vital limpiar la caché de configuración, rutas, vistas, etc., para que Laravel cargue la nueva configuración y código.

    ```bash
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan optimize # Para optimizar la carga de Laravel
    ```

4.  **Ejecutar Migraciones de Base de Datos:**
    **Este es el paso crucial para crear la tabla `jobs` en tu base de datos de producción (si aún no existe) o aplicar cualquier otra migración pendiente.** El flag `--force` es necesario para ejecutar migraciones en un entorno de producción, ya que previene la ejecución accidental.

    ```bash
    php artisan migrate --force
    ```

5.  **Crear Enlace Simbólico de Almacenamiento (si aplica):**
    Si tu aplicación guarda archivos de usuario (subidas, etc.) en `storage/app/public` y los sirve a través de un enlace simbólico en `public/storage`, necesitas asegurar que este enlace exista o se actualice.

    ```bash
    php artisan storage:link
    ```

6.  **Gestionar el Worker de la Cola en Producción (Windows):**
    En producción, el worker de la cola debe ejecutarse de forma persistente y ser resiliente a fallos. **NSSM** es la opción más robusta y recomendada para Windows.

    * **NSSM (Non-Sucking Service Manager):**
        Ya se explicó en la sección de desarrollo. Sigue los mismos pasos para crear un servicio de Windows para `php artisan queue:work`.

        * **Instalar el servicio (si no existe):**
            ```bash
            nssm install MiAppLaravelQueueProd
            ```
            (Configura `Path` a `php.exe`, `Startup directory` a la raíz de tu proyecto, y `Arguments` a `artisan queue:work --tries=3 --daemon`).

        * **Reiniciar el worker de la cola (después de un despliegue):**
            Cuando despliegas nuevo código (especialmente si hay cambios en los Jobs), el worker de la cola que está corriendo puede tener en memoria la versión antigua del código. Necesitas reiniciarlo para que cargue la nueva versión.

            ```bash
            net stop MiAppLaravelQueueProd
            net start MiAppLaravelQueueProd
            ```
            Esto detendrá y volverá a iniciar el servicio de Windows que ejecuta tu worker de cola.

    * **Laravel Horizon (si usas Redis):**
        Si usas Redis y Horizon, también deberías configurarlo como un servicio de Windows con NSSM.

        * **Instalar el servicio de Horizon:**
            ```bash
            nssm install MiAppLaravelHorizonProd
            ```
            (Configura `Path` a `php.exe`, `Startup directory` a la raíz de tu proyecto, y `Arguments` a `artisan horizon`).

        * **Reiniciar Horizon (después de un despliegue):**
            Para que Horizon cargue los nuevos Jobs y código:
            ```bash
            php artisan horizon:terminate
            # Luego, el servicio de NSSM lo reiniciará automáticamente (o puedes forzarlo)
            # net stop MiAppLaravelHorizonProd
            # net start MiAppLaravelHorizonProd
            ```

---

**Resumen de los comandos críticos en producción:**

```bash
# Navega a la raíz de tu proyecto Laravel en el servidor
cd C:\ruta\a\tu\proyecto\laravel

git pull origin main
composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

php artisan migrate --force # ¡Fundamental para la tabla 'jobs'!

# Si cambiaste jobs o quieres que el worker cargue el nuevo código
# Para workers gestionados por NSSM (MiAppLaravelQueueProd es un ejemplo de nombre de servicio)
net stop MiAppLaravelQueueProd
net start MiAppLaravelQueueProd

# O si usas Horizon
php artisan horizon:terminate # Horizon se reiniciará automáticamente vía su supervisor o NSSM