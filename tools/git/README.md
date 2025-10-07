# Git Branch Management Tools

Comprehensive tools for managing branches in the WillowCMS repository.

## 📚 Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Tools](#tools)
- [Workflows](#workflows)
- [Integration](#integration)
- [Troubleshooting](#troubleshooting)

---

## Overview

This toolset helps you:
- ✅ **Analyze** branch health and merge status
- 🧹 **Clean up** stale and merged branches safely
- 📊 **Track** branch activity and maintenance
- 💾 **Archive** deleted branches with restore capability
- 🔒 **Protect** critical branches from accidental deletion

### Protected Branches

The following branches are **never** deleted:
- `main` - Stable production branch
- `main-clean` - Clean development baseline
- `portainer-stack` - Active portainer/deployment work
- `main-server` - Server deployment branch
- `main-prototype` - Prototype branch
- `demo` - Demo/presentation branch
- `master` - Legacy master branch

---

## Quick Start

### 1. Analyze Your Branches

```bash
./tools/git/branch-cleanup.sh --analyze
```

This generates a comprehensive report showing:
- Protected branches
- Merged branches (safe to delete)
- Stale branches (28+ days old)
- Active branches (recent work)

### 2. Preview Cleanup (Dry-Run)

```bash
./tools/git/branch-cleanup.sh --cleanup --dry-run
```

See what would be deleted without making changes.

### 3. Interactive Cleanup

```bash
./tools/git/branch-cleanup.sh --cleanup --interactive
```

Review and confirm each deletion one by one.

### 4. Automatic Cleanup

```bash
./tools/git/branch-cleanup.sh --cleanup
```

Automatically delete merged branches with backup tags created.

---

## Tools

### branch-cleanup.sh

**Purpose:** Analyze and safely cleanup git branches

**Features:**
- Automatic categorization of branches
- Merge status detection
- Stale branch identification (28+ days)
- Backup creation before deletion
- Interactive and dry-run modes
- Comprehensive logging

**Usage:**
```bash
# Show help
./tools/git/branch-cleanup.sh --help

# Analyze branches
./tools/git/branch-cleanup.sh --analyze

# Dry-run cleanup
./tools/git/branch-cleanup.sh --cleanup --dry-run

# Interactive cleanup
./tools/git/branch-cleanup.sh --cleanup --interactive

# Auto cleanup merged branches
./tools/git/branch-cleanup.sh --cleanup

# Show latest report
./tools/git/branch-cleanup.sh --report
```

**Options:**
- `-h, --help` - Show help message
- `-a, --analyze` - Analyze branches (default)
- `-c, --cleanup` - Cleanup merged branches
- `-i, --interactive` - Interactive mode
- `-d, --dry-run` - Preview changes
- `-s, --stale` - Include stale branches
- `-r, --report` - Show latest report
- `--force` - Skip safety confirmations

---

## Workflows

### Regular Maintenance Workflow

**Frequency:** Weekly or bi-weekly

```bash
# 1. Analyze current branch state
./tools/git/branch-cleanup.sh --analyze

# 2. Review the report
./tools/git/branch-cleanup.sh --report

# 3. Cleanup merged branches
./tools/git/branch-cleanup.sh --cleanup

# 4. Review stale branches (manual)
# Check the report for stale branches with unique commits
# Decide which to keep or delete
```

### Before Major Release Workflow

```bash
# 1. Analyze all branches
./tools/git/branch-cleanup.sh --analyze --force

# 2. Interactive cleanup of merged branches
./tools/git/branch-cleanup.sh --cleanup --interactive

# 3. Document remaining active branches
./tools/git/branch-cleanup.sh --report > docs/active-branches-$(date +%Y%m%d).txt
```

### Emergency Cleanup Workflow

When you need to quickly clean up many branches:

```bash
# 1. Create backup of current state
git tag "backup/pre-cleanup-$(date +%Y%m%d)"

# 2. Dry-run to see what will be deleted
./tools/git/branch-cleanup.sh --cleanup --dry-run

# 3. Execute cleanup
./tools/git/branch-cleanup.sh --cleanup --force
```

---

## Integration

### With Continuous Testing Workflow

The branch cleanup tool integrates with the continuous testing workflow:

```bash
# Before cleanup, ensure tests pass on protected branches
./tools/testing/continuous-test.sh --type model --all

# Then cleanup
./tools/git/branch-cleanup.sh --cleanup
```

### With Docker Compose

Branch cleanup works seamlessly with your Docker environment:

```bash
# No need to stop Docker services
./tools/git/branch-cleanup.sh --analyze

# Switch branches after cleanup
git checkout main-clean
./run_dev_env.sh
```

### With Portainer Stack

Compatible with your portainer deployment workflow:

```bash
# Analyze before deployment
./tools/git/branch-cleanup.sh --analyze

# Ensure portainer-stack is protected
# (already configured as protected branch)

# Deploy
./tools/deploy/portainer/deploy.sh
```

###With Backup System

Branch cleanup creates automatic backups:

```bash
# Cleanup with backup
./tools/git/branch-cleanup.sh --cleanup

# Backups stored as git tags:
git tag -l "backup/*"

# Restore a deleted branch:
git checkout -b branch-name backup/branch-name-YYYYMMDD
```

---

## Safety Features

### Automatic Backups

Before deleting any branch, a backup tag is created:
```
backup/branch-name-YYYYMMDD-HHMMSS
```

### Restore Deleted Branch

```bash
# List backups
git tag -l "backup/*"

# Restore a branch
git checkout -b restored-branch-name backup/branch-name-20251007-150802

# Or create from backup tag
git branch restored-branch-name backup/branch-name-20251007-150802
```

### Archive Log

All deletions are logged to:
```
tools/git/archives/deleted-branches-YYYYMMDD-HHMMSS.log
```

Format:
```
2025-10-07 15:08:02 | branch-name | backup/branch-name-20251007-150802
```

---

## File Structure

```
tools/git/
├── README.md                      # This file
├── branch-cleanup.sh              # Main cleanup tool
├── .gitignore                     # Ignore logs and temp files
├── archives/                      # Deletion archives
│   └── deleted-branches-*.log
├── reports/                       # Analysis reports
│   └── branch-analysis-*.txt
├── logs/                          # Operation logs
│   └── cleanup-*.log
└── config/                        # Temporary config files
    ├── merged-branches.tmp
    └── stale-branches.tmp
```

---

## Troubleshooting

### Issue: "Not a git repository"

**Solution:**
```bash
cd /Volumes/1TB_DAVINCI/docker/willow
./tools/git/branch-cleanup.sh --analyze
```

### Issue: "You have uncommitted changes"

**Options:**
1. Commit or stash changes first
2. Use `--force` flag to bypass (analysis only):
   ```bash
   ./tools/git/branch-cleanup.sh --analyze --force
   ```

### Issue: Script shows wrong branch count

**Solution:** The script only analyzes local branches. Fetch latest:
```bash
git fetch --all --prune
./tools/git/branch-cleanup.sh --analyze
```

### Issue: Need to restore deleted branch

**Solution:**
```bash
# Find the backup tag
git tag -l "backup/*branch-name*"

# Restore it
git checkout -b branch-name backup/branch-name-TIMESTAMP
```

### Issue: Want to delete stale branches

**Solution:** Stale branches have unique commits and require manual review:
```bash
# Interactive mode for stale branches
./tools/git/branch-cleanup.sh --cleanup --interactive --stale
```

---

## Best Practices

### 1. Regular Maintenance
- Run analysis weekly
- Cleanup merged branches monthly
- Review stale branches quarterly

### 2. Before Major Work
- Cleanup before starting new features
- Analyze after completing releases
- Document branch purposes in commit messages

### 3. Team Collaboration
- Communicate before bulk cleanups
- Share analysis reports with team
- Use interactive mode for shared branches

### 4. Backup Strategy
- Keep backup tags for 30 days
- Clean up old backup tags monthly:
  ```bash
  # Remove backup tags older than 30 days
  git tag -l "backup/*" | while read tag; do
    date=$(git log -1 --format=%ci $tag | cut -d' ' -f1)
    age=$(($(date +%s) - $(date -d $date +%s)))
    days=$((age / 86400))
    if [ $days -gt 30 ]; then
      git tag -d $tag
    fi
  done
  ```

---

## Related Documentation

- [Branch Strategy](../../docs/BRANCH_STRATEGY.md) - Branch naming and lifecycle
- [Branch Comparison](../../docs/BRANCH_COMPARISON.md) - Branch diff analysis
- [Branch Sync](../../docs/BRANCH_SYNC.md) - Branch synchronization
- [Continuous Testing](../../docs/CONTINUOUS_TESTING_WORKFLOW.md) - Testing workflow
- [Backup Archival](../../docs/BACKUP_ARCHIVAL.md) - Backup management

---

## Support

### Log Files

Check log files for detailed operation history:
```bash
# Latest cleanup log
ls -t tools/git/logs/cleanup-*.log | head -1 | xargs cat

# Latest analysis report
ls -t tools/git/reports/branch-analysis-*.txt | head -1 | xargs cat
```

### Debug Mode

For verbose output, check the log file after running:
```bash
./tools/git/branch-cleanup.sh --analyze
cat tools/git/logs/cleanup-*.log | tail -50
```

---

**Last Updated:** 2025-10-07  
**Version:** 1.0.0  
**Maintainer:** WillowCMS Team
