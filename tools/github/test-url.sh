#!/bin/bash
# GitHub URL Deployment Tester for WillowCMS
# Validates GitHub repository URLs and references before deployment

set -euo pipefail

# Script directory and project root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
ENV_LINK="${PROJECT_ROOT}/.env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Usage information
usage() {
    cat << EOF
WillowCMS GitHub URL Deployment Tester

USAGE:
    $0 [options]

OPTIONS:
    -u, --url <url>     Override Git URL from environment
    -r, --ref <ref>     Override Git reference from environment  
    -j, --json          Output results in JSON format
    -v, --verbose       Enable verbose output
    -h, --help          Show this help message

DESCRIPTION:
    Validates GitHub repository URLs and references before deployment.
    Tests both Git remote accessibility and download availability.
    Uses active environment configuration (.env) by default.

TESTS PERFORMED:
    1. Git Remote Validation - Tests git ls-remote access
    2. Archive Download Test - Tests GitHub archive accessibility
    3. Reference Validation - Confirms branch/tag/commit exists
    4. Repository Metadata - Fetches basic repo information

EXAMPLES:
    $0                              # Test active environment settings
    $0 -u https://github.com/user/repo -r main
    $0 --json > test-results.json   # JSON output for automation

EXIT CODES:
    0  - All tests passed
    1  - Git remote test failed
    2  - Archive download test failed
    3  - Invalid reference
    4  - Environment/configuration error

EOF
}

# Parse command line arguments
GIT_URL_OVERRIDE=""
GIT_REF_OVERRIDE=""
JSON_OUTPUT=false
VERBOSE=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -u|--url)
            GIT_URL_OVERRIDE="$2"
            shift 2
            ;;
        -r|--ref)
            GIT_REF_OVERRIDE="$2"
            shift 2
            ;;
        -j|--json)
            JSON_OUTPUT=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            log_error "Unknown option: $1"
            usage
            exit 4
            ;;
    esac
done

# Load environment if not overridden
if [[ -z "$GIT_URL_OVERRIDE" || -z "$GIT_REF_OVERRIDE" ]]; then
    if [[ ! -f "$ENV_LINK" ]]; then
        log_error "No environment file found at $ENV_LINK"
        log_error "Run './tools/env/switch-env.sh <local|remote>' first"
        exit 4
    fi
    
    # Source environment
    set +u
    source "$ENV_LINK"
    set -u
fi

# Set final values
FINAL_GIT_URL="${GIT_URL_OVERRIDE:-${GIT_URL:-}}"
FINAL_GIT_REF="${GIT_REF_OVERRIDE:-${GIT_REF:-}}"
REMOTE_NAME="${GIT_REMOTE_NAME:-unknown}"

# Validate required parameters
if [[ -z "$FINAL_GIT_URL" ]]; then
    log_error "No Git URL specified in environment or command line"
    exit 4
fi

if [[ -z "$FINAL_GIT_REF" ]]; then
    log_error "No Git reference specified in environment or command line"
    exit 4
fi

# Extract repository owner and name from URL
if [[ "$FINAL_GIT_URL" =~ github\.com[/:](.*)/(.*)\.git$ ]] || [[ "$FINAL_GIT_URL" =~ github\.com[/:](.*)/([^/]+)$ ]]; then
    REPO_OWNER="${BASH_REMATCH[1]}"
    REPO_NAME="${BASH_REMATCH[2]%.git}"
else
    log_error "Invalid GitHub URL format: $FINAL_GIT_URL"
    exit 4
fi

# Initialize test results
START_TIME=$(date +%s)
TEST_RESULTS=()

# Function to add test result
add_test_result() {
    local test_name="$1"
    local status="$2"
    local message="$3"
    local details="${4:-}"
    
    if [[ "$JSON_OUTPUT" == "false" ]]; then
        if [[ "$status" == "PASS" ]]; then
            log_success "$test_name: $message"
        elif [[ "$status" == "WARN" ]]; then
            log_warn "$test_name: $message"
        else
            log_error "$test_name: $message"
        fi
        
        if [[ -n "$details" && "$VERBOSE" == "true" ]]; then
            echo "  Details: $details"
        fi
    fi
    
    TEST_RESULTS+=("{\"test\":\"$test_name\",\"status\":\"$status\",\"message\":\"$message\",\"details\":\"$details\"}")
}

# Function to execute test with timeout
run_with_timeout() {
    local timeout_duration="$1"
    shift
    if command -v timeout >/dev/null 2>&1; then
        timeout "$timeout_duration" "$@" 2>/dev/null
    else
        # Fallback for systems without timeout command
        "$@" 2>/dev/null
    fi
}

# Start testing
if [[ "$JSON_OUTPUT" == "false" ]]; then
    echo
    log_info "Testing GitHub URL Deployment"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Repository URL:       $FINAL_GIT_URL"
    echo "Repository Owner:     $REPO_OWNER"
    echo "Repository Name:      $REPO_NAME"
    echo "Git Reference:        $FINAL_GIT_REF"
    echo "Remote Name:          $REMOTE_NAME"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
fi

# Test 1: Git Remote Validation
if [[ "$JSON_OUTPUT" == "false" ]]; then
    log_info "Test 1: Git Remote Validation"
fi

GIT_REMOTE_OUTPUT=""
GIT_REMOTE_EXIT_CODE=0

if GIT_REMOTE_OUTPUT=$(run_with_timeout 30s git ls-remote --heads --tags "$FINAL_GIT_URL" 2>&1); then
    if echo "$GIT_REMOTE_OUTPUT" | grep -q "refs/"; then
        add_test_result "git_remote_validation" "PASS" "Git remote is accessible" "$GIT_REMOTE_OUTPUT"
    else
        add_test_result "git_remote_validation" "FAIL" "Git remote returned no references" "$GIT_REMOTE_OUTPUT"
        GIT_REMOTE_EXIT_CODE=1
    fi
else
    add_test_result "git_remote_validation" "FAIL" "Cannot access Git remote" "$GIT_REMOTE_OUTPUT"
    GIT_REMOTE_EXIT_CODE=1
fi

# Test 2: Reference Validation
if [[ "$JSON_OUTPUT" == "false" ]]; then
    log_info "Test 2: Reference Validation"
fi

REF_VALIDATION_EXIT_CODE=0
if [[ $GIT_REMOTE_EXIT_CODE -eq 0 ]]; then
    # Check if reference exists
    if echo "$GIT_REMOTE_OUTPUT" | grep -q "refs/heads/$FINAL_GIT_REF\|refs/tags/$FINAL_GIT_REF\|$FINAL_GIT_REF"; then
        add_test_result "reference_validation" "PASS" "Reference '$FINAL_GIT_REF' exists"
    else
        # Try to find similar references
        SIMILAR_REFS=$(echo "$GIT_REMOTE_OUTPUT" | grep -i "$FINAL_GIT_REF" | head -3 || true)
        if [[ -n "$SIMILAR_REFS" ]]; then
            add_test_result "reference_validation" "WARN" "Reference '$FINAL_GIT_REF' not found, but similar refs exist" "$SIMILAR_REFS"
        else
            add_test_result "reference_validation" "FAIL" "Reference '$FINAL_GIT_REF' does not exist"
            REF_VALIDATION_EXIT_CODE=3
        fi
    fi
else
    add_test_result "reference_validation" "SKIP" "Skipped due to git remote failure"
    REF_VALIDATION_EXIT_CODE=1
fi

# Test 3: Archive Download Test
if [[ "$JSON_OUTPUT" == "false" ]]; then
    log_info "Test 3: Archive Download Test"
fi

ARCHIVE_EXIT_CODE=0
if [[ $REF_VALIDATION_EXIT_CODE -eq 0 ]]; then
    # Test both branch and tag archive formats
    ARCHIVE_URLS=(
        "https://codeload.github.com/$REPO_OWNER/$REPO_NAME/zip/refs/heads/$FINAL_GIT_REF"
        "https://codeload.github.com/$REPO_OWNER/$REPO_NAME/zip/refs/tags/$FINAL_GIT_REF"
        "https://github.com/$REPO_OWNER/$REPO_NAME/archive/refs/heads/$FINAL_GIT_REF.zip"
    )
    
    ARCHIVE_SUCCESS=false
    ARCHIVE_DETAILS=""
    
    for archive_url in "${ARCHIVE_URLS[@]}"; do
        if ARCHIVE_OUTPUT=$(run_with_timeout 15s curl -sI "$archive_url" 2>&1); then
            if echo "$ARCHIVE_OUTPUT" | grep -q "HTTP/[12].[01] 200"; then
                add_test_result "archive_download" "PASS" "Archive download URL accessible" "$archive_url"
                ARCHIVE_SUCCESS=true
                break
            else
                ARCHIVE_DETAILS+="$archive_url: $(echo "$ARCHIVE_OUTPUT" | head -1)\n"
            fi
        else
            ARCHIVE_DETAILS+="$archive_url: Connection failed\n"
        fi
    done
    
    if [[ "$ARCHIVE_SUCCESS" == "false" ]]; then
        add_test_result "archive_download" "FAIL" "No archive download URLs accessible" "$ARCHIVE_DETAILS"
        ARCHIVE_EXIT_CODE=2
    fi
else
    add_test_result "archive_download" "SKIP" "Skipped due to reference validation failure"
    ARCHIVE_EXIT_CODE=$REF_VALIDATION_EXIT_CODE
fi

# Test 4: Repository Metadata (optional, best effort)
if [[ "$JSON_OUTPUT" == "false" ]]; then
    log_info "Test 4: Repository Metadata"
fi

if command -v curl >/dev/null 2>&1; then
    if REPO_API_OUTPUT=$(run_with_timeout 10s curl -s "https://api.github.com/repos/$REPO_OWNER/$REPO_NAME" 2>&1); then
        if echo "$REPO_API_OUTPUT" | grep -q '"full_name"'; then
            REPO_SIZE=$(echo "$REPO_API_OUTPUT" | grep -o '"size":[0-9]*' | cut -d: -f2)
            REPO_LANGUAGE=$(echo "$REPO_API_OUTPUT" | grep -o '"language":"[^"]*"' | cut -d: -f2 | tr -d '"')
            REPO_UPDATED=$(echo "$REPO_API_OUTPUT" | grep -o '"updated_at":"[^"]*"' | cut -d: -f2- | tr -d '"')
            
            METADATA="Size: ${REPO_SIZE:-unknown} KB, Language: ${REPO_LANGUAGE:-unknown}, Updated: ${REPO_UPDATED:-unknown}"
            add_test_result "repository_metadata" "PASS" "Repository metadata retrieved" "$METADATA"
        else
            add_test_result "repository_metadata" "WARN" "Repository metadata unavailable or private"
        fi
    else
        add_test_result "repository_metadata" "WARN" "Could not fetch repository metadata"
    fi
else
    add_test_result "repository_metadata" "SKIP" "curl not available"
fi

# Calculate final exit code
FINAL_EXIT_CODE=0
if [[ $GIT_REMOTE_EXIT_CODE -ne 0 ]]; then
    FINAL_EXIT_CODE=$GIT_REMOTE_EXIT_CODE
elif [[ $REF_VALIDATION_EXIT_CODE -ne 0 ]]; then
    FINAL_EXIT_CODE=$REF_VALIDATION_EXIT_CODE
elif [[ $ARCHIVE_EXIT_CODE -ne 0 ]]; then
    FINAL_EXIT_CODE=$ARCHIVE_EXIT_CODE
fi

# Output results
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

if [[ "$JSON_OUTPUT" == "true" ]]; then
    # JSON output
    echo "{"
    echo "  \"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%S.%3NZ)\","
    echo "  \"duration_seconds\": $DURATION,"
    echo "  \"repository\": {"
    echo "    \"url\": \"$FINAL_GIT_URL\","
    echo "    \"owner\": \"$REPO_OWNER\","
    echo "    \"name\": \"$REPO_NAME\","
    echo "    \"reference\": \"$FINAL_GIT_REF\","
    echo "    \"remote_name\": \"$REMOTE_NAME\""
    echo "  },"
    echo "  \"exit_code\": $FINAL_EXIT_CODE,"
    echo "  \"tests\": ["
    
    # Output test results
    for i in "${!TEST_RESULTS[@]}"; do
        echo "    ${TEST_RESULTS[$i]}"
        if [[ $i -lt $((${#TEST_RESULTS[@]} - 1)) ]]; then
            echo ","
        fi
    done
    
    echo "  ]"
    echo "}"
else
    # Human-readable summary
    echo
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Test Summary (completed in ${DURATION}s):"
    
    PASSED=0
    FAILED=0
    WARNINGS=0
    SKIPPED=0
    
    for result in "${TEST_RESULTS[@]}"; do
        if echo "$result" | grep -q '"status":"PASS"'; then
            ((PASSED++))
        elif echo "$result" | grep -q '"status":"FAIL"'; then
            ((FAILED++))
        elif echo "$result" | grep -q '"status":"WARN"'; then
            ((WARNINGS++))
        elif echo "$result" | grep -q '"status":"SKIP"'; then
            ((SKIPPED++))
        fi
    done
    
    echo "  Passed: $PASSED"
    echo "  Failed: $FAILED"
    echo "  Warnings: $WARNINGS"
    echo "  Skipped: $SKIPPED"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [[ $FINAL_EXIT_CODE -eq 0 ]]; then
        log_success "All tests passed! Repository is ready for deployment."
    else
        log_error "Some tests failed. Check the output above for details."
        echo
        log_info "Suggested next steps:"
        if [[ $GIT_REMOTE_EXIT_CODE -ne 0 ]]; then
            echo "  1. Verify the Git URL is correct and accessible"
            echo "  2. Check your network connection"
            echo "  3. Confirm the repository exists and is public"
        fi
        if [[ $REF_VALIDATION_EXIT_CODE -ne 0 ]]; then
            echo "  1. Verify the Git reference (branch/tag) exists"
            echo "  2. Use 'git ls-remote $FINAL_GIT_URL' to list available references"
            echo "  3. Update your environment configuration with the correct reference"
        fi
    fi
fi

exit $FINAL_EXIT_CODE