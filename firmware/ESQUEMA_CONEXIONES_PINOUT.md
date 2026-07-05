# Diagrama de Conexiones y Pinout – Lector Biométrico ESP32 (PROYECTO-DAC)

Este documento detalla la asignación de pines y conexiones físicas entre la placa de desarrollo **ESP32 Dev Module**, el **Sensor de Huella Dactilar R307S**, la **Pantalla LCD 16x2 I2C**, el **Teclado Matricial 4x4**, **Buzzer** y **LEDs indicadoras**.

---

## 1. Tabla de Asignación de Pines (Pinout Map)

| Componente | Pin del Componente | Pin del ESP32 | Descripción |
|------------|-------------------|---------------|-------------|
| **Sensor R307S** | VCC (Rojo) | **5V (VIN)** | Alimentación principal (5V) |
| | GND (Negro) | **GND** | Tierra común |
| | TX (Amarillo) | **GPIO 16 (RX2)** | Recepción UART 2 del ESP32 |
| | RX (Blanco) | **GPIO 17 (TX2)** | Transmisión UART 2 del ESP32 |
| **LCD 16x2 I2C** | VCC | **5V / 3.3V** | Alimentación lógica |
| | GND | **GND** | Tierra común |
| | SDA | **GPIO 21** | Bus I2C Datos |
| | SCL | **GPIO 22** | Bus I2C Reloj |
| **Buzzer Pasivo** | Positivo (+) | **GPIO 25** | Señal PWM audio |
| | Negativo (-) | **GND** | Tierra |
| **LED Verde (Éxito)**| Ánodo (+) | **GPIO 32** (con res. 220Ω) | Indicador visual de éxito |
| | Cátodo (-) | **GND** | Tierra |
| **LED Rojo (Error)** | Ánodo (+) | **GPIO 33** (con res. 220Ω) | Indicador de error / rechazo |
| | Cátodo (-) | **GND** | Tierra |
| **Teclado 4x4** | Filas (R1-R4) | **GPIO 13, 12, 14, 27** | Escaneo de filas |
| | Columnas (C1-C4) | **GPIO 26, 25, 33, 32** | Escaneo de columnas (compartidos si aplica) |

---

## 2. Recomendaciones Electrónicas
1. **Alimentación Estable**: Utilizar una fuente externa regulada de 5V @ 2A para alimentar el ESP32 y el sensor R307S (el sensor óptico requiere hasta 120mA durante el escaneo).
2. **Resistencias de Limitación**: Conectar resistencias de `220Ω` en serie con el ánodo de los LEDs Verde y Rojo.
3. **Módulo I2C LCD**: Ajustar el potenciómetro de contraste azul en el adaptador I2C del LCD para asegurar legibilidad clara.
