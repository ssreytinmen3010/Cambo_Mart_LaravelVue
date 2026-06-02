# Database Schema Documentation

## Overview

This document provides a comprehensive reference of all database tables, their columns, and relationships in the Cambo-Mart system.

---

## Tables

### 1. users

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique user identifier |
| role_id | foreignId | FK → roles.id, cascade on delete | User role reference |
| name | string(255) | Required | User's full name |
| email | string | Unique | User's email address |
| phone | string(50) | Unique, Nullable | User's phone number |
| password | string | Required | Hashed password |
| image | string | Nullable | User profile image path |
| status | boolean | Default: null | User status (Active/Inactive) |
| remember_token | string | Nullable | For "remember me" functionality |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Role (role_id)
- `hasMany`: Addresses (user_id)
- `hasMany`: Reviews (user_id)
- `hasMany`: Wishlists (user_id)
- `hasMany`: Contacts (user_id)
- `hasMany`: Orders (user_id)
- `hasMany`: HistoryOrders (user_id)

---

### 2. roles

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique role identifier |
| name | string(50) | Required | Role name (e.g., admin, user) |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `hasMany`: Users (role_id)

---

### 3. brands

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique brand identifier |
| name | string | Required | Brand name |
| image | string | Nullable | Brand logo/image path |
| status | boolean | Default: true | Brand status (active/inactive) |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `hasMany`: Products (brand_id)
- `belongsToMany`: Categories (via brand_category pivot)

---

### 4. categories

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique category identifier |
| name | string | Required | Category name |
| image | string | Nullable | Category image path |
| description | text | Nullable | Category description |
| brand_id | bigint, unsigned | Required | Reference to brand |
| qty_item | bigint | Default: 0 | Number of items in category |
| status | boolean | Default: true | Category status |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Brand (brand_id)
- `hasMany`: Products (category_id)
- `belongsToMany`: Brands (via brand_category pivot)

---

### 5. products

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique product identifier |
| name | string | Required | Product name |
| image | string | Nullable | Product image path |
| product_code | string | Unique | Unique product SKU/code |
| category_id | bigint, unsigned | Required | Reference to category |
| brand_id | bigint, unsigned | Required | Reference to brand |
| price | decimal(10,2) | Required | Product price |
| stock | integer | Default: 0 | Available stock quantity |
| description | text | Nullable | Product description |
| status | boolean | Default: true | Product status |
| status_stock | string | Default: 'Sale' | Stock status ('Sale' or 'Sale Out') |
| cal_avg_rating | decimal(3,2) | Default: 0 | Calculated average rating |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Category (category_id)
- `belongsTo`: Brand (brand_id)
- `hasOne`: Promotion (product_id)
- `hasMany`: Reviews (product_id)
- `hasMany`: Wishlists (product_id)
- `hasMany`: CartItems (product_id)
- `hasMany`: OrderItems (product_id)

---

### 6. promotions

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique promotion identifier |
| code | string | Unique | Promotion code |
| name | string | Required | Promotion name |
| image | string | Nullable | Promotion banner image |
| product_id | unsignedBigInteger | Nullable, FK → products.id | Optional: specific product |
| promo_type | string | Required | Type: 'PERCENTAGE' or 'BOGO' |
| discount_value | decimal(10,2) | Required | Discount percentage/value |
| buy_qty | integer | Nullable | For BOGO: buy quantity |
| get_qty | integer | Nullable | For BOGO: get quantity |
| status | string | Required | 'ACTIVE', 'PAUSED', 'DRAFT', 'EXPIRED' |
| start_date | dateTime | Nullable | Promotion start date |
| end_date | dateTime | Nullable | Promotion end date |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Product (product_id, optional)
- `hasMany`: Orders (promotion_id)

---

### 7. orders

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique order identifier |
| order_number | string | Unique | Order reference number |
| user_id | unsignedBigInteger | Nullable | Reference to user |
| address_id | BigInteger | Required | Shipping address reference |
| promotion_id | unsignedBigInteger | Nullable | Applied promotion |
| subtotal_amount | decimal(12,2) | Required | Price before discount |
| discount_amount | decimal(12,2) | Default: 0 | Total discount applied |
| total_amount | decimal(12,2) | Required | Final order total |
| delivery_fee | decimal(12,2) | Default: 0 | Delivery fee |
| discount_type | string(50) | Nullable | Discount type |
| discount_value | decimal(12,2) | Default: 0 | Discount value |
| order_status | string | Required | 'PENDING', 'COMPLETED', 'CANCELLED', 'REFUNDED' |
| payment_status | string | Required | 'PENDING', 'PAID', 'FAILED', 'REFUNDED' |
| payment_method | string(50) | Required | 'cash' or 'online' |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: User (user_id)
- `belongsTo`: Promotion (promotion_id)
- `belongsTo`: Address (address_id)
- `hasMany`: OrderItems (order_id)
- `hasMany`: Deliveries (order_id)
- `hasMany`: PromotionSeasons (order_id)

---

### 8. order_items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique line item identifier |
| order_id | unsignedBigInteger | Required, FK → orders.id | Parent order reference |
| product_id | unsignedBigInteger | Required, FK → products.id | Product reference |
| qty | integer | Required | Quantity ordered |
| unit_price | decimal(10,2) | Required | Price at purchase time |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Order (order_id)
- `belongsTo`: Product (product_id)

---

### 9. cart

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique cart identifier |
| user_id | unsignedBigInteger | Required, FK → users.id | Owner reference |
| subtotal | decimal(10,2) | Default: 0 | Cart subtotal |
| discount | decimal(10,2) | Default: 0 | Total discount |
| total_amount | decimal(10,2) | Default: 0 | Final cart total |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: User (user_id)
- `hasMany`: CartItems (cart_id)
- `hasMany`: Deliveries (cart_id)
- `hasMany`: PromotionSeasons (cart_id)

---

### 10. cart_items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique cart item identifier |
| cart_id | unsignedBigInteger | Required, FK → cart.id | Parent cart reference |
| product_id | unsignedBigInteger | Required, FK → products.id | Product reference |
| quantity | integer | Required | Item quantity |
| price | decimal(10,2) | Required | Discounted unit price |
| discount_value | decimal(10,2) | Default: 0 | Percentage discount |
| discount_amount | decimal(10,2) | Default: 0 | Per-unit discount |
| discount_amount_total | decimal(10,2) | Default: 0 | Total discount amount |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Cart (cart_id)
- `belongsTo`: Product (product_id)

---

### 11. address

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique address identifier |
| user_id | BigInteger | Required, FK → users.id | Address owner |
| name | string | Required | Recipient name |
| phone | string(50) | Required | Phone number |
| address | text | Required | Full address |
| floor | string | Nullable | Floor/room number |
| qty_kilo | decimal(12,3) | Default: 1 | Weight in kilograms |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: User (user_id)
- `hasMany`: Orders (address_id)
- `hasMany`: HistoryOrders (address_id)

---

### 12. deliveries

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique delivery identifier |
| fee_amount_per | decimal(12,2) | Default: 0 | Fee per kg |
| qty_kg | decimal(10,3) | Default: 0 | Total weight |
| total | decimal(12,2) | Default: 0 | Total delivery fee |
| discount_type | string | Default: 'fix' | 'fix', 'percentage', or 'free' |
| discount_value | decimal(12,2) | Nullable | Discount value |
| cart_id | unsignedBigInteger | Nullable, FK → cart.id | Cart reference |
| order_id | unsignedBigInteger | Nullable, FK → orders.id | Order reference |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Cart (cart_id)
- `belongsTo`: Order (order_id)

---

### 13. promotion_seasons

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique season promotion identifier |
| image | string | Nullable | Banner image |
| code | string | Unique | Promotion code |
| start_date | dateTime | Nullable | Start date |
| end_date | dateTime | Nullable | End date |
| promotion_type | string(50) | Nullable | 'FIX', 'PERCENTAGE', 'FREE_DELIVERY' |
| promotion_value | decimal(12,2) | Default: 0 | Promotion value |
| cart_id | unsignedBigInteger | Nullable, FK → cart.id | Cart reference |
| order_id | unsignedBigInteger | Nullable, FK → orders.id | Order reference |
| status | string | Default: 'DRAFT' | 'ACTIVE', 'DRAFT', 'PAUSE', 'EXPIRE' |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Cart (cart_id)
- `belongsTo`: Order (order_id)

---

### 14. reviews

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique review identifier |
| review_number | string | Unique | Review reference number |
| product_id | unsignedBigInteger | Required, FK → products.id | Product being reviewed |
| user_id | unsignedBigInteger | Nullable, FK → users.id | Reviewer (optional) |
| rating_score | tinyInteger | Required | Rating 1-5 |
| review_text | text | Nullable | Review content |
| review_status | string | Required | 'PENDING', 'APPROVED', 'REJECTED' |
| create_date | dateTime | Required | Review date |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Product (product_id)
- `belongsTo`: User (user_id)

---

### 15. wishlists

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique wishlist item identifier |
| user_id | unsignedBigInteger | Nullable | Wishlist owner |
| product_id | unsignedBigInteger | Nullable | Product added to wishlist |
| added_date | dateTime | Required | Date added |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: User (user_id)
- `belongsTo`: Product (product_id)

---

### 16. contacts

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique contact identifier |
| user_id | BigInteger | Nullable, FK → users.id | Contact sender |
| full_name | string | Required | Sender name |
| email | string | Required | Sender email |
| subject | string | Required | Message subject |
| message | text | Required | Message content |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: User (user_id)

---

### 17. locations

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique location identifier |
| name | string | Nullable | Location name |
| address | text | Nullable | Location address |
| phone | string | Nullable | Contact phone |
| map_lat | decimal(10,8) | Nullable | Latitude coordinate |
| map_long | decimal(11,8) | Nullable | Longitude coordinate |
| is_active | boolean | Default: true | Location active status |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

---

### 18. settings

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique setting identifier |
| key | string | Unique | Setting key name |
| value | text | Nullable | Setting value |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

---

### 19. history_orders

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique history record identifier |
| user_id | BigInteger | Required | User reference |
| order_id | BigInteger | Required | Order reference |
| address_id | BigInteger | Required | Address reference |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |

**Relationships:**
- `belongsTo`: Order (order_id)

---

### 20. brand_category (Pivot Table)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint, auto-increment | Primary Key | Unique pivot identifier |
| brand_id | foreignId | FK → brands.id, cascade on delete | Brand reference |
| category_id | foreignId | FK → categories.id, cascade on delete | Category reference |
| created_at | timestamp | - | Record creation timestamp |
| updated_at | timestamp | - | Record update timestamp |
| Unique constraint: (brand_id, category_id) | - | - | Prevents duplicate pairs |

**Relationships:**
- `belongsTo`: Brand (brand_id)
- `belongsTo`: Category (category_id)

---

## Entity Relationship Diagram (Textual)

```
roles ──< users >── addresses ──< orders >── order_items >── products
              │                │         │                 │
              │                │         │                 └── reviews
              │                │         │
              │                │         └── deliveries
              │                │
              │                └── history_orders
              │
              └── wishlists
              │
              └── contacts
              │
              └── cart >── cart_items
              │
              └── promotion_seasons

brands ──< products
       │
       └──< categories >──< products
       │
       └── brand_category (pivot) ── categories

promotions ── products (optional, 1:1)
        │
        └──< orders

settings (standalone)

locations (standalone)
```

---

## Cardinality Summary

| From Table | To Table | Relationship Type |
|------------|----------|-----------------|
| roles | users | One-to-Many |
| users | addresses | One-to-Many |
| users | orders | One-to-Many |
| users | reviews | One-to-Many |
| users | wishlists | One-to-Many |
| users | contacts | One-to-Many |
| users | cart | One-to-One |
| users | history_orders | One-to-Many |
| brands | products | One-to-Many |
| brands | categories | Many-to-Many (via brand_category) |
| categories | products | One-to-Many |
| products | reviews | One-to-Many |
| products | wishlists | One-to-Many |
| products | cart_items | One-to-Many |
| products | order_items | One-to-Many |
| products | promotions | One-to-One (optional) |
| orders | order_items | One-to-Many |
| orders | deliveries | One-to-Many |
| orders | promotion_seasons | One-to-Many |
| orders | promotions | Many-to-One |
| orders | addresses | Many-to-One |
| cart | cart_items | One-to-Many |
| cart | deliveries | One-to-Many |
| cart | promotion_seasons | One-to-Many |