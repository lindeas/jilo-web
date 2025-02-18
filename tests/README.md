# Jilo Web Test Suite

This directory contains the test suite for the Jilo Web application. All testing-related files are isolated here to keep the main application clean.

## Structure

```
tests/
├── framework/           # Test framework files
│   ├── composer.json   # Composer configuration for tests
│   ├── phpunit.xml     # PHPUnit configuration
│   ├── Unit/          # Unit tests
│   ├── Integration/   # Integration tests
│   └── TestCase.php   # Base test case class
└── README.md          # This file
```

## Running Tests

1. Change to the framework directory:
```bash
cd tests/framework
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

The coverage report will be generated in `tests/framework/coverage/`.
