# Guía de Despliegue y DevOps – Sistema Biométrico DAC v2.1
**Programa DAC · Unidad Educativa Tiquipaya**

---

## 1. Entorno de Desarrollo y Requisitos del Sistema

### 1.1. Servidor Local (XAMPP)
* **PHP**: 8.1 o 8.2 con extensiones `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `gd`, `zip`.
* **Servidor Web**: Apache (incluido en XAMPP).
* **Base de Datos**: MySQL / MariaDB (Servicio MySQL en XAMPP en puerto `3306`).
* **Composer**: Versión 2.x.

---

## 2. Configuración del Archivo `.env` y Base de Datos

### 2.1. Archivo `.env` de Producción Local
Asegurar las credenciales exactas de XAMPP / MySQL Workbench:

```env
APP_NAME="Sistema Biométrico DAC"
APP_ENV=local
APP_KEY=base64:US3euwrhl5SYMfY65Vfy/fbzaXXv/avIbkhbgHa+cvs=
APP_DEBUG=true
APP_URL=http://localhost/PROYECTO-DAC/sistema-biometrico-dac/public

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dac_biometrico
DB_USERNAME=root
DB_PASSWORD=Root

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 2.2. Inicialización de la Base de Datos
Ejecutar desde la terminal en el directorio raíz del proyecto:

```bash
# Instalación de dependencias de Composer
composer install --ignore-platform-reqs

# Ejecutar migraciones y datos semilla de producción
php artisan migrate:fresh --seed --force

# Crear enlace simbólico de archivos públicos
php artisan storage:link
```

---

## 3. Registro de Lectores ESP32 y Generación de Tokens Sanctum

1. Iniciar sesión en la consola web como **Administrador** (`admin@dac.edu.bo` / `Admin@2024!`).
2. Dirigirse al módulo **Lectores ESP32** (`/devices`).
3. Hacer clic en **Registrar Nuevo Lector**.
4. Completar el formulario:
   * **Código**: `ESP32-DAC-001`
   * **Nombre**: Lector Biométrico Principal - Aula DAC
   * **Ubicación**: Sala DAC - Unidad Educativa Tiquipaya
   * **Dirección MAC**: `24:0A:C4:DA:C0:01`
   * **Versión Firmware**: `2.1.0`
5. Al guardar, el sistema generará automáticamente un **API Token (Bearer)** (Ej: `dac_dev_secret_token_123`). Copiar este token para reemplazarlo en la constante `API_TOKEN` del firmware C++.

---

## 4. Carga de Firmware en el ESP32 (Arduino IDE)

### 4.1. Configuración de Arduino IDE (v1.8.19 o 2.x)
* **Gestor de Tarjetas**: Agregar la URL de placas ESP32 (`https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json`).
* **Placa Seleccionada**: `DOIT ESP32 DEVKIT V1`.
* **Librerías Necesarias**:
  * `Adafruit Fingerprint Sensor Library` (Adafruit)
  * `Adafruit BusIO`
  * `LiquidCrystal_I2C` (Frank de Brabander / Marco Schwartz)
  * `Keypad` (Mark Stanley, Alexander Brevig)
  * `ArduinoJson` (v7.x de Benoit Blanchon)

### 4.2. Flasheo e Instrucciones del Botón BOOT
1. Abrir `firmware/esp32_dac.ino`.
2. Actualizar las constantes de red y token:
   ```cpp
   const char* WIFI_SSID     = "MiRedWiFi_2.4G";
   const char* WIFI_PASSWORD = "MiClaveSuperSegura";
   const String API_BASE     = "http://192.168.1.100/PROYECTO-DAC/sistema-biometrico-dac/public/api/v1";
   const String API_TOKEN    = "dac_dev_secret_token_123";
   ```
3. Conectar el ESP32 mediante cable Micro-USB de datos al ordenador.
4. Seleccionar el puerto COM correspondiente.
5. Hacer clic en **Subir (Upload)**.
6. **Truco del Botón BOOT**: Cuando la consola de salida muestre el texto `Connecting........_____`, mantener presionado el botón físicamente etiquetado como **BOOT** (o `IO0`) en la placa ESP32 por 2 segundos hasta que comience el porcentaje de escritura (`Writing at 0x00010000... (10%)`), luego soltarlo.

---

## 5. Exposición Pública HTTPS con Cloudflare Tunnel

Para realizar pruebas con servidores remotos o conectar el ESP32 desde redes externas mediante HTTPS:

```bash
# Descargar e instalar Cloudflare Tunnel (cloudflared)
cloudflared tunnel --url http://127.0.0.1/PROYECTO-DAC/sistema-biometrico-dac/public
```

La consola generará una URL pública segura (Ej: `https://mi-tunnel-dac.trycloudflare.com`). Actualizar dicha URL en la variable `API_BASE` del firmware ESP32.

---

## 6. Respaldos de Base de Datos (Backups con Mysqldump)

### 6.1. Copia de Seguridad Manual
```bash
# Exportar base de datos completa con estructura y datos
mysqldump -u root -pRoot dac_biometrico > dac_biometrico_backup_$(date +%Y%m%d).sql
```

### 6.2. Restauración de Base de Datos
```bash
mysql -u root -pRoot dac_biometrico < dac_biometrico_backup_20260820.sql
```

---

## 7. Flujo de Control de Versiones con GitHub

### 7.1. Reglas de Commit y Branches
* **Rama Principal**: `main` (Código de producción probado).
* **Mensajes de Commit Estandarizados**:
  * `feat:` Nuevas funcionalidades (Ej: `feat: add R307S 1:1 compatibility mode`)
  * `fix:` Corrección de errores (Ej: `fix: resolve ScheduleController syntax error`)
  * `docs:` Actualización de documentación (Ej: `docs: update DevOps guide and pinout`)

### 7.2. Archivo `.gitignore` Obligatorio
Asegurar que las variables sensibles y archivos generados no se suban al repositorio:

```gitignore
/vendor
/.env
/storage/*.key
/public/storage
.DS_Store
Thumbs.db
```
