# 🛠️ Laravel Smart Dev Kit

[![Laravel](https://img.shields.io/badge/Laravel-13.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Sail-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://laravel.com/docs/sail)
[![Pest](https://img.shields.io/badge/Tests-Pest-F4645F?style=for-the-badge&logo=testinglibrary&logoColor=white)](https://pestphp.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

**Laravel Smart Dev Kit** is a professional, production-ready starter kit for high-speed API development. It ships with a built-in SDK (`muhammad/easy-dev`) that automates the generation of entire feature layers — so you can focus purely on **business logic**.

---

## ✨ The SDK generates 11 files with a single command

```bash
php artisan smart:crud Product \
  --api --with-service --with-data \
  --with-contracts --with-tests \
  --translatable=name,description \
  --soft-delete \
  --module=Inventory
```

| # | File | Layer |
|---|------|-------|
| 1 | `Models/Product.php` | Eloquent Model |
| 2 | `xxxx_create_products_table.php` | Migration |
| 3 | `Http/Controllers/Api/V1/Inventory/ProductController.php` | Controller |
| 4 | `Http/Requests/Api/V1/Inventory/StoreProductRequest.php` | Store Request |
| 5 | `Http/Requests/Api/V1/Inventory/UpdateProductRequest.php` | Update Request |
| 6 | `Http/Resources/Api/V1/Inventory/ProductResource.php` | API Resource |
| 7 | `Http/Resources/Api/V1/Inventory/ProductCollection.php` | API Collection |
| 8 | `Services/Contracts/ProductServiceInterface.php` + `Services/ProductService.php` | Service Layer |
| 9 | `Repositories/Contracts/ProductRepositoryInterface.php` + `Repositories/ProductRepository.php` | Repository Layer |
| 10 | `DTOs/ProductData.php` | Spatie Data DTO |
| 11 | `Policies/ProductPolicy.php` + `Tests/Feature/ProductTest.php` | Policy + Pest Tests |

---

## 🏛️ Tech Stack

| Category | Package |
|---|---|
| Framework | Laravel 13 + PHP 8.3 |
| Auth | `php-open-source-saver/jwt-auth` (Stateless JWT) |
| Permissions | `spatie/laravel-permission` |
| DTOs | `spatie/laravel-data` |
| Translatable | `spatie/laravel-translatable` |
| Media | `spatie/laravel-medialibrary` |
| Query Builder | `spatie/laravel-query-builder` |
| Activity Log | `spatie/laravel-activitylog` |
| Modules | `nwidart/laravel-modules` |
| Multi-tenancy | `stancl/tenancy` |
| API Docs | `knuckleswtf/scribe` |
| Testing | `pestphp/pest` |
| Dev Environment | Laravel Sail (Docker) |

---

## 🏗️ Architecture

The flow is strict and never broken:

```
Request → Controller → ServiceInterface → ServiceImpl
                                       → RepositoryInterface → RepositoryImpl → Model
```

```
app/
├── Http/
│   ├── Controllers/Api/V1/{Module}/    ← calls ServiceInterface only
│   ├── Requests/Api/V1/{Module}/
│   │   ├── Store{Model}Request.php
│   │   └── Update{Model}Request.php
│   └── Resources/Api/V1/{Module}/
│       ├── {Model}Resource.php
│       └── {Model}Collection.php
├── Models/
├── Services/
│   ├── Contracts/{Model}ServiceInterface.php
│   └── {Model}Service.php             ← injects RepositoryInterface, uses DTO
├── Repositories/
│   ├── Contracts/{Model}RepositoryInterface.php
│   └── {Model}Repository.php          ← extends BaseRepository
├── DTOs/
│   └── {Model}Data.php                ← Spatie Data Object (readonly properties)
├── Policies/
│   └── {Model}Policy.php              ← uses hasPermissionTo()
└── Actions/

Modules/{ModuleName}/                  ← same structure inside a module
```

**Strict Rules:**
1. Interface قبل Implementation — دايماً
2. Spatie Data للـ input في الـ Service — مش array
3. API Resource للـ output — مش `return $model`
4. Pest للـ tests — مش PHPUnit syntax
5. Full integration tests — مش mocks

---

## 📡 Unified API Response

كل response في المشروع بيطلع بالشكل ده:

```json
{
  "success": true,
  "message": "Done",
  "data":    {},
  "meta":    {}
}
```

---

## 🚀 Quick Start

```bash
# 1. Clone
git clone https://github.com/MohammedTaha187/Laravel-Smart-Dev-Kit.git my-project
cd my-project

# 2. Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 3. Start Docker environment
./vendor/bin/sail up -d

# 4. Run migrations
./vendor/bin/sail artisan migrate

# 5. Auto-detect DB relationships
./vendor/bin/sail artisan smart:sync-relations

# 6. Generate a full feature (11 files)
./vendor/bin/sail artisan smart:crud Product \
  --api --with-service --with-data \
  --with-contracts --with-tests \
  --module=Inventory
```

---

## 🪄 Available Commands

### `smart:crud` — الأمر الرئيسي

```bash
php artisan smart:crud {ModelName} [options]

Options:
  --api                     API Controller بـ Api/V1 namespace
  --with-service            Service + ServiceInterface
  --with-data               Spatie Data DTO
  --with-contracts          Repository + Service Interfaces
  --translatable=name,desc  Spatie Translatable fields
  --with-media              Spatie Media Library
  --soft-delete             SoftDeletes + restore
  --with-tests              Pest Feature Test
  --with-logs               Activity logging
  --with-notification       Notification class
  --with-event              Model events
  --module={ModuleName}     Generate inside Modules/{Name}/
```

### `smart:sync-relations` — اكتشاف العلاقات

```bash
php artisan smart:sync-relations
```

يقرأ الـ DB الفعلية → يكتشف foreign keys → يكتب `belongsTo` / `hasMany` في الـ Models تلقائياً.

### `smart:from-migration` — بناء من migration موجودة

```bash
php artisan smart:from-migration products
```

يقرأ الـ migration → يستنتج الـ Model → يولّد الـ 11 ملف.

---

## 🧪 Testing

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run with coverage
./vendor/bin/sail artisan test --coverage

# Run specific feature
./vendor/bin/sail artisan test --filter=ProductTest
```

كل feature مُولَّدة بتيجي مع **Pest Feature Test كامل** بـ real admin auth وـunified response assertions.

---

## 🐳 Docker Services

| Service | Port | Description |
|---|---|---|
| `laravel.test` | 80 | PHP 8.3 App Server |
| `mysql` | 3306 | MySQL 8.4 |
| `redis` | 6379 | Redis (Cache + Queue) |
| `mailpit` | 8025 | Email Testing UI |
| `horizon` | — | Queue Worker Dashboard |
| `scheduler` | — | Cron Jobs |
| `nginx` | 8080 | Production Reverse Proxy |

---

## 🔒 Smart Validation (Auto-generated from DB Schema)

الـ SDK بيقرأ columns الـ DB ويولّد validation rules تلقائياً:

```
'email'      → ['required', 'email', 'max:255']
'*_id'       → ['required', 'exists:{table},id']
'nullable'   → ['nullable']
'varchar(n)' → ['max:n']
'boolean'    → ['boolean']
'decimal'    → ['numeric', 'min:0']
'enum(...)'  → ['in:value1,value2,...']
```

---

## 👨‍💻 Author

**Muhammad Taha** — Backend Developer

---

*Built for speed, stability, and clean code. 🎯*
