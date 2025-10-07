# Schema Issues Blocking Test Execution

**Date:** 2025-10-07  
**Status:** BLOCKING  
**Priority:** HIGH  
**Affects:** ArticlesTableTest, ProductsTableTest

---

## Overview

Multiple test files cannot execute due to database schema definition errors in fixtures. These issues are causing PHPUnit to fail before any tests can run.

---

## Critical Issues

### 1. `articles_translations` Table - Invalid Column Definition

**Error:**
```
SQLSTATE[HY000]: General error: 1 near ")": syntax error
Query: CREATE TABLE "articles_translations" (
"id" CHAR(36),
"locale" CHAR(),  ← PROBLEM: No length specified
...
```

**Problem:** 
- `locale` column defined as `CHAR()` without length specification
- SQLite requires explicit length for CHAR columns

**Fix Required:**
- Update fixture or migration to specify: `CHAR(5)` or `VARCHAR(10)`
- Typical locale format is `en_GB` (5 chars) or `en_US` (5 chars)

**Affected Files:**
- `app/tests/Fixture/ArticlesTranslationsFixture.php`
- Database migration file defining this table
- `app/src/Model/Table/ArticlesTable.php` (Translate behavior)

**Suggested Fix:**
```sql
"locale" CHAR(5)  -- or VARCHAR(10) for flexibility
```

---

### 2. `products` Table - Invalid Column Definitions

**Error:**
```
SQLSTATE[HY000]: General error: 1 near ")": syntax error
Query: CREATE TABLE "products" (
...
"currency" CHAR(),  ← PROBLEM: No length specified
...
```

**Problem:**
- `currency` column defined as `CHAR()` without length
- ISO 4217 currency codes are 3 characters (USD, EUR, GBP)

**Fix Required:**
- Update to: `CHAR(3)` or `VARCHAR(3)`

**Affected Files:**
- `app/tests/Fixture/ProductsFixture.php`
- Database migration file
- `app/src/Model/Table/ProductsTable.php`

**Suggested Fix:**
```sql
"currency" CHAR(3)  -- ISO 4217 standard
```

---

### 3. `products_purchase_links` Table - Invalid Column Definition

**Error:**
```
SQLSTATE[HY000]: General error: 1 near ")": syntax error
Query: CREATE TABLE "products_purchase_links" (
...
"price_currency" CHAR(),  ← PROBLEM: No length specified
...
```

**Problem:**
- Same as above - currency code needs explicit length

**Fix Required:**
- Update to: `CHAR(3)`

**Suggested Fix:**
```sql
"price_currency" CHAR(3)
```

---

### 4. `products_reliability_logs` Table - Invalid Column Definition

**Error:**
```
SQLSTATE[HY000]: General error: 1 near ")": syntax error
Query: CREATE TABLE "products_reliability_logs" (
...
"checksum_sha256" CHAR(),  ← PROBLEM: No length specified
...
```

**Problem:**
- SHA256 checksums are 64 hexadecimal characters
- `CHAR()` needs explicit length

**Fix Required:**
- Update to: `CHAR(64)` for SHA256 hex representation

**Suggested Fix:**
```sql
"checksum_sha256" CHAR(64)  -- SHA256 in hex = 64 chars
```

---

### 5. `tags_translations` Table - Invalid Column Definition

**Error:**
```
SQLSTATE[HY000]: General error: 1 near ")": syntax error
Query: CREATE TABLE "tags_translations" (
...
"locale" CHAR(),  ← PROBLEM: No length specified
...
```

**Problem:**
- Same locale issue as articles_translations

**Fix Required:**
- Update to: `CHAR(5)` or `VARCHAR(10)`

**Suggested Fix:**
```sql
"locale" CHAR(5)
```

---

### 6. Unknown Type Error - `upload.file`

**Error:**
```
InvalidArgumentException: Unknown type `upload.file`
/var/www/html/vendor/cakephp/cakephp/src/Database/TypeFactory.php:83
```

**Problem:**
- Custom database type `upload.file` is not registered
- Likely used in fixtures or schema for file upload fields

**Fix Required:**
1. Register custom type in `config/bootstrap.php`:
   ```php
   use Cake\Database\TypeFactory;
   use App\Database\Type\UploadFileType;
   
   TypeFactory::map('upload.file', UploadFileType::class);
   ```

2. Or replace with standard type in affected fixtures/migrations:
   ```php
   // Instead of:
   'type' => 'upload.file'
   
   // Use:
   'type' => 'string'
   ```

**Affected Files:**
- Check fixtures for `upload.file` type usage
- May be in Images or Articles fixtures

---

## Secondary Issues (Warnings)

### Index Already Exists Warnings

**Multiple tables** have duplicate index creation attempts:
- `models_images`: `image_id` index
- `products_cord_categories`: `is_active` index  
- `products_tags`: `product_id` index
- `products_reliability_fields`: `model_foreign_key` index
- `quiz_submissions`: Multiple indexes
- `roles`: `priority` index
- `slugs`: `model_foreign_key` index
- `users`: `role_id` index
- `users_groups`: `user_id` index

**Problem:**
- Fixtures attempting to create indexes that already exist
- Causes warnings but doesn't block tests

**Fix Required:**
- Review fixture definitions
- Remove duplicate index definitions
- Ensure fixtures match actual schema

---

## Resolution Steps

### Immediate Actions (HIGH PRIORITY)

1. **Fix CHAR() Columns Without Length**
   ```bash
   # Files to update:
   - app/tests/Fixture/ArticlesTranslationsFixture.php
   - app/tests/Fixture/ProductsFixture.php
   - app/tests/Fixture/ProductsPurchaseLinksFixture.php
   - app/tests/Fixture/ProductsReliabilityLogsFixture.php
   - app/tests/Fixture/TagsTranslationsFixture.php
   ```

2. **Register or Remove `upload.file` Custom Type**
   - Check if custom type is needed
   - If yes: Create and register type class
   - If no: Replace with standard `string` type

3. **Clean Up Duplicate Indexes**
   - Review all fixture files for duplicate indexes
   - Match fixture definitions to actual schema

### Testing After Fixes

```bash
# Test Settings (currently working)
docker compose exec willowcms php vendor/bin/phpunit \
  tests/TestCase/Model/Table/SettingsTableTest.php

# Test Articles (after fixes)
docker compose exec willowcms php vendor/bin/phpunit \
  tests/TestCase/Model/Table/ArticlesTableTest.php

# Test all three together
docker compose exec willowcms php vendor/bin/phpunit \
  tests/TestCase/Model/Table/SettingsTableTest.php \
  tests/TestCase/Model/Table/ArticlesTableTest.php \
  tests/TestCase/Model/Table/ProductsTableTest.php
```

---

## Migration Recommendations

### Option 1: Fix Fixtures (RECOMMENDED)

Update fixture files to match the actual database schema exactly.

**Pros:**
- Fixtures accurately represent actual database
- Tests run against correct schema
- No production database changes needed

**Cons:**
- Requires updating multiple fixture files

### Option 2: Update Migrations

Modify migrations to fix schema definitions in the source.

**Pros:**
- Fixes root cause
- Future fixtures auto-generated correctly

**Cons:**
- Requires migration rollback/replay
- May affect production database

### Recommendation

Use **Option 1** first (fix fixtures) to unblock testing immediately, then create migrations to fix the schema permanently if needed.

---

## Impact Assessment

### Blocked Tests

- ✅ **SettingsTableTest**: WORKING (24 tests passing)
- ❌ **ArticlesTableTest**: BLOCKED (24 tests written, 0 can run)
- ❌ **ProductsTableTest**: BLOCKED (not yet implemented)

### Test Coverage Impact

- Current: **24 tests passing** (SettingsTable only)
- Potential: **70+ tests** once schema issues resolved
- Blocked coverage: **~50+ test methods** ready but cannot execute

---

## References

- **CakePHP Documentation**: https://book.cakephp.org/5/en/development/testing.html
- **Fixture Guide**: https://book.cakephp.org/5/en/development/testing.html#fixtures
- **Database Types**: https://book.cakephp.org/5/en/orm/database-basics.html#data-types
- **ISO 4217 Currency Codes**: https://en.wikipedia.org/wiki/ISO_4217
- **SHA256**: 64 hexadecimal characters (256 bits / 4 bits per hex char)

---

## Next Steps

1. ✅ Document issues (this file)
2. ⏳ Fix CHAR() length specifications in fixtures
3. ⏳ Resolve `upload.file` type issue  
4. ⏳ Clean up duplicate index warnings
5. ⏳ Re-run ArticlesTableTest
6. ⏳ Implement ProductsTableTest
7. ⏳ Achieve 75+ tests passing across all three tables

---

**Last Updated:** 2025-10-07  
**Updated By:** AI Assistant  
**Status:** Issues Documented, Awaiting Fixes
