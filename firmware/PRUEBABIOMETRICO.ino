/*
 * ============================================================================
 *  SISTEMA BIOMÉTRICO DAC - Firmware ESP32 + Sensor R307
 * ============================================================================
 *
 *  Esta es la "cabeza" del sistema: el ESP32 con el sensor de huella R307
 *  registra la asistencia de los docentes y la envía al servidor (Laravel).
 *
 *  MATERIALES QUE USA:
 *    - ESP32 DevKit V1 (30 pines)
 *    - Sensor de huella R307
 *    - LCD 16x2 con módulo I2C
 *    - Teclado matricial 4x4
 *    - 3 LEDs (verde, amarillo, rojo) + resistencias 220 ohm
 *    - Buzzer
 *    - Sensor ultrasónico HC-SR04
 *
 *  BIBLIOTECAS NECESARIAS (Arduino IDE → Administrador de librerías):
 *    1. "Adafruit Fingerprint Sensor Library"   (Adafruit)
 *    2. "Adafruit BusIO"                        (Adafruit) [se instala sola]
 *    3. "LiquidCrystal I2C"                     (Frank de Brabander)
 *    4. "Keypad"                                (Mark Stanley)
 *    5. "ArduinoJson"                           (Benoit Blanchon) - v7 o reciente
 *
 *  CONFIGURA TU PLACA ASÍ EN ARDUINO IDE:
 *    Herramientas → Board: "DOIT ESP32 DEVKIT V1"
 *    Herramientas → Port: (el puerto COM de tu ESP32)
 *
 * ============================================================================
 *  ¡¡LO PRIMERO: CAMBIA LOS 5 DATOS DE LA SECCIÓN "CONFIGURACIÓN" DE ABAJO!!
 *  (nombre del WiFi, contraseña, dirección del servidor y token del lector)
 * ============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>
#include <Adafruit_Fingerprint.h>

/* ╔═══════════════════════════════════════════════════════════════════╗
   ║                 1) CONFIGURACIÓN  (CÁMBIA ESTO)                   ║
   ╚═══════════════════════════════════════════════════════════════════╝ */

// Tu red WiFi (el ESP32 debe estar en la MISMA red que la computadora del servidor)
const char* WIFI_SSID     = "FLIA H&A";
const char* WIFI_PASSWORD = "Amo-mi-FAMILIA-86!";

const String API_BASE = "http://192.168.100.32/PROYECTO-DAC/sistema-biometrico-dac/public/api/v1";

const String API_TOKEN   = "dac_esp32_bearer_token_secure_2024";

const String DEVICE_MAC      = "24:0A:C4:DA:C0:01";
const String FIRMWARE_VERSION = "2.1.0";

// Dirección I2C del LCD. Si no aparece nada en pantalla, cambia 0x27 por 0x3F.
const int LCD_ADDR = 0x27;

/* ╔═══════════════════════════════════════════════════════════════════╗
   ║          2) PINES (CABLEADO) — revisa la tabla de la guía           ║
   ╚═══════════════════════════════════════════════════════════════════╝ */

// Sensor R307 conectado al Serial2 del ESP32
#define FP_RX 16        // R307 TX (amarillo) → GPIO16
#define FP_TX 17        // R307 RX (blanco)   → GPIO17

// LCD I2C
#define LCD_SDA 21
#define LCD_SCL 22

// LEDs
#define LED_VERDE    4
#define LED_AMARILLO 5
#define LED_ROJO     2

// Buzzer
#define BUZZER 18

// Sensor ultrasónico HC-SR04
#define ULTRA_TRIG 19
#define ULTRA_ECHO 23

/* ╔═══════════════════════════════════════════════════════════════════╗
   ║          3) CONFIGURACIÓN INTERNA (no toques nada de aquí)          ║
   ╚═══════════════════════════════════════════════════════════════════╝ */

// Teclado 4x4
const byte ROWS = 4;
const byte COLS = 4;
char keys[ROWS][COLS] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};
byte rowPins[ROWS] = {13, 12, 14, 27};
byte colPins[COLS] = {26, 25, 33, 32};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

// Sensor de huella
HardwareSerial fingerSerial(2);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerSerial);

// LCD
LiquidCrystal_I2C lcd(LCD_ADDR, 16, 2);

// Mapa de huellas descargado del servidor (ID del R307 → nombre del docente)
#define MAX_FP 50
int    fpIds[MAX_FP];
char   fpNames[MAX_FP][40];
int    fpCount = 0;
int    nextFreeR307Id = 1;

// Temporizadores
unsigned long lastHeartbeat = 0;
unsigned long lastScan      = 0;
const unsigned long HEARTBEAT_INTERVAL = 30000; // cada 30 segundos

/* ════════════════════════════════════════════════════════════════════
   FUNCIONES AYUDANTES PARA LA PANTALLA Y LOS SONIDOS
   ════════════════════════════════════════════════════════════════════ */

void lcdPrint(const String &line1, const String &line2 = "") {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1.substring(0, 16));
  lcd.setCursor(0, 1);
  lcd.print(line2.substring(0, 16));
}

void beep(int veces, int duracionMs) {
  for (int i = 0; i < veces; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(duracionMs);
    digitalWrite(BUZZER, LOW);
    delay(duracionMs);
  }
}

/* ════════════════════════════════════════════════════════════════════
   WIFI + COMUNICACIÓN CON EL SERVIDOR (Laravel)
   ════════════════════════════════════════════════════════════════════ */

void conectarWiFi() {
  lcdPrint("Conectando...", "WiFi");
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  int intentos = 0;
  while (WiFi.status() != WL_CONNECTED && intentos < 30) {
    delay(1000);
    intentos++;
    lcdPrint("Conectando...", String("Intento ") + intentos);
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("[OK] WiFi conectada. IP del ESP32: ");
    Serial.println(WiFi.localIP());
    lcdPrint("WiFi OK!", WiFi.localIP().toString());
  } else {
    Serial.println("[FALLO] No se pudo conectar al WiFi.");
    lcdPrint("SIN WIFI", "Revisa clave");
  }
  delay(1500);
}

// Envía una petición POST con JSON y devuelve el cuerpo de la respuesta.
String apiPost(const String &ruta, const String &jsonBody) {
  if (WiFi.status() != WL_CONNECTED) return "";
  HTTPClient http;
  http.begin(API_BASE + ruta);
  http.addHeader("Authorization", "Bearer " + API_TOKEN);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(10000);
  int code = http.POST(jsonBody);
  String resp = (code > 0) ? http.getString() : "";
  http.end();
  return resp;
}

// Envía una petición GET con token y devuelve el cuerpo de la respuesta.
String apiGet(const String &ruta) {
  if (WiFi.status() != WL_CONNECTED) return "";
  HTTPClient http;
  http.begin(API_BASE + ruta);
  http.addHeader("Authorization", "Bearer " + API_TOKEN);
  http.addHeader("Accept", "application/json");
  http.setTimeout(10000);
  int code = http.GET();
  String resp = (code > 0) ? http.getString() : "";
  http.end();
  return resp;
}

// Avisa al servidor que el lector sigue encendido (heartbeat).
void enviarHeartbeat() {
  String body = String("{\"firmware_version\":\"") + FIRMWARE_VERSION
              + "\",\"mac_address\":\"" + DEVICE_MAC + "\"}";
  apiPost("/device/heartbeat", body);
}

// Descarga del servidor el mapa de huellas (ID R307 → nombre) y el próximo ID libre.
void sincronizarConfig() {
  String resp = apiGet("/device/config");
  if (resp.length() < 3) {
    lcdPrint("Error config", "revisa token/red");
    delay(1500);
    return;
  }

  JsonDocument doc;
  if (deserializeJson(doc, resp)) {
    lcdPrint("JSON invalido", "del servidor");
    delay(1500);
    return;
  }

  nextFreeR307Id = doc["next_free_r307_id"] | 1;

  fpCount = 0;
  JsonArray fps = doc["fingerprints"].as<JsonArray>();
  for (JsonObject o : fps) {
    if (fpCount >= MAX_FP) break;
    fpIds[fpCount] = o["r307_id"] | -1;
    const char* nombre = o["name"] | "---";
    strncpy(fpNames[fpCount], nombre, 39);
    fpNames[fpCount][39] = '\0';
    fpCount++;
  }

  lcdPrint("Cargadas " + String(fpCount) + " h.", "Listo para marcar");
  Serial.print("[OK] Config sincronizada. Huellas en servidor: ");
  Serial.print(fpCount);
  Serial.print(" | Próximo ID libre: ");
  Serial.println(nextFreeR307Id);
  delay(1200);
}

// Devuelve el nombre del docente para un ID del R307 (o "ID n" si no se conoce).
String nombreDeId(int id) {
  for (int i = 0; i < fpCount; i++) {
    if (fpIds[i] == id) return String(fpNames[i]);
  }
  return "ID " + String(id);
}

/* ════════════════════════════════════════════════════════════════════
   LECTURA DE TECLADO
   ════════════════════════════════════════════════════════════════════ */

// Lee números desde el teclado hasta que se presiona #.
// Devuelve "" si se cancela con * o si pasa demasiado tiempo.
String leerNumeros(const String &titulo, const String &defecto) {
  String numero = defecto;
  unsigned long inicio = millis();
  lcdPrint(titulo, defecto + "_");

  while (millis() - inicio < 30000) {
    char k = keypad.getKey();
    if (k) {
      if (k == '#') {
        lcdPrint(titulo, numero);
        delay(200);
        return numero;
      }
      if (k == '*') return "";                     // cancelar
      if (k >= '0' && k <= '9') {                  // solo dígitos
        if (numero.length() < 10) numero += k;
      }
      lcdPrint(titulo, numero + "_");
    }
    delay(20);
  }
  return "";
}

/* ════════════════════════════════════════════════════════════════════
   ENROLAMIENTO DE UNA HUELLA (proceso en 2 pasadas de captura)
   ════════════════════════════════════════════════════════════════════ */

// Espera a que el dedo se apoye en el sensor (con tiempo máximo).
// Regresa:  true  = dedo listo    false = se acabó el tiempo o se canceló
bool esperarDedo(unsigned long tiempoMs) {
  unsigned long inicio = millis();
  while (millis() - inicio < tiempoMs) {
    char k = keypad.getKey();
    if (k == '*') { Serial.println(">> CANCELADO con tecla *"); return false; }
    int p = finger.getImage();
    if (p == FINGERPRINT_OK) { Serial.println(">> DEDO DETECTADO OK"); return true; }
    if (p != FINGERPRINT_NOFINGER) {
      Serial.print(">> getImage fallo codigo: "); Serial.println(p);
    }
    delay(50);
  }
  Serial.println(">> TIMEOUT: se acabo el tiempo sin detectar dedo");
  return false;
}

// Espera a que el dedo se retire del sensor.
void esperarRetiro() {
  while (finger.getImage() != FINGERPRINT_NOFINGER) {
    delay(50);
  }
}

// Enrola (guarda) una huella en el sensor R307 bajo el ID indicado.
// Regresa: FINGERPRINT_OK = éxito, otro valor = error.
uint8_t enrolar(int id) {
  uint8_t p;

  // Pasada 1 (mochila 1)
  lcdPrint("Dedo 1 de 2", "Coloque su dedo");
  if (!esperarDedo(60000)) return FINGERPRINT_PACKETRECIEVEERR;
  p = finger.image2Tz(1);
  Serial.print(">> Pasada1 image2Tz: "); Serial.println(p);
  if (p != FINGERPRINT_OK) return p;
  lcdPrint("Dedo 1 de 2", "Retire el dedo");
  esperarRetiro(); delay(300);

  // Pasada 2 (mochila 2)
  lcdPrint("Dedo 2 de 2", "Coloque su dedo");
  if (!esperarDedo(60000)) return FINGERPRINT_PACKETRECIEVEERR;
  p = finger.image2Tz(2);
  Serial.print(">> Pasada2 image2Tz: "); Serial.println(p);
  if (p != FINGERPRINT_OK) return p;
  lcdPrint("Dedo 2 de 2", "Retire el dedo");
  esperarRetiro(); delay(300);

  // Combinar las 2 mochilas y guardar
  delay(200);
  p = finger.createModel();
  Serial.print(">> createModel: "); Serial.println(p);
  if (p != FINGERPRINT_OK) return p;

  lcdPrint("Guardando...", "ID " + String(id));
  p = finger.storeModel(id);
  Serial.print(">> storeModel: "); Serial.println(p);
  finger.getTemplateCount();
  Serial.print(">> Huellas en sensor ahora: "); Serial.println(finger.templateCount);
  return p;
}

/* ════════════════════════════════════════════════════════════════════
   MODOS: MARCAR, REGISTRAR, ELIMINAR
   ════════════════════════════════════════════════════════════════════ */

// MODO NORMAL: el docente pone el dedo y se registra la asistencia.
void modoMarcar(int id) {
  String nombre = nombreDeId(id);

  digitalWrite(LED_VERDE, HIGH);
  beep(1, 150);

  // Enviar la marcación al servidor
  String body = String("{\"r307_id\":") + id + "}";
  String resp = apiPost("/attendance", body);
  Serial.print("[MARCACION] r307_id=");
  Serial.print(id);
  Serial.print(" -> respuesta: ");
  Serial.println(resp);

  String mensaje;
  if (resp.length() < 3) {
    mensaje = "SIN CONEXION";
  } else {
    JsonDocument doc;
    if (!deserializeJson(doc, resp)) {
      mensaje = String((const char*)(doc["message"] | "OK"));
    } else {
      mensaje = "ERROR";
    }
  }

  lcdPrint("Bienvenido!", nombre);
  delay(2500);
  lcdPrint(mensaje, nombre);
  delay(3500);

  digitalWrite(LED_VERDE, LOW);
  lcdPrint("Listo para marcar", "Coloque su dedo");
}

// MODO REGISTRAR: el administrador vincula un docente a un ID del R307.
void modoRegistrar() {
  lcdPrint("REGISTRAR HUELLA", "tecla * cancela");

  // 1) Pedir el ID del sensor (por defecto el próximo libre)
  String idStr = leerNumeros("ID del sensor:", String(nextFreeR307Id));
  if (idStr.length() == 0) { lcdPrint("Cancelado", "tecla * "); delay(1200); return; }
  int id = idStr.toInt();
  if (id < 1 || id > 300) { lcdPrint("ID invalido", "usa 1 a 300"); delay(1500); return; }

  // 2) Pedir el código del docente (solo el número, ej. 1001)
  String codigo = leerNumeros("Codigo docente:", "1001");
  if (codigo.length() == 0) { lcdPrint("Cancelado", "tecla * "); delay(1200); return; }

  // 3) Enrolar la huella en el sensor (2 pasadas)
  lcdPrint("Enrolar ID " + String(id), "tecla * cancela");
  uint8_t resultado = enrolar(id);

  if (resultado != FINGERPRINT_OK) {
    digitalWrite(LED_ROJO, HIGH);
    beep(3, 200);
    lcdPrint("ERROR " + String(resultado), "intenta de nuevo");
    delay(2500);
    digitalWrite(LED_ROJO, LOW);
    return;
  }

  // 4) Informar al servidor la asociación ID ↔ código
  String body = String("{\"codigo_docente\":\"") + codigo
              + "\",\"r307_id\":" + id
              + ",\"dedo_numero\":1,\"dedo_nombre\":\"Huella principal\"}";
  String resp = apiPost("/biometria/enroll", body);
  Serial.print("[ENROLL] Respuesta servidor: "); Serial.println(resp);

  digitalWrite(LED_VERDE, HIGH);
  beep(1, 300);

  if (resp.length() > 3 && resp.indexOf("\"success\":true") >= 0) {
    // Actualizar el mapa local
    if (fpCount < MAX_FP) {
      fpIds[fpCount] = id;
      strncpy(fpNames[fpCount], ("Docente " + String(codigo)).c_str(), 39);
      fpNames[fpCount][39] = '\0';
      fpCount++;
    }
    if (id >= nextFreeR307Id) nextFreeR307Id = id + 1;

    lcdPrint("¡ENROLADO!", "ID " + String(id));
  } else {
    // El servidor no aceptó; borramos del sensor para no dejar huérfana
    finger.deleteModel(id);
    lcdPrint("Fallo en servidor", "revisa el codigo");
  }
  delay(2500);
  digitalWrite(LED_VERDE, LOW);
  lcdPrint("Listo para marcar", "Coloque su dedo");
}

// MODO ELIMINAR: borra una huella del sensor y del servidor.
void modoEliminar() {
  lcdPrint("ELIMINAR HUELLA", "tecla * cancela");

  String idStr = leerNumeros("ID a eliminar:", "");
  if (idStr.length() == 0) { lcdPrint("Cancelado", "tecla * "); delay(1200); return; }
  int id = idStr.toInt();

  // 1) Borrar del sensor R307
  finger.deleteModel(id);

  // 2) Borrar del servidor
  String body = String("{\"r307_id\":") + id + "}";
  apiPost("/biometria/delete", body);

  // 3) Quitar del mapa local
  for (int i = 0; i < fpCount; i++) {
    if (fpIds[i] == id) {
      for (int j = i; j < fpCount - 1; j++) {
        fpIds[j] = fpIds[j + 1];
        strcpy(fpNames[j], fpNames[j + 1]);
      }
      fpCount--;
      break;
    }
  }

  digitalWrite(LED_AMARILLO, HIGH);
  beep(2, 200);
  lcdPrint("ELIMINADO", "ID " + String(id));
  delay(2500);
  digitalWrite(LED_AMARILLO, LOW);
  lcdPrint("Listo para marcar", "Coloque su dedo");
}

/* ════════════════════════════════════════════════════════════════════
   SENSOR ULTRASÓNICO (detecta que alguien se acerca)
   ════════════════════════════════════════════════════════════════════ */

long medirDistanciaCm() {
  digitalWrite(ULTRA_TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(ULTRA_TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(ULTRA_TRIG, LOW);
  long duracion = pulseIn(ULTRA_ECHO, HIGH, 30000);
  if (duracion == 0) return 400;   // sin eco → lejos
  return duracion / 58;
}

/* ════════════════════════════════════════════════════════════════════
   MARCACIÓN POR HUELLA (modo normal)
   ════════════════════════════════════════════════════════════════════ */

// Escanea la huella y, si hay coincidencia, marca la asistencia.
void escanearYMarcar() {
  // MODO COMPATIBILIDAD: el docente escribe su ID y se verifica que haya dedo vivo
  lcdPrint("Ingrese ID sensor", "y presione #");
  
  String idStr = leerNumeros("ID sensor:", "");
  if (idStr.length() == 0) {
    lcdPrint("Listo para marcar", "Coloque su dedo");
    return;   // cancelo con *
  }
  int id = idStr.toInt();

  lcdPrint("Ponga su dedo", "verificacion viva");
  digitalWrite(LED_AMARILLO, HIGH);
  
  unsigned long inicio = millis();
  bool dedo = false;
  while (millis() - inicio < 20000) {
    char k = keypad.getKey();
    if (k == '*') {
      digitalWrite(LED_AMARILLO, LOW);
      lcdPrint("Cancelado", "Listo para marcar");
      delay(1500);
      return;
    }
    if (finger.getImage() == FINGERPRINT_OK) { dedo = true; break; }
    delay(50);
  }
  digitalWrite(LED_AMARILLO, LOW);

  if (!dedo) {
    lcdPrint("Sin dedo", "intente de nuevo");
    delay(1500);
    lcdPrint("Listo para marcar", "Coloque su dedo");
    return;
  }

  int p = finger.image2Tz();
  if (p != FINGERPRINT_OK) {
    lcdPrint("No se pudo leer", "intente de nuevo");
    delay(1500);
    lcdPrint("Listo para marcar", "Coloque su dedo");
    return;
  }

  modoMarcar(id);   // envia la asistencia al servidor como siempre
}

/* ════════════════════════════════════════════════════════════════════
   ARRANQUE
   ════════════════════════════════════════════════════════════════════ */

void setup() {
  // Puerto serie de depuración (abrir el Monitor Serie a 115200 en Arduino IDE)
  Serial.begin(115200);
  delay(500);
  Serial.println();
  Serial.println("=== SISTEMA BIOMETRICO DAC - ESP32 R307 ===");

  // LED y buzzer
  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_AMARILLO, OUTPUT);
  pinMode(LED_ROJO, OUTPUT);
  pinMode(BUZZER, OUTPUT);

  // Ultrasónico
  pinMode(ULTRA_TRIG, OUTPUT);
  pinMode(ULTRA_ECHO, INPUT);

  digitalWrite(LED_VERDE, HIGH);
  beep(1, 200);
  digitalWrite(LED_VERDE, LOW);

  // LCD
  Wire.begin(LCD_SDA, LCD_SCL);
  lcd.init();
  lcd.backlight();
  lcdPrint("Sistema DAC", "Iniciando...");

  // Sensor R307
  fingerSerial.begin(57600, SERIAL_8N1, FP_RX, FP_TX);
  finger.begin(57600);

  if (finger.verifyPassword()) {
    Serial.println("[OK] Sensor R307 detectado.");
    finger.getTemplateCount();
    Serial.print(">> Huellas en sensor: "); Serial.println(finger.templateCount);
    finger.getParameters();
    Serial.print(">> Nivel de seguridad de fabrica: "); Serial.println(finger.security_level);
    finger.setSecurityLevel(2);
    Serial.println(">> Nivel ajustado a 2 (modo amable)");
    lcdPrint("R307 detectado", "OK");
  } else {
    Serial.println("[FALLO] No se encuentra el R307. Revisa los cables TX/RX y la alimentacion.");
    lcdPrint("R307 NO detecta", "revisa cableado");
    beep(3, 300);
    delay(2500);
  }
  delay(1200);

  // WiFi
  conectarWiFi();
  if (WiFi.status() == WL_CONNECTED) {
    enviarHeartbeat();
    sincronizarConfig();
  }

  lcdPrint("Listo para marcar", "Coloque su dedo");
}

/* ════════════════════════════════════════════════════════════════════
   BUCLE PRINCIPAL
   ════════════════════════════════════════════════════════════════════ */

void loop() {
  // Revisar WiFi (reconectar si se cayó)
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
    if (WiFi.status() == WL_CONNECTED) {
      enviarHeartbeat();
      sincronizarConfig();
    }
    delay(3000);
    return;
  }

  // Heartbeat cada 30 segundos
  if (millis() - lastHeartbeat > HEARTBEAT_INTERVAL) {
    lastHeartbeat = millis();
    enviarHeartbeat();
  }

  // Leer teclado
  char k = keypad.getKey();
  if (k == '*') {
    modoRegistrar();        // Registrar nueva huella
    lastScan = millis();
  } else if (k == 'A') {
    modoEliminar();         // Eliminar huella
    lastScan = millis();
  }

  // Si alguien se acerca al sensor (menos de 12 cm) y no estamos recién ocupados...
  long distancia = medirDistanciaCm();
  if (distancia < 12 && millis() - lastScan > 3000) {
    lastScan = millis();
    escanearYMarcar();
  }

  delay(20);
}
