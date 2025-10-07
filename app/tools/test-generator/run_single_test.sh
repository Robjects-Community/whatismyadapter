#!/bin/bash
# Run a single controller test file

if [ -z "$1" ]; then
    echo "Usage: ./run_single_test.sh <ControllerName>"
    echo "Example: ./run_single_test.sh Articles"
    exit 1
fi

cd /Volumes/1TB_DAVINCI/docker/willow

CONTROLLER_NAME="$1"
TEST_FILE="tests/TestCase/Controller/${CONTROLLER_NAME}ControllerTest.php"

# Check different possible locations
if [ -f "app/${TEST_FILE}" ]; then
    echo "🧪 Running ${CONTROLLER_NAME}Controller tests..."
    docker compose exec willowcms php vendor/bin/phpunit "app/${TEST_FILE}" --testdox
elif [ -f "app/tests/TestCase/Controller/Admin/${CONTROLLER_NAME}ControllerTest.php" ]; then
    echo "🧪 Running Admin ${CONTROLLER_NAME}Controller tests..."
    docker compose exec willowcms php vendor/bin/phpunit "app/tests/TestCase/Controller/Admin/${CONTROLLER_NAME}ControllerTest.php" --testdox
elif [ -f "app/tests/TestCase/Controller/Api/${CONTROLLER_NAME}ControllerTest.php" ]; then
    echo "🧪 Running API ${CONTROLLER_NAME}Controller tests..."
    docker compose exec willowcms php vendor/bin/phpunit "app/tests/TestCase/Controller/Api/${CONTROLLER_NAME}ControllerTest.php" --testdox
else
    echo "❌ Test file not found for ${CONTROLLER_NAME}Controller"
    echo "Tried:"
    echo "  - tests/TestCase/Controller/${CONTROLLER_NAME}ControllerTest.php"
    echo "  - tests/TestCase/Controller/Admin/${CONTROLLER_NAME}ControllerTest.php"
    echo "  - tests/TestCase/Controller/Api/${CONTROLLER_NAME}ControllerTest.php"
    exit 1
fi
