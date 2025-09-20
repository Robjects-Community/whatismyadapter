#!/bin/bash

# Quick WillowCMS Security Check
# Run this script to verify your repository has no sensitive data before committing

echo "🔐 WillowCMS Quick Security Check"
echo "================================"
echo

# Check 1: Look for sensitive files in the working directory
echo "1. 🔍 Scanning for sensitive files in working directory..."
sensitive_count=0

# Check for SQL files
sql_files=$(find . -name "*.sql" -not -path "./storage/backups/data-cleanse/*" -not -name "*example.sql" -not -name "schema.sql" 2>/dev/null)
if [ -n "$sql_files" ]; then
    echo "⚠️  WARNING: SQL files found:"
    echo "$sql_files" | sed 's/^/   /'
    sensitive_count=$((sensitive_count + 1))
fi

# Check for dump files  
dump_files=$(find . -name "*.dump" -not -path "./storage/backups/data-cleanse/*" 2>/dev/null)
if [ -n "$dump_files" ]; then
    echo "⚠️  WARNING: Database dump files found:"no
    echo "$dump_files" | sed 's/^/   /'
    sensitive_count=$((sensitive_count + 1))
fi

# Check for backup files
backup_files=$(find . -name "*.backup" -o -name "*backup*.tar.gz" -o -name "*backup*.zip" -not -path "./storage/backups/*" 2>/dev/null)
if [ -n "$backup_files" ]; then
    echo "⚠️  WARNING: Backup files found:"
    echo "$backup_files" | sed 's/^/   /'
    sensitive_count=$((sensitive_count + 1))
fi

# Check for project backup directories
project_backups=$(find . -maxdepth 1 -type d -name "project_*_backups" 2>/dev/null)
if [ -n "$project_backups" ]; then
    echo "⚠️  WARNING: Project backup directories found:"
    echo "$project_backups" | sed 's/^/   /'
    sensitive_count=$((sensitive_count + 1))
fi

if [ $sensitive_count -eq 0 ]; then
    echo "✅ No sensitive files found in working directory"
fi

# Check 2: Look for sensitive files in Git index
echo
echo "2. 🔍 Checking Git index for sensitive files..."
if command -v git >/dev/null 2>&1 && git rev-parse --git-dir >/dev/null 2>&1; then
    git_sensitive=$(git ls-files | grep -E '\.(sql|dump|backup)$' | grep -v -E '(example\.sql|schema\.sql)$' || true)
    if [ -z "$git_sensitive" ]; then
        echo "✅ No sensitive files found in Git index"
    else
        echo "🚨 CRITICAL: Sensitive files detected in Git index:"
        echo "$git_sensitive" | sed 's/^/   /'
        sensitive_count=$((sensitive_count + 1))
    fi
else
    echo "⚠️  Not a Git repository or Git not available"
fi

# Check 3: Verify .gitignore exists and has security patterns
echo
echo "3. 🔍 Checking .gitignore security patterns..."
if [ -f ".gitignore" ]; then
    if grep -q "^\*\.sql$" .gitignore && grep -q "^\*\.dump$" .gitignore && grep -q "^\*\.backup$" .gitignore; then
        echo "✅ .gitignore has basic security patterns"
    else
        echo "⚠️  WARNING: .gitignore may be missing security patterns"
        sensitive_count=$((sensitive_count + 1))
    fi
else
    echo "⚠️  WARNING: No .gitignore file found"
    sensitive_count=$((sensitive_count + 1))
fi

# Check 4: Verify data cleanse backup directory exists
echo
echo "4. 🔍 Checking for data cleanse backup directory..."
if [ -d "storage/backups/data-cleanse" ]; then
    backup_count=$(find storage/backups/data-cleanse -type f 2>/dev/null | wc -l)
    if [ $backup_count -gt 0 ]; then
        echo "✅ Data cleanse backup directory exists with $backup_count files"
    else
        echo "📁 Data cleanse backup directory exists but is empty"
    fi
else
    echo "📁 No data cleanse backup directory found (may not have run secure reorganization yet)"
fi

# Check 5: Look for environment files with potential secrets
echo
echo "5. 🔍 Checking for environment files with potential secrets..."
env_files=$(find . -name ".env*" -not -name "*.example" -not -name "*.template" -not -path "./app/config/environments/*" 2>/dev/null)
if [ -n "$env_files" ]; then
    echo "⚠️  WARNING: Environment files found outside secure location:"
    echo "$env_files" | sed 's/^/   /'
    sensitive_count=$((sensitive_count + 1))
else
    echo "✅ Environment files properly located"
fi

# Final report
echo
echo "================================"
if [ $sensitive_count -eq 0 ]; then
    echo "🎉 SECURITY CHECK PASSED!"
    echo "✅ Repository appears to be clean of sensitive data"
    echo "✅ Safe to commit to version control"
else
    echo "🚨 SECURITY CHECK FAILED!"
    echo "❌ Found $sensitive_count security issue(s)"
    echo "❌ DO NOT commit until issues are resolved"
    echo
    echo "🔧 Recommended actions:"
    echo "   1. Run the secure reorganization script: ./reorganize_willow_secure.sh"
    echo "   2. Move sensitive files to storage/backups/data-cleanse/"
    echo "   3. Update .gitignore to exclude sensitive patterns"
    echo "   4. Remove sensitive files from Git index: git rm --cached <file>"
fi

echo
echo "📖 For help: See IMPLEMENTATION_CHECKLIST.md"
echo "🔐 For full reorganization: Run ./reorganize_willow_secure.sh"

exit $sensitive_count