#!/usr/bin/env python3
"""
Generate comprehensive test failure report for WillowCMS
Parses PHPUnit output and creates structured markdown report
"""

import subprocess
import re
from collections import defaultdict
from datetime import datetime

def run_phpunit(path="tests/TestCase/Controller/"):
    """Run PHPUnit and capture output"""
    cmd = f"docker compose exec -T willowcms php vendor/bin/phpunit {path} --testdox"
    result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return result.stdout + result.stderr

def parse_test_results(output):
    """Parse PHPUnit output to extract test results"""
    results = {
        'total_tests': 0,
        'total_assertions': 0,
        'errors': 0,
        'failures': 0,
        'warnings': 0,
        'skipped': 0,
        'failing_tests': defaultdict(list),
        'passing_tests': defaultdict(list),
        'schema_warnings': set(),
        'missing_templates': set()
    }
    
    # Extract summary line
    summary_match = re.search(r'Tests: (\d+), Assertions: (\d+), Errors: (\d+), Failures: (\d+)', output)
    if summary_match:
        results['total_tests'] = int(summary_match.group(1))
        results['total_assertions'] = int(summary_match.group(2))
        results['errors'] = int(summary_match.group(3))
        results['failures'] = int(summary_match.group(4))
    
    # Extract warnings
    warnings_match = re.search(r'PHPUnit Warnings: (\d+)', output)
    if warnings_match:
        results['warnings'] = int(warnings_match.group(1))
    
    # Extract skipped
    skipped_match = re.search(r'Skipped: (\d+)', output)
    if skipped_match:
        results['skipped'] = int(skipped_match.group(1))
    
    # Parse test results by controller
    current_controller = None
    for line in output.split('\n'):
        # Match controller name
        controller_match = re.match(r'^([A-Z][a-zA-Z\s]+Controller)', line)
        if controller_match:
            current_controller = controller_match.group(1).strip()
            continue
        
        # Match failing tests
        if '✘' in line and current_controller:
            test_name = line.strip().replace('✘', '').strip()
            results['failing_tests'][current_controller].append(test_name)
        
        # Match passing tests
        if '✔' in line and current_controller:
            test_name = line.strip().replace('✔', '').strip()
            results['passing_tests'][current_controller].append(test_name)
        
        # Extract schema warnings
        if 'Schema warning for' in line:
            match = re.search(r'Schema warning for (\w+)', line)
            if match:
                results['schema_warnings'].add(match.group(1))
        
        # Extract missing templates
        if 'MissingTemplateException' in line:
            match = re.search(r'Template file `([^`]+)`', line)
            if match:
                results['missing_templates'].add(match.group(1))
    
    return results

def generate_report(results):
    """Generate markdown report from parsed results"""
    report = []
    report.append("# WillowCMS Controller Test Detailed Report")
    report.append(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    report.append("")
    
    # Executive Summary
    report.append("## Executive Summary")
    report.append("")
    report.append(f"- **Total Tests**: {results['total_tests']}")
    report.append(f"- **Total Assertions**: {results['total_assertions']}")
    report.append(f"- **Passing**: {results['total_tests'] - results['errors'] - results['failures']} ({((results['total_tests'] - results['errors'] - results['failures']) / results['total_tests'] * 100):.1f}%)")
    report.append(f"- **Errors**: {results['errors']} ({(results['errors'] / results['total_tests'] * 100):.1f}%)")
    report.append(f"- **Failures**: {results['failures']} ({(results['failures'] / results['total_tests'] * 100):.1f}%)")
    if results['warnings'] > 0:
        report.append(f"- **Warnings**: {results['warnings']}")
    if results['skipped'] > 0:
        report.append(f"- **Skipped**: {results['skipped']}")
    report.append("")
    
    # Failing Controllers Summary
    report.append("## Failing Controllers Summary")
    report.append("")
    report.append("| Controller | Failing Tests | Passing Tests | Total | Pass Rate |")
    report.append("|------------|---------------|---------------|-------|-----------|")
    
    all_controllers = set(list(results['failing_tests'].keys()) + list(results['passing_tests'].keys()))
    controller_stats = []
    
    for controller in sorted(all_controllers):
        failing = len(results['failing_tests'][controller])
        passing = len(results['passing_tests'][controller])
        total = failing + passing
        pass_rate = (passing / total * 100) if total > 0 else 0
        controller_stats.append((controller, failing, passing, total, pass_rate))
    
    # Sort by most failing first
    controller_stats.sort(key=lambda x: (-x[1], x[0]))
    
    for controller, failing, passing, total, pass_rate in controller_stats:
        if failing > 0:  # Only show controllers with failures
            report.append(f"| {controller} | {failing} | {passing} | {total} | {pass_rate:.1f}% |")
    report.append("")
    
    # Detailed Failing Tests by Controller
    report.append("## Detailed Failing Tests by Controller")
    report.append("")
    
    for controller in sorted(results['failing_tests'].keys()):
        if results['failing_tests'][controller]:
            report.append(f"### {controller}")
            report.append("")
            for test_name in results['failing_tests'][controller]:
                report.append(f"- ✘ {test_name}")
            report.append("")
    
    # Fixture Schema Issues
    if results['schema_warnings']:
        report.append("## Fixture Schema Issues")
        report.append("")
        report.append("The following fixtures have SQLite compatibility issues (missing length specifications):")
        report.append("")
        for fixture in sorted(results['schema_warnings']):
            report.append(f"- `{fixture}`")
        report.append("")
        report.append("**Impact**: These schema errors prevent test fixtures from loading properly in SQLite.")
        report.append("")
    
    # Missing Templates
    if results['missing_templates']:
        report.append("## Missing Templates")
        report.append("")
        report.append("The following template files need to be created:")
        report.append("")
        for template in sorted(results['missing_templates']):
            report.append(f"- `{template}`")
        report.append("")
    
    # Recommendations
    report.append("## Recommended Action Plan")
    report.append("")
    report.append("### Priority 1: Fix Fixture Schema Issues (High Impact)")
    report.append(f"- **Affected Fixtures**: {len(results['schema_warnings'])} fixtures")
    report.append("- **Impact**: Prevents ~" + str(results['errors']) + " test errors")
    report.append("- **Action**: Add explicit field schemas with length specifications to fixtures")
    report.append("")
    
    report.append("### Priority 2: Create Missing Templates")
    report.append(f"- **Missing Templates**: {len(results['missing_templates'])} templates")
    report.append("- **Impact**: Prevents view-related test failures")
    report.append("- **Action**: Create minimal template files for each missing view")
    report.append("")
    
    report.append("### Priority 3: Fix Controller Logic Issues")
    report.append(f"- **Failing Tests**: {results['failures']} failures")
    report.append("- **Action**: Review and fix controller logic, authentication, and authorization")
    report.append("")
    
    return '\n'.join(report)

if __name__ == "__main__":
    print("Running PHPUnit tests and generating report...")
    print("This may take a few minutes...")
    print()
    
    output = run_phpunit()
    results = parse_test_results(output)
    report = generate_report(results)
    
    # Save report
    report_file = "/tmp/willow_test_report.md"
    with open(report_file, 'w') as f:
        f.write(report)
    
    print(report)
    print()
    print(f"Report saved to: {report_file}")
