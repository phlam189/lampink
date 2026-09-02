# 📚 Web Đọc Truyện Laravel 13 (Novel & Manga Platform)

Hệ thống Website đọc truyện chữ & truyện tranh hiện đại, tối ưu hiệu năng cao, xây dựng trên nền tảng **Laravel 13**, **FilamentPHP v4/v5 (TALL Stack)**, **Livewire 4**, **Redis Cache** và **MySQL**.

---

## 🌟 Tính Năng Chính (Features)

### 👤 Dành cho Độc Giả (Frontend User)
- **Đọc Truyện Chữ & Truyện Tranh**: Giao diện tối ưu, tùy chỉnh kích thước chữ / font / nền (truyện chữ) hoặc cuộn dọc mượt mà (truyện tranh).
- **Tìm Kiếm & Lọc Nâng Cao**: Tìm theo từ khóa, tác giả, trạng thái (Đang ra/Hoàn thành), lọc đa thể loại.
- **Tủ Sách Cá Nhân**: Theo dõi truyện yêu thích, tự động lưu lịch sử chương vừa đọc.
- **Tương Tác**: Bình luận chương truyện, đánh giá xếp hạng.

### ⚙️ Dành cho Quản Trị & Biên Tập (Filament v4/v5 Admin Panel)
- **Quản Lý Bộ Truyện & Chương**: Quản lý thông tin bộ truyện, uploader, tác giả, thể loại.
- **RelationManager Quản Lý Chương**: Thêm, sửa, xem danh sách chương trực tiếp trong bộ truyện.
- **Trình Biên Tập Đa Năng**: Rich Text Editor cho truyện chữ, Bulk Image Uploader cho truyện tranh.
- **Phân Quyền Chi Tiết (RBAC)**: Admin, Translator/Uploader, Reader via `spatie/laravel-permission`.
- **Dashboard Widgets**: Thống kê số chương mới, lượt đọc, biểu đồ tăng trưởng.

---

## 🛠️ Yêu Cầu Hệ Thống (Requirements - Laravel 13 Stack)
- **PHP**: `>= 8.3` (Khuyên dùng PHP 8.3 hoặc PHP 8.4)
- **Framework**: `Laravel 13.x`
- **Admin CMS**: `FilamentPHP 4.x / 5.x` & `Livewire 4.x`
- **Composer**: `>= 2.7`
- **Database**: MySQL `>= 8.0` hoặc MariaDB `>= 10.11`
- **Cache / Queue**: Redis `>= 7.2`
- **Node.js & NPM**: Node `>= 20.x` (Build assets với Vite)

---

## 🚀 Hướng Dẫn Cài Đặt (Installation)

### 🐳 Phương Án A: Cài Đặt Với Laradock / Docker (Khuyên dùng)

Laradock hoặc Docker Lite giúp đóng gói toàn bộ môi trường PHP 8.3+, Nginx, MySQL 8.0, Redis 7+ một cách nhất quán.

#### 1. Clone Project & Thêm Laradock Submodule
```bash
git clone https://github.com/your-username/web-doc-truyen-laravel13.git
cd web-doc-truyen-laravel13

# Add Laradock dưới dạng Git Submodule
git submodule add https://github.com/Laradock/laradock.git laradock
```

#### 2. Cấu Hình Laradock Environment
```bash
cd laradock
cp .env.example .env
```
Chỉnh sửa các thông số cơ bản trong file `laradock/.env`:
```env
# Định vị thư mục gốc source code Laravel
APP_CODE_PATH_HOST=../

# PHP 8.3 cho Laravel 13
PHP_VERSION=8.3

# Cấu hình MySQL & Redis
MYSQL_VERSION=8.0
MYSQL_DATABASE=web_doc_truyen
MYSQL_USER=default
MYSQL_PASSWORD=secret
MYSQL_PORT=3306

REDIS_PORT=6379
```

#### 3. Khởi Chạy Các Docker Containers
```bash
docker-compose up -d nginx mysql redis workspace phpmyadmin
```

#### 4. Cấu Hình Laravel Trong Workspace Container
```bash
docker-compose exec workspace bash
```

Bên trong Workspace Container (`/var/www`):
```bash
# 1. Cài đặt Composer & NPM Dependencies
composer install
npm install && npm run build

# 2. Tạo file cấu hình .env
cp .env.example .env
php artisan key:generate
```

Cập nhật thông số kết nối Database/Redis trong file `.env` của Laravel 13:
```env
APP_NAME="Web Đọc Truyện"
APP_URL=http://localhost

# Kết nối MySQL qua container "mysql"
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=web_doc_truyen
DB_USERNAME=default
DB_PASSWORD=secret

# Kết nối Redis qua container "redis"
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

#### 5. Migration, Seed & Khởi Tạo Admin
```bash
# Chạy Migration & Seed dữ liệu mẫu
php artisan migrate --seed

# Link Storage & Publish Filament Assets
php artisan storage:link
php artisan filament:assets

# Tạo tài khoản Admin Filament
php artisan make:filament-user
```

#### 6. Chạy Queue Worker Trực Tiếp Hoặc Bằng Worker Container
```bash
php artisan queue:work redis
```

#### 🌐 Các Địa Chỉ Truy Cập
* **Website Frontend**: `http://localhost`
* **Admin Panel (Filament v4/v5)**: `http://localhost/admin`
* **Database Manager (PhpMyAdmin)**: `http://localhost:8080`

---

### 🖥️ Phương Án B: Cài Đặt Truyền Thống (Local Native PHP 8.3+)

```bash
# 1. Clone & Cài đặt
git clone https://github.com/your-username/web-doc-truyen-laravel13.git
cd web-doc-truyen-laravel13
composer install
npm install && npm run build

# 2. Cấu hình .env
cp .env.example .env
php artisan key:generate

# 3. Migration, Seed & Assets
php artisan migrate --seed
php artisan storage:link
php artisan filament:assets
php artisan make:filament-user

# 4. Chạy App & Queue
php artisan serve
php artisan queue:work redis
```

---

## 🏗️ Kiến Trúc & Source Code Patterns (Laravel 13)

Dự án áp dụng cấu trúc gọn nhẹ của **Laravel 13** kết hợp các Design Pattern hiện đại:

### 1. Cấu Trúc Khung Ứng Dụng Tinh Gọn (Laravel 13 Architecture)
- Cấu hình Middleware, Exception Handling và Schedule tập trung tại `bootstrap/app.php`.
- Đăng ký Artisan Commands & Scheduled Jobs gọn gàng trong `routes/console.php`.

### 2. Action Pattern (`app/Actions`)
Mỗi tác vụ nghiệp vụ đơn lẻ (Single Responsibility) được đóng gói thành một Action Class:
- `CreateChapterAction`: Xử lý tạo chương mới, ghi log và clear cache.
- `RecordChapterViewAction`: Ghi nhận lượt xem vào Redis Buffer.

### 3. Service-Repository Pattern
- **Repositories** (`app/Repositories`): Đóng gói truy vấn Eloquent (`NovelRepository`, `ChapterRepository`).
- **Services** (`app/Services`): Kết hợp logic từ nhiều Repositories & Caches.

### 4. Filament v4/v5 Resources & Relation Managers (`app/Filament/Resources`)
- Quản lý giao diện Admin bằng PHP Declarative Code (`NovelResource`, `ChapterResource`).
- Sử dụng `ChaptersRelationManager` để biên tập chương mượt mà ngay tại chi tiết bộ truyện.

### 5. Buffered Views Counter (Redis + Queue Job)
- Lượt xem chương ghi tăng trong Redis via `HINCRBY`.
- `SyncViewsToDatabaseJob` định kỳ 5 phút ghi ngược lượt xem từ Redis về MySQL.

---

## 📏 Quy Chuẩn Code (Coding Standards)

- **Coding Style**: Tương thích **PSR-12 / PER Coding Style 2.0**.
- **Linter & Formatter**: Sử dụng **Laravel Pint**:
  ```bash
  ./vendor/bin/pint
  ```
- **Khai Báo Kiểu Dữ Liệu Chặt Chẽ (Strict Types)**:
  - Khai báo `declare(strict_types=1);` ở đầu mọi file PHP.
  - Type-hinting bắt buộc cho Parameter và Return Types.
- **Naming Conventions**:
  - `Models / Classes / Actions`: PascalCase (`Novel`, `CreateChapterAction`).
  - `Database Tables / Columns`: snake_case (`novels`, `views_count`).
  - `Methods / Variables`: camelCase (`getLatestChapters()`, `$novelSlug`).
  - `Enums`: Native PHP 8.3+ Backed Enums (`NovelStatus::Ongoing`).
