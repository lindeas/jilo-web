# Jilo Web Test Suite

This directory contains the test suite for the Jilo Web application. All testing-related files are isolated here to keep the main application clean.

## Structure

```
tests/
├── Unit/            # Unit tests (individual classes and methods)
    ├── Classes/
    └── Helpers/
├── Feature/         # Feature (integration) tests (covering multiple components)
    ├── Midleware/
    └── Security/
├── Functional/      # Functionl tests (real usage scenarios)
├── Utils/           # Custom test utilities and libraries
├── TestCase.php     # Base test case class
├── composer.json    # Composer configuration for tests
├── phpunit.xml      # PHPUnit configuration
└── README.md        # This file
```

## Running Tests

1. Change to the test framework directory:
```bash
cd tests
```

2. Install dependencies (first time only):
```bash
composer install
```

3. Run all tests:
```bash
composer test
```

4. Generate coverage report:
```bash
composer test-coverage
```

The coverage report will be generated in `tests/coverage/`.
