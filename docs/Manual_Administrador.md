# Manual del Administrador – Sistema Biométrico DAC v2.1
**Programa DAC · Unidad Educativa Tiquipaya**

---

## 1. Perfil y Privilegios del Administrador (`ADMIN`)

El rol de **Administrador** posee el control total del sistema. Tiene acceso sin restricciones a todos los módulos funcionales, configuraciones de hardware, administración de usuarios, auditoría y reportes institucionales.

---

## 2. Descripción Pantalla por Pantalla de Funciones Administrativas

### 2.1. Panel Principal y Dashboard (`/dashboard`)
* **Descripción de Interfaz**: Al ingresar, el Administrador visualiza 6 tarjetas KPI con contadores en tiempo real (Docentes Activos, Asistencias Hoy, Tardanzas, % Puntualidad, Lectores ESP32 Online, Justificaciones Pendientes).
* **Gráficos e Indicadores**: Gráfico de barras de asistencia semanal, gráfico de dona de puntualidad del día y tabla con las últimas marcaciones en tiempo real.
* **Controles**: Selector de tema de color (Modo Claro, Modo Oscuro `#1a1a2e` y Modo Cuidado Visual `#f5e7d3`).

---

### 2.2. Gestión de Usuarios y Docentes (`/users`)
* **Acceso**: Menú lateral $\rightarrow$ **Docentes y Usuarios**.
* **Nómina de Usuarios**: Muestra la lista paginada de todos los docentes y administradores con su fotografía, DNI, Código Docente, Carrera/Especialidad, Rol y Estado (`ACTIVO`, `INACTIVO`, `LICENCIA`).
* **Registrar Nuevo Docente (`/users/create`)**:
  1. Hacer clic en **Registrar Nuevo Docente**.
  2. Completar: Nombre Completo, Correo Institucional, Contraseña, Rol (`Administrador`, `Coordinador DAC`, `Docente`), DNI, Código Docente, Teléfono, Carrera y Cargo.
  3. Adjuntar fotografía de perfil (JPG/PNG).
  4. Presionar **Registrar Docente**.
* **Ver / Editar / Eliminar (`/users/{id}`, `/users/{id}/edit`)**: Botones de acción directa en la tabla con confirmación de seguridad para eliminación.

---

### 2.3. Gestión de Lectores ESP32 y Tokens Sanctum (`/devices`)
* **Acceso**: Menú lateral $\rightarrow$ **Lectores ESP32**.
* **Registro de Dispositivo (`/devices/create`)**:
  1. Introducir Código identificador (Ej: `ESP32-DAC-001`), Nombre descriptivo, Ubicación (Ej: *Sala DAC*) y Dirección MAC.
  2. Al guardar, el sistema asigna de forma automática un **Bearer API Token** de 80 caracteres.
* **Visualización de Token**: En la lista de lectores, el Administrador puede hacer clic en el ícono de ojo para revelar u ocultar el token asignado al hardware.

---

### 2.4. Enrolamiento Biométrico y Vinculación R307S (`/biometria`)
* **Acceso**: Menú lateral $\rightarrow$ **Enrolamiento Biométrico**.
* **Proceso de Vinculación Docente ↔ ID Sensor R307S**:
  1. Seleccionar al docente en la lista (visualiza su indicador de huellas `0/3` a `3/3`).
  2. En el panel de ranuras dactilares (Slot 1: Índice Derecho, Slot 2: Medio Derecho, Slot 3: Pulgar Derecho), hacer clic en **Registrar**.
  3. En el quiosco físico ESP32, ingresar la clave de enrolamiento o confirmar desde el modal en pantalla.
  4. El sensor R307S solicitará colocar el mismo dedo **2 veces** (`image2Tz(1)` y `image2Tz(2)`).
  5. Una vez guardado en la memoria física del R307S, el ID asignado se vincula automáticamente en la base de datos con el docente.

---

### 2.5. Centro de Monitoreo de Hardware (`/monitoreo`)
* **Acceso**: Menú lateral $\rightarrow$ **Centro de Monitoreo**.
* **Estado en Tiempo Real**: Panel que consulta el estado del quiosco vía `/monitoreo/estado`.
* **Indicadores**:
  * Badge verde `ONLINE` si reportó heartbeat en los últimos 5 minutos; rojo `OFFLINE` si supera los 5 minutos.
  * Fecha/hora exacta del último pulso telemétrico.
  * Versión de firmware en ejecución (`2.1.0`).

---

### 2.6. Reportes Avanzados PDF y Excel (`/reports/...`)
* **Acceso**: Menú lateral $\rightarrow$ **Asistencias y Reportes** / **Reportes Avanzados**.
* **Reportes Mensuales y Tardanzas**: Generación instantánea de archivos PDF oficiales institucionales con logo y resumen.
* **Exportación a Excel**: Descarga de archivos `.xlsx` filtrados por fecha, docente o estado para análisis financiero o administrativo.

---

### 2.7. Módulo de Memorandos Automáticos (`/memorandos`)
* **Acceso**: Menú lateral $\rightarrow$ **Memorandos**.
* **Emisión de Memorándum**:
  1. Seleccionar al docente sancionado.
  2. Definir rango de fechas y tipo de falta (`TARDANZA`, `INASISTENCIA`).
  3. Hacer clic en **Generar Memorándum PDF**.
  4. El sistema compila el documento institucional listo con firma y artículos del reglamento de la Unidad Educativa Tiquipaya.

---

### 2.8. Revisión de Justificaciones (`/justifications`)
* **Acceso**: Menú lateral $\rightarrow$ **Justificaciones**.
* **Aprobación / Rechazo**:
  1. Revisar la solicitud enviada por el docente y descargar el comprobante adjunto.
  2. Hacer clic en **Revisar Solicitud**.
  3. Seleccionar dictamen (`APROBADO` / `RECHAZADO`), ingresar observaciones y guardar.
  4. Si es aprobada, el sistema actualiza la asistencia a `FALTA_JUSTIFICADA`.
