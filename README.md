# 🖐️ Sistema Biométrico Automatizado DAC

**Sistema Biométrico Automatizado para el Registro y Control de Asistencia Docente
del Programa DAC — Unidad Educativa Tiquipaya, Cochabamba, Bolivia**

> *"Tu huella es única, tu presencia es importante."*

![Laravel](https://img.shields.io/badge/Laravel-10.50-red?logo=laravel)
![ESP32](https://img.shields.io/badge/ESP32-IoT-blue?logo=espressif)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql)

## 📋 Descripción

Proyecto de grado para el INSTIC "Federico Álvarez Plata" que implementa una
solución IoT para el control biométrico de asistencia de los 9 docentes del
Programa DAC (Deporte, Arte y Cultura) del Municipio de Tiquipaya.

## 🗂️ Estructura del repositorio

| Carpeta | Contenido |
|---------|-----------|
| `/app`, `/routes`, `/config` | Aplicación Laravel 10 (controladores, API REST v1, middleware RBAC) |
| `/database` | Migraciones y seeders (MySQL) |
| `/resources`, `/public` | Interfaz web (AdminLTE + Bootstrap) y reportes PDF/Excel |
| `/firmware` | Código del ESP32 (`esp32_dac.ino`) |
| `/hardware` | Guía de cableado y armado paso a paso |
| `/docs` | Informe técnico, diagramas UML y evidencia de pruebas piloto |

## 🏗️ Arquitectura

- **Capa IoT:** ESP32 DevKit V1 + sensor R307S (UART 57600) + LCD I2C + teclado 4x4 + HC-SR04
- **Capa API:** Laravel 10 + API REST v1 + Sanctum (tokens por dispositivo) + motor horario PUNTUAL/TARDANZA
- **Capa Web:** AdminLTE + Bootstrap + MySQL (RBAC 3 roles, reportes PDF/Excel)
- **Comunicación:** JSON (ArduinoJson 7), heartbeat 30 s, idempotencia 30 s, modo offline

## 👥 Roles del sistema (RBAC)

| Rol | Usuario demo | Funciones |
|-----|-------------|-----------|
| Administrador | `bhuallpa@dac.edu.bo` | Todo el sistema |
| Coordinador DAC | `aterrazas@dac.edu.bo` | Reportes, justificaciones, horarios |
| Docente | `mguterrez@dac.edu.bo` | Mi historial, justificaciones |

## 🚀 Instalación

### Backend / Web (Laravel) — en la raíz del repositorio

```bash
composer install
cp .env.example .env
php artisan key:generate
# Editar .env con credenciales de BD (MySQL, puerto 3306)
php artisan migrate --seed
php artisan serve
