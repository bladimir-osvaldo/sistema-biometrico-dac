# Manual del Coordinador DAC – Sistema Biométrico DAC v2.1
**Programa DAC · Unidad Educativa Tiquipaya**

---

## 1. Perfil y Alcance del Coordinador DAC (`COORDINADOR DAC`)

El **Coordinador DAC** es el responsable de la gestión académica operativa del programa. Sus funciones están protegidas mediante el middleware de roles `role:admin,coordinador`.

### Resumen de Permisos según `routes/web.php`:
* ✅ **Permitido**: Dashboard, Gestión de Horarios de Clases, Revisión y Dictamen de Justificaciones, Emisión de Memorandos Automáticos, Reportes PDF/Excel y Consulta de Asistencias.
* ❌ **Restringido**: Gestión de Usuarios/Docentes, Configuración de Dispositivos Lectores ESP32 y Enrolamiento Biométrico directo (reservados exclusivamente para el Administrador `role:admin`).

---

## 2. Guía Operativa de Funciones del Coordinador

### 2.1. Dashboard y Monitoreo General (`/dashboard`)
Visualización de métricas generales de puntualidad, asistencias del día, justificaciones pendientes de dictamen y gráficos estadísticos semanales para la toma de decisiones académicas.

---

### 2.2. Gestión de Horarios de Clases (`/schedules`)
* **Acceso**: Menú lateral $\rightarrow$ **Horarios y Aulas**.
* **Horarios Oficiales del Programa DAC**:
  * **Turno 1**: 13:15 a 14:20 (Lunes a Viernes).
  * **Turno 2**: 14:30 a 15:30 (Lunes a Viernes).
* **Asignación de Horario (`/schedules/create`)**:
  1. Hacer clic en **Asignar Nuevo Horario**.
  2. Seleccionar al Docente de la lista desplegable.
  3. Ingresar el Nombre de la Materia, Código, Aula y Día de la semana.
  4. Definir Hora de Inicio (Ej: `13:15`), Hora de Fin (Ej: `14:20`) y Minutos de Tolerancia (defecto: `10` minutos).
  5. **Validación Antisolapamiento**: El sistema impedirá guardar si el docente ya tiene una materia asignada en ese mismo día u horario.

---

### 2.3. Dictamen y Revisión de Justificaciones (`/justifications`)
* **Acceso**: Menú lateral $\rightarrow$ **Justificaciones**.
* **Procedimiento de Revisión**:
  1. En la tabla de solicitudes pendientes, hacer clic en **Revisar**.
  2. Examinar el motivo expuesto por el docente y hacer clic en el enlace del comprobante adjunto (imagen o PDF).
  3. En la ventana modal, seleccionar el dictamen: **APROBAR JUSTIFICACIÓN** o **RECHAZAR SOLICITUD**.
  4. Redactar las observaciones institucionales del dictamen y presionar **Guardar Dictamen**.
  5. Al aprobar, el sistema actualizará automáticamente el estado del registro de asistencia a `FALTA_JUSTIFICADA`.

---

### 2.4. Generación de Memorandos Automáticos (`/memorandos`)
* **Acceso**: Menú lateral $\rightarrow$ **Memorandos**.
* **Procedimiento de Emisión**:
  1. Seleccionar al docente en la lista.
  2. Ingresar la fecha inicial y final del periodo a evaluar.
  3. Elegir el tipo de falta (`TARDANZA`, `INASISTENCIA`).
  4. Hacer clic en **Generar Memorándum PDF**.
  5. El sistema descargará un documento PDF formal listo para imprimir, con el cuadro de faltas del periodo, la normativa interna de la Unidad Educativa Tiquipaya y las líneas de firma para la Coordinación y el Docente.

---

### 2.5. Exportación de Reportes PDF y Excel (`/reports/...`)
* **Acceso**: Menú lateral $\rightarrow$ **Asistencias y Reportes**.
* **Filtros Disponibles**: Por docente, fecha inicio, fecha fin y estado (`PUNTUAL`, `TARDANZA`, `FALTA_JUSTIFICADA`).
* **Descarga de Reporte PDF**: Hacer clic en **Exportar PDF** para obtener la planilla imprimible.
* **Descarga de Planilla Excel**: Hacer clic en **Exportar Excel** para descargar el archivo `.xlsx` editable.
