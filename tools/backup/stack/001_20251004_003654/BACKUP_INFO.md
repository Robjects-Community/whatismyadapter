# WillowCMS Portainer Stack Backup

**Backup ID:** 001_20251004_003654
**Created:** 2025-10-04 00:36:54 CDT
**Git Branch:** portainer-stack
**Git Commit:** 29264264

## Files Backed Up
- docker-compose.yml (Portainer stack configuration)
- stack.env (Environment variables)
- SHA256SUMS (Checksum verification file)

## Verification
To verify backup integrity:
```bash
cd "/Volumes/1TB_DAVINCI/docker/willow/tools/backup/stack/001_20251004_003654"
shasum -c SHA256SUMS
```

## Restoration
To restore this backup:
```bash
cp "/Volumes/1TB_DAVINCI/docker/willow/tools/backup/stack/001_20251004_003654/docker-compose.yml" "/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/"
cp "/Volumes/1TB_DAVINCI/docker/willow/tools/backup/stack/001_20251004_003654/stack.env" "/Volumes/1TB_DAVINCI/docker/willow/"
```

**Note:** Always verify checksums before restoration and ensure Portainer services are stopped.
