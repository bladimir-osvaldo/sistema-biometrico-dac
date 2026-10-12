\# Bitácora: Diagnóstico del Sensor Biométrico R307S



\*\*Proyecto:\*\* Sistema Biométrico DAC — Tiquipaya  

\*\*Autor:\*\* Bladimir Huallpa Telleria  

\*\*Fecha:\*\* 12/10/2026  



\## 1. Contexto



Durante las pruebas de integración del firmware v2.3 (verificación 1:1 real con

cola offline y RTC DS3231), se detectó un comportamiento anómalo: las huellas

enroladas con éxito no eran reconocidas al momento de la marcación biométrica.

El objetivo de esta bitácora es \*\*documentar el método científico\*\* aplicado

para aislar la causa raíz y la decisión técnica tomada.



\## 2. Pruebas realizadas (en orden cronológico)



| # | Prueba | Método | Resultado |

|---|--------|--------|-----------|

| 1 | Detección de dedo vivo | `getImage()` + `image2Tz()` | ✅ OK (código 0) |

| 2 | Comparación vivo vs vivo | `createModel()` sobre 2 capturas del mismo dedo | ✅ OK (código 0) |

| 3 | Guardado de plantilla | `storeModel(200)` | ✅ OK (código 0) |

| 4 | Búsqueda 1:N nativa | `fingerFastSearch()` del mismo dedo recién guardado | ❌ `fastSearch=9, ID=0` |

| 5 | Verificación cruzada | `loadModel(200)` + `createModel()` contra vivo | ❌ código 10 |

| 6 | Match manual (0x03) | Comando directo del protocolo R307S | ❌ código 8 |



\## 3. Análisis técnico



\- El sensor \*\*SÍ\*\* captura imágenes y genera modelos biométricos válidos en vivo.

\- El sensor \*\*SÍ\*\* reporta guardado exitoso de plantillas.

\- El sensor \*\*NO\*\* recupera ni reconoce las plantillas que él mismo guardó.

\- El fallo es consistente en los 3 métodos de verificación probados.



\*\*Causa raíz:\*\* falla en la memoria EEPROM o en el algoritmo de búsqueda del

módulo R307S (unidad clon con chip defectuoso). El cableado UART (TX→D16,

RX→D17, 5V, GND) se validó con comunicador serie estable a 57600 baud.



\## 4. Decisión técnica



\*\*Reemplazo del módulo R307S\*\* por uno verificado (AS608 o R307S original JRT).



\### Criterio de aceptación del nuevo sensor (prueba de ingreso)



Al conectar el nuevo sensor, se ejecutará `SENSOR\_LAB\_v3.ino`:



1\. Enrolar un dedo de prueba en ID 200.

2\. Verificación inmediata con el mismo dedo: debe devolver `fastSearch=0, ID=200`.

3\. Verificación con dedo ajeno: debe devolver `fastSearch=9`.



Si el sensor pasa las 2 pruebas → aceptado. Si no → devolución al proveedor.



\## 5. Impacto en el cronograma



\- \*\*No afecta\*\* al backend Laravel 10, la API REST v1, la base de datos MySQL,

&#x20; la interfaz web responsive ni el RTC DS3231. Todos estos componentes están

&#x20; verificados y funcionando en producción.

\- \*\*No afecta\*\* al firmware v2.3 en su arquitectura: solo se reemplaza la

&#x20; función `buscarHuellaReal()` por la búsqueda nativa `fingerFastSearch()`.

\- Tiempo estimado de integración tras llegada del nuevo módulo: \*\*30 minutos\*\*.



\## 6. Lección aprendida



La prueba controlada "enrolar y verificar al instante con el mismo dedo"

demostró ser el método más eficaz para diagnosticar fallos de memoria en

sensores biométricos clon. Se incorpora como \*\*protocolo de aceptación\*\*

estándar en la `GUIA\_DESPLIEGUE.md` del proyecto.

