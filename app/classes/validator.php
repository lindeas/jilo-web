<?php

class Validator {
    private $errors = [];
    private $data = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function validate(array $rules) {
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule => $parameter) {
                $this->applyRule($field, $rule, $parameter);
            }
        }
        return empty($this->errors);
    }

    private function applyRule($field, $rule, $parameter) {
        $value = $this->data[$field] ?? null;

        switch ($rule) {
            case 'required':
                if ($parameter && empty($value)) {
                    $this->addError($field, "Field is required");
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Invalid email format");
                }
                break;
            case 'min':
                if (!empty($value) && strlen($value) < $parameter) {
                    $this->addError($field, "Minimum length is $parameter characters");
                }
                break;
            case 'max':
                if (!empty($value) && strlen($value) > $parameter) {
                    $this->addError($field, "Maximum length is $parameter characters");
                }
                break;
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "Must be a number");
                }
                break;
            case 'phone':
                if (!empty($value) && !preg_match('/^[+]?[\d\s-()]{7,}$/', $value)) {
                    $this->addError($field, "Invalid phone number format");
                }
                break;
            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "Invalid URL format");
                }
                break;
            case 'date':
                if (!empty($value)) {
                    $date = date_parse($value);
                    if ($date['error_count'] > 0) {
                        $this->addError($field, "Invalid date format");
                    }
                }
                break;
            case 'in':
                if (!empty($value) && !in_array($value, $parameter)) {
                    $this->addError($field, "Invalid option selected");
                }
                break;
            case 'matches':
                if ($value !== ($this->data[$parameter] ?? null)) {
                    $this->addError($field, "Does not match $parameter field");
                }
                break;
        }
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function getFirstError() {
        if (!$this->hasErrors()) {
            return null;
        }
        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0];
    }
}
