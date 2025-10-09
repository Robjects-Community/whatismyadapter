# Thread 2: Public Controllers - Progress Tracker

**Start Date:** 2025-10-07  
**Thread Priority:** MEDIUM  
**Estimated Time:** 10-14 hours  
**Controllers:** 25

---

## 🎯 Objectives

Fix all public-facing controller tests to achieve >80% pass rate.

---

## 📋 Controller Checklist

### ✅ Passing (1/25)
- [x] `HomeController` - 1/1 tests passing

### 🔄 In Progress (0/25)

### ❌ Failing (24/25)
- [ ] `ArticlesController` - Public article viewing
- [ ] `ArticlesTagsController` - Article tag management
- [ ] `ArticlesTranslationsController` - Article translations
- [ ] `AuthorController` - Author profiles
- [ ] `BlockedIpsController` - Public IP checking
- [ ] `CommentsController` - Public comments
- [ ] `CookieConsentsController` - Cookie consent management
- [ ] `EmailTemplatesController` - Public email templates
- [ ] `ErrorController` - Error handling
- [ ] `HealthController` - Health checks
- [ ] `ImageGalleriesController` - Public galleries
- [ ] `ImageGalleriesImagesController` - Gallery images
- [ ] `ImageGalleriesTranslationsController` - Gallery translations
- [ ] `ImagesController` - Public images
- [ ] `InternationalisationsController` - i18n
- [ ] `PagesController` - Static pages
- [ ] `ProductsController` - Public product browsing
- [ ] `QuizController` - Quiz functionality
- [ ] `RobotsController` - robots.txt
- [ ] `SearchController` - Search functionality
- [ ] `SettingsController` - Public settings
- [ ] `SlugsController` - URL slug management
- [ ] `TagsController` - Tag browsing
- [ ] `TagsTranslationsController` - Tag translations

---

## 🐛 Common Issues Found

### 1. Schema CHAR() Issues ⚠️
**Status:** PARTIALLY FIXED (needs completion)

**Affected Tables:**
- `articles_translations` - `locale` field
- `products` - `currency` field
- `products_purchase_links` - `price_currency` field
- `products_reliability_logs` - `checksum_sha256` field
- `tags_translations` - `locale` field

**Solution:** These tables fail to create because CHAR() has no length. Need to fix schema files.

---

## 📊 Progress Metrics

```
Total Controllers: 25
✅ Passing: 1 (4%)
🔄 In Progress: 0 (0%)
❌ Failing: 24 (96%)
⏱️  Time Spent: 0 hours
🎯 Target: >80% pass rate
```

---

## 🔧 Next Actions

1. **Fix remaining CHAR() schema issues** in schema files
2. **Test each controller** individually using filter command
3. **Fix fixtures** for controllers with missing data
4. **Update this document** as each controller is fixed

---

## 📝 Notes

- HomeController is working perfectly - use as reference
- Schema warnings are non-blocking but should be fixed
- Use `--filter` flag to test individual controllers for faster iteration

---

**Last Updated:** 2025-10-07 21:25  
**Status:** Started - Initial Assessment Complete
