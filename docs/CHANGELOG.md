# Historial de Cambios de la Documentación (CHANGELOG.md)
**Sistema Biométrico DAC v2.1 · Unidad Educativa Tiquipaya**

---

## [2.1.0] - 2026-08-20

### 🚀 Actualización Integral de Documentación y Firmware

Se actualizó la totalidad de los archivos de documentación técnica y firmware en el repositorio para garantizar **100% de coherencia con el sistema real desplegado en producción**.

#### 1. Firmware Oficial (`firmware/esp32_dac.ino` & `firmware/README.md`)
* **Creado/Actualizado**: Guardado el código fuente C++ de producción oficial para **ESP32 DevKit V1** y sensor **R307S** (familia AS608) a 57600 baudios.
* **Modo Compatibilidad 1:1**: Implementado el flujo asistido de ingreso de ID por teclado matricial 4x4 + comprobación de dedo vivo en el sensor, sustituyendo los algoritmos de búsqueda no soportados por clones R307S.
* **Librerías & Telemetría**: Integrado `ArduinoJson` v7, Adafruit Fingerprint y pulso telemétrico automatizado (`HEARTBEAT_INTERVAL = 30000`).
* **Documentación del Hardware**: Creado `firmware/README.md` con la tabla completa de cableado (pinout), librerías necesarias e instrucciones de compilación.

#### 2. Manual Técnico (`docs/MANUAL_TECNICO.md`)
* **Arquitectura 3 Capas**: Documentada la estructura desacoplada Quiosco IoT $\rightarrow$ REST API v1 (Sanctum) $\rightarrow$ Web MVC / RBAC / MySQL XAMPP.
* **Pinout Oficial**: Añadida la tabla completa de conexiones para ESP32, R307S (`GPIO16`/`GPIO17`), LCD I2C (`0x27`), Teclado 4x4, LEDs (`GPIO4`, `GPIO5`, `GPIO2`), Buzzer (`GPIO18`) y Ultrasonido HC-SR04 (`GPIO19`/`GPIO23`).
* **Justificación Técnica Obligatoria**: Añadida la justificación explícita sobre el Modo Compatibilidad 1:1 para sensores R307S clon.
* **Credenciales XAMPP/Workbench**: Actualizado con `127.0.0.1:3306`, usuario `root`, contraseña `Root`.
* **API REST v1**: Ejemplos cURL y respuestas JSON uniformes (`{success, message, data}`).
* **Apéndice A**: Incluido el código fuente completo C++ del firmware.

#### 3. Guía de Despliegue DevOps (`docs/Guia_Despliegue_DevOps.md`)
* **Credenciales BD**: Configuración `.env` oficial con `DB_PORT=3306`, `DB_USERNAME=root`, `DB_PASSWORD=Root`.
* **Flasheo ESP32**: Añadido el procedimiento del botón **BOOT** en el ESP32 cuando el IDE muestra `Connecting........_____`.
* **Herramientas**: Guía para Cloudflare Tunnel (HTTPS demo), copias de seguridad con `mysqldump` y flujo de trabajo GitHub (`main`, `.gitignore`).

#### 4. Manuales de Roles (`Manual_Administrador.md`, `Manual_Coordinador.md`, `Manual_Docente.md`)
* **Administrador**: Paso a paso de todas las funciones (Gestión de usuarios, Dispositivos/Tokens, Enrolamiento biométrico 3 huellas, Monitoreo HW, Reportes PDF/Excel, Memorandos, Justificaciones).
* **Coordinador**: Alineación estricta a `routes/web.php` (`role:admin,coordinador`), gestión de horarios DAC (13:15-14:20 y 14:30-15:30), dictamen de justificaciones y memorandos.
* **Docente**: Guía de autoservicio para consulta de asistencias, horarios, perfil y solicitudes de justificación con comprobantes.

#### 5. Guía de Operación Rápida (`docs/MANUAL_USUARIO.md`)
* **Flujo Quiosco 2 Páginas**: Diagrama paso a paso (Acercarse $\rightarrow$ Ingresar ID $\rightarrow$ `#` $\rightarrow$ Colocar Dedo $\rightarrow$ Beep/Bienvenido) y tabla de solución de problemas ("Sin dedo", "Fallo en servidor", "R307 NO detecta").

#### 6. Guía de Identidad Visual (`docs/GUIA_INSERCION_LOGOS.md`)
* **Regla de Fondos**: Añadida la restricción obligatoria contra imágenes de fondo recortadas e inclusión del degradado institucional `linear-gradient(160deg,#0f172a 0%,#4c0519 55%,#881337 100%)`.

#### 7. Entregables Nuevos (`docs/ROLES_MATRIZ.md` & `docs/CHANGELOG.md`)
* **ROLES_MATRIZ.md**: Matriz detallada Módulo $\times$ Acción $\times$ Rol según `routes/web.php`.
* **CHANGELOG.md**: Registro histórico de cambios y auditoría de la documentación.
