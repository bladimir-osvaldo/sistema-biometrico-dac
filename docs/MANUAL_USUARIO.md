# Guía Rápida de Operación del Quiosco Biométrico – MANUAL_USUARIO.md
**Programa DAC · Unidad Educativa Tiquipaya**

---

## 1. Flujo Paso a Paso para la Marcación de Asistencia

El quiosco biométrico se encuentra ubicado en el ingreso principal del aula DAC. Opera bajo el **Modo de Compatibilidad 1:1** (Ingreso de ID + Verificación de Dedo Vivo).

```
┌──────────────────────────────────────────────────────────────────────────┐
│  PASO 1: ACERCASE AL QUIOSCO                                             │
│  El sensor ultrasónico detectará su presencia a menos de 12 cm          │
│  y activará la pantalla LCD: "Ingrese ID sensor y presione #"            │
└────────────────────────────────────┬─────────────────────────────────────┘
                                     │
                                     ▼
┌──────────────────────────────────────────────────────────────────────────┐
│  PASO 2: INGRESAR ID ASIGNADO EN EL TECLADO                              │
│  Digite su número de ID de sensor dactilar (Ej: 5) y presione '#'        │
│  (Si comete un error, presione '*' para borrar y cancelar)              │
└────────────────────────────────────┬─────────────────────────────────────┘
                                     │
                                     ▼
┌──────────────────────────────────────────────────────────────────────────┐
│  PASO 3: COLOCAR EL DEDO EN EL LECTOR R307S                              │
│  El LED Amarillo se encenderá. Coloque suavemente el dedo registrado     │
│  sobre el cristal del sensor R307S para la verificación viva             │
└────────────────────────────────────┬─────────────────────────────────────┘
                                     │
                                     ▼
┌──────────────────────────────────────────────────────────────────────────┐
│  PASO 4: CONFIRMACIÓN Y MENSAJE                                          │
│  - Emitirá un BEEP sonoro y encenderá el LED Verde                       │
│  - LCD mostrará: "Bienvenido! [Su Nombre]"                               │
│  - Registra el estado: PUNTUAL (13:15 / 14:30) o TARDANZA                │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Solución Rápida a Mensajes en la Pantalla LCD

| Mensaje en la Pantalla LCD | Causa Probable | Acción Sugerida |
|---|---|---|
| **"Bienvenido! [Nombre]"** | Marcación capturada y guardada en el servidor. | Proceda a ingresar a su aula de clases. |
| **"Tardanza (+Xm)"** | Marcación realizada fuera de la tolerancia de 10 min. | Queda registrada la tardanza. Puede presentar una justificación desde el sistema web. |
| **"Sin dedo"** | No se colocó el dedo en el sensor dentro de los 20 segundos. | Presione `*` o vuelva a acercarse para reiniciar la marcación. |
| **"No se pudo leer"** | Posición incorrecta o superficie del dedo sucia/húmeda. | Limpie suavemente su dedo y vuelva a intentarlo asegurando cubrir el lector. |
| **"SIN CONEXION"** | Pérdida temporal de la señal Wi-Fi de la Unidad Educativa. | El quiosco reintentará conectarse automáticamente. |
| **"R307 NO detecta"** | Desconexión del módulo de huellas dactilares. | Notificar inmediatamente al Administrador de Sistemas. |
| **"Fallo en servidor"** | Error de comunicación con la base de datos web. | Notificar al Administrador de Sistemas (`admin@dac.edu.bo`). |
