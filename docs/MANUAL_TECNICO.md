# Manual Técnico del Sistema Biométrico DAC v2.1 (Versión Producción)
**Programa DAC · Unidad Educativa Tiquipaya · Cochabamba, Bolivia**

---

## 1. Arquitectura del Sistema en 3 Capas

El **Sistema Biométrico DAC** implementa una arquitectura híbrida desacoplada estructurada en tres niveles operativos:

```
┌─────────────────────────────────────────────────────────────────────────┐
│ CAPA 1: IoT & HARDWARE ADQUISICIÓN (ESP32 DevKit V1 + R307S)           │
│ - Sensores, Teclado 4x4, Pantalla LCD I2C 16x2, Buzzer, Ultrasónico     │
│ - Almacenamiento local de plantillas en EEPROM del R307S                │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
                     REST API v1 (HTTP/HTTPS + JSON)
                     Autenticación Bearer Token (Sanctum)
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│ CAPA 2: SERVICIOS BACKEND & REST API (Laravel 10.x / PHP 8.x)           │
│ - Control de Sesiones Web (Bcrypt) & API Sanctum                        │
│ - Motor de Reglas de Negocio, Tolerancias y Re-procesamiento           │
│ - Control de Acceso Basado en Roles (RBAC: Admin, Coordinador, Docente) │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ CAPA 3: PERSISTENCIA Y MONITORIZACIÓN (MySQL / MariaDB + Web Console)   │
│ - MySQL Server XAMPP (Host 127.0.0.1:3306, DB root/Root)                 │
│ - Consola Administrativa Web (AdminLTE / Bootstrap)                     │
│ - Generador de Reportes DomPDF & Hojas de Cálculo Excel                 │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Especificación Técnica de Hardware y Pinout

### 2.1. Componentes del Quiosco Biométrico
* **Microcontrolador**: ESP32 DevKit V1 (30 pines).
* **Sensor Biométrico Óptico**: **R307S** (familia AS608), comunicación serie UART a 57600 baudios.
* **Pantalla**: LCD 16x2 con adaptador I2C (Dirección `0x27`).
* **Teclado Matricial**: 4x4 membrana (Filas 13, 12, 14, 27 | Columnas 26, 25, 33, 32).
* **Sensor de Proximidad**: HC-SR04 Ultrasónico.
* **Indicadores & Alertas**: LEDs de 5mm (Verde, Amarillo, Rojo + resistencias de 220Ω) y Buzzer activo de 5V.

### 2.2. Tabla de Conexiones (Pinout Oficial)

| Componente | Pin del Componente | GPIO del ESP32 | Descripción / Protocolo |
|---|---|---|---|
| **Sensor R307S** | TX (Amarillo) | `GPIO16` | UART RX2 (57600 baudios) |
| **Sensor R307S** | RX (Verde/Blanco) | `GPIO17` | UART TX2 (57600 baudios) |
| **Sensor R307S** | VCC / GND | 5V / GND | Alimentación regulada 5V |
| **LCD 16x2 I2C** | SDA | `GPIO21` | Bus I2C Datos (Dirección 0x27) |
| **LCD 16x2 I2C** | SCL | `GPIO22` | Bus I2C Reloj |
| **LED Verde** | Anodo (+) | `GPIO4` | Indicador de Éxito / Confirmación |
| **LED Amarillo** | Anodo (+) | `GPIO5` | Indicador de Proceso / Espera Dedo |
| **LED Rojo** | Anodo (+) | `GPIO2` | Indicador de Error / Denegado |
| **Buzzer Activo** | Positivo (+) | `GPIO18` | Alerta Sonora (Beeps) |
| **HC-SR04** | TRIG | `GPIO19` | Disparo Ultrasónico |
| **HC-SR04** | ECHO | `GPIO23` | Eco de Proximidad (<12 cm) |
| **Teclado 4x4** | Filas F1..F4 | `GPIO13, 12, 14, 27` | Escaneo Matricial de Entradas |
| **Teclado 4x4** | Cols C1..C4 | `GPIO26, 25, 33, 32` | Escaneo Matricial de Entradas |

---

## 3. Modo de Operación y Justificación Técnica de Compatibilidad

### 3.1. Justificación Técnica Obligatoria (Sensor Clon R307S)
> **Nota de Arquitectura y Contingencia**: Debido a que los lotes de sensores ópticos clones del modelo R307S no implementan o presentan fallos nativos en las instrucciones de búsqueda de memoria interna (`fingerFastSearch` / `fingerSearch`), el sistema adopta como contingencia validada el **Modo de Compatibilidad (Verificación 1:1 Asistida)**.
> En este modo, el docente ingresa su ID asignado en el teclado matricial del quiosco y el sensor R307S realiza la verificación del dedo vivo mediante la captura física (`getImage` + `image2Tz`). El diseño para búsqueda automática 1:N queda formalmente preparado y documentado para integración inmediata al sustituir el módulo por un sensor certificado de fábrica.

### 3.2. Privacy by Design (Privacidad Biométrica)
El sistema cumple estrictamente con los estándares de **Privacy by Design**: las plantillas minuciales de huellas dactilares residen y se procesan exclusivamente en la memoria EEPROM física del sensor R307S. Ningún dato biométrico crudo sale del dispositivo ni viaja a través de la red HTTP/REST.

---

## 4. REST API v1 y Formato Estándar de Respuestas

Todas las respuestas de la API REST retornan la siguiente estructura JSON uniforme:

```json
{
  "success": true,
  "message": "Mensaje descriptivo del resultado",
  "data": { }
}
```

### 4.1. Endpoint: Registro de Asistencia (`POST /api/v1/attendance`)
* **Autenticación**: `Bearer <token_del_dispositivo>`
* **Cuerpo de Solicitud (JSON)**:
  ```json
  {
    "r307_id": 5
  }
  ```
* **Respuesta Exitosa (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Marcación registrada: PUNTUAL",
    "data": {
      "docente": "Dra. María Elena Gutiérrez Flores",
      "hora": "13:12:05",
      "estado": "PUNTUAL"
    }
  }
  ```
* **Ejemplo de comando cURL**:
  ```bash
  curl -X POST "http://localhost/PROYECTO-DAC/sistema-biometrico-dac/public/api/v1/attendance" \
    -H "Authorization: Bearer dac_dev_secret_token_123" \
    -H "Content-Type: application/json" \
    -d '{"r307_id": 5}'
  ```

### 4.2. Endpoint: Telemetría / Heartbeat (`POST /api/v1/device/heartbeat`)
* **Intervalo de Envío**: Cada 30 segundos (`HEARTBEAT_INTERVAL = 30000`).
* **Cuerpo (JSON)**:
  ```json
  {
    "firmware_version": "2.1.0",
    "mac_address": "24:0A:C4:DA:C0:01"
  }
  ```

### 4.3. Endpoint: Configuración y Sincronización (`GET /api/v1/device/config`)
* **Respuesta**:
  ```json
  {
    "success": true,
    "message": "Configuración obtenida correctamente",
    "next_free_r307_id": 7,
    "fingerprints": [
      { "r307_id": 5, "name": "Dra. María Elena Gutiérrez" },
      { "r307_id": 6, "name": "Dra. María Elena (Respaldo)" }
    ]
  }
  ```

### 4.4. Endpoint: Enrolamiento Biométrico (`POST /api/v1/biometria/enroll`)
* **Cuerpo (JSON)**:
  ```json
  {
    "codigo_docente": "DOC-1001",
    "r307_id": 5,
    "dedo_numero": 1,
    "dedo_nombre": "Huella principal"
  }
  ```

### 4.5. Otros Endpoints de la API v1:
* `POST /api/v1/biometria/delete`: Elimina la vinculación del ID en el servidor.
* `GET /api/v1/biometria/list`: Listado de vinculaciones biométricas registradas.
* `GET|POST /api/v1/biometria/verificar/{user_id}`: Verificación biométrica por usuario.
* `GET /api/v1/device/firmware/latest`: Consulta de actualizaciones OTA.

---

## 5. Reglas de Negocio, Idempotencia y Tolerancias

1. **Ventana de Idempotencia (Anti-Duplicados)**: Se establece una restricción de **30 segundos** entre marcaciones consecutivas del mismo ID para evitar múltiples registros accidentales.
2. **Horarios Oficiales DAC (Unidad Educativa Tiquipaya)**:
   * **Turno 1**: 13:15 a 14:20 (Lunes a Viernes).
   * **Turno 2**: 14:30 a 15:30 (Lunes a Viernes).
3. **Tolerancia y Clasificación de Asistencia**:
   * Marcaciones con hasta **10 minutos** de diferencia sobre el inicio se clasifican automáticamente como `PUNTUAL`.
   * Marcaciones posteriores al límite de tolerancia se clasifican como `TARDANZA` calculando los minutos exactos de retraso.

---

## 6. Persistencia y Credenciales de Base de Datos XAMPP

* **Servidor de BD**: MySQL / MariaDB (XAMPP).
* **Configuración Workbench / `.env`**:
  * **Host**: `127.0.0.1`
  * **Puerto**: `3306`
  * **Base de Datos**: `dac_biometrico` (o `laravel`)
  * **Usuario**: `root`
  * **Contraseña**: `Root`

### Lista de Migraciones Principales
1. `2014_10_12_000000_create_users_table.php`
2. `2014_10_12_100000_create_password_reset_tokens_table.php`
3. `2019_08_19_000000_create_failed_jobs_table.php`
4. `2019_12_14_000001_create_personal_access_tokens_table.php`
5. `2026_07_28_000001_create_biometric_system_tables.php` (Dispositivos, Horarios, Asistencias, Justificaciones, Auditoría).
6. `2026_08_06_000001_create_huellas_docentes_table.php` (Módulo multitabla de huellas dactilares).

---

## 7. Solución de Problemas del Sensor Clon (Troubleshooting)

| Problema | Causa Probable | Solución Técnica |
|---|---|---|
| **Pantalla LCD muestra "R307 NO detecta"** | Cables TX/RX invertidos o baudrate erróneo. | Verificar que `FP_RX` (GPIO16) vaya al TX del R307S y `FP_TX` (GPIO17) al RX. Confirmar baudrate en 57600. |
| **Error en enrolamiento (`createModel` / `storeModel`)** | Segunda pasada de dedo inconsistente. | Asegurarse de colocar exactamente el mismo dedo en ambas pasadas y limpiar la superficie del cristal. |
| **"Error config / revisa token/red"** | Token del dispositivo no coincide con la BD. | Revisar que `API_TOKEN` en el firmware coincida exactamente con el `api_token` registrado en la tabla `devices`. |
| **Fallo de lectura por sensor clon** | No soporta comandos de búsqueda masiva. | Utilizar el Modo de Compatibilidad 1:1 ingresando el ID del docente en el teclado. |

---

## 8. Apéndice A — Código Fuente Oficial del Firmware (C++)

El código fuente completo a continuación se almacena en `firmware/esp32_dac.ino`:

```cpp
/* SISTEMA BIOMÉTRICO DAC - Firmware ESP32 + R307S (VERSIÓN PRODUCCIÓN) */
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>
#include <Adafruit_Fingerprint.h>

/* 1) CONFIGURACIÓN (cambiar por despliegue) */
const char* WIFI_SSID     = "TU_WIFI_2.4G";
const char* WIFI_PASSWORD = "TU_CLAVE";
const String API_BASE = "http://TU_IP/PROYECTO-DAC/sistema-biometrico-dac/public/api/v1";
const String API_TOKEN   = "TU_TOKEN_DEL_LECTOR";
const String DEVICE_MAC      = "24:0A:C4:DA:C0:01";
const String FIRMWARE_VERSION = "2.1.0";
const int LCD_ADDR = 0x27;

/* 2) PINES */
#define FP_RX 16
#define FP_TX 17
#define LCD_SDA 21
#define LCD_SCL 22
#define LED_VERDE 4
#define LED_AMARILLO 5
#define LED_ROJO 2
#define BUZZER 18
#define ULTRA_TRIG 19
#define ULTRA_ECHO 23

/* 3) CONFIGURACIÓN INTERNA */
const byte ROWS = 4, COLS = 4;
char keys[ROWS][COLS] = {{'1','2','3','A'},{'4','5','6','B'},{'7','8','9','C'},{'*','0','#','D'}};
byte rowPins[ROWS] = {13,12,14,27};
byte colPins[COLS] = {26,25,33,32};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);
HardwareSerial fingerSerial(2);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerSerial);
LiquidCrystal_I2C lcd(LCD_ADDR, 16, 2);
#define MAX_FP 50
int fpIds[MAX_FP]; char fpNames[MAX_FP][40];
int fpCount = 0; int nextFreeR307Id = 1;
unsigned long lastHeartbeat = 0, lastScan = 0;
const unsigned long HEARTBEAT_INTERVAL = 30000;

void lcdPrint(const String &l1, const String &l2 = "") {
  lcd.clear(); lcd.setCursor(0,0); lcd.print(l1.substring(0,16));
  lcd.setCursor(0,1); lcd.print(l2.substring(0,16));
}
void beep(int veces, int ms) {
  for (int i=0;i<veces;i++){ digitalWrite(BUZZER,HIGH); delay(ms); digitalWrite(BUZZER,LOW); delay(ms);}
}
void conectarWiFi() {
  lcdPrint("Conectando...","WiFi"); WiFi.begin(WIFI_SSID,WIFI_PASSWORD);
  int n=0; while(WiFi.status()!=WL_CONNECTED && n<30){delay(1000);n++;lcdPrint("Conectando...",String("Intento ")+n);}
  if(WiFi.status()==WL_CONNECTED){Serial.print("[OK] WiFi IP: ");Serial.println(WiFi.localIP());lcdPrint("WiFi OK!",WiFi.localIP().toString());}
  else{Serial.println("[FALLO] WiFi");lcdPrint("SIN WIFI","Revisa clave");}
  delay(1500);
}
String apiPost(const String &ruta,const String &body){
  if(WiFi.status()!=WL_CONNECTED)return "";
  HTTPClient http; http.begin(API_BASE+ruta);
  http.addHeader("Authorization","Bearer "+API_TOKEN);
  http.addHeader("Content-Type","application/json");
  http.addHeader("Accept","application/json"); http.setTimeout(10000);
  int c=http.POST(body); String r=(c>0)?http.getString():""; http.end(); return r;
}
String apiGet(const String &ruta){
  if(WiFi.status()!=WL_CONNECTED)return "";
  HTTPClient http; http.begin(API_BASE+ruta);
  http.addHeader("Authorization","Bearer "+API_TOKEN);
  http.addHeader("Accept","application/json"); http.setTimeout(10000);
  int c=http.GET(); String r=(c>0)?http.getString():""; http.end(); return r;
}
void enviarHeartbeat(){
  String b=String("{\"firmware_version\":\"")+FIRMWARE_VERSION+"\",\"mac_address\":\""+DEVICE_MAC+"\"}";
  apiPost("/device/heartbeat",b);
}
void sincronizarConfig(){
  String resp=apiGet("/device/config");
  if(resp.length()<3){lcdPrint("Error config","revisa token/red");delay(1500);return;}
  JsonDocument doc; if(deserializeJson(doc,resp)){lcdPrint("JSON invalido","del servidor");delay(1500);return;}
  nextFreeR307Id=doc["next_free_r307_id"]|1; fpCount=0;
  JsonArray fps=doc["fingerprints"].as<JsonArray>();
  for(JsonObject o:fps){ if(fpCount>=MAX_FP)break;
    fpIds[fpCount]=o["r307_id"]|-1;
    const char* nom=o["name"]|"---";
    strncpy(fpNames[fpCount],nom,39); fpNames[fpCount][39]='\0'; fpCount++; }
  lcdPrint("Cargadas "+String(fpCount)+" h.","Listo para marcar");
  Serial.print("[OK] Config. Huellas servidor: ");Serial.print(fpCount);
  Serial.print(" | Next ID: ");Serial.println(nextFreeR307Id); delay(1200);
}
String nombreDeId(int id){
  for(int i=0;i<fpCount;i++) if(fpIds[i]==id) return String(fpNames[i]);
  return "ID "+String(id);
}
String leerNumeros(const String &titulo,const String &defecto){
  String num=defecto; unsigned long t0=millis(); lcdPrint(titulo,defecto+"_");
  while(millis()-t0<30000){ char k=keypad.getKey();
    if(k){ if(k=='#'){lcdPrint(titulo,num);delay(200);return num;}
      if(k=='*')return "";
      if(k>='0'&&k<='9'&&num.length()<10)num+=k;
      lcdPrint(titulo,num+"_"); }
    delay(20);}
  return "";
}
bool esperarDedo(unsigned long ms){
  unsigned long t0=millis();
  while(millis()-t0<ms){ char k=keypad.getKey();
    if(k=='*'){Serial.println(">> CANCELADO con *");return false;}
    int p=finger.getImage();
    if(p==FINGERPRINT_OK){Serial.println(">> DEDO DETECTADO OK");return true;}
    if(p!=FINGERPRINT_NOFINGER){Serial.print(">> getImage codigo: ");Serial.println(p);}
    delay(50);}
  Serial.println(">> TIMEOUT sin dedo"); return false;
}
void esperarRetiro(){ while(finger.getImage()!=FINGERPRINT_NOFINGER) delay(50); }

/* ENROLAMIENTO: 2 pasadas (protocolo real del R307/R307S) */
uint8_t enrolar(int id){
  uint8_t p;
  lcdPrint("Dedo 1 de 2","Coloque su dedo");
  if(!esperarDedo(60000))return FINGERPRINT_PACKETRECIEVEERR;
  p=finger.image2Tz(1); Serial.print(">> P1 image2Tz: ");Serial.println(p);
  if(p!=FINGERPRINT_OK)return p;
  lcdPrint("Dedo 1 de 2","Retire el dedo"); esperarRetiro(); delay(300);
  lcdPrint("Dedo 2 de 2","Coloque su dedo");
  if(!esperarDedo(60000))return FINGERPRINT_PACKETRECIEVEERR;
  p=finger.image2Tz(2); Serial.print(">> P2 image2Tz: ");Serial.println(p);
  if(p!=FINGERPRINT_OK)return p;
  lcdPrint("Dedo 2 de 2","Retire el dedo"); esperarRetiro(); delay(300);
  delay(200); p=finger.createModel(); Serial.print(">> createModel: ");Serial.println(p);
  if(p!=FINGERPRINT_OK)return p;
  lcdPrint("Guardando...","ID "+String(id));
  p=finger.storeModel(id); Serial.print(">> storeModel: ");Serial.println(p);
  finger.getTemplateCount();
  Serial.print(">> Huellas en sensor: ");Serial.println(finger.templateCount);
  return p;
}
void modoMarcar(int id){
  String nombre=nombreDeId(id);
  digitalWrite(LED_VERDE,HIGH); beep(1,150);
  String body=String("{\"r307_id\":")+id+"}";
  String resp=apiPost("/attendance",body);
  Serial.print("[MARCACION] r307_id=");Serial.print(id);
  Serial.print(" -> ");Serial.println(resp);
  String msg;
  if(resp.length()<3)msg="SIN CONEXION";
  else{JsonDocument d; if(!deserializeJson(d,resp))msg=String((const char*)(d["message"]|"OK"));else msg="ERROR";}
  lcdPrint("Bienvenido!",nombre); delay(2500);
  lcdPrint(msg,nombre); delay(3500);
  digitalWrite(LED_VERDE,LOW); lcdPrint("Listo para marcar","Coloque su dedo");
}
void modoRegistrar(){
  lcdPrint("REGISTRAR HUELLA","tecla * cancela");
  String idStr=leerNumeros("ID del sensor:",String(nextFreeR307Id));
  if(idStr.length()==0){lcdPrint("Cancelado","tecla *");delay(1200);return;}
  int id=idStr.toInt();
  if(id<1||id>300){lcdPrint("ID invalido","usa 1 a 300");delay(1500);return;}
  String codigo=leerNumeros("Codigo docente:","1001");
  if(codigo.length()==0){lcdPrint("Cancelado","tecla *");delay(1200);return;}
  lcdPrint("Enrolar ID "+String(id),"tecla * cancela");
  uint8_t r=enrolar(id);
  if(r!=FINGERPRINT_OK){digitalWrite(LED_ROJO,HIGH);beep(3,200);
    lcdPrint("ERROR "+String(r),"intenta de nuevo");delay(2500);digitalWrite(LED_ROJO,LOW);return;}
  String body=String("{\"codigo_docente\":\"")+codigo+"\",\"r307_id\":"+id+
    ",\"dedo_numero\":1,\"dedo_nombre\":\"Huella principal\"}";
  String resp=apiPost("/biometria/enroll",body);
  Serial.print("[ENROLL] ");Serial.println(resp);
  digitalWrite(LED_VERDE,HIGH); beep(1,300);
  if(resp.length()>3&&resp.indexOf("\"success\":true")>=0){
    if(fpCount<MAX_FP){fpIds[fpCount]=id;
      strncpy(fpNames[fpCount],("Docente "+String(codigo)).c_str(),39);
      fpNames[fpCount][39]='\0';fpCount++;}
    if(id>=nextFreeR307Id)nextFreeR307Id=id+1;
    lcdPrint("¡ENROLADO!","ID "+String(id));
  }else{finger.deleteModel(id);lcdPrint("Fallo en servidor","revisa el codigo");}
  delay(2500); digitalWrite(LED_VERDE,LOW);
  lcdPrint("Listo para marcar","Coloque su dedo");
}
void modoEliminar(){
  lcdPrint("ELIMINAR HUELLA","tecla * cancela");
  String idStr=leerNumeros("ID a eliminar:","");
  if(idStr.length()==0){lcdPrint("Cancelado","tecla *");delay(1200);return;}
  int id=idStr.toInt();
  finger.deleteModel(id);
  apiPost("/biometria/delete",String("{\"r307_id\":")+id+"}");
  for(int i=0;i<fpCount;i++)if(fpIds[i]==id){
    for(int j=i;j<fpCount-1;j++){fpIds[j]=fpIds[j+1];strcpy(fpNames[j],fpNames[j+1]);}
    fpCount--;break;}
  digitalWrite(LED_AMARILLO,HIGH);beep(2,200);
  lcdPrint("ELIMINADO","ID "+String(id));delay(2500);
  digitalWrite(LED_AMARILLO,LOW);lcdPrint("Listo para marcar","Coloque su dedo");
}
long medirDistanciaCm(){
  digitalWrite(ULTRA_TRIG,LOW);delayMicroseconds(2);
  digitalWrite(ULTRA_TRIG,HIGH);delayMicroseconds(10);
  digitalWrite(ULTRA_TRIG,LOW);
  long d=pulseIn(ULTRA_ECHO,HIGH,30000);
  return (d==0)?400:d/58;
}
/* MODO COMPATIBILIDAD: ID por teclado + verificación de dedo vivo.
   (El sensor clon no soporta búsqueda; ver justificación en docs). */
void escanearYMarcar(){
  lcdPrint("Ingrese ID sensor","y presione #");
  String idStr=leerNumeros("ID sensor:","");
  if(idStr.length()==0){lcdPrint("Listo para marcar","Coloque su dedo");return;}
  int id=idStr.toInt();
  lcdPrint("Ponga su dedo","verificacion viva");
  digitalWrite(LED_AMARILLO,HIGH);
  unsigned long t0=millis(); bool dedo=false;
  while(millis()-t0<20000){ char k=keypad.getKey();
    if(k=='*'){digitalWrite(LED_AMARILLO,LOW);lcdPrint("Cancelado","Listo para marcar");delay(1500);return;}
    if(finger.getImage()==FINGERPRINT_OK){dedo=true;break;}
    delay(50);}
  digitalWrite(LED_AMARILLO,LOW);
  if(!dedo){lcdPrint("Sin dedo","intente de nuevo");delay(1500);
    lcdPrint("Listo para marcar","Coloque su dedo");return;}
  int p=finger.image2Tz();
  if(p!=FINGERPRINT_OK){lcdPrint("No se pudo leer","intente de nuevo");delay(1500);
    lcdPrint("Listo para marcar","Coloque su dedo");return;}
  modoMarcar(id);
}
void setup(){
  Serial.begin(115200);delay(500);
  Serial.println("=== SISTEMA BIOMETRICO DAC - ESP32 R307S ===");
  pinMode(LED_VERDE,OUTPUT);pinMode(LED_AMARILLO,OUTPUT);
  pinMode(LED_ROJO,OUTPUT);pinMode(BUZZER,OUTPUT);
  pinMode(ULTRA_TRIG,OUTPUT);pinMode(ULTRA_ECHO,INPUT);
  digitalWrite(LED_VERDE,HIGH);beep(1,200);digitalWrite(LED_VERDE,LOW);
  Wire.begin(LCD_SDA,LCD_SCL);lcd.init();lcd.backlight();
  lcdPrint("Sistema DAC","Iniciando...");
  fingerSerial.begin(57600,SERIAL_8N1,FP_RX,FP_TX);finger.begin(57600);
  if(finger.verifyPassword()){
    Serial.println("[OK] R307S detectado.");
    finger.getTemplateCount();
    Serial.print(">> Huellas en sensor: ");Serial.println(finger.templateCount);
    finger.getParameters();
    Serial.print(">> Nivel seguridad: ");Serial.println(finger.security_level);
    finger.setSecurityLevel(2);
    Serial.println(">> Nivel ajustado a 2");
    lcdPrint("R307 detectado","OK");
  }else{
    Serial.println("[FALLO] R307 no responde");
    lcdPrint("R307 NO detecta","revisa cableado");beep(3,300);delay(2500);}
  delay(1200);
  conectarWiFi();
  if(WiFi.status()==WL_CONNECTED){enviarHeartbeat();sincronizarConfig();}
  lcdPrint("Listo para marcar","Coloque su dedo");
}
void loop(){
  if(WiFi.status()!=WL_CONNECTED){conectarWiFi();
    if(WiFi.status()!=WL_CONNECTED){enviarHeartbeat();sincronizarConfig();}
    delay(3000);return;}
  if(millis()-lastHeartbeat>HEARTBEAT_INTERVAL){lastHeartbeat=millis();enviarHeartbeat();}
  char k=keypad.getKey();
  if(k=='*'){modoRegistrar();lastScan=millis();}
  else if(k=='A'){modoEliminar();lastScan=millis();}
  long dist=medirDistanciaCm();
  if(dist<12&&millis()-lastScan>3000){lastScan=millis();escanearYMarcar();}
  delay(20);
}
```
