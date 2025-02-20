<?php

require_once dirname(__DIR__, 3) . '/app/classes/validator.php';

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testRequired()
    {
        // Test valid data
        $data = ['name' => 'John'];
        $validator = new Validator($data);
        $rules = ['name' => ['required' => true]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid data
        $data = ['name' => ''];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testEmail()
    {
        // Test valid email
        $data = ['email' => 'test@example.com'];
        $validator = new Validator($data);
        $rules = ['email' => ['email' => true]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid email
        $data = ['email' => 'invalid-email'];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testMinLength()
    {
        // Test valid length
        $data = ['password' => '123456'];
        $validator = new Validator($data);
        $rules = ['password' => ['min' => 6]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid length
        $data = ['password' => '12345'];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testMaxLength()
    {
        // Test valid length
        $data = ['username' => '12345'];
        $validator = new Validator($data);
        $rules = ['username' => ['max' => 5]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid length
        $data = ['username' => '123456'];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testNumeric()
    {
        // Test valid number
        $data = ['age' => '25'];
        $validator = new Validator($data);
        $rules = ['age' => ['numeric' => true]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid number
        $data = ['age' => 'twenty-five'];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testUrl()
    {
        // Test valid URL
        $data = ['website' => 'https://example.com'];
        $validator = new Validator($data);
        $rules = ['website' => ['url' => true]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid URL
        $data = ['website' => 'not-a-url'];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testMultipleRules()
    {
        // Test valid data
        $data = ['email' => 'test@example.com'];
        $validator = new Validator($data);
        $rules = ['email' => [
            'required' => true,
            'email' => true,
            'max' => 50
        ]];

        $this->assertTrue($validator->validate($rules));
        $this->assertEmpty($validator->getErrors());

        // Test invalid data
        $data = ['email' => str_repeat('a', 51) . '@example.com'];
        $validator = new Validator($data);
        $this->assertFalse($validator->validate($rules));
        $this->assertNotEmpty($validator->getErrors());
    }

    public function testCustomErrorMessages()
    {
        $data = ['age' => 'not-a-number'];
        $validator = new Validator($data);
        $rules = ['age' => ['numeric' => true]];

        $this->assertFalse($validator->validate($rules));
        $errors = $validator->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertEquals('Must be a number', $errors['age'][0]);
    }
}
