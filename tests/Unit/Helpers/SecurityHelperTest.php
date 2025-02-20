<?php

require_once dirname(__DIR__, 3) . '/app/helpers/security.php';

use PHPUnit\Framework\TestCase;

class SecurityHelperTest extends TestCase
{
    private SecurityHelper $security;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = SecurityHelper::getInstance();
    }

    public function testGenerateCsrfToken()
    {
        $token = $this->security->generateCsrfToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    public function testVerifyCsrfToken()
    {
        $token = $this->security->generateCsrfToken();

        $this->assertTrue($this->security->verifyCsrfToken($token));
        $this->assertFalse($this->security->verifyCsrfToken('invalid_token'));
        $this->assertFalse($this->security->verifyCsrfToken(''));
    }

    public function testSanitizeString()
    {
        $input = '<script>alert("xss")</script>';
        $expected = 'alert(&quot;xss&quot;)';

        $this->assertEquals($expected, $this->security->sanitizeString($input));
        $this->assertEquals('', $this->security->sanitizeString(null));
        $this->assertEquals('', $this->security->sanitizeString([]));
    }

    public function testValidateEmail()
    {
        $this->assertTrue($this->security->validateEmail('test@example.com'));
        $this->assertTrue($this->security->validateEmail('user.name+tag@example.co.uk'));
        $this->assertFalse($this->security->validateEmail('invalid.email'));
        $this->assertFalse($this->security->validateEmail('@example.com'));
    }

    public function testValidateInt()
    {
        $this->assertTrue($this->security->validateInt('123'));
        $this->assertTrue($this->security->validateInt('-123'));
        $this->assertFalse($this->security->validateInt('12.3'));
        $this->assertFalse($this->security->validateInt('abc'));
    }

    public function testValidateUrl()
    {
        $this->assertTrue($this->security->validateUrl('https://example.com'));
        $this->assertTrue($this->security->validateUrl('http://sub.example.co.uk/path?query=1'));
        $this->assertTrue($this->security->validateUrl('ftp://example.com')); // Any valid URL is accepted
        $this->assertFalse($this->security->validateUrl('not-a-url'));
    }

    public function testSanitizeArray()
    {
        $input = [
            'name' => '<b>John</b>',
            'email' => 'john@example.com',
            'nested' => [
                'key' => '<i>value</i>'
            ]
        ];

        $allowedKeys = ['name', 'email'];
        $result = $this->security->sanitizeArray($input, $allowedKeys);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayNotHasKey('nested', $result);
        $this->assertEquals('John', $result['name']); // HTML tags are stripped
        $this->assertEquals('john@example.com', $result['email']);
    }

    public function testValidateFormData()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'age' => 'not-a-number',
            'website' => 'not-a-url'
        ];

        $rules = [
            'name' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 50],
            'email' => ['type' => 'email', 'required' => true],
            'age' => ['type' => 'integer', 'required' => true],
            'website' => ['type' => 'url', 'required' => true]
        ];

        $errors = $this->security->validateFormData($data, $rules);

        $this->assertIsArray($errors);
        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertArrayHasKey('website', $errors);
    }
}
