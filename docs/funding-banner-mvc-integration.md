# Funding Banner MVC Integration Guide for WillowCMS

## 🏗️ MVC Architecture Overview

In CakePHP's MVC pattern, the funding banner integrates across all three layers:

```
┌─────────────────────────────────────────────────────────┐
│                     MODEL LAYER                          │
│  • SettingsTable (Configuration Storage)                 │
│  • FundingSettings Entity                                │
│  • Database: funding_settings table                      │
└─────────────────────────────────────────────────────────┘
                            ⬇️
┌─────────────────────────────────────────────────────────┐
│                   CONTROLLER LAYER                       │
│  • AppController (Global availability)                   │
│  • Admin/FundingController (Management)                  │
│  • Api/FundingController (API endpoints)                 │
└─────────────────────────────────────────────────────────┘
                            ⬇️
┌─────────────────────────────────────────────────────────┐
│                      VIEW LAYER                          │
│  • Layout Files (Global placement)                       │
│  • Element File (Reusable component)                     │
│  • Template Files (Page-specific)                        │
│  • Assets (CSS/JS in webroot)                           │
└─────────────────────────────────────────────────────────┘
```

## 📍 View Layer Integration Points

### 1. **Layout Files (Global Display)**

The banner is typically added to layout files for site-wide visibility:

#### Frontend Layout: `/app/plugins/DefaultTheme/templates/layout/default.php`

```php
<?php use App\Utility\SettingsManager; ?>
<!doctype html>
<html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
  <head>
    <!-- Existing head content... -->
    
    <!-- ADD: Funding Banner CSS -->
    <?= $this->Html->css('funding-banner') ?>
    
    <?= $this->fetch('css') ?>
  </head>
  <body>
    <!-- OPTION 1: Banner at very top of page -->
    <?= $this->element('funding_banner', ['position' => 'top']) ?>
    
    <?= $this->element('site/bootstrap') ?>
    
    <div class="container">
      <?= $this->element('site/header'); ?>
      
      <!-- OPTION 2: Banner after header, before menu -->
      <?= $this->element('funding_banner', ['position' => 'top']) ?>
      
      <?= $this->element('site/main_menu', ['mbAmount' => 3]); ?>
    </div>
    
    <main class="container" id="main-content">
      <!-- Main content -->
    </main>
    
    <!-- OPTION 3: Banner before footer -->
    <?= $this->element('funding_banner', ['position' => 'bottom']) ?>
    
    <?= $this->element('site/footer'); ?>
    
    <!-- Scripts at bottom -->
    <?= $this->Html->script('funding-banner') ?>
    <?= $this->fetch('scriptBottom') ?>
  </body>
</html>
```

#### Admin Layout: `/app/plugins/AdminTheme/templates/layout/default.php`

```php
<!-- Admin Header -->
<header class="admin-header">
    <div class="container-fluid">
        <!-- Existing header content... -->
    </div>
</header>

<!-- ADD: Admin funding banner (optional) -->
<?php if (SettingsManager::read('Funding.showInAdmin', false)): ?>
    <?= $this->element('funding_banner', [
        'position' => 'top',
        'custom_class' => 'admin-funding-banner'
    ]) ?>
<?php endif; ?>

<!-- Main Content -->
<div class="container-fluid">
    <!-- Content... -->
</div>
```

### 2. **Element File (Reusable Component)**

The element file is the core reusable component:

**Location:** `/app/templates/element/funding_banner.php`

This file:
- Can be included anywhere using `<?= $this->element('funding_banner') ?>`
- Accepts parameters for customization
- Reads from SettingsManager for configuration
- Is shared across all themes and layouts

### 3. **Specific Template Files**

You can also add the banner to specific pages only:

#### Homepage: `/app/templates/Home/index.php`

```php
<?php
// Page-specific banner with custom settings
echo $this->element('funding_banner', [
    'campaign_name' => 'Homepage Campaign',
    'cta_text' => 'Support our mission!'
]);
?>

<!-- Rest of homepage content -->
```

#### Articles: `/app/templates/Articles/view.php`

```php
<article>
    <!-- Article content -->
</article>

<!-- Show banner after article content -->
<?php if ($article->show_funding_banner): ?>
    <?= $this->element('funding_banner', [
        'campaign_name' => 'Article Support',
        'cta_text' => 'Enjoyed this article? Support us!'
    ]) ?>
<?php endif; ?>
```

## 🎛️ Controller Layer Integration

### AppController (Global Configuration)

**File:** `/app/src/Controller/AppController.php`

```php
public function beforeRender(EventInterface $event): void
{
    parent::beforeRender($event);
    
    // Make funding data available to all views
    $fundingEnabled = SettingsManager::read('Funding.enabled', true);
    $fundingData = [
        'current_amount' => SettingsManager::read('Funding.currentAmount', 0),
        'goal_amount' => SettingsManager::read('Funding.goalAmount', 12000),
        'campaign_name' => SettingsManager::read('Funding.campaignName', 'Monthly Goal')
    ];
    
    $this->set(compact('fundingEnabled', 'fundingData'));
}
```

### Admin/FundingController (Management)

**File:** `/app/src/Controller/Admin/FundingController.php`

```php
namespace App\Controller\Admin;

use App\Controller\AppController;

class FundingController extends AppController
{
    public function index()
    {
        // Display current funding settings
        $settings = $this->Settings->find()
            ->where(['category' => 'Funding'])
            ->all();
            
        $this->set(compact('settings'));
    }
    
    public function edit()
    {
        // Edit funding banner settings
        if ($this->request->is(['post', 'put'])) {
            // Save settings to database
            $data = $this->request->getData();
            
            foreach ($data as $key => $value) {
                $this->Settings->saveSetting('Funding', $key, $value);
            }
            
            $this->Flash->success(__('Funding settings updated.'));
            return $this->redirect(['action' => 'index']);
        }
    }
}
```

## 📊 Model Layer Integration

### Settings Table (Already Exists)

The existing SettingsTable handles funding configuration storage:

```php
// Save funding settings
$this->Settings->saveSetting('Funding', 'enabled', true);
$this->Settings->saveSetting('Funding', 'currentAmount', 8226);
$this->Settings->saveSetting('Funding', 'goalAmount', 12000);

// Read funding settings
$enabled = SettingsManager::read('Funding.enabled', true);
```

## 📁 Complete File Structure in MVC

```
willow/
├── app/
│   ├── src/                              [CONTROLLER LAYER]
│   │   ├── Controller/
│   │   │   ├── AppController.php         # Global controller setup
│   │   │   ├── Admin/
│   │   │   │   └── FundingController.php # Admin management
│   │   │   └── Api/
│   │   │       └── FundingController.php # API endpoints
│   │   │
│   │   └── Model/                        [MODEL LAYER]
│   │       ├── Table/
│   │       │   └── SettingsTable.php     # Settings storage
│   │       └── Entity/
│   │           └── Setting.php           # Setting entity
│   │
│   ├── templates/                        [VIEW LAYER - Templates]
│   │   ├── element/
│   │   │   └── funding_banner.php        # Reusable element
│   │   ├── layout/
│   │   │   └── default.php              # Default layout
│   │   └── Admin/
│   │       └── Funding/
│   │           ├── index.php            # Settings list
│   │           └── edit.php             # Edit form
│   │
│   ├── plugins/                          [VIEW LAYER - Themes]
│   │   ├── DefaultTheme/
│   │   │   └── templates/
│   │   │       └── layout/
│   │   │           └── default.php      # Frontend layout
│   │   └── AdminTheme/
│   │       └── templates/
│   │           └── layout/
│   │               └── default.php      # Admin layout
│   │
│   └── webroot/                          [VIEW LAYER - Assets]
│       ├── css/
│       │   └── funding-banner.css       # Styles
│       └── js/
│           └── funding-banner.js        # JavaScript
│
└── config/
    └── routes.php                        # Route definitions
```

## 🔧 Integration Options by Visibility

### Option 1: Site-wide Display (Recommended)
- **Where:** Layout files
- **Files:** `/app/plugins/DefaultTheme/templates/layout/default.php`
- **Visibility:** Every page on the site
- **Use Case:** Maximum visibility for funding campaigns

### Option 2: Homepage Only
- **Where:** Homepage template
- **Files:** `/app/templates/Home/index.php`
- **Visibility:** Only on homepage
- **Use Case:** Less intrusive, targeted display

### Option 3: Conditional Display
- **Where:** Layout with conditions
- **Code:**
```php
<?php if ($this->request->getParam('controller') !== 'Checkout'): ?>
    <?= $this->element('funding_banner') ?>
<?php endif; ?>
```
- **Use Case:** Hide on certain pages (checkout, login, etc.)

### Option 4: User-triggered Display
- **Where:** Floating button or modal
- **Code:**
```php
<button onclick="showFundingBanner()">Support Us</button>
<div id="funding-modal" style="display:none;">
    <?= $this->element('funding_banner') ?>
</div>
```
- **Use Case:** Non-intrusive, user-initiated

## 🚀 Quick Implementation Steps

1. **Add to Frontend Layout** (Most Common)
```bash
# Edit the default theme layout
nano app/plugins/DefaultTheme/templates/layout/default.php
```

2. **Add the element call after the header:**
```php
<?= $this->element('site/header'); ?>
<?= $this->element('funding_banner') ?>  <!-- ADD THIS LINE -->
<?= $this->element('site/main_menu', ['mbAmount' => 3]); ?>
```

3. **Include CSS and JS:**
```php
<!-- In <head> section -->
<?= $this->Html->css('funding-banner') ?>

<!-- Before </body> -->
<?= $this->Html->script('funding-banner') ?>
```

4. **Configure settings:**
```bash
# Run in your container
docker compose exec willowcms bin/cake console

# In the console
$settings = TableRegistry::getTableLocator()->get('Settings');
$settings->saveSetting('Funding', 'enabled', true);
$settings->saveSetting('Funding', 'goalAmount', 12000);
```

## 💡 Best Practices

1. **Performance:** Use layout integration for best performance (loads once)
2. **Caching:** Element output is automatically cached by CakePHP
3. **Responsive:** Banner adapts to container width automatically
4. **Accessibility:** Includes ARIA labels and semantic HTML
5. **SEO:** Non-blocking, doesn't affect page content or rankings

## 🔍 Testing the Integration

```bash
# Clear cache after adding
docker compose exec willowcms bin/cake cache clear_all

# Check the frontend
open http://localhost:8195

# Check the admin
open http://localhost:8195/admin
```

The funding banner is now fully integrated into your MVC application structure!