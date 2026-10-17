# Ocean Shop - Mini E-Commerce Order Management API

API لإدارة المستخدمين والمنتجات والطلبات، مع صلاحيات مختلفة بين Customer و Admin.

## التقنيات (Stack)

- PHP 8.x + Laravel 10
- MySQL
- Laravel Sanctum (Token Authentication)

**سبب الاختيار:** لارافيل يوفر أدوات جاهزة للـ Authentication والـ Migrations والـ Validation، مما يسرّع التطوير ويحافظ على تنظيم الكود.

## طريقة التشغيل

```bash
composer install
cp .env.example .env
php artisan key:generate
```

أنشئي قاعدة بيانات اسمها `ocean_shop`، وعدّلي إعدادات `DB_` في ملف `.env`، ثم:

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
php artisan serve
```

## حساب الأدمن (للتجربة)

- Email: `admin@example.com`
- Password: `admin12345`

## الـ Endpoints

كل الطلبات يجب أن تحتوي Header: `Accept: application/json`
والمحمية تحتاج: `Authorization: Bearer {token}`

| الطريقة   | الرابط                        | الوصف            | الصلاحية   |
| --------- | ----------------------------- | ---------------- | ---------- |
| POST      | /api/auth/register            | إنشاء حساب       | عام        |
| POST      | /api/auth/login               | تسجيل الدخول     | عام        |
| GET       | /api/products                 | عرض المنتجات     | عام        |
| GET       | /api/products/{id}            | عرض منتج         | عام        |
| POST      | /api/products                 | إضافة منتج       | Admin      |
| PUT/PATCH | /api/products/{id}            | تعديل منتج       | Admin      |
| DELETE    | /api/products/{id}            | حذف منتج         | Admin      |
| POST      | /api/orders                   | إنشاء طلب        | مسجّل دخول |
| GET       | /api/orders/my-orders         | طلباتي           | مسجّل دخول |
| GET       | /api/admin/orders             | كل الطلبات       | Admin      |
| PATCH     | /api/admin/orders/{id}/status | تغيير حالة الطلب | Admin      |

مثال إنشاء طلب:

```json
{ "items": [{ "product_id": 1, "quantity": 2 }] }
```

## قاعدة البيانات

الجداول: `users` (مع role)، `products`، `orders`، `order_items`.
العلاقات: User ← Orders ← Order Items → Product.
