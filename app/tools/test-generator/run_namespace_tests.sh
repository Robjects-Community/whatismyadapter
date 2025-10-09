#!/bin/bash
# Run tests for specific namespace (Admin, Api, or Root)

if [ -z "$1" ]; then
    echo "Usage: ./run_namespace_tests.sh [admin|api|root]"
    exit 1
fi

cd /Volumes/1TB_DAVINCI/docker/willow

case "$1" in
    admin)
        echo "🧪 Running Admin controller tests..."
        docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/
        ;;
    api)
        echo "🧪 Running API controller tests..."
        docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Api/
        ;;
    root)
        echo "🧪 Running root controller tests..."
        # Get all test files excluding Admin and Api directories
        find app/tests/TestCase/Controller -maxdepth 1 -name "*Test.php" -exec docker compose exec willowcms php vendor/bin/phpunit {} \;
        ;;
    *)
        echo "Invalid namespace. Use: admin, api, or root"
        exit 1
        ;;
esac
