#!/bin/bash
# =============================================================================
# WillowCMS Application Debug Script
# =============================================================================
# This script provides comprehensive curl-based debugging for the WillowCMS
# application service running on http://localhost:9090
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
WILLOWCMS_URL="http://localhost:9090"
TIMEOUT=10
USER_AGENT="WillowCMS-Debug/1.0"

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}                    WillowCMS Application Debug Report${NC}"
echo -e "${BLUE}==============================================================================${NC}"
echo "Timestamp: $(date)"
echo "Target URL: $WILLOWCMS_URL"
echo ""

# =============================================================================
# Helper Functions
# =============================================================================

print_section() {
    echo -e "\n${YELLOW}>>> $1${NC}"
    echo "$(printf '=%.0s' {1..80})"
}

run_curl_test() {
    local test_name="$1"
    local url="$2"
    local extra_args="$3"
    
    echo -e "\n${BLUE}Test: $test_name${NC}"
    echo "URL: $url"
    
    if [ -n "$extra_args" ]; then
        echo "Extra Args: $extra_args"
    fi
    
    echo "Response:"
    curl -s -m $TIMEOUT \
         -H "User-Agent: $USER_AGENT" \
         -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
         -H "Accept-Language: en-US,en;q=0.5" \
         -H "Accept-Encoding: gzip, deflate" \
         -H "Connection: keep-alive" \
         $extra_args \
         "$url" || echo -e "${RED}❌ Request failed${NC}"
    echo ""
}

run_curl_headers() {
    local test_name="$1"
    local url="$2"
    local extra_args="$3"
    
    echo -e "\n${BLUE}Headers Test: $test_name${NC}"
    echo "URL: $url"
    
    curl -I -s -m $TIMEOUT \
         -H "User-Agent: $USER_AGENT" \
         $extra_args \
         "$url" || echo -e "${RED}❌ Request failed${NC}"
    echo ""
}

# =============================================================================
# Basic Connectivity Tests
# =============================================================================

print_section "1. BASIC CONNECTIVITY TESTS"

echo -e "${GREEN}1.1 Basic HTTP Response Headers${NC}"
run_curl_headers "Root Path" "$WILLOWCMS_URL/"

echo -e "${GREEN}1.2 Verbose Connection Info${NC}"
echo "Connection details:"
curl -v -s -m $TIMEOUT \
     -o /dev/null \
     -w "DNS Lookup: %{time_namelookup}s\nTCP Connect: %{time_connect}s\nTLS Handshake: %{time_appconnect}s\nPre-Transfer: %{time_pretransfer}s\nRedirect: %{time_redirect}s\nStart Transfer: %{time_starttransfer}s\nTotal Time: %{time_total}s\nHTTP Code: %{http_code}\nResponse Size: %{size_download} bytes\n" \
     "$WILLOWCMS_URL/" 2>&1 || echo -e "${RED}❌ Connection test failed${NC}"
echo ""

echo -e "${GREEN}1.3 Service Health Check${NC}"
echo "Testing if service is responding on port 9090:"
nc -z -v localhost 9090 2>&1 || echo -e "${RED}❌ Port 9090 is not accessible${NC}"
echo ""

# =============================================================================
# Application Route Tests
# =============================================================================

print_section "2. APPLICATION ROUTE TESTS"

echo -e "${GREEN}2.1 Root Route${NC}"
run_curl_test "Homepage" "$WILLOWCMS_URL/"

echo -e "${GREEN}2.2 Language Routes${NC}"
run_curl_test "English Route" "$WILLOWCMS_URL/en"
run_curl_test "Default Locale" "$WILLOWCMS_URL/en/"

echo -e "${GREEN}2.3 Admin Routes${NC}"
run_curl_headers "Admin Login" "$WILLOWCMS_URL/admin"
run_curl_headers "Admin Dashboard" "$WILLOWCMS_URL/admin/"

echo -e "${GREEN}2.4 API Routes (if available)${NC}"
run_curl_headers "API Endpoint" "$WILLOWCMS_URL/api"
run_curl_headers "Health Check" "$WILLOWCMS_URL/health"

echo -e "${GREEN}2.5 Static Assets${NC}"
run_curl_headers "CSS Assets" "$WILLOWCMS_URL/default_theme/css/app.css"
run_curl_headers "JS Assets" "$WILLOWCMS_URL/default_theme/js/app.js"
run_curl_headers "Favicon" "$WILLOWCMS_URL/favicon.ico"

# =============================================================================
# Content-Type and Accept Header Tests
# =============================================================================

print_section "3. CONTENT-TYPE AND ACCEPT HEADER TESTS"

echo -e "${GREEN}3.1 JSON Accept Header${NC}"
run_curl_headers "JSON Request" "$WILLOWCMS_URL/" "-H 'Accept: application/json'"

echo -e "${GREEN}3.2 XML Accept Header${NC}"
run_curl_headers "XML Request" "$WILLOWCMS_URL/" "-H 'Accept: application/xml'"

echo -e "${GREEN}3.3 Plain Text Accept Header${NC}"
run_curl_headers "Text Request" "$WILLOWCMS_URL/" "-H 'Accept: text/plain'"

# =============================================================================
# Authentication and Session Tests
# =============================================================================

print_section "4. AUTHENTICATION AND SESSION TESTS"

echo -e "${GREEN}4.1 Session Cookie Test${NC}"
echo "Testing session handling:"
COOKIE_JAR="/tmp/willowcms_cookies_$(date +%s).txt"
curl -c "$COOKIE_JAR" -b "$COOKIE_JAR" -s -m $TIMEOUT \
     -H "User-Agent: $USER_AGENT" \
     -w "HTTP Code: %{http_code}\nCookies Set: " \
     "$WILLOWCMS_URL/" > /dev/null

if [ -f "$COOKIE_JAR" ]; then
    echo "$(cat "$COOKIE_JAR" | grep -v "^#" | wc -l) cookie(s)"
    echo "Cookie contents:"
    cat "$COOKIE_JAR" | grep -v "^#" || echo "No cookies set"
    rm -f "$COOKIE_JAR"
else
    echo "No cookies file created"
fi
echo ""

echo -e "${GREEN}4.2 CSRF Token Test${NC}"
echo "Looking for CSRF tokens in response:"
curl -s -m $TIMEOUT "$WILLOWCMS_URL/" | grep -i "csrf\|token\|_token" | head -5 || echo "No CSRF tokens found"
echo ""

# =============================================================================
# Error Handling Tests
# =============================================================================

print_section "5. ERROR HANDLING TESTS"

echo -e "${GREEN}5.1 404 Error Handling${NC}"
run_curl_headers "Non-existent Route" "$WILLOWCMS_URL/this-route-does-not-exist-test-404"

echo -e "${GREEN}5.2 Method Not Allowed${NC}"
run_curl_headers "POST to GET route" "$WILLOWCMS_URL/" "-X POST"

echo -e "${GREEN}5.3 Invalid Accept Header${NC}"
run_curl_headers "Invalid Accept" "$WILLOWCMS_URL/" "-H 'Accept: invalid/mimetype'"

# =============================================================================
# Performance and Caching Tests
# =============================================================================

print_section "6. PERFORMANCE AND CACHING TESTS"

echo -e "${GREEN}6.1 Caching Headers${NC}"
echo "Checking for cache-related headers:"
curl -I -s -m $TIMEOUT "$WILLOWCMS_URL/" | grep -i "cache\|etag\|expires\|last-modified" || echo "No cache headers found"
echo ""

echo -e "${GREEN}6.2 Compression Test${NC}"
echo "Testing gzip/deflate compression:"
curl -H "Accept-Encoding: gzip, deflate" -I -s -m $TIMEOUT "$WILLOWCMS_URL/" | grep -i "content-encoding" || echo "No compression headers found"
echo ""

echo -e "${GREEN}6.3 Response Time Test${NC}"
echo "Performance metrics (5 requests):"
for i in {1..5}; do
    TIME=$(curl -o /dev/null -s -m $TIMEOUT -w "%{time_total}" "$WILLOWCMS_URL/")
    echo "Request $i: ${TIME}s"
done
echo ""

# =============================================================================
# Security Headers Tests
# =============================================================================

print_section "7. SECURITY HEADERS TESTS"

echo -e "${GREEN}7.1 Security Headers Check${NC}"
echo "Checking for security headers:"
SECURITY_HEADERS=$(curl -I -s -m $TIMEOUT "$WILLOWCMS_URL/" | grep -i "x-frame-options\|x-xss-protection\|x-content-type-options\|strict-transport-security\|content-security-policy\|referrer-policy")

if [ -n "$SECURITY_HEADERS" ]; then
    echo "$SECURITY_HEADERS"
else
    echo "No security headers found"
fi
echo ""

# =============================================================================
# Database Connectivity Test (via app)
# =============================================================================

print_section "8. APPLICATION HEALTH TESTS"

echo -e "${GREEN}8.1 Application Debug Info${NC}"
echo "Looking for debug information in response:"
RESPONSE=$(curl -s -m $TIMEOUT "$WILLOWCMS_URL/")
echo "Response contains debug info: $(echo "$RESPONSE" | grep -i "debug\|error\|exception\|stack" >/dev/null && echo "Yes" || echo "No")"
echo "Response size: $(echo "$RESPONSE" | wc -c) characters"
echo "Response contains HTML: $(echo "$RESPONSE" | grep -i "<html" >/dev/null && echo "Yes" || echo "No")"
echo "Response contains CakePHP: $(echo "$RESPONSE" | grep -i "cakephp\|cake" >/dev/null && echo "Yes" || echo "No")"
echo ""

echo -e "${GREEN}8.2 Container Health Status${NC}"
echo "Docker container health:"
docker compose ps willowcms --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || echo "Could not get container status"
echo ""

# =============================================================================
# Advanced Debugging Commands
# =============================================================================

print_section "9. ADVANCED DEBUGGING COMMANDS"

echo -e "${GREEN}9.1 Complete curl Debug Command${NC}"
echo "Run this command for maximum debugging detail:"
echo ""
cat << 'EOF'
curl -v -L -s -m 30 \
  -H "User-Agent: WillowCMS-Debug/1.0" \
  -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
  -H "Accept-Language: en-US,en;q=0.5" \
  -H "Accept-Encoding: gzip, deflate" \
  -H "Cache-Control: no-cache" \
  -H "Pragma: no-cache" \
  -w "\n\nTiming breakdown:\nDNS: %{time_namelookup}s\nConnect: %{time_connect}s\nPre-transfer: %{time_pretransfer}s\nStart-transfer: %{time_starttransfer}s\nTotal: %{time_total}s\nHTTP Code: %{http_code}\nSize: %{size_download} bytes\nRedirect Count: %{num_redirects}\nRedirect URL: %{redirect_url}\nContent Type: %{content_type}\n" \
  http://localhost:9090/
EOF
echo ""

echo -e "${GREEN}9.2 Log Monitoring Commands${NC}"
echo "Monitor application logs with:"
echo "docker compose logs -f willowcms"
echo ""
echo "Monitor nginx access logs:"
echo "docker compose exec willowcms tail -f /var/log/nginx/access.log"
echo ""
echo "Monitor nginx error logs:"
echo "docker compose exec willowcms tail -f /var/log/nginx/error.log"
echo ""

echo -e "${GREEN}9.3 Container Shell Access${NC}"
echo "Access container shell for detailed debugging:"
echo "docker compose exec willowcms /bin/sh"
echo ""

print_section "10. SUMMARY"

echo -e "${GREEN}Debug script completed!${NC}"
echo ""
echo "If you're experiencing issues, check:"
echo "1. Container status: docker compose ps"
echo "2. Application logs: docker compose logs willowcms"
echo "3. Network connectivity: nc -zv localhost 9090"
echo "4. Database connectivity: docker compose exec willowcms /var/www/html/bin/cake check_table_exists settings"
echo ""
echo -e "${YELLOW}For immediate debugging, run:${NC}"
echo "curl -v http://localhost:9090/"
echo ""
echo -e "${BLUE}==============================================================================${NC}"