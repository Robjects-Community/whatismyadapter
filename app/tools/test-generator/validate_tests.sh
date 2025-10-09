#!/bin/bash
# Validate all generated test files for syntax errors

cd /Volumes/1TB_DAVINCI/docker/willow

echo "🔍 Validating test file syntax..."
echo ""

ERRORS=0
CHECKED=0

for file in $(find app/tests/TestCase/Controller -name "*Test.php"); do
    ((CHECKED++))
    if docker compose exec -T willowcms php -l "$file" > /dev/null 2>&1; then
        echo "✅ $file"
    else
        echo "❌ Syntax error in: $file"
        ((ERRORS++))
    fi
done

echo ""
echo "========================================"
if [ $ERRORS -eq 0 ]; then
    echo "✅ All $CHECKED test files have valid syntax"
    exit 0
else
    echo "❌ Found $ERRORS syntax errors in $CHECKED files"
    exit 1
fi
