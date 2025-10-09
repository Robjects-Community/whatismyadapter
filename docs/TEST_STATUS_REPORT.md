# WillowCMS Controller Test Detailed Report
Generated: 2025-10-07 17:11:11

## Executive Summary

- **Total Tests**: 698
- **Total Assertions**: 495
- **Passing**: 255 (36.5%)
- **Errors**: 236 (33.8%)
- **Failures**: 207 (29.7%)
- **Warnings**: 1

## Failing Controllers Summary

| Controller | Failing Tests | Passing Tests | Total | Pass Rate |
|------------|---------------|---------------|-------|-----------|
| Products Controller | 84 | 0 | 84 | 0.0% |
| Pages Controller | 24 | 0 | 24 | 0.0% |
| Product Page Views Controller | 18 | 0 | 18 | 0.0% |
| Quiz Controller | 18 | 0 | 18 | 0.0% |
| Articles Controller | 16 | 14 | 30 | 46.7% |
| Cable Capabilities Controller | 16 | 0 | 16 | 0.0% |
| Image Galleries Controller | 15 | 15 | 30 | 50.0% |
| Images Controller | 15 | 15 | 30 | 50.0% |
| Tags Controller | 12 | 14 | 26 | 53.8% |
| Email Templates Controller | 11 | 11 | 22 | 50.0% |
| Ai Metrics Controller | 10 | 9 | 19 | 47.4% |
| Aiprompts Controller | 10 | 10 | 20 | 50.0% |
| Articles Translations Controller | 10 | 0 | 10 | 0.0% |
| Blocked Ips Controller | 10 | 10 | 20 | 50.0% |
| Image Galleries Translations Controller | 10 | 0 | 10 | 0.0% |
| Internationalisations Controller | 10 | 10 | 20 | 50.0% |
| Products Translations Controller | 10 | 0 | 10 | 0.0% |
| Slugs Controller | 10 | 10 | 20 | 50.0% |
| Tags Translations Controller | 10 | 0 | 10 | 0.0% |
| Comments Controller | 9 | 9 | 18 | 50.0% |
| Page Views Controller | 9 | 9 | 18 | 50.0% |
| Product Form Fields Controller | 9 | 9 | 18 | 50.0% |
| Reliability Controller | 9 | 0 | 9 | 0.0% |
| Author Controller | 8 | 0 | 8 | 0.0% |
| Homepage Feeds Controller | 8 | 0 | 8 | 0.0% |
| Image Generation Controller | 8 | 0 | 8 | 0.0% |
| Queue Configurations Controller | 8 | 8 | 16 | 50.0% |
| System Logs Controller | 8 | 8 | 16 | 50.0% |
| Users Controller | 7 | 29 | 36 | 80.6% |
| Articles Tags Controller | 5 | 5 | 10 | 50.0% |
| Image Galleries Images Controller | 5 | 5 | 10 | 50.0% |
| Models Images Controller | 5 | 5 | 10 | 50.0% |
| Products Tags Controller | 5 | 5 | 10 | 50.0% |
| User Account Confirmations Controller | 5 | 5 | 10 | 50.0% |
| Cookie Consents Controller | 4 | 1 | 5 | 20.0% |
| Ai Form Suggestions Controller | 3 | 1 | 4 | 25.0% |
| Cache Controller | 2 | 2 | 4 | 50.0% |
| Error Controller | 2 | 0 | 2 | 0.0% |
| Login Test Controller | 2 | 12 | 14 | 85.7% |
| Rss Controller | 1 | 2 | 3 | 66.7% |
| Sitemap Controller | 1 | 2 | 3 | 66.7% |
| Videos Controller | 1 | 1 | 2 | 50.0% |

## Detailed Failing Tests by Controller

### Ai Form Suggestions Controller

- ✘ Index api missing field name
- ✘ Index api with valid field name
- ✘ Index api with non existent field

### Ai Metrics Controller

- ✘ Dashboard as admin
- ✘ Realtime data as admin
- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Add post as admin
- ✘ Edit as admin
- ✘ Edit post as admin
- ✘ Delete as admin
- ✘ Delete requires admin

### Aiprompts Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Articles Controller

- ✘ Tree index as admin
- ✘ Update tree as admin
- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Bulk action as admin
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated
- ✘ View by slug authenticated
- ✘ View by slug unauthenticated
- ✘ Add comment authenticated

### Articles Tags Controller

- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Articles Translations Controller

- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Add authenticated
- ✘ Add unauthenticated
- ✘ Edit authenticated
- ✘ Edit unauthenticated
- ✘ Delete authenticated
- ✘ Delete unauthenticated

### Author Controller

- ✘ About authenticated
- ✘ About unauthenticated
- ✘ Hire me authenticated
- ✘ Hire me unauthenticated
- ✘ Social authenticated
- ✘ Social unauthenticated
- ✘ Github authenticated
- ✘ Github unauthenticated

### Blocked Ips Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Cable Capabilities Controller

- ✘ Index as admin
- ✘ Index requires admin
- ✘ View as admin
- ✘ View requires admin
- ✘ Category as admin
- ✘ Category requires admin
- ✘ Certified as admin
- ✘ Certified requires admin
- ✘ Search as admin
- ✘ Search requires admin
- ✘ Analytics as admin
- ✘ Analytics requires admin
- ✘ Export as admin
- ✘ Export requires admin
- ✘ Return to products as admin
- ✘ Return to products requires admin

### Cache Controller

- ✘ Clear all as admin
- ✘ Clear as admin

### Comments Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Cookie Consents Controller

- ✘ Index unauthenticated
- ✘ View unauthenticated
- ✘ Add unauthenticated
- ✘ Delete unauthenticated

### Email Templates Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Send email as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Error Controller

- ✘ View classes authenticated
- ✘ View classes unauthenticated

### Homepage Feeds Controller

- ✘ Index as admin
- ✘ Index requires admin
- ✘ Configure as admin
- ✘ Configure requires admin
- ✘ Preview as admin
- ✘ Preview requires admin
- ✘ Reset as admin
- ✘ Reset requires admin

### Image Galleries Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Manage images as admin
- ✘ Add images as admin
- ✘ Remove image as admin
- ✘ Update image order as admin
- ✘ Picker as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Image Galleries Images Controller

- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Image Galleries Translations Controller

- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Add authenticated
- ✘ Add unauthenticated
- ✘ Edit authenticated
- ✘ Edit unauthenticated
- ✘ Delete authenticated
- ✘ Delete unauthenticated

### Image Generation Controller

- ✘ Index as admin
- ✘ Index requires admin
- ✘ Statistics as admin
- ✘ Statistics requires admin
- ✘ Batch as admin
- ✘ Batch requires admin
- ✘ Config as admin
- ✘ Config requires admin

### Images Controller

- ✘ View classes as admin
- ✘ Index as admin
- ✘ View as admin
- ✘ Image select as admin
- ✘ Add as admin
- ✘ Bulk upload as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Delete uploaded image as admin
- ✘ Picker as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Internationalisations Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Login Test Controller

- ✘ Index unauthenticated
- ✘ Login unauthenticated

### Models Images Controller

- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Page Views Controller

- ✘ Page view stats as admin
- ✘ View records as admin
- ✘ Filter stats as admin
- ✘ Dashboard as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Pages Controller

- ✘ Index as admin
- ✘ Index requires admin
- ✘ View as admin
- ✘ View requires admin
- ✘ Add as admin
- ✘ Add requires admin
- ✘ Edit as admin
- ✘ Edit requires admin
- ✘ Delete as admin
- ✘ Delete requires admin
- ✘ Bulk actions as admin
- ✘ Bulk actions requires admin
- ✘ Create connect pages as admin
- ✘ Create connect pages requires admin
- ✘ Extract as admin
- ✘ Extract requires admin
- ✘ Extract preview as admin
- ✘ Extract preview requires admin
- ✘ Extract webpage as admin
- ✘ Extract webpage requires admin
- ✘ Cost analysis as admin
- ✘ Cost analysis requires admin
- ✘ Display authenticated
- ✘ Display unauthenticated

### Product Form Fields Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Reorder as admin
- ✘ Toggle ai as admin
- ✘ Test ai as admin
- ✘ Reset order as admin

### Product Page Views Controller

- ✘ Page view stats as admin
- ✘ Page view stats requires admin
- ✘ View records as admin
- ✘ View records requires admin
- ✘ Filter stats as admin
- ✘ Filter stats requires admin
- ✘ Dashboard as admin
- ✘ Dashboard requires admin
- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Add authenticated
- ✘ Add unauthenticated
- ✘ Edit authenticated
- ✘ Edit unauthenticated
- ✘ Delete authenticated
- ✘ Delete unauthenticated

### Products Controller

- ✘ Dashboard as admin
- ✘ Dashboard requires admin
- ✘ Index as admin
- ✘ Index requires admin
- ✘ Pending review as admin
- ✘ Pending review requires admin
- ✘ Index 2 as admin
- ✘ Index 2 requires admin
- ✘ Forms dashboard as admin
- ✘ Forms dashboard requires admin
- ✘ View as admin
- ✘ View requires admin
- ✘ View 2 as admin
- ✘ View 2 requires admin
- ✘ Add 2 as admin
- ✘ Add 2 requires admin
- ✘ Edit 2 as admin
- ✘ Edit 2 requires admin
- ✘ Add beautiful as admin
- ✘ Add beautiful requires admin
- ✘ Ai score as admin
- ✘ Ai score requires admin
- ✘ Add as admin
- ✘ Add requires admin
- ✘ Edit as admin
- ✘ Edit requires admin
- ✘ Delete as admin
- ✘ Delete requires admin
- ✘ Verify as admin
- ✘ Verify requires admin
- ✘ Toggle featured as admin
- ✘ Toggle featured requires admin
- ✘ Toggle published as admin
- ✘ Toggle published requires admin
- ✘ Approve as admin
- ✘ Approve requires admin
- ✘ Reject as admin
- ✘ Reject requires admin
- ✘ Bulk verify as admin
- ✘ Bulk verify requires admin
- ✘ Bulk approve as admin
- ✘ Bulk approve requires admin
- ✘ Bulk reject as admin
- ✘ Bulk reject requires admin
- ✘ Forms redirect as admin
- ✘ Forms redirect requires admin
- ✘ Forms as admin
- ✘ Forms requires admin
- ✘ Forms quiz as admin
- ✘ Forms quiz requires admin
- ✘ Forms customer quiz as admin
- ✘ Forms customer quiz requires admin
- ✘ Forms fields as admin
- ✘ Forms fields requires admin
- ✘ Forms stats as admin
- ✘ Forms stats requires admin
- ✘ Ai suggest as admin
- ✘ Ai suggest requires admin
- ✘ Bulk edit as admin
- ✘ Bulk edit requires admin
- ✘ Bulk toggle published as admin
- ✘ Bulk toggle published requires admin
- ✘ Bulk toggle featured as admin
- ✘ Bulk toggle featured requires admin
- ✘ Bulk delete as admin
- ✘ Bulk delete requires admin
- ✘ Bulk update fields as admin
- ✘ Bulk update fields requires admin
- ✘ Quiz settings as admin
- ✘ Quiz settings requires admin
- ✘ Index api
- ✘ View api
- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Quiz authenticated
- ✘ Quiz unauthenticated
- ✘ Edit authenticated
- ✘ Edit unauthenticated
- ✘ Delete authenticated
- ✘ Delete unauthenticated
- ✘ Add authenticated
- ✘ Add unauthenticated

### Products Tags Controller

- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Products Translations Controller

- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Add authenticated
- ✘ Add unauthenticated
- ✘ Edit authenticated
- ✘ Edit unauthenticated
- ✘ Delete authenticated
- ✘ Delete unauthenticated

### Queue Configurations Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Sync as admin
- ✘ Health check as admin
- ✘ Health check all as admin

### Quiz Controller

- ✘ Akinator start api
- ✘ Akinator next api
- ✘ Akinator result api
- ✘ Comprehensive submit api
- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ Akinator authenticated
- ✘ Akinator unauthenticated
- ✘ Comprehensive authenticated
- ✘ Comprehensive unauthenticated
- ✘ Submit authenticated
- ✘ Submit unauthenticated
- ✘ Result authenticated
- ✘ Result unauthenticated
- ✘ Take authenticated
- ✘ Take unauthenticated
- ✘ Preview authenticated
- ✘ Preview unauthenticated

### Reliability Controller

- ✘ View as admin
- ✘ View requires admin
- ✘ Recalc as admin
- ✘ Recalc requires admin
- ✘ Verify checksums as admin
- ✘ Verify checksums requires admin
- ✘ Score api
- ✘ Verify checksum api
- ✘ Field stats api

### Rss Controller

- ✘ View classes authenticated

### Sitemap Controller

- ✘ View classes authenticated

### Slugs Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### System Logs Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Delete as admin
- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Tags Controller

- ✘ Tree index as admin
- ✘ Update tree as admin
- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated
- ✘ View by slug authenticated

### Tags Translations Controller

- ✘ Index authenticated
- ✘ Index unauthenticated
- ✘ View authenticated
- ✘ View unauthenticated
- ✘ Add authenticated
- ✘ Add unauthenticated
- ✘ Edit authenticated
- ✘ Edit unauthenticated
- ✘ Delete authenticated
- ✘ Delete unauthenticated

### User Account Confirmations Controller

- ✘ Index authenticated
- ✘ View authenticated
- ✘ Add authenticated
- ✘ Edit authenticated
- ✘ Delete authenticated

### Users Controller

- ✘ Index as admin
- ✘ View as admin
- ✘ Add as admin
- ✘ Edit as admin
- ✘ Delete as admin
- ✘ Login as admin
- ✘ Logout as admin

### Videos Controller

- ✘ Video select as admin

## Fixture Schema Issues

The following fixtures have SQLite compatibility issues (missing length specifications):

- `articles_translations`
- `products`
- `products_purchase_links`
- `products_reliability_logs`
- `tags_translations`

**Impact**: These schema errors prevent test fixtures from loading properly in SQLite.

## Missing Templates

The following template files need to be created:

- `Admin/Aiprompts/add.php`
- `Admin/Aiprompts/index.php`
- `Admin/Articles/add.php`
- `Admin/Articles/index.php`
- `Admin/Articles/tree_index.php`
- `Admin/BlockedIps/add.php`
- `Admin/BlockedIps/index.php`
- `Admin/Cache/clear_all.php`
- `Admin/Comments/index.php`
- `Admin/EmailTemplates/add.php`
- `Admin/EmailTemplates/index.php`
- `Admin/EmailTemplates/send_email.php`
- `Admin/ImageGalleries/add.php`
- `Admin/ImageGalleries/index_grid.php`
- `Admin/ImageGalleries/picker.php`
- `Admin/Images/add.php`
- `Admin/Images/bulk_upload.php`
- `Admin/Images/image_select.php`
- `Admin/Images/index_grid.php`
- `Admin/Images/picker_grid.php`
- `Admin/Internationalisations/add.php`
- `Admin/Internationalisations/index.php`
- `Admin/ProductFormFields/add.php`
- `Admin/ProductFormFields/index.php`
- `Admin/QueueConfigurations/add.php`
- `Admin/QueueConfigurations/health_check_all.php`
- `Admin/QueueConfigurations/index.php`
- `Admin/Slugs/add.php`
- `Admin/Slugs/index.php`
- `Admin/SystemLogs/index.php`
- `Admin/Tags/add.php`
- `Admin/Tags/index.php`
- `Admin/Tags/tree_index.php`
- `Admin/Users/add.php`
- `Admin/Users/index.php`
- `Admin/Videos/video_select.php`
- `Aiprompts/add.php`
- `Aiprompts/index.php`
- `Articles/add.php`
- `ArticlesTags/add.php`
- `ArticlesTags/index.php`
- `BlockedIps/add.php`
- `BlockedIps/index.php`
- `Comments/index.php`
- `EmailTemplates/add.php`
- `EmailTemplates/index.php`
- `ImageGalleries/add.php`
- `ImageGalleries/index.php`
- `ImageGalleriesImages/add.php`
- `ImageGalleriesImages/index.php`
- `Images/add.php`
- `Images/index.php`
- `Internationalisations/add.php`
- `Internationalisations/index.php`
- `ModelsImages/add.php`
- `ModelsImages/index.php`
- `PageViews/add.php`
- `PageViews/index.php`
- `SystemLogs/add.php`
- `SystemLogs/index.php`
- `Tags/add.php`
- `UserAccountConfirmations/add.php`
- `UserAccountConfirmations/index.php`

## Recommended Action Plan

### Priority 1: Fix Fixture Schema Issues (High Impact)
- **Affected Fixtures**: 5 fixtures
- **Impact**: Prevents ~236 test errors
- **Action**: Add explicit field schemas with length specifications to fixtures

### Priority 2: Create Missing Templates
- **Missing Templates**: 63 templates
- **Impact**: Prevents view-related test failures
- **Action**: Create minimal template files for each missing view

### Priority 3: Fix Controller Logic Issues
- **Failing Tests**: 207 failures
- **Action**: Review and fix controller logic, authentication, and authorization
