#!/bin/bash
# Fix addIndex syntax in all test schema files
# Converts from: $table->addIndex('name', ['type' => 'index', 'columns' => ['col']])
# To: $table->addIndex(['col'], ['name' => 'name'])

set -e

SCHEMA_DIR="/Volumes/1TB_DAVINCI/docker/willow/app/tests/schema"

echo "Fixing addIndex syntax in schema files..."

# Find all PHP files with the old syntax
files=$(find "$SCHEMA_DIR" -name "*.php" -exec grep -l "addIndex.*'type'.*'index'" {} \; 2>/dev/null || true)

if [ -z "$files" ]; then
    echo "No files need fixing!"
    exit 0
fi

count=0
for file in $files; do
    echo "Processing: $(basename "$file")"
    
    # Use Perl for complex regex replacement
    perl -i -pe '
        # Match: $table->addIndex(INDEXNAME, [options with columns])
        # Replace with: $table->addIndex([columns], [name => INDEXNAME])
        s/\$table->addIndex\(
            '\''([^'\'']+)'\''\s*,\s*     # Capture index name (group 1)
            \[\s*
                '\''type'\''\s*=>\s*'\''index'\'',\s*
                '\''columns'\''\s*=>\s*(\[[^\]]+\])  # Capture columns array (group 2)
            \s*\]
        \)/\$table->addIndex($2, ['\''name'\'' => '\''$1'\''])/gx
    ' "$file"
    
    ((count++))
done

echo "✅ Fixed $count files"
echo "Done!"
