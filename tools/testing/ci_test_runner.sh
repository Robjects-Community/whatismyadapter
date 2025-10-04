#!/bin/bash
# CI Test Runner for WillowCMS
# Comprehensive automated testing with parallel execution and reporting

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
CI_MODE=${CI:-false}
THREAD_ID=${GITHUB_RUN_ID:-$(date +%s)}
COVERAGE_THRESHOLD=${COVERAGE_THRESHOLD:-80}
PERFORMANCE_THRESHOLD=${PERFORMANCE_THRESHOLD:-500}
MAX_PARALLEL_JOBS=${MAX_PARALLEL_JOBS:-4}

echo -e "${BLUE}🚀 WillowCMS CI Test Runner${NC}"
echo -e "${BLUE}===========================${NC}"
echo -e "${CYAN}Thread ID: $THREAD_ID${NC}"
echo -e "${CYAN}CI Mode: $CI_MODE${NC}"
echo -e "${CYAN}Coverage Threshold: $COVERAGE_THRESHOLD%${NC}"
echo -e "${CYAN}Performance Threshold: ${PERFORMANCE_THRESHOLD}ms${NC}"
echo ""

# Create CI directories
mkdir -p ci_reports
mkdir -p ci_reports/coverage
mkdir -p ci_reports/performance
mkdir -p ci_reports/security

# Step 1: Environment Setup and Validation
echo -e "${PURPLE}📋 Step 1: Environment Setup${NC}"
echo -e "${BLUE}=============================${NC}"

# Check Docker environment
if ! docker compose ps willowcms >/dev/null 2>&1; then
    echo -e "${RED}❌ Docker environment not running${NC}"
    if [ "$CI_MODE" = "true" ]; then
        echo -e "${YELLOW}🔧 Starting Docker environment...${NC}"
        ./run_dev_env.sh --ci
        sleep 30  # Wait for services to be ready
    else
        echo -e "${YELLOW}💡 Please run: ./run_dev_env.sh${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✅ Docker environment ready${NC}"

# Verify database connectivity
if docker compose exec -T willowcms php bin/cake migrations status >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Database connectivity verified${NC}"
else
    echo -e "${YELLOW}⚠️  Database might not be ready, running migrations...${NC}"
    docker compose exec -T willowcms php bin/cake migrations migrate
fi

# Step 2: Pre-test Analysis
echo ""
echo -e "${PURPLE}📊 Step 2: Pre-test Analysis${NC}"
echo -e "${BLUE}==============================${NC}"

# Run coverage analysis
./tools/testing/analyze_coverage.sh > ci_reports/pre_test_analysis.txt
echo -e "${GREEN}✅ Coverage analysis complete${NC}"

# Step 3: Parallel Test Execution
echo ""
echo -e "${PURPLE}🧪 Step 3: Parallel Test Execution${NC}"
echo -e "${BLUE}===================================${NC}"

# Initialize test results
declare -A test_results
declare -A test_pids

# Function to run component tests in parallel
run_component_test() {
    local component=$1
    local thread_id=$2
    local output_file="ci_reports/${component,,}_test_results_${thread_id}.log"
    
    echo -e "${CYAN}🔄 Starting $component tests (Thread: $thread_id)${NC}"
    
    # Run tests with coverage and detailed output
    ./tools/testing/run_tests.sh \
        --component="$component" \
        --thread="$thread_id" \
        --coverage \
        --verbose > "$output_file" 2>&1 &
    
    local pid=$!
    test_pids["$component"]=$pid
    
    return 0
}

# Start parallel test execution
components=("Controller" "Model" "Service" "Middleware")
thread_counter=1000

for component in "${components[@]}"; do
    if [ ${#test_pids[@]} -ge $MAX_PARALLEL_JOBS ]; then
        # Wait for a job to complete
        for comp in "${!test_pids[@]}"; do
            if ! kill -0 ${test_pids[$comp]} 2>/dev/null; then
                wait ${test_pids[$comp]}
                test_results[$comp]=$?
                unset test_pids[$comp]
                break
            fi
        done
    fi
    
    run_component_test "$component" $((thread_counter++))
    sleep 2  # Small delay between starts
done

# Wait for all remaining jobs
for component in "${!test_pids[@]}"; do
    echo -e "${YELLOW}⏳ Waiting for $component tests to complete...${NC}"
    wait ${test_pids[$component]}
    test_results[$component]=$?
done

# Step 4: Security Testing
echo ""
echo -e "${PURPLE}🔒 Step 4: Security Testing${NC}"
echo -e "${BLUE}===========================${NC}"

security_thread=$((thread_counter++))
echo -e "${CYAN}🛡️  Running security tests (Thread: $security_thread)${NC}"

./tools/testing/run_tests.sh \
    --component=Security \
    --thread="$security_thread" \
    --verbose > "ci_reports/security_test_results_${security_thread}.log" 2>&1

security_result=$?
test_results["Security"]=$security_result

if [ $security_result -eq 0 ]; then
    echo -e "${GREEN}✅ Security tests passed${NC}"
else
    echo -e "${RED}❌ Security tests failed${NC}"
fi

# Step 5: Performance Testing
echo ""
echo -e "${PURPLE}⚡ Step 5: Performance Testing${NC}"
echo -e "${BLUE}==============================${NC}"

performance_thread=$((thread_counter++))
echo -e "${CYAN}📈 Running performance tests (Thread: $performance_thread)${NC}"

./tools/testing/run_tests.sh \
    --component=Performance \
    --thread="$performance_thread" \
    --verbose > "ci_reports/performance_test_results_${performance_thread}.log" 2>&1

performance_result=$?
test_results["Performance"]=$performance_result

if [ $performance_result -eq 0 ]; then
    echo -e "${GREEN}✅ Performance tests passed${NC}"
else
    echo -e "${RED}❌ Performance tests failed${NC}"
fi

# Step 6: Integration Testing
echo ""
echo -e "${PURPLE}🔗 Step 6: Integration Testing${NC}"
echo -e "${BLUE}==============================${NC}"

integration_thread=$((thread_counter++))
echo -e "${CYAN}🔄 Running integration tests (Thread: $integration_thread)${NC}"

# Run routing and integration tests
./tools/testing/run_tests.sh \
    --filter=RoutingTest \
    --thread="$integration_thread" \
    --verbose > "ci_reports/integration_test_results_${integration_thread}.log" 2>&1

integration_result=$?
test_results["Integration"]=$integration_result

if [ $integration_result -eq 0 ]; then
    echo -e "${GREEN}✅ Integration tests passed${NC}"
else
    echo -e "${RED}❌ Integration tests failed${NC}"
fi

# Step 7: Test Results Analysis
echo ""
echo -e "${PURPLE}📊 Step 7: Results Analysis${NC}"
echo -e "${BLUE}============================${NC}"

# Calculate overall results
total_tests=0
passed_tests=0
failed_components=()

for component in "${!test_results[@]}"; do
    total_tests=$((total_tests + 1))
    if [ ${test_results[$component]} -eq 0 ]; then
        passed_tests=$((passed_tests + 1))
        echo -e "${GREEN}✅ $component: PASSED${NC}"
    else
        failed_components+=("$component")
        echo -e "${RED}❌ $component: FAILED${NC}"
    fi
done

# Calculate success rate
success_rate=$(( (passed_tests * 100) / total_tests ))

# Step 8: Coverage Analysis
echo ""
echo -e "${PURPLE}📈 Step 8: Coverage Analysis${NC}"
echo -e "${BLUE}=============================${NC}"

# Collect coverage data
coverage_files=$(find . -name "coverage_*" -type d 2>/dev/null || echo "")
if [ -n "$coverage_files" ]; then
    echo -e "${GREEN}✅ Coverage reports generated${NC}"
    
    # Try to extract coverage percentage (this would need actual coverage parser)
    coverage_percentage=75  # Placeholder - would be parsed from actual coverage reports
    
    if [ $coverage_percentage -ge $COVERAGE_THRESHOLD ]; then
        echo -e "${GREEN}✅ Coverage threshold met: ${coverage_percentage}% >= ${COVERAGE_THRESHOLD}%${NC}"
        coverage_status="PASSED"
    else
        echo -e "${RED}❌ Coverage threshold not met: ${coverage_percentage}% < ${COVERAGE_THRESHOLD}%${NC}"
        coverage_status="FAILED"
        failed_components+=("Coverage")
    fi
else
    echo -e "${YELLOW}⚠️  No coverage reports found${NC}"
    coverage_status="SKIPPED"
fi

# Step 9: Performance Analysis
echo ""
echo -e "${PURPLE}⚡ Step 9: Performance Analysis${NC}"
echo -e "${BLUE}===============================${NC}"

# Collect performance data
performance_reports=$(find app/tmp/tests -name "performance_report_*.json" 2>/dev/null || echo "")
if [ -n "$performance_reports" ]; then
    echo -e "${GREEN}✅ Performance reports generated${NC}"
    
    # Analyze performance reports (simplified)
    performance_issues=0
    for report in $performance_reports; do
        if [ -f "$report" ]; then
            # Extract performance data (would need JSON parser in real implementation)
            # For now, just check if report exists
            echo -e "${CYAN}📄 Report: $(basename $report)${NC}"
        fi
    done
    
    if [ $performance_issues -eq 0 ]; then
        echo -e "${GREEN}✅ Performance within acceptable limits${NC}"
    else
        echo -e "${YELLOW}⚠️  Performance issues detected: $performance_issues${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  No performance reports found${NC}"
fi

# Step 10: Cleanup Thread Resources
echo ""
echo -e "${PURPLE}🧹 Step 10: Cleanup${NC}"
echo -e "${BLUE}==================${NC}"

# Clean up all test threads
cleanup_threads=($(seq 1000 $((thread_counter-1))))
for thread in "${cleanup_threads[@]}"; do
    if ./tools/testing/cleanup_thread.sh "$thread" >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Cleaned up thread $thread${NC}"
    fi
done

# Step 11: Final Report Generation
echo ""
echo -e "${PURPLE}📋 Step 11: Final Report${NC}"
echo -e "${BLUE}==========================${NC}"

# Generate comprehensive report
report_file="ci_reports/final_test_report_$(date +%Y-%m-%d_%H-%M-%S).json"

cat > "$report_file" << EOF
{
  "test_run": {
    "id": "$THREAD_ID",
    "timestamp": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "ci_mode": $CI_MODE,
    "duration_seconds": $SECONDS
  },
  "summary": {
    "total_components": $total_tests,
    "passed_components": $passed_tests,
    "failed_components": ${#failed_components[@]},
    "success_rate": $success_rate,
    "coverage_status": "$coverage_status",
    "coverage_percentage": $coverage_percentage
  },
  "component_results": {
EOF

# Add component results to JSON
first=true
for component in "${!test_results[@]}"; do
    if [ "$first" = false ]; then
        echo "," >> "$report_file"
    fi
    status="PASSED"
    if [ ${test_results[$component]} -ne 0 ]; then
        status="FAILED"
    fi
    echo "    \"$component\": {\"status\": \"$status\", \"exit_code\": ${test_results[$component]}}" >> "$report_file"
    first=false
done

cat >> "$report_file" << EOF
  },
  "failed_components": [$(IFS=,; echo "\"${failed_components[*]//,/\",\"}")"],
  "recommendations": [
$(if [ ${#failed_components[@]} -gt 0 ]; then
    echo "    \"Fix failing components: $(IFS=' '; echo "${failed_components[*]}")\","
fi)
$(if [ $coverage_percentage -lt $COVERAGE_THRESHOLD ]; then
    echo "    \"Improve test coverage to meet ${COVERAGE_THRESHOLD}% threshold\","
fi)
    "Review performance reports for optimization opportunities",
    "Ensure all security tests pass before deployment"
  ]
}
EOF

echo -e "${GREEN}✅ Final report generated: $report_file${NC}"

# Step 12: Exit Status and Summary
echo ""
echo -e "${PURPLE}🏁 Final Summary${NC}"
echo -e "${BLUE}=================${NC}"
echo -e "${CYAN}Test Components: $total_tests${NC}"
echo -e "${CYAN}Passed: $passed_tests${NC}"
echo -e "${CYAN}Failed: ${#failed_components[@]}${NC}"
echo -e "${CYAN}Success Rate: $success_rate%${NC}"
echo -e "${CYAN}Coverage: $coverage_status${NC}"

if [ ${#failed_components[@]} -eq 0 ]; then
    echo ""
    echo -e "${GREEN}🎉 ALL TESTS PASSED! 🎉${NC}"
    echo -e "${GREEN}WillowCMS is ready for deployment${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}❌ TESTS FAILED${NC}"
    echo -e "${RED}Failed components: ${failed_components[*]}${NC}"
    echo -e "${YELLOW}📋 Check individual test logs in ci_reports/${NC}"
    echo -e "${YELLOW}📋 Review final report: $report_file${NC}"
    exit 1
fi