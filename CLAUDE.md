# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Development Server
```bash
composer dev  # Starts all services (server, queue, logs, vite) concurrently
# Or run individually:
php artisan serve
php artisan queue:work
npm run dev
```

### Testing
```bash
composer test  # Runs Laravel tests with config clear
php artisan test  # Run tests directly
./vendor/bin/phpunit  # Run PHPUnit directly
./vendor/bin/phpunit tests/Feature/ChirpTest.php  # Run specific test file
./vendor/bin/phpunit --filter test_name  # Run specific test method
```

### Code Quality
```bash
./vendor/bin/pint  # Laravel Pint for code formatting
./vendor/bin/phpstan  # Static analysis with Larastan
```

### Build
```bash
npm run build  # Build assets for production
```

## Architecture Overview

This is a Laravel 12 application implementing a Twitter-like "Chirper" social media platform using modern Laravel patterns:

### Tech Stack
- **Backend**: Laravel 12 with PHP 8.2+
- **Frontend**: Livewire 3 + Volt for reactive components
- **Styling**: Tailwind CSS with forms plugin
- **Database**: SQLite (default), supports other drivers
- **Build**: Vite with Laravel plugin
- **Authentication**: Laravel Breeze

### Key Architectural Patterns

**Livewire Components**: The app uses Livewire 3 with Volt for frontend reactivity instead of traditional Blade views. Main components are in `resources/views/livewire/`.

**Event-Driven Architecture**: Chirps dispatch `ChirpCreated` events when created, which trigger notifications via `SendChirpCreatedNotifications` listener.

**Notification System**: Uses Laravel's notification system with `NewChirp` notification class for user alerts.

**Policy-Based Authorization**: `ChirpPolicy` handles authorization for chirp operations.

### Database Schema
- `users` table with standard Laravel auth fields
- `chirps` table with `message` and `user_id` columns
- Uses SQLite by default for development simplicity

### File Structure Notes
- Controllers are minimal - most logic is in Livewire components
- Models use events (`$dispatchesEvents`) for automatic event firing
- Views are primarily Livewire Volt components, not traditional Blade
- Uses database sessions and queue drivers for development

### Development Notes
- Uses Husky for Git hooks with gitmoji-cli for commit messages
- Configured with PHPStan (Larastan) for static analysis
- Laravel Pint for code style enforcement
- Queue processing required for notifications to work properly

## Testing Architecture

### Test Environment
- **Database**: SQLite in-memory (`:memory:`) for fast test execution
- **Configuration**: Separate `.env.testing` and `phpunit.xml` settings
- **Mail**: Array driver for testing (captures emails without sending)
- **Queue**: Synchronous processing for immediate test results

### Test Types
- **Feature Tests**: Test complete user workflows using Livewire testing
- **Livewire Component Tests**: Test individual Volt components (`chirps.create`, `chirps.list`, `chirps.edit`)
- **Notification Tests**: Test event dispatching and email content
- **Authorization Tests**: Test policy enforcement for edit/delete operations

### Testing Key Components
- **ChirpTest.php**: Tests CRUD operations via Livewire components
- **ChirpNotificationTest.php**: Tests event-driven notification system
- **ChirpFactory.php**: Generates test data for chirps and users

### Livewire Testing Patterns
```php
// Test component actions
Livewire::actingAs($user)
    ->test('chirps.create')
    ->set('message', 'Test message')
    ->call('store')
    ->assertDispatched('chirp-created');

// Test authorization
Livewire::actingAs($user)
    ->test('chirps.edit', ['chirp' => $chirp])
    ->call('update')
    ->assertForbidden();
```