#!/bin/bash
# Fix addIndex syntax in all test schema files to CakePHP 5 format
# Converts from: $table->addIndex(['col'], ['name' => 'idx_name'])
# To: $table->addIndex('idx_name', ['columns' => ['col']])

set -e

SCHEMA_DIR="/Volumes/1TB_DAVINCI/docker/willow/app/tests/schema"

echo "Fixing addIndex syntax to CakePHP 5 format..."

# Find all PHP files
files=$(find "$SCHEMA_DIR" -name "*.php" -type f)

count=0
for file in $files; do
    if grep -q "addIndex" "$file"; then
        echo "Processing: $(basename "$file")"
        
        # Use Perl for complex regex replacement
        perl -i -pe '
            # Match: $table->addIndex([columns], ['\''name'\'' => '\''indexname'\''])
            # Replace with: $table->addIndex('\''indexname'\'', ['\''columns'\'' => [columns]])
            s/\$table->addIndex\(
                (\[[^\]]+\])\s*,\s*                    # Capture columns array (group 1)
                \[\s*'\''name'\''\s*=>\s*'\''([^'\'']+)'\''  # Capture index name (group 2)
            \s*\]
            \)/\$table->addIndex('\''$2'\'', ['\''columns'\'' => $1])/gx
        ' "$file"
        
        ((count++))
    fi
done

echo "✅ Fixed $count files"
echo "Done!"
