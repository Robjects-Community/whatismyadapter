# Docker Cleanup Quick Reference

## 🚀 Quick Start

**Interactive Cleanup Tool:**
```bash
./cleanup.sh
```

**Direct Commands:**
```bash
# Soft cleanup (preserve data)
./cleanup.sh --soft

# Hard cleanup (remove all data)
./cleanup.sh --hard

# Nuclear cleanup (system-wide)
./cleanup.sh --nuclear
```

## 📋 Cleanup Types

### 🧹 Soft Cleanup (Preserve Data)
**What it does:** Cleans temporary files, keeps your data
- ✅ Removes stopped containers
- ✅ Cleans unused networks
- ✅ Removes dangling images
- ✅ Cleans build cache
- ❌ **Preserves all data volumes**

**Command:**
```bash
docker-compose down && docker-compose rm -f && docker network prune -f && docker image prune -f && docker builder prune -f && docker-compose up -d
```

### 🔥 Hard Cleanup (Fresh Start)
**What it does:** Complete reset with data loss
- ✅ Removes all containers
- ✅ Removes all data volumes
- ✅ Removes custom images
- ⚠️ **DELETES ALL DATA**

**Commands:**
```bash
docker-compose down -v
docker volume rm mysql_data redis_data rabbitmq_data jenkins_home mailpit_data
docker rmi garzarobmdocker/willowcms:latest garzarobmdocker/jenkins:latest
docker builder prune -f
```

### ☢️ Nuclear Cleanup (System-Wide)
**What it does:** Removes ALL Docker data system-wide
- ⚠️ **Affects ALL Docker projects**
- ⚠️ **Deletes everything Docker-related**

**Command:**
```bash
docker system prune --all -f && docker volume prune -f
```

## 💾 Backup First!

**Database Backup (using manage.sh):**
```bash
./manage.sh
# Select option 3: "Dump MySQL Database"
```

**Manual Database Backup:**
```bash
docker-compose exec mysql mysqldump --verbose --routines --triggers --events --no-tablespaces --single-transaction -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_DATABASE" > "./project_mysql_backups/backup_$(date +%Y%m%d_%H%M%S).sql"
```

**Configuration Backup:**
```bash
mkdir -p ./config_backups/$(date +%Y%m%d_%H%M%S)
cp -r ./config/ ./config_backups/$(date +%Y%m%d_%H%M%S)/
cp docker-compose.yml ./config_backups/$(date +%Y%m%d_%H%M%S)/
```

## 🔄 Recovery

**Database Restore (using manage.sh):**
```bash
./manage.sh
# Select option 4: "Load Database from Backup"
```

**Restart After Cleanup:**
```bash
docker-compose up -d --build
```

## ⚠️ Safety Checklist

**Before Cleanup:**
- [ ] Stop services: `docker-compose down`
- [ ] Create database backup
- [ ] Backup configuration files
- [ ] Verify what will be deleted

**After Cleanup:**
- [ ] Restart services: `docker-compose up -d`
- [ ] Test application access
- [ ] Verify database connection
- [ ] Check logs for errors

## 📁 Files Created

- `CLEANUP_PROCEDURES.md` - Complete documentation
- `cleanup.sh` - Interactive cleanup script
- `CLEANUP_SUMMARY.md` - This quick reference

## 🆘 Emergency Recovery

If something goes wrong:
```bash
# Restore database from backup
./manage.sh  # Select option 4

# Rebuild and restart
docker-compose up -d --build

# Check status
docker-compose ps
docker-compose logs
```

---
**Remember:** Always backup before cleanup! 🛡️
