# 🖐️ GUÍA PASO A PASO — Lector de Huella ESP32 + R307 para el Sistema DAC

> Léela despacio. Haz **un paso a la vez** y **marca cada casilla con una X**
> cuando lo termines. Si algo falla, ve a la sección **"Problemas frecuentes"** al final.

---

## ✅ ¿Qué vas a construir?

Un **quiosco de asistencia**: el docente pone el dedo, el sensor R307 reconoce su
huella, el ESP32 se conecta por WiFi al servidor (tu proyecto Laravel) y se registra
la asistencia **PUNTUAL** o **TARDANZA** automáticamente.

El sistema quedará así:

```
    [Docente pone el dedo]
             │
        [Sensor R307]
             │
        [ESP32] ── WiFi ──► [Servidor Laravel (XAMPP)]
             │                    │
       [LCD + LEDs + Buzzer]   [Base de datos]
```

---

## 📦 Materiales (los que ya tienes)

| # | Material | Cantidad |
|---|----------|----------|
| 1 | ESP32 DevKit V1 (30 pines) | 1 |
| 2 | Placa de expansión para ESP32 30 pines | 1 |
| 3 | Sensor de huella **R307** | 1 |
| 4 | Fuente DC 12V / 2A | 1 |
| 5 | LCD 16x2 con módulo **I2C** | 1 |
| 6 | Teclado matricial 4x4 | 1 |
| 7 | Sensor ultrasónico HC-SR04 | 1 |
| 8 | LEDs rojo, verde, amarillo | 3 |
| 9 | Buzzer | 1 |
| 10 | Cables, protoboard, gabinete | - |

**Extra que necesitas:** resistencias de **220 Ω** (3, una por LED) y un módulo
reductor **LM2596** (buck) **SOLO si** tu placa de expansión no tiene entrada de 12V
(se explica en el Paso 1). También un cable USB para programar el ESP32.

---

## 🔌 PASO 1 — Conectar los cables (¡lo más importante!)

### 1.1 Primero: la alimentación (¡MUCHO CUIDADO!)

- El ESP32 y casi todo se alimentan con **5V**.
- Tu fuente es de **12V**. Entonces:

> ⚠️ **NUNCA** conectes 12V directo al ESP32 (ni a VIN ni a 5V). ¡Se quema!

**Mira tu placa de expansión:**
- Si tiene una **bornera o conector DC con 2 tornillos** (suele decir "12V~24V IN" o
  tiene un diodo/fusible) → puedes conectar la fuente 12V AHÍ.
- Si **solo tiene pines 5V/GND/3V3** (la mayoría) → necesitas el módulo **LM2596**:

**Cómo usar el LM2596:**
1. Conecta la fuente 12V a la bornera **IN** del LM2596 (IN+ rojo, IN− negro).
2. Gira el tornillito de ajuste hasta que el multímetro marque **5.0V** en la salida.
   (Sin multímetro: gira media vuelta hacia "menos" y prueba; verás el LED del ESP32).
3. Conecta la salida **OUT+** a la línea **5V** de la protoboard y **OUT−** a **GND**.

> 💡 Más fácil aún: mientras pruebas en tu casa, **alimenta el ESP32 por el cable USB**
> (el ESP32 regala 5V por sus pines 5V/VIN). El 12V + LM2596 se usan para el gabinete final.

### 1.2 Conecta todo (tabla de conexiones)

Sigue esta tabla **letra por letra**. Revisa cada cable **2 veces** antes de encender.

| Módulo | Pin del módulo | Conectar a | Nota |
|--------|----------------|------------|------|
| **R307** | Rojo | 5V | Alimentación 5V |
| **R307** | Negro | GND | Tierra |
| **R307** | Amarillo (TX) | **GPIO16** | sale del sensor → entra al ESP32 |
| **R307** | Blanco (RX) | **GPIO17** | entra al sensor → sale del ESP32 |
| **LCD I2C** | VCC | 5V | |
| **LCD I2C** | GND | GND | |
| **LCD I2C** | SDA | **GPIO21** | |
| **LCD I2C** | SCL | **GPIO22** | |
| **Teclado 4x4** | Fila 1 | **GPIO13** | (pin R1 del teclado) |
| **Teclado 4x4** | Fila 2 | **GPIO12** | |
| **Teclado 4x4** | Fila 3 | **GPIO14** | |
| **Teclado 4x4** | Fila 4 | **GPIO27** | |
| **Teclado 4x4** | Columna 1 | **GPIO26** | (pin C1 del teclado) |
| **Teclado 4x4** | Columna 2 | **GPIO25** | |
| **Teclado 4x4** | Columna 3 | **GPIO33** | |
| **Teclado 4x4** | Columna 4 | **GPIO32** | |
| **LED verde** | + (pata larga) | **GPIO4** | con resistor 220 Ω en serie |
| **LED verde** | − (pata corta) | GND | |
| **LED amarillo** | + | **GPIO5** | con resistor 220 Ω |
| **LED amarillo** | − | GND | |
| **LED rojo** | + | **GPIO2** | con resistor 220 Ω |
| **LED rojo** | − | GND | |
| **Buzzer** | + | **GPIO18** | |
| **Buzzer** | − | GND | |
| **HC-SR04** | VCC | 5V | |
| **HC-SR04** | GND | GND | |
| **HC-SR04** | TRIG | **GPIO19** | |
| **HC-SR04** | ECHO | **GPIO23** | ver nota ⬇️ |

> **Nota HC-SR04:** el pin ECHO del sensor entrega 5V y el ESP32 trabaja a 3.3V.
> Lo más seguro es poner un divisor de voltaje: un resistor de **1 kΩ** entre
> `ECHO` y `GPIO23`, y un resistor de **2 kΩ`** de `GPIO23` a GND.
> (Si no tienes esos resistores, en la práctica también funciona conectado directo,
> pero el divisor es lo correcto.)

**El sensor R307 tiene un conector de 4 cables (o cables sueltos):**

```
   R307                      ESP32
 ┌──────────┐
 │ ROJO ────┼──────────────► 5V    (rojo = positivo)
 │ NEGRO ───┼──────────────► GND   (negro = tierra)
 │ AMARILLO ┼──────────────► GPIO16 (TX del sensor va al RX del ESP32)
 │ BLANCO ──┼──────────────► GPIO17 (RX del sensor viene del TX del ESP32)
 └──────────┘
```

> ⚠️ **Importante:** los cables TX/RX se **cruzan**. Amarillo → 16, Blanco → 17.
> Si lo pones al revés, el LCD dirá "R307 NO detecta".

### 1.3 Revisa tu cableado con este dibujo

```
       5V ──┬────── Rojo R307
            ├────── VCC LCD
            ├────── VCC HC-SR04
        GND ─┬────── Negro R307
             ├────── GND LCD
             ├────── GND HC-SR04
             ├────── cátodos LEDs (−)
             ├────── Buzzer (−)

  GPIO16 ──── RX ← TX (amarillo) R307
  GPIO17 ──── TX → RX (blanco)   R307
  GPIO21 ──── SDA LCD
  GPIO22 ──── SCL LCD
  GPIO19 ──── TRIG HC-SR04
  GPIO23 ──── ECHO HC-SR04
  GPIO4  ──── LED VERDE (+)
  GPIO5  ──── LED AMARILLO (+)
  GPIO2  ──── LED ROJO (+)
  GPIO18 ──── Buzzer (+)
  13,12,14,27 ── filas del teclado
  26,25,33,32 ── columnas del teclado
```

**Cuando todo esté conectado:**
- [ ] R307 con 5V y GND y sus 2 cables TX/RX cruzados
- [ ] LCD con SDA/SCL y alimentación
- [ ] LEDs con sus resistencias
- [ ] Buzzer conectado
- [ ] HC-SR04 conectado
- [ ] Teclado conectado (8 pines)
- [ ] Revisé cada cable una segunda vez 🔍

---

## 💻 PASO 2 — Instalar Arduino IDE

1. Ve a https://www.arduino.cc/en/software y descarga **Arduino IDE 2** (Windows).
2. Instálalo con **Siguiente / Siguiente / Instalar** (todo por defecto).
3. Abre Arduino IDE.

### 2.1 Agregar la placa ESP32 (se hace una sola vez)

1. En el menú: **Archivo → Preferencias**.
2. En el campo **"URLs adicionales de Boards Manager"** pega esto:

```
https://dl.espressif.com/dl/package_esp32_index.json
```

3. Clic en **OK**.
4. Ve a **Herramientas → Board → Boards Manager** (o el ícono del bloque).
5. Escribe `esp32` en la casilla de búsqueda.
6. Encuentra **"esp32 by Espressif Systems"** → clic en **Install**. (Espera a que
   termine; puede tardar varios minutos. Necesitas internet).

### 2.2 Conectar el ESP32 a la computadora

1. Conecta el ESP32 a tu PC con el **cable USB**.
2. En Windows abre el **Administrador de dispositivos → Puertos (COM y LPT)**.
   Deberías ver algo como `COM3`, `COM5`, etc. **Anota ese número.**
   - Si no aparece, prueba otro cable USB (algunos cables solo cargan y no transmiten).

---

## 📚 PASO 3 — Instalar las bibliotecas (se hace una sola vez)

En Arduino IDE: **Herramientas → Manage Libraries** (o ícono de libros 📚).
Busca **una por una** y haz clic en **Install**:

1. **Adafruit Fingerprint Sensor Library** (de *Adafruit*) → Install
   (te preguntará si instalar dependencias → **Install All**)
2. **LiquidCrystal I2C** (de *Frank de Brabander*) → Install
3. **Keypad** (de *Mark Stanley*) → Install
4. **ArduinoJson** (de *Benoit Blanchon*) → Install (instala la más reciente, v7)

> Debes tener **las 4** (Adafruit BusIO se instala sola con la primera).

- [ ] 4 bibliotecas instaladas ✅

---

## 🌐 PASO 4 — Crear el "Lector" en el sistema web (para obtener el token)

El ESP32 necesita un **token secreto** para poder hablar con tu servidor.

1. Enciende XAMPP (**Apache** y **MySQL**).
2. Entra como **Administrador**: `http://localhost/PROYECTO-DAC/sistema-biometrico-dac/public/login`
   (admin@dac.edu.bo / Admin@2024!)
3. En el menú lateral ve a **Lectores ESP32** → **Nuevo Lector** (o el botón de crear).
4. Llena:
   - **Código:** `ESP32-AULA-101`
   - **Nombre:** `Lector Aula 101`
   - **Ubicación:** `Aula 101 - Bloque A`
   - (MAC puedes dejarla vacía o poner la del ESP32, no es obligatoria)
5. **Guardar.** El sistema generará automáticamente un **Token API** (una cadena larga
   de letras y números). **CÓPIALA** en un bloc de notas, la necesitarás en el Paso 5.

> Si el panel no te muestra el token después de guardar, entra a
> **Lectores ESP32 → el lector → Editar** o revisa la tabla.

---

## ✏️ PASO 5 — Poner TUS datos en el código

1. En Arduino IDE: **Archivo → Abrir** y busca el archivo
   `hardware/esp32_dac/esp32_dac.ino` (dentro de tu carpeta del proyecto).
2. Arriba del archivo hay una sección llamada **"CONFIGURACIÓN"**. Cambia estos 5 datos:

```cpp
const char* WIFI_SSID     = "TU_NOMBRE_DE_WIFI";           // ← tu WiFi
const char* WIFI_PASSWORD = "TU_CONTRASENA_DE_WIFI";       // ← tu contraseña

const String API_BASE = "http://192.168.1.50/PROYECTO-DAC/sistema-biometrico-dac/public/api/v1";
//                            ⬆ este número debe ser la IP de tu PC

const String API_TOKEN = "PEGA_AQUI_EL_TOKEN_DEL_LECTOR";  // ← token del Paso 4
```

**¿Cómo saber la IP de tu PC?**
1. En Windows presiona `Windows + R`, escribe `cmd` y Enter.
2. Escribe `ipconfig` y Enter.
3. Busca la línea **"Dirección IPv4"** (por ejemplo `192.168.1.50`) y ponla en `API_BASE`.
   ⚠️ **No** escribas `localhost`, el ESP32 no sabrá qué significa.

> **Importante:** la computadora del servidor y el ESP32 deben estar en la **misma red**.
> También puedes dejar el token de ejemplo que el sistema ya tiene si creaste el lector
> desde una guía anterior, pero es más limpio usar uno nuevo.

---

## 📤 PASO 6 — Subir el programa al ESP32

1. En Arduino IDE selecciona:
   - **Herramientas → Board → ESP32 Arduino → DOIT ESP32 DEVKIT V1**
   - **Herramientas → Port → el COM que anotaste** (ej. COM3)
2. Clic en el botón **"Subir"** (la flechita hacia la derecha ➡️).
   - Espera que compile (puede tardar 1-2 minutos la primera vez).
   - Verás en la ventana inferior `Connecting........_____.....` y luego
     `Hard resetting via RTS pin...`
   - Si falla con `A fatal error occurred: Failed to connect`, **mantén presionado
     el botón "BOOT"** del ESP32 mientras se sube, y suéltalo cuando veas
     `Connecting...`.
3. Cuando termine, abre el **Monitor Serie** (botón con forma de lupa 🔍, o
   Herramientas → Monitor Serie). Pon la velocidad **115200** (abajo a la derecha).
   Verás mensajes de inicio.

**En el LCD debe aparecer:**
```
R307 detectado    →  luego →   WiFi OK!      →  luego →   Listo para marcar
```
> Si dice `R307 NO detecta`, revisa los cables TX/RX. Si dice `SIN WIFI`,
> revisa el nombre y contraseña del WiFi.

---

## 🧪 PASO 7 — Pruebas

### Prueba 1: ¿Hay docentes registrados?
Entra como Admin → **Docentes y Usuarios**. Tiene que haber docentes con su
**Código de docente** (ej. `DOC-1001`). Si no hay, créalos.

### Prueba 2: Registrar la huella de un docente (enrolamiento)
Ahora el administrador va al quiosco y hace esto:

1. Presiona **`*`** en el teclado.
   - LCD: `ID del sensor:` → presiona **`#`** para aceptar el número sugerido
     (es el "próximo ID libre").
   - LCD: `Codigo docente:` → escribe el número del código **sin** las letras,
     por ejemplo **`1001`** para el docente `DOC-1001` → presiona **`#`**.
2. LCD: `Dedo 1 de 3 — Coloque su dedo` → el docente apoya el dedo en el sensor.
3. LCD: `Retire el dedo` → el docente retira el dedo.
4. Se repite **3 veces** en total (dedo 1, dedo 2, dedo 3).
5. Si todo salió bien: 🔊 pitido, LED **verde** y LCD `¡ENROLADO! ID 1`.
6. **En la web:** ahora el docente aparece vinculado en **Enrolamiento Biométrico**
   (o puedes confirmar en la tabla de huellas que tiene `R307:1`).

> 💡 La huella queda guardada **en el sensor** (ID 1) y **en el servidor**
> (docente DOC-1001 ↔ ID 1). Si el docente no aparece, revisa que escribiste bien
> el código (ej. `1001`).

### Prueba 3: Marcar asistencia
1. Acércate al sensor ultrasónico (a menos de ~12 cm) o pon el dedo en el sensor.
2. LCD: `Ponga su dedo` → apoya el dedo ~1 segundo.
3. LCD: `Buscando...` → luego 🔊 y LED verde + `Bienvenido! <nombre>`.
4. En la web: **Dashboard** → verás un nuevo registro de asistencia. Si la hora de
   marcación supera la tolerancia del horario, saldrá **TARDANZA**; si no, **PUNTUAL**.

### Prueba 4: Eliminar una huella (si se equivocaron)
1. Presiona **`A`** en el teclado.
2. Escribe el ID a eliminar y presiona **`#`**.
3. LCD: `ELIMINADO ID n`. Se borra del sensor y del servidor.

---

## 🆘 PROBLEMAS FRECUENTES

| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| LCD no enciende / solo muestra cuadros | Dirección I2C incorrecta o cableado | Prueba con `0x3F` en vez de `0x27` (ver anexo). Revisa SDA/SCL y alimentación |
| LCD dice `R307 NO detecta` | TX/RX cruzados mal | Amarillo → GPIO16, Blanco → GPIO17. Revisa que el R307 reciba 5V |
| `Failed to connect` al subir | Botón BOOT | Mantén presionado **BOOT** al subir y suéltalo al ver `Connecting...` |
| `SIN WIFI` | Nombre/contraseña mal | Revisa `WIFI_SSID` y `WIFI_PASSWORD`. Usa una red de 2.4GHz |
| Se conecta a WiFi pero "Error config" | Token mal o IP mal | Revisa `API_TOKEN` y `API_BASE`. Prueba la URL en tu navegador del PC |
| El ESP32 no llega al servidor | Firewall de Windows | Permite Apache/puerto 80 en red privada, o prueba con firewall apagado |
| Huella no reconocida | Dedo sucio, mal registrada, ID borrado | Limpia el dedo, vuelve a enrolar (Paso 7, Prueba 2) |
| `Fallo en servidor` al enrolar | Código de docente no existe | Verifica el código exacto en Docentes y Usuarios (sin espacios) |
| No aparece la asistencia | Token mal | Revisa que el lector exista en web y el token sea el correcto |

> **Tip:** revisa siempre el **Monitor Serie** del Arduino IDE; ahí se imprime cada
> respuesta del servidor.

---

## 📍 ANEXO A — Escáner de dirección I2C del LCD

Si el LCD no enciende, sube este programa **mínimo** para descubrir la dirección:

```cpp
#include <Wire.h>
void setup() {
  Wire.begin(21, 22);
  Serial.begin(115200);
  Serial.println("Buscando dispositivos I2C...");
  for (byte dir = 1; dir < 127; dir++) {
    Wire.beginTransmission(dir);
    if (Wire.endTransmission() == 0) {
      Serial.print("LCD encontrado en 0x");
      Serial.println(dir, HEX);
    }
  }
}
void loop() {}
```

Si imprime `0x27` deja `LCD_ADDR = 0x27`; si imprime `0x3F`, cámbialo a `0x3F`.

---

## 📍 ANEXO B — Mapa de pines (referencia rápida)

| Función | GPIO ESP32 |
|---------|-----------|
| RX del R307 (TX del sensor) | 16 |
| TX del R307 (RX del sensor) | 17 |
| LCD SDA | 21 |
| LCD SCL | 22 |
| Teclado Filas R1–R4 | 13, 12, 14, 27 |
| Teclado Columnas C1–C4 | 26, 25, 33, 32 |
| LED Verde | 4 |
| LED Amarillo | 5 |
| LED Rojo | 2 |
| Buzzer | 18 |
| Ultrasonido TRIG | 19 |
| Ultrasonido ECHO | 23 |

---

## 🎉 ¡Listo!

Con esto tu quiosco biométrico queda integrado al proyecto:
- La asistencia se registra automáticamente en **Asistencias y Reportes**.
- Los **KPIs del Dashboard** se actualizan.
- El lector aparece **ONLINE** en **Lectores ESP32** (gracias al heartbeat cada 30 s).

Si necesitas cambiar de red o de servidor, solo cambia los 5 datos del Paso 5 y
vuelve a subir el programa.
