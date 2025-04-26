<?php
require_once 'Autoloader.php';
Autoloader::register();

// Initialize database
$database = new Database();
$db = $database->init();

// Create docs directory if it doesn't exist
if (!is_dir('docs')) {
    mkdir('docs', 0755, true);
}

// Generate API documentation
$documentation = new ApiDocumentation();
$documentation->generate();

echo "Project setup completed successfully!\n";
echo "You can now run the API server using PHP's built-in server:\n";
echo "php -S localhost:8000\n";
echo "\n";
echo "API Documentation is available in docs/api.md\n";
echo "You can view it using any markdown viewer or convert it to HTML.\n"; 