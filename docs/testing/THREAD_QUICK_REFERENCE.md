# Controller Test Multi-Thread Quick Reference

## 🎯 Thread Assignments

### Thread 1: Admin Controllers (17 controllers)
**Time**: 8-12 hours | **Priority**: HIGH
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/
```

### Thread 2: Public Controllers (25 controllers)  
**Time**: 10-14 hours | **Priority**: MEDIUM
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/ \
  --exclude-group admin,api
```

### Thread 3: API Controllers (8 controllers)
**Time**: 4-6 hours | **Priority**: MEDIUM
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Api/
```

### Thread 4: User & Auth (6 controllers)
**Time**: 6-8 hours | **Priority**: HIGH
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Users*
```

### Thread 5: Specialized/Products (12 controllers)
**Time**: 6-10 hours | **Priority**: LOW-MEDIUM
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Products*
```

---

## ✅ Current Status
- **Total Controllers**: 68
- **With Tests**: 68 (100%)
- **Total Test Methods**: 691
- **Current Pass Rate**: ~31%
- **Target Pass Rate**: >80%

---

## 🚀 Quick Commands

### Run All Controller Tests
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/
```

### Generate Fixtures
```bash
docker compose exec willowcms bin/cake bake fixture ModelName --records 5
```

### Clear Cache
```bash
docker compose exec willowcms bin/cake cache clear_all
```

### Check Test Status
```bash
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/ --testdox
```

---

## 📋 Pre-Flight Checklist
- [ ] Docker containers running
- [ ] Database schema up to date
- [ ] Cache cleared
- [ ] Composer dependencies installed
- [ ] Test bootstrap configured

---

**See**: `CONTROLLER_TEST_PLAN_2025-10-07.md` for full details
