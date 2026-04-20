# Manual del Docente – Sistema Biométrico DAC v2.1
**Programa DAC · Unidad Educativa Tiquipaya**

---

## 1. Perfil y Alcance del Docente (`DOCENTE`)

El **Docente** utiliza la plataforma web para consultar su historial de asistencia, revisar su horario de clases asignado, verificar el estado de su registro biométrico y presentar solicitudes formales de justificación por tardanzas o licencias. Sus permisos están regulados por el middleware `role:admin,coordinador,docente`.

---

## 2. Guía Operativa para el Docente

### 2.1. Inicio de Sesión
1. Ingrese a la dirección web del sistema.
2. Escriba su correo institucional (Ej: `mguterrez@dac.edu.bo`) y su contraseña.
3. Presione **Acceder al Sistema**.

---

### 2.2. Mi Perfil y Fotografía (`/profile/edit`)
* **Acceso**: Menú superior derecho $\rightarrow$ Hacer clic en la foto o nombre $\rightarrow$ **Editar Mi Perfil**.
* **Funciones**:
  * Actualizar su Nombre Completo, Correo y Teléfono de contacto.
  * Cambiar la Fotografía de Perfil (subida de archivos JPG/PNG).
  * Cambiar su contraseña personal de acceso.

---

### 2.3. Consulta de Mi Horario de Clases (`/mi-horario`)
* **Acceso**: Menú lateral $\rightarrow$ **Mi Horario**.
* Muestra la grilla con las materias, aulas asignadas y los turnos oficiales del Programa DAC:
  * **Turno 1**: 13:15 a 14:20
  * **Turno 2**: 14:30 a 15:30
  * **Días**: Lunes a Viernes (Unidad Educativa Tiquipaya).

---

### 2.4. Historial de Mis Asistencias (`/attendances`)
* **Acceso**: Menú lateral $\rightarrow$ **Asistencias y Reportes**.
* **Visualización**: El docente consulta sus marcaciones registradas por el quiosco biométrico ESP32.
* **Detalles**: Muestra Fecha, Hora de Marcado, Materia, Estado (`PUNTUAL`, `TARDANZA`, `FALTA_JUSTIFICADA`), Modo de registro (`ONLINE`, `OFFLINE_SYNC`) y minutos de retraso acumulados.
* **Descarga de Reporte**: Botón **Exportar PDF** para descargar su comprobante de asistencias personal.

---

### 2.5. Presentación de Solicitudes de Justificación (`/justifications`)
* **Acceso**: Menú lateral $\rightarrow$ **Justificaciones**.
* **Procedimiento para Solicitar Justificación**:
  1. Hacer clic en el botón **Nueva Justificación**.
  2. Seleccionar la **Fecha de Inasistencia / Tardanza** a justificar.
  3. Ingresar el **Motivo Resumido** (Ej: *Licencia Médica*, *Inconveniente de Tráfico*).
  4. Redactar la **Explicación Detallada**.
  5. Adjuntar el documento de respaldo o evidencia (comprobante médico o fotografía en formato PDF, JPG o PNG).
  6. Presionar **Enviar Solicitud**.
  7. En la tabla de justificaciones podrá seguir en tiempo real el estado de su solicitud (`PENDIENTE`, `APROBADO`, `RECHAZADO`) y leer las observaciones emitidas por la Coordinación.
