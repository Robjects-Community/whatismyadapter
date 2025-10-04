#!/usr/bin/env bash
set -euo pipefail

# Test file organization script for CakePHP 5.x WillowCMS
# Moves scattered test_*.php files into proper test directory structure

BASE="$(git rev-parse --show-toplevel)"
if [ -d "$BASE/app/tests" ]; then
  TEST_BASE="$BASE/app/tests"
else
  TEST_BASE="$BASE/tests"
fi

mkdir -p "$TEST_BASE/TestCase"

# Check if dry-run mode
dryrun="${1:-}"

# Classify test files based on content analysis and file location
classify() {
    local f="$1"
    local basename_file="$(basename "$f")"
    
    # Specific file classifications based on analysis
    case "$basename_file" in
        "test_email.php")
            echo "Utility"
            ;;
        "test_image_generation.php"|"test_image_generation_service.php"|"test_single_image_generation.php")
            echo "Command"
            ;;
        "test_health_check.php"|"test_queue_health.php")
            echo "Command" 
            ;;
        "test_admin_interface.php")
            echo "Integration"
            ;;
        *)
            # Fallback to content-based classification
            if grep -Eiq 'IntegrationTestTrait|Controller\\|App\\Controller\\|makeRequest.*curl|http.*request' "$f"; then 
                echo "Integration"
            elif grep -Eiq 'TableRegistry|Model\\|App\\Model\\|ORM\\' "$f"; then 
                echo "Model"
            elif grep -Eiq 'Command\\|Console\\|bin/|Shell\\|Job\\|Service\\' "$f"; then 
                echo "Command"
            elif grep -Eiq 'Helper\\|View\\' "$f"; then 
                echo "View"
            elif grep -Eiq 'Middleware\\|Router\\|Route\\' "$f"; then 
                echo "Routing"
            elif grep -Eiq 'Mailer\\|Email\\|mail' "$f"; then 
                echo "Utility"
            else 
                echo "Legacy"
            fi
            ;;
    esac
}

# Convert snake_case to StudlyCase
studly() {
    php -r 'echo preg_replace_callback("/(^|_)([a-z0-9])/", fn($m)=>strtoupper($m[2]), $argv[1]);' "$1"
}

echo "=== Test File Organization Script ==="
echo "Target test base: $TEST_BASE"
echo "Mode: $( [[ "$dryrun" == "--dry-run" ]] && echo "DRY RUN (no files will be moved)" || echo "LIVE MODE (files will be moved)" )"
echo ""

# Find and process test files
file_count=0
while IFS= read -r -d '' f; do
    if [[ ! -f "$f" ]]; then
        continue
    fi
    
    ((file_count++))
    
    base="$(basename "$f")"           # e.g. test_user_controller.php
    stem="${base#test_}"              # user_controller.php
    stem="${stem%.php}"               # user_controller
    
    # Handle naming conflicts by including parent directory for uniqueness
    parent_dir="$(basename "$(dirname "$f")")" 
    if [[ "$f" == *"/app/bin/"* && "$stem" == "health_check" ]]; then
        stem="app_bin_health_check"
    elif [[ "$f" == *"/bin/"* && "$stem" == "health_check" && "$f" != *"/app/bin/"* ]]; then
        stem="root_bin_health_check"
    elif [[ "$parent_dir" == "webroot" ]]; then
        stem="webroot_${stem}"
    fi
    
    name="$(studly "$stem")"          # UserController
    catdir="$(classify "$f")"         # Controller | Model | Command | Integration | ...
    
    # Handle special directory structures
    target_dir="$TEST_BASE/TestCase/$catdir"
    if [[ "$catdir" == "View" ]]; then
        target_dir="$TEST_BASE/TestCase/View/Helper"
    elif [[ "$catdir" == "Integration" ]]; then
        target_dir="$TEST_BASE/TestCase/Integration"
    fi
    
    mkdir -p "$target_dir"
    target="$target_dir/${name}Test.php"
    
    # Display the planned move
    rel_source="${f#$BASE/}"
    rel_target="${target#$BASE/}"
    echo "[$file_count] $catdir: $rel_source -> $rel_target"
    
    # Execute the move (unless dry-run)
    if [[ "$dryrun" != "--dry-run" ]]; then
        git mv "$f" "$target"
        echo "   ✅ Moved"
    else
        echo "   📋 Planned (dry-run)"
    fi
    echo ""
done < <(find "$BASE/app" "$BASE/bin" "$BASE/scripts" -type f -name 'test_*.php' -print0 2>/dev/null || true)

if [[ $file_count -eq 0 ]]; then
    echo "✨ No test_*.php files found to organize!"
else
    echo "=== Summary ==="
    echo "Files processed: $file_count"
    if [[ "$dryrun" == "--dry-run" ]]; then
        echo "🔍 This was a dry-run. Run without --dry-run to execute the moves."
    else
        echo "✅ Files moved successfully!"
        echo "📝 Next steps:"
        echo "   1. Update file contents to proper PHPUnit format"
        echo "   2. Fix namespaces and class names"
        echo "   3. Run tests to verify functionality"
    fi
fi

echo ""
echo "Target directory structure created under: $TEST_BASE/TestCase/"