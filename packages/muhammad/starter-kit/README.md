# Laravel Smart CRUD Starter Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muhammad/starter-kit.svg?style=flat-square)](https://packagist.org/packages/muhammad/starter-kit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/muhammad/starter-kit/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/muhammad/starter-kit/actions)

A professional Laravel package designed to automate API development following **Clean Architecture** principles.

## 🚀 Key Features

- **Smart CRUD Generator**: Build full features in seconds.
- **Smart Sync**: Detects database relationships from foreign keys.
- **Smart Validation**: Auto-generates rules from DB metadata.
- **Clean Architecture**: Services and Repositories by default.

## 🛠️ The New Workflow 🚀

Build your next project with lightning speed:

1.  **Clone & Setup**: Setup your Docker environment.
2.  **Define Schema**: Create your Laravel migrations.
3.  **Migrate & Sync**: Run `php artisan migrate && php artisan smart:sync-relations`.
4.  **Generate Feature**: Run `php artisan smart:crud Product --api --with-service --with-tests`.
5.  **Focus on Business**: Boilperlate is done. Focus on your unique logic!

## 📦 Installation

```bash
composer require muhammad/starter-kit --dev
```

Publish stubs (optional):
```bash
php artisan vendor:publish --tag="starter-kit-stubs"
```

## 🧪 Testing

```bash
composer test
```

## 📜 License

The MIT License (MIT).
