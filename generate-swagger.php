<?php
require_once 'Autoloader.php';
Autoloader::register();

// Define the routes directly since we can't instantiate Api class
$routes = [
    'get constructionStages' => [
        'class' => 'ConstructionStages',
        'method' => 'getAll',
        'description' => 'Get all construction stages',
        'parameters' => [],
        'response' => [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'startDate' => ['type' => 'string', 'format' => 'date-time'],
                    'endDate' => ['type' => 'string', 'format' => 'date-time'],
                    'duration' => ['type' => 'number'],
                    'durationUnit' => ['type' => 'string', 'enum' => ['HOURS', 'DAYS', 'WEEKS']],
                    'color' => ['type' => 'string', 'pattern' => '^#([a-f0-9]{3}){1,2}$'],
                    'externalId' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['NEW', 'PLANNED', 'DELETED']]
                ]
            ]
        ]
    ],
    'get constructionStages/(:num)' => [
        'class' => 'ConstructionStages',
        'method' => 'getSingle',
        'description' => 'Get a specific construction stage',
        'parameters' => [
            'id' => ['type' => 'integer', 'description' => 'The ID of the construction stage']
        ],
        'response' => [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'startDate' => ['type' => 'string', 'format' => 'date-time'],
                'endDate' => ['type' => 'string', 'format' => 'date-time'],
                'duration' => ['type' => 'number'],
                'durationUnit' => ['type' => 'string', 'enum' => ['HOURS', 'DAYS', 'WEEKS']],
                'color' => ['type' => 'string', 'pattern' => '^#([a-f0-9]{3}){1,2}$'],
                'externalId' => ['type' => 'string'],
                'status' => ['type' => 'string', 'enum' => ['NEW', 'PLANNED', 'DELETED']]
            ]
        ]
    ],
    'post constructionStages' => [
        'class' => 'ConstructionStages',
        'method' => 'post',
        'bodyType' => 'ConstructionStagesCreate',
        'description' => 'Create a new construction stage',
        'request' => [
            'type' => 'object',
            'required' => ['name', 'startDate'],
            'properties' => [
                'name' => ['type' => 'string', 'maxLength' => 255],
                'startDate' => ['type' => 'string', 'format' => 'date-time'],
                'endDate' => ['type' => 'string', 'format' => 'date-time'],
                'durationUnit' => ['type' => 'string', 'enum' => ['HOURS', 'DAYS', 'WEEKS']],
                'color' => ['type' => 'string', 'pattern' => '^#([a-f0-9]{3}){1,2}$'],
                'externalId' => ['type' => 'string', 'maxLength' => 255],
                'status' => ['type' => 'string', 'enum' => ['NEW', 'PLANNED', 'DELETED']]
            ]
        ]
    ],
    'patch constructionStages/(:num)' => [
        'class' => 'ConstructionStages',
        'method' => 'update',
        'bodyType' => 'ConstructionStagesUpdate',
        'description' => 'Update a construction stage',
        'parameters' => [
            'id' => ['type' => 'integer', 'description' => 'The ID of the construction stage']
        ],
        'request' => [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'maxLength' => 255],
                'startDate' => ['type' => 'string', 'format' => 'date-time'],
                'endDate' => ['type' => 'string', 'format' => 'date-time'],
                'durationUnit' => ['type' => 'string', 'enum' => ['HOURS', 'DAYS', 'WEEKS']],
                'color' => ['type' => 'string', 'pattern' => '^#([a-f0-9]{3}){1,2}$'],
                'externalId' => ['type' => 'string', 'maxLength' => 255],
                'status' => ['type' => 'string', 'enum' => ['NEW', 'PLANNED', 'DELETED']]
            ]
        ]
    ],
    'delete constructionStages/(:num)' => [
        'class' => 'ConstructionStages',
        'method' => 'delete',
        'description' => 'Delete a construction stage',
        'parameters' => [
            'id' => ['type' => 'integer', 'description' => 'The ID of the construction stage']
        ],
        'response' => [
            'type' => 'object',
            'properties' => [
                'success' => [
                    'type' => 'object',
                    'properties' => [
                        'code' => ['type' => 'integer'],
                        'message' => ['type' => 'string']
                    ]
                ]
            ]
        ]
    ]
];

try {
    $generator = new SwaggerGenerator($routes);
    $swagger = $generator->generate();
    
    $outputFile = __DIR__ . '/docs/swagger.json';
    $outputDir = dirname($outputFile);
    
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    file_put_contents(
        $outputFile,
        json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
    
    echo "Swagger documentation generated successfully at: {$outputFile}\n";
} catch (Exception $e) {
    echo "Error generating Swagger documentation: " . $e->getMessage() . "\n";
    exit(1);
} 