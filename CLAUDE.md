# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 10 web application with Vue.js 3 frontend and Bootstrap 5 styling, set up for developing a chess game. The project uses Vite as the build tool for modern asset compilation.

## Architecture

- **Backend**: Laravel 10 (PHP 8.1)
- **Frontend**: Vue.js 3 with Composition API
- **CSS Framework**: Bootstrap 5
- **Build Tool**: Vite (replaces Laravel Mix)
- **Package Manager**: Composer (PHP) and npm (JavaScript)

## Development Workflow

### Common Commands

- **Start development server**: `php artisan serve`
- **Build assets for development**: `npm run dev`
- **Build assets for production**: `npm run build` 
- **Watch assets during development**: `npm run dev` (Vite hot reload)
- **Install PHP dependencies**: `composer install`
- **Install JavaScript dependencies**: `npm install`

### Key Files and Directories

- `resources/js/app.js` - Main Vue.js application entry point
- `resources/css/app.css` - Main stylesheet with Bootstrap imports
- `resources/views/app.blade.php` - Main Vue.js application template
- `resources/views/welcome.blade.php` - Default Laravel welcome page
- `routes/web.php` - Web routes definition
- `vite.config.js` - Vite configuration with Vue and Laravel plugins

### Frontend Development

- Vue.js 3 components should be added to `resources/js/components/`
- The main Vue app is mounted on `#app` element
- Bootstrap 5 classes are available globally
- Vite provides hot module replacement for rapid development

### Backend Development

- Controllers in `app/Http/Controllers/`
- Models in `app/Models/`
- Routes in `routes/web.php` and `routes/api.php`
- Views in `resources/views/`

## Testing

- **PHPUnit**: `php artisan test` or `vendor/bin/phpunit`
- **Laravel Feature Tests**: Located in `tests/Feature/`
- **Laravel Unit Tests**: Located in `tests/Unit/`

## Project Structure

The application follows Laravel's standard MVC structure with Vue.js handling the frontend interactivity. The main application view (`app.blade.php`) contains the Vue.js mount point and includes the compiled assets via Vite.