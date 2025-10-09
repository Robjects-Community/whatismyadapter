#!/bin/bash

# Batch Upload Environment Variables to GitHub Repository Secrets
# This script reads a .env file and uploads each variable as a GitHub secret

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/.env"
REPO="garzarobm/willow"  # Change this to your repo

# Default exclusions (variables that shouldn't be uploaded as secrets)
EXCLUDE_PATTERNS=(
    "^#"           # Comments
    "^$"           # Empty lines
    "SSH_KEY_PATH" # Local file paths
    "BACKUP_PATH"  # Local paths
    "PWD"          # Current directory
    "HOME"         # Home directory
    "USER"         # Current user
)

# Function to display usage
usage() {
    echo -e "${BLUE}Usage: $0 [OPTIONS]${NC}"
    echo ""
    echo -e "${YELLOW}Options:${NC}"
    echo "  -f, --file FILE       Specify .env file path (default: .env)"
    echo "  -r, --repo REPO       Specify GitHub repository (default: garzarobm/willow)"
    echo "  -d, --dry-run         Show what would be uploaded without uploading"
    echo "  -e, --exclude VAR     Exclude specific variable (can be used multiple times)"
    echo "  -h, --help            Show this help message"
    echo ""
    echo -e "${YELLOW}Examples:${NC}"
    echo "  $0                                    # Upload from default .env file"
    echo "  $0 -f ./config/.env                  # Upload from specific file"
    echo "  $0 -d                                 # Dry run to see what would be uploaded"
    echo "  $0 -e DEBUG -e LOG_LEVEL             # Exclude specific variables"
    echo ""
}

# Parse command line arguments
DRY_RUN=false
CUSTOM_EXCLUDES=()

while [[ $# -gt 0 ]]; do
    case $1 in
        -f|--file)
            ENV_FILE="$2"
            shift 2
            ;;
        -r|--repo)
            REPO="$2"
            shift 2
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -e|--exclude)
            CUSTOM_EXCLUDES+=("$2")
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            usage
            exit 1
            ;;
    esac
done

# Check if .env file exists
if [[ ! -f "$ENV_FILE" ]]; then
    echo -e "${RED}Error: .env file not found at $ENV_FILE${NC}"
    echo -e "${YELLOW}Please specify a valid .env file with -f option${NC}"
    exit 1
fi

# Check if GitHub CLI is authenticated
if ! gh auth status &>/dev/null; then
    echo -e "${RED}Error: GitHub CLI not authenticated${NC}"
    echo -e "${YELLOW}Please run: gh auth login${NC}"
    exit 1
fi

echo -e "${BLUE}🔐 Batch Upload Environment Variables to GitHub Secrets${NC}"
echo -e "${BLUE}Repository: ${REPO}${NC}"
echo -e "${BLUE}Environment File: ${ENV_FILE}${NC}"
if [[ "$DRY_RUN" == "true" ]]; then
    echo -e "${YELLOW}Mode: DRY RUN (no actual uploads)${NC}"
fi
echo ""

# Function to check if variable should be excluded
should_exclude() {
    local var_name="$1"
    local var_line="$2"
    
    # Check default exclusion patterns
    for pattern in "${EXCLUDE_PATTERNS[@]}"; do
        if echo "$var_line" | grep -qE "$pattern"; then
            return 0  # Should exclude
        fi
    done
    
    # Check custom exclusions
    for exclude in "${CUSTOM_EXCLUDES[@]}"; do
        if [[ "$var_name" == "$exclude" ]]; then
            return 0  # Should exclude
        fi
    done
    
    return 1  # Should not exclude
}

# Read .env file and process variables
uploaded_count=0
skipped_count=0
failed_count=0

echo -e "${YELLOW}Processing environment variables...${NC}"
echo ""

while IFS= read -r line || [[ -n "$line" ]]; do
    # Skip empty lines and comments
    if [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]]; then
        continue
    fi
    
    # Extract variable name and value
    if [[ "$line" =~ ^[[:space:]]*([A-Za-z_][A-Za-z0-9_]*)=(.*)$ ]]; then
        var_name="${BASH_REMATCH[1]}"
        var_value="${BASH_REMATCH[2]}"
        
        # Remove quotes if present
        var_value=$(echo "$var_value" | sed 's/^"//;s/"$//' | sed "s/^'//;s/'$//")
        
        # Check if variable should be excluded
        if should_exclude "$var_name" "$line"; then
            echo -e "⏭️  ${YELLOW}Skipping${NC} $var_name (excluded)"
            ((skipped_count++))
            continue
        fi
        
        # Check if value is not empty
        if [[ -z "$var_value" ]]; then
            echo -e "⏭️  ${YELLOW}Skipping${NC} $var_name (empty value)"
            ((skipped_count++))
            continue
        fi
        
        if [[ "$DRY_RUN" == "true" ]]; then
            echo -e "📋 ${BLUE}Would upload${NC} $var_name"
            ((uploaded_count++))
        else
            # Upload to GitHub secrets
            echo -e "⬆️  ${BLUE}Uploading${NC} $var_name..."
            
            if gh secret set "$var_name" --body "$var_value" --repo "$REPO" &>/dev/null; then
                echo -e "✅ ${GREEN}Success${NC} $var_name"
                ((uploaded_count++))
            else
                echo -e "❌ ${RED}Failed${NC} $var_name"
                ((failed_count++))
            fi
        fi
    else
        echo -e "⚠️  ${YELLOW}Warning${NC}: Invalid line format: $line"
    fi
done < "$ENV_FILE"

echo ""
echo -e "${BLUE}📊 Summary:${NC}"
if [[ "$DRY_RUN" == "true" ]]; then
    echo -e "  • Variables that would be uploaded: ${GREEN}$uploaded_count${NC}"
else
    echo -e "  • Successfully uploaded: ${GREEN}$uploaded_count${NC}"
    echo -e "  • Failed uploads: ${RED}$failed_count${NC}"
fi
echo -e "  • Skipped variables: ${YELLOW}$skipped_count${NC}"

# Show current secrets (first 10)
echo ""
echo -e "${BLUE}📋 Current repository secrets (showing first 10):${NC}"
gh secret list --repo "$REPO" | head -10

if [[ "$DRY_RUN" == "true" ]]; then
    echo ""
    echo -e "${YELLOW}💡 This was a dry run. To actually upload the secrets, run:${NC}"
    echo -e "   $0 $(echo "$@" | sed 's/-d//g' | sed 's/--dry-run//g')"
fi

if [[ $failed_count -gt 0 ]]; then
    echo ""
    echo -e "${RED}⚠️  Some uploads failed. Check your GitHub CLI authentication and repository permissions.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}🎉 Batch upload completed successfully!${NC}"