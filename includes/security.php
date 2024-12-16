<?php
class Security {
    public static function sanitizeInput($input) {
        if (empty($input)) {
            return '';
        }
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    public static function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }
    
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
}