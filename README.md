# Chirper - Tutorial Oficial de Laravel

## Descripción del Proyecto

Chirper es el tutorial oficial de Laravel que enseña a construir una aplicación de microblogging (similar a Twitter) utilizando Laravel, Livewire y Volt. A través de este proyecto uso los conceptos fundamentales de Laravel incluyendo autenticación, CRUD operations, notificaciones por correo, y más.

**Demo en vivo**: [https://chirper-main-wf4bpr.laravel.cloud/login](https://chirper-main-wf4bpr.laravel.cloud/login)

> 💡 **Tip**: Se recomienda registrar al menos 2 usuarios con diferentes correos electrónicos para visualizar las notificaciones por correo cuando un usuario agrega nuevos chirps.

## Requisitos Previos

- **Docker** y **Docker Compose** instalados en tu sistema local
- **Git** para clonar el repositorio

## Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone https://github.com/EduerPJ/chirper.git
cd chirper
```

### 2. Instalar Dependencias de PHP

Si tienes **Composer** instalado localmente:
```bash
composer install
```

Si **NO** tienes Composer instalado, usa este contenedor temporal:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd)":/var/www/html \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    bash -c "composer install --ignore-platform-reqs"
```

### 3. Configurar Archivos de Entorno

```bash
cp .env.example .env
cp .env.testing.example .env.testing
```

### 4. Iniciar Laravel Sail

```bash
vendor/bin/sail up -d
```

### 5. Instalar Dependencias de Node.js y Configurar Husky

```bash
vendor/bin/sail npm install
```

### 6. Generar Clave de la Aplicación

```bash
vendor/bin/sail artisan key:generate
```

### 7. Ejecutar Migraciones

```bash
vendor/bin/sail artisan migrate
```

### 8. Compilar Assets Frontend

```bash
vendor/bin/sail npm run dev
```

### 9. Acceder a la Aplicación

Abre tu navegador y ve a: **http://localhost**

¡Regístrate e interactúa con la aplicación!

## Herramientas de Desarrollo

Este proyecto incluye un conjunto de herramientas para mantener la calidad del código:

### Pre-commit con Husky
- **Laravel Pint**: Formateador de código PHP (configuración por defecto)
- **Larastan (PHPStan)**: Análisis estático de código PHP (Nivel 5)
- **Gitmoji**: El repositorio está automatizado para solicitar un emoji descriptivo en cada commit usando gitmoji
- Se ejecuta automáticamente en cada commit

> 📝 **Nota sobre Gitmoji**: Al hacer commit, el sistema te solicitará automáticamente seleccionar un emoji que represente el tipo de cambio (✨ para features, 🐛 para fixes, 📝 para documentación, etc.). Esto mejora la legibilidad del historial de commits.

### Comandos Útiles

```bash
# Ejecutar todas las pruebas
vendor/bin/sail test

# Ejecutar pruebas específicas (chirps y notificaciones)
vendor/bin/sail test --filter=ChirpTest
vendor/bin/sail test --filter=NotificationTest

# Formatear código con Laravel Pint
vendor/bin/sail pint

# Análisis estático con Larastan
vendor/bin/sail php vendor/bin/phpstan analyse

# Detener contenedores
vendor/bin/sail down

# Ver logs en tiempo real
vendor/bin/sail logs -f

```

## Funcionalidades Principales

- ✅ **Autenticación de usuarios** (registro, login, logout)
- ✅ **CRUD de Chirps** (crear, leer, actualizar, eliminar)
- ✅ **Notificaciones por correo** cuando otros usuarios publican chirps
- ✅ **Interfaz moderna** con Tailwind CSS
- ✅ **Componentes reactivos** con Livewire/Volt

## Estructura del Proyecto

- **Backend**: Laravel 12 con PHP 8.4
- **Frontend**: Livewire + Volt + Tailwind CSS
- **Base de datos**: MySQL (via Docker)
- **Calidad de código**: Laravel Pint + Larastan + Husky
- **Testing**: PHPUnit


## Licencia

Este proyecto está bajo la licencia MIT.
