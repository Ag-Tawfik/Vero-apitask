<?php
require_once 'Autoloader.php';
Autoloader::register();

$documentation = new ApiDocumentation();
if ($documentation->generate()) {
    echo "API documentation generated successfully in docs/api.md\n";
} else {
    echo "Failed to generate API documentation\n";
    exit(1);
} 