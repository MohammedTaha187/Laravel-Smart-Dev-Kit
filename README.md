# 🚀 Laravel Smart Dev Kit (Easy Dev)

A powerful, production-ready SDK for rapid Laravel API development. This kit follows the **Interface-Service-Repository** architecture and automates the creation of 11+ essential files per CRUD module, ensuring PSR-4 compliance and full compatibility with **Modular Architecture**.

---

## ✨ Features

- **Modular Architecture Support**: Seamlessly integrate with `nwidart/laravel-modules`.
- **Automated CRUD Generation**: Generates Models, Migrations, Controllers, Services, Repositories, Interfaces, DTOs, Requests, Resources, and Policies.
- **Smart Mass Assignment**: Automatically detects database columns and populates `$fillable` fields.
- **Automated Factories**: Generates Eloquent Factories and correctly resolves them in modular namespaces.
- **Instant Testing**: Generates **Pest Integration Tests** with automated role/permission seeding.
- **Clean Architecture**: Enforces a strict separation of concerns for maintainable code.

---

## 🛠️ Installation & Setup

### 1. Requirements
Ensure you are using **PHP 8.3+** and **Laravel 12/13**.

### 2. Initial Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### 3. Running with Sail (Docker)
```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan migrate
```

---

## 📖 Usage Guide

### Generating a New CRUD Module
To generate a complete CRUD in a specific module (e.g., `Catalog`), run:

```bash
php artisan smart:crud Product --module=Catalog
```

### What happens under the hood?
1. **Initialization**: Checks for `module.json` and creates it if missing.
2. **Generation**: Creates 11 files with correct namespaces.
3. **Discovery**: Automatically enables the module and refreshes the autoloader.
4. **Service Binding**: Binds interfaces to implementations in the generated `CatalogServiceProvider`.

---

## 🧪 Testing

The generated tests are ready to run out of the box with **Pest**. To test a specific module:

```bash
./vendor/bin/sail artisan test Modules/Catalog/Tests/Feature/ProductTest.php
```

---

## 📂 Project Structure

- `app/`: Core application logic.
- `Modules/`: Self-contained business modules.
- `packages/muhammad/easy-dev/`: The core SDK source and stubs.
- `database/`: Migrations and seeders.

---

## 🤝 Contribution & License
Developed by **Muhammad Taha**. Licensed under the **MIT License**.
