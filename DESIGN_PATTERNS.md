# 🏗️ Laravel 13 Source Code Design Patterns & Coding Standards (`DESIGN_PATTERNS.md`)

Tài liệu quy định các **Mẫu thiết kế phần mềm (Design Patterns)**, **Kiến trúc mã nguồn (Source Architecture)** và **Quy chuẩn lập trình (Coding Standards)** áp dụng cho dự án **Web Đọc Truyện (Novel & Manga Platform)**.

---

## 📐 1. Kiến Trúc Tổng Thể (Architecture Overview)

Dự án áp dụng mô hình **Layered Architecture** kết hợp với **TALL Stack (Tailwind, Alpine, Livewire, Laravel 13)** & **FilamentPHP v4/v5**. 

```
                               ┌────────────────────────────────────────┐
                               │       HTTP / CLI / Filament UI         │
                               └───────────────────┬────────────────────┘
                                                   │
                                                   ▼
                               ┌────────────────────────────────────────┐
                               │           Actions / Controllers        │
                               └───────────────────┬────────────────────┘
                                                   │
                                                   ▼
                               ┌────────────────────────────────────────┐
                               │               Services                 │
                               └─────────┬────────────────────┬─────────┘
                                         │                    │
                                         ▼                    ▼
                               ┌──────────────────┐  ┌──────────────────┐
                               │   Repositories   │  │   Redis / Jobs   │
                               └─────────┬────────┘  └──────────────────┘
                                         │
                                         ▼
                               ┌──────────────────┐
                               │   Eloquent / DB  │
                               └──────────────────┘
```

* **UI/Admin Layer**: Filament Resources & Livewire Components.
* **Execution Layer**: Action Classes (Single-purpose domain tasks).
* **Business Logic Layer**: Service Classes (Complex orchestration & domain rules).
* **Data Access Layer**: Repositories & Eloquent Models.
* **Performance & Async Layer**: Redis Buffer & Laravel Queue Jobs.

---

## 🛠️ 2. Các Design Patterns Cốt Lõi (Core Design Patterns)

### 2.1. Action Pattern (`app/Actions/`)
* **Mục đích**: Áp dụng nguyên lý **Single Responsibility (SRP)**. Mỗi class chỉ đảm nhận đúng **một tác vụ duy nhất** trong hệ thống và có thể gọi lại dễ dàng từ Controller, Livewire Component, Filament hay Queue Job.
* **Quy tắc**:
  * Đặt tên theo cú pháp hành động: `[Verb][Entity]Action` (Ví dụ: `CreateChapterAction`, `ProcessPayoutAction`).
  * Sử dụng magic method `__invoke()` hoặc method `execute()` duy nhất.

#### 💻 Code Minh Họa: `CreateChapterAction.php`
```php
declare(strict_types=1);

namespace App\Actions\Chapters;

use App\Models\Chapter;
use App\Models\ChapterBody;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateChapterAction
{
    /**
     * Tạo chương mới và cập nhật con trỏ Linked List chuyển chương.
     */
    public function execute(array $data): Chapter
    {
        return DB::transaction(function () use ($data) {
            // 1. Tìm chương vừa đăng trước đó của bộ truyện này
            $previousChapter = Chapter::where('novel_id', $data['novel_id'])
                ->where('chapter_number', '<', $data['chapter_number'])
                ->orderBy('chapter_number', 'desc')
                ->first();

            // 2. Tạo chương mới (Metadata)
            $chapter = Chapter::create([
                'novel_id' => $data['novel_id'],
                'chapter_number' => $data['chapter_number'],
                'title' => $data['title'] ?? null,
                'slug' => Str::slug("chuong-{$data['chapter_number']}-" . ($data['title'] ?? '')),
                'price_coins' => $data['price_coins'] ?? 0,
                'is_vip' => $data['is_vip'] ?? false,
                'prev_chapter_id' => $previousChapter?->id,
                'published_at' => $data['published_at'] ?? now(),
            ]);

            // 3. Tạo nội dung chương (Tách bảng nặng chapter_bodies)
            ChapterBody::create([
                'chapter_id' => $chapter->id,
                'content' => $data['content'] ?? null,
                'images' => $data['images'] ?? null,
            ]);

            // 4. Cập nhật con trỏ next_chapter_id cho chương trước đó (Linked List)
            if ($previousChapter) {
                $previousChapter->update(['next_chapter_id' => $chapter->id]);
            }

            return $chapter;
        });
    }
}
```

---

### 2.2. Service - Repository Pattern (`app/Services/` & `app/Repositories/`)
* **Repository**: Chịu trách nhiệm đóng gói toàn bộ câu truy vấn CSDL (`Eloquent Query`), giúp code không bị lặp lại và dễ mock khi viết Unit Test.
* **Service**: Chịu trách nhiệm điều phối công việc giữa Repositories, Caches, Events và Notifications.

#### 💻 Code Minh Họa: `NovelRepository.php`
```php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Novel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class NovelRepository
{
    /**
     * Lấy danh sách truyện HOT có caching 15 phút.
     */
    public function getHotNovels(int $limit = 10): mixed
    {
        return Cache::remember("novels:hot:{$limit}", now()->addMinutes(15), function () use ($limit) {
            return Novel::query()
                ->where('is_hot', true)
                ->where('status', '!=', 'paused')
                ->with(['author:id,name,slug', 'categories:id,name,slug'])
                ->orderByDesc('views_total')
                ->limit($limit)
                ->get();
        });
    }
}
```

---

### 2.3. Buffered Views Counter Pattern (Redis + Queue Job)
* **Mục đích**: Giải quyết điểm nghẽn **I/O CSDL khi cao điểm** (hàng vạn lượt đọc chương đồng thời).
* **Luồng xử lý**:
  1. Khi đọc chương, ứng dụng không `UPDATE` MySQL trực tiếp mà ghi nhận tăng biến đếm trong **Redis Hash**.
  2. Scheduled Job `SyncViewsToDatabaseJob` chạy 5 phút/lần gom tổng lượt đọc từ Redis và cập nhật hàng loạt (Batch Update) vào MySQL.

#### 💻 Code Minh Họa: `RecordChapterViewAction.php`
```php
declare(strict_types=1);

namespace App\Actions\Chapters;

use Illuminate\Support\Facades\Redis;

final class RecordChapterViewAction
{
    public function execute(int $novelId, int $chapterId): void
    {
        // Tăng đệm lượt xem trong Redis Hash (Bất đồng bộ)
        Redis::hincrby('buffer:novel_views', (string) $novelId, 1);
        Redis::hincrby('buffer:chapter_views', (string) $chapterId, 1);
    }
}
```

---

### 2.4. Double-Entry Ledger & Royalty Distribution Pattern
* **Mục đích**: Bảo toàn tính toàn vẹn tài chính khi độc giả dùng xu mua chương VIP và tự động phân bổ doanh thu cho Tác giả / Nhóm dịch.
* **Luồng xử lý**:
  1. Sử dụng **DB Transaction** & **Pessimistic Locking (`lockForUpdate()`)** chống Race Condition.
  2. Trừ xu người mua và ghi sổ cái `coin_transactions`.
  3. Trích `%` doanh thu ghi nhận vào `revenue_logs` và cộng số dư ví `author_wallets`.

#### 💻 Code Minh Họa: `PurchaseChapterAction.php`
```php
declare(strict_types=1);

namespace App\Actions\Finance;

use App\Models\Chapter;
use App\Models\ChapterPurchase;
use App\Models\CoinTransaction;
use App\Models\RevenueLog;
use App\Models\User;
use App\Models\AuthorWallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PurchaseChapterAction
{
    public function execute(User $user, Chapter $chapter): ChapterPurchase
    {
        return DB::transaction(function () use ($user, $chapter) {
            // Lock dòng user để tránh Race Condition mua song song
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            if ($user->coins_balance < $chapter->price_coins) {
                throw new RuntimeException('Số dư xu không đủ để mở khóa chương này.');
            }

            // 1. Trừ xu người đọc
            $user->coins_balance -= $chapter->price_coins;
            $user->save();

            // 2. Ghi nhật ký sổ cái (Ledger Log)
            CoinTransaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => -$chapter->price_coins,
                'balance_after' => $user->coins_balance,
                'reference_id' => $chapter->id,
            ]);

            // 3. Ghi nhận mở khóa chương
            $purchase = ChapterPurchase::create([
                'user_id' => $user->id,
                'chapter_id' => $chapter->id,
                'coins_paid' => $chapter->price_coins,
                'purchased_at' => now(),
            ]);

            // 4. Chia sẻ doanh thu cho Tác giả / Dịch giả (Ví dụ: 70%)
            $novel = $chapter->novel;
            $authorId = $novel->uploader_id ?? $novel->team?->leader_id;

            if ($authorId) {
                $shareRate = $novel->revenue_share_rate ?? 70.00;
                $authorCoins = (int) round($chapter->price_coins * ($shareRate / 100));
                $platformCoins = $chapter->price_coins - $authorCoins;

                RevenueLog::create([
                    'author_id' => $authorId,
                    'novel_id' => $novel->id,
                    'chapter_id' => $chapter->id,
                    'purchase_id' => $purchase->id,
                    'gross_coins' => $chapter->price_coins,
                    'author_share_rate' => $shareRate,
                    'author_earned_coins' => $authorCoins,
                    'platform_earned_coins' => $platformCoins,
                ]);

                // Cộng xu vào ví tác giả
                AuthorWallet::firstOrCreate(['user_id' => $authorId])
                    ->increment('balance_coins', $authorCoins);
            }

            return $purchase;
        });
    }
}
```

---

## 📏 3. Quy Chuẩn Code (Coding Standards & Quality Gates)

### 3.1. Standards & Code Formatter
* **Chuẩn Coding Style**: Tuân thủ tiêu chuẩn **PSR-12 / PER Coding Style 2.0**.
* **Công cụ Tự động formatting**: Bắt buộc chạy **Laravel Pint** trước khi thực hiện commit:
  ```bash
  ./vendor/bin/pint
  ```

### 3.2. Khai Báo Kiểu Dữ Liệu Chặt Chẽ (Strict Typing)
* Bắt buộc có `declare(strict_types=1);` ở đầu **MỌI** file PHP trong dự án.
* Bắt buộc ghi nhận **Type-hinting** cho thuộc tính, tham số truyền vào và kiểu dữ liệu trả về (Return Types).

### 3.3. Quy Tắc Đặt Tên (Naming Conventions)
* **Classes / Actions / Services**: `PascalCase` (`NovelRepository`, `ProcessPayoutAction`).
* **Methods / Variables**: `camelCase` (`getChapterBySlug()`, `$novelList`).
* **Database Tables / Columns**: `snake_case` (`chapter_bodies`, `views_total`).
* **Enums**: Native PHP 8.1+ Backed Enums (`enum NovelStatus: string`).

---

📁 *Tài liệu này được lưu trữ chính thức tại `DESIGN_PATTERNS.md` trong bộ khung mã nguồn của dự án.*
