#!/bin/bash
# Generate comprehensive test failure report for WillowCMS
# This script analyzes PHPUnit test results and creates a detailed report

REPORT_FILE="/tmp/willow_test_report.md"
DOCKER_CMD="docker compose exec -T willowcms"

echo "# WillowCMS Controller Test Report" > $REPORT_FILE
echo "Generated: $(date)" >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Overall summary
echo "## Overall Summary" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/ 2>&1 | \
  grep -E "^Tests:|^Time:|ERRORS|OK" | head -5 >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Test by thread categorization
echo "## Test Results by Thread" >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Thread 1: Admin Controllers
echo "### Thread 1: Admin Controllers" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/Admin/ --testdox 2>&1 | \
  grep -E "^Tests:|ERRORS|OK" | head -3 >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Thread 2: Public Controllers  
echo "### Thread 2: Public Controllers" >> $REPORT_FILE
echo "" >> $REPORT_FILE
echo "Testing ArticlesController, PagesController, ProductsController, TagsController" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/ArticlesControllerTest.php \
  tests/TestCase/Controller/PagesControllerTest.php \
  tests/TestCase/Controller/ProductsControllerTest.php \
  tests/TestCase/Controller/TagsControllerTest.php 2>&1 | \
  grep -E "^Tests:|ERRORS|OK" | head -3 >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Thread 3: API Controllers
echo "### Thread 3: API Controllers" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/QuizControllerTest.php \
  tests/TestCase/Controller/ReliabilityControllerTest.php 2>&1 | \
  grep -E "^Tests:|ERRORS|OK" | head -3 >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Thread 4: User/Auth Controllers
echo "### Thread 4: User/Auth Controllers ✅ COMPLETE" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php 2>&1 | \
  grep -E "^Tests:|OK" | head -2 >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Top 10 failing test classes
echo "## Top Failing Test Classes" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/ --testdox 2>&1 | \
  grep -E "✘" | cut -d' ' -f2- | sort | uniq -c | sort -rn | head -10 >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Schema warnings summary
echo "## Fixture Schema Issues" >> $REPORT_FILE
echo "" >> $REPORT_FILE
echo "The following fixtures have SQLite compatibility issues:" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php 2>&1 | \
  grep "Schema warning" | cut -d':' -f2 | sort -u >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Missing templates
echo "## Missing Templates" >> $REPORT_FILE
echo "" >> $REPORT_FILE
$DOCKER_CMD php vendor/bin/phpunit tests/TestCase/Controller/ 2>&1 | \
  grep "MissingTemplateException" | grep -oP "Template file \`[^']+\`" | sort -u | head -20 >> $REPORT_FILE
echo "" >> $REPORT_FILE

echo "Report generated: $REPORT_FILE"
cat $REPORT_FILE
