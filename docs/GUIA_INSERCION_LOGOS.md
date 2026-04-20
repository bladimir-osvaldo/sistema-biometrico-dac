# Guía de Inserción de Logotipos e Identidad Visual
**Programa DAC · Unidad Educativa Tiquipaya**

---

## 1. Archivos de Logotipos e Imágenes Oficiales

Los archivos de imagen institucionales deben ubicarse en la carpeta `public/images/`:

* **`public/images/logo.png`**: Logotipo institucional de alta resolución (utilizado en el panel lateral y encabezados de la aplicación).
* **`public/images/logo_thumb.png`**: Versión optimizada liviana (250px) para la compilación ultrarrápida de documentos PDF mediante DomPDF.
* **`public/images/banner.png`**: Imagen de cabecera y banner oficial del Programa DAC.

---

## 2. Regla Obligatoria de Fondos y Degradado Institucional

> **RESTRICCIÓN OBLIGATORIA DE DISEÑO**: Queda estrictamente prohibido utilizar imágenes de fondo recortadas, con marquetado visible o de baja resolución para los contenedores principales.
> En su lugar, se debe emplear siempre el **degradado institucional estandarizado** definido a continuación:

```css
/* Degradado Institucional DAC */
background: linear-gradient(160deg, #0f172a 0%, #4c0519 55%, #881337 100%);
```

### Paleta de Colores Oficiales
* **Guindo Institucional**: `#6b0c24` / `#4a0818` / `#881337`
* **Dorado Primario**: `#d4af37` / `#b89428`
* **Plomo Noche**: `#0f172a` / `#1e293b` / `#334155`
* **Blanco Neutro**: `#f8fafc` / `#ffffff`

---

## 3. Código HTML/Blade para Incorporar los Logotipos

### En la Barra Lateral (`layouts/app.blade.php`):
```html
<div class="logo-circle">
    <img src="{{ asset('images/logo.png') }}"
         alt="Logo DAC"
         onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-fingerprint\'></i>'">
</div>
```

### En Plantillas de PDF (`reports/attendance_pdf.blade.php`):
```html
@if(file_exists(public_path('images/logo_thumb.png')))
    <img src="{{ public_path('images/logo_thumb.png') }}" class="logo" alt="Logo DAC">
@elseif(file_exists(public_path('images/logo.png')))
    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo DAC">
@endif
```
