<?php
class Router {
    private $routes;
    private $page;
    
    public function __construct() {
        $this->routes = require_once 'config/routes.php';
        $this->page = $this->getRequestedPage();
    }
    
    private function sanitizeInput($input) {
        if (empty($input)) {
            return '';
        }
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    }
    
    private function getRequestedPage() {
        $page = isset($_GET['page']) ? $this->sanitizeInput($_GET['page']) : 'home';
        return $page;
    }
    
    public function isValidPage() {
        $allRoutes = array_merge(
            $this->routes['public'], 
            $this->routes['auth'],
            $this->routes['admin']
        );
        return in_array($this->page, $allRoutes, true);
    }
    
    public function requiresAuth() {
        return in_array($this->page, $this->routes['auth'], true);
    }
    
    public function requiresAdmin() {
        return in_array($this->page, $this->routes['admin'], true);
    }
    
    public function getPagePath() {
        return sprintf("pages/%s.php", $this->page);
    }
    
    public function getCurrentPage() {
        return $this->page;
    }
}