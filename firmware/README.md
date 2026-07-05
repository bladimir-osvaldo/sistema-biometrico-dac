# Firmware Oficial ESP32 – Sistema Biométrico DAC v2.1
**Microcontrolador ESP32 DevKit V1 + Sensor Biométrico Óptico R307S**

---

## 1. Descripción General

Este directorio contiene el firmware oficial C++ en código abierto (`esp32_dac.ino`) para el quiosco biométrico del **Sistema Biométrico DAC** (Unidad Educativa Tiquipaya).

El firmware permite:
* Captura y verificación biométrica viva de docentes mediante el sensor óptico **R307S** (familia AS608).
* Comunicación HTTP/REST bidireccional mediante JSON y librerías `ArduinoJson` v7.
* Autenticación segura vía tokens **Sanctum (Bearer)**.
* Pantalla informativa LCD 16x2 I2C, Teclado Matricial 4x4, Sensor Ultrasónico de Proximidad HC-SR04, LEDs de estado y Alerta Sonora (Buzzer).
* Envío de telemetría (Heartbeat) automático cada 30 segundos.
* **Modo Compatibilidad 1:1**: Ingreso asistido de ID en teclado + comprobación de dedo vivo para compatibilidad total con sensores R307S clon.

---

## 2. Diagrama de Cableado (Pinout)

| Periférico | Pin Periférico | GPIO ESP32 | Descripción |
|---|---|---|---|
| **Sensor R307S** | TX (Amarillo) | `GPIO16` | RX2 Serie |
| **Sensor R307S** | RX (Verde/Blanco) | `GPIO17` | TX2 Serie |
| **Sensor R307S** | VCC (Rojo) / GND (Negro) | 5V / GND | Alimentación 5V regulada |
| **LCD 16x2 I2C** | SDA / SCL | `GPIO21` / `GPIO22` | Bus I2C (Dirección `0x27`) |
| **LED Verde** | Anodo (+) | `GPIO4` | Éxito / Confirmación (+220Ω) |
| **LED Amarillo** | Anodo (+) | `GPIO5` | Proceso / Espera Dedo (+220Ω) |
| **LED Rojo** | Anodo (+) | `GPIO2` | Error / Rechazado (+220Ω) |
| **Buzzer Activo** | Positivo (+) | `GPIO18` | Tono Beep |
| **HC-SR04** | TRIG / ECHO | `GPIO19` / `GPIO23` | Proximidad (<12 cm) |
| **Teclado 4x4** | Filas (F1..F4) | `GPIO13, 12, 14, 27` | Filas matriciales |
| **Teclado 4x4** | Columnas (C1..C4) | `GPIO26, 25, 33, 32` | Columnas matriciales |

---

## 3. Requisitos y Librerías de Arduino IDE

* **Placa**: `DOIT ESP32 DEVKIT V1`
* **Velocidad Serial Monitor**: `115200`
* **Librerías**:
  1. `Adafruit Fingerprint Sensor Library` (Adafruit)
  2. `Adafruit BusIO`
  3. `LiquidCrystal_I2C`
  4. `Keypad` (Community)
  5. `ArduinoJson` (v7.x)

---

## 4. Instrucciones de Compilación y Carga

1. Abrir `esp32_dac.ino` en Arduino IDE.
2. Modificar las constantes en las primeras líneas del archivo:
   ```cpp
   const char* WIFI_SSID     = "TU_RED_WIFI_2.4G";
   const char* WIFI_PASSWORD = "TU_CONTRASEÑA";
   const String API_BASE     = "http://192.168.1.100/PROYECTO-DAC/sistema-biometrico-dac/public/api/v1";
   const String API_TOKEN    = "TU_TOKEN_SANCTUM_DEL_LECTOR";
   ```
3. Conectar la placa ESP32 por USB.
4. Presionar el botón **Subir**.
5. **Nota sobre el botón BOOT**: Si Arduino IDE se queda en `Connecting........_____`, mantener presionado el botón **BOOT** en la placa ESP32 por 2 segundos hasta que comience el grabado.

---

## 5. Modos de Operación

1. **Modo Normal (Marcación)**:
   * Al acercarse (<12 cm), el LCD solicitará ingresar su ID y presionar `#`.
   * Introducir ID (Ej: `5#`) y colocar el dedo vivo en el sensor.
   * El sistema emite un Beep, ilumina el LED verde y registra la asistencia enviando `POST /api/v1/attendance`.

2. **Modo Enrolamiento (Registro)**:
   * Presionar la tecla `*` en el teclado.
   * Ingresar el ID de sensor a asignar y el código docente.
   * El sensor solicitará 2 pasadas del mismo dedo (`Dedo 1 de 2` y `Dedo 2 de 2`).
   * Guarda la plantilla en la memoria física del R307S y envía `POST /api/v1/biometria/enroll`.

3. **Modo Eliminación**:
   * Presionar la tecla `A` en el teclado.
   * Ingresar el ID a eliminar y presionar `#`.
