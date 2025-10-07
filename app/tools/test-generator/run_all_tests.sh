#!/bin/bash
# Run all controller tests with coverage

cd /Volumes/1TB_DAVINCI/docker/willow

echo "🧪 Running all controller tests..."
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/

echo ""
echo "📊 Generating coverage report..."
docker compose exec willowcms php vendor/bin/phpunit \
    --coverage-html webroot/coverage \
    --coverage-text \
    tests/TestCase/Controller/

echo ""
echo "✅ Coverage report available at: http://localhost:8080/coverage"
