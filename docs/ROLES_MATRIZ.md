# Matriz de Control de Acceso Basado en Roles (RBAC)
**Sistema Biométrico DAC v2.1 · Unidad Educativa Tiquipaya**

---

## 1. Matriz Módulo × Acción × Rol

La siguiente matriz detalla el acceso y permisos sobre cada ruta y función del sistema, estrictamente alineados con los middleware de seguridad definidos en `routes/web.php` (`role:admin`, `role:admin,coordinador`, `role:admin,coordinador,docente`).

| Módulo / Funcionalidad | Ruta Web | Ver / Consultar | Crear / Registrar | Editar / Actualizar | Eliminar / Dictaminar | Rol Mínimo Requerido |
|---|---|:---:|:---:|:---:|:---:|---|
| **Dashboard General** | `/dashboard` | ✅ | — | — | — | Todos (`admin`, `coordinador`, `docente`) |
| **Perfil propio (Mi Cuenta)** | `/profile`, `/profile/edit` | ✅ | — | ✅ | — | Todos (`admin`, `coordinador`, `docente`) |
| **Docentes y Usuarios** | `/users` | ✅ | ✅ | ✅ | ✅ | Exclusivo `admin` |
| **Lectores ESP32 & Tokens** | `/devices` | ✅ | ✅ | ✅ | ✅ | Exclusivo `admin` |
| **Enrolamiento Biométrico** | `/biometria` | ✅ | ✅ | — | — | Exclusivo `admin` |
| **Centro de Monitoreo HW** | `/monitoreo`, `/monitoreo/estado` | ✅ | — | — | — | Exclusivo `admin` |
| **Horarios de Clases** | `/schedules` | ✅ | ✅ | ✅ | ✅ | `admin`, `coordinador` |
| **Revisiones de Justificación** | `/justifications/{id}/review` | ✅ | — | ✅ | ✅ | `admin`, `coordinador` |
| **Memorandos Automáticos** | `/memorandos`, `/memorandos/generar` | ✅ | ✅ | — | — | `admin`, `coordinador` |
| **Reportes Avanzados PDF/Excel** | `/reports/...` | ✅ | ✅ | — | — | `admin`, `coordinador` |
| **Mi Horario Personal** | `/mi-horario` | ✅ | — | — | — | Todos (`admin`, `coordinador`, `docente`) |
| **Consulta de Asistencias** | `/attendances` | ✅ | — | — | — | Todos (Docentes ven solo su historial) |
| **Solicitar Justificación** | `/justifications` (POST) | ✅ | ✅ | — | — | Todos (`admin`, `coordinador`, `docente`) |

---

## 2. Resumen de Perfiles

1. **Administrador (`ADMIN`)**: Acceso ilimitado al 100% de los módulos y configuraciones críticas.
2. **Coordinador DAC (`COORDINADOR DAC`)**: Control de la gestión académica (horarios, revisión de licencias, reportes, memorandos). Sin acceso a la creación de usuarios o dispositivos.
3. **Docente (`DOCENTE`)**: Autoservicio personal (consulta de marcaciones, horario asignado, solicitud de justificaciones y datos de perfil).
