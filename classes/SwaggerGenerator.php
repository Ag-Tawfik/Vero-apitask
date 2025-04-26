<?php

/**
 * Class SwaggerGenerator
 * Generates Swagger/OpenAPI documentation from route metadata
 */
class SwaggerGenerator
{
    private const SWAGGER_VERSION = '2.0';
    private const API_VERSION = '1.0.0';
    private const API_TITLE = 'Construction Stages API';
    private const API_DESCRIPTION = 'API for managing construction stages with validation and automatic duration calculation';
    private const API_HOST = 'localhost:8000';
    private const API_BASE_PATH = '/';
    private const API_SCHEMES = ['http'];

    private array $routes;

    /**
     * Constructor
     * @param array $routes The routes to generate documentation for
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Generate Swagger documentation
     * @return array
     */
    public function generate(): array
    {
        return [
            'swagger' => self::SWAGGER_VERSION,
            'info' => [
                'title' => self::API_TITLE,
                'description' => self::API_DESCRIPTION,
                'version' => self::API_VERSION,
                'contact' => [
                    'name' => 'API Support',
                    'email' => 'support@example.com'
                ]
            ],
            'host' => self::API_HOST,
            'basePath' => self::API_BASE_PATH,
            'schemes' => self::API_SCHEMES,
            'consumes' => ['application/json'],
            'produces' => ['application/json'],
            'paths' => $this->generatePaths(),
            'definitions' => $this->generateDefinitions(),
            'securityDefinitions' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'Authorization',
                    'in' => 'header'
                ]
            ]
        ];
    }

    /**
     * Generate paths from route metadata
     * @return array
     */
    private function generatePaths(): array
    {
        $paths = [];

        foreach ($this->routes as $pattern => $route) {
            $path = $this->convertPatternToPath($pattern);
            $method = strtolower(explode(' ', $pattern)[0]);
            
            $paths[$path][$method] = [
                'summary' => $route['description'],
                'tags' => ['Construction Stages'],
                'parameters' => $this->generateParameters($route),
                'responses' => $this->generateResponses($route),
                'security' => [['api_key' => []]]
            ];

            if (isset($route['request'])) {
                $paths[$path][$method]['parameters'][] = [
                    'name' => 'body',
                    'in' => 'body',
                    'required' => true,
                    'schema' => $route['request']
                ];
            }
        }

        return $paths;
    }

    /**
     * Convert route pattern to Swagger path
     * @param string $pattern
     * @return string
     */
    private function convertPatternToPath(string $pattern): string
    {
        $parts = explode(' ', $pattern);
        $path = $parts[1];
        return str_replace(['(:num)', '(:alpha)', '(:alnum)', '(:any)'], ['{id}', '{param}', '{param}', '{param}'], $path);
    }

    /**
     * Generate parameters from route metadata
     * @param array $route
     * @return array
     */
    private function generateParameters(array $route): array
    {
        $parameters = [];
        
        if (isset($route['parameters'])) {
            foreach ($route['parameters'] as $name => $param) {
                $parameters[] = [
                    'name' => $name,
                    'in' => 'path',
                    'required' => true,
                    'type' => $param['type'],
                    'description' => $param['description']
                ];
            }
        }

        return $parameters;
    }

    /**
     * Generate responses from route metadata
     * @param array $route
     * @return array
     */
    private function generateResponses(array $route): array
    {
        $responses = [
            '400' => [
                'description' => 'Bad Request',
                'schema' => [
                    '$ref' => '#/definitions/Error'
                ]
            ],
            '401' => [
                'description' => 'Unauthorized',
                'schema' => [
                    '$ref' => '#/definitions/Error'
                ]
            ],
            '404' => [
                'description' => 'Not Found',
                'schema' => [
                    '$ref' => '#/definitions/Error'
                ]
            ],
            '500' => [
                'description' => 'Internal Server Error',
                'schema' => [
                    '$ref' => '#/definitions/Error'
                ]
            ]
        ];

        if (isset($route['response'])) {
            $responses['200'] = [
                'description' => 'Success',
                'schema' => $route['response']
            ];
        }

        return $responses;
    }

    /**
     * Generate common definitions
     * @return array
     */
    private function generateDefinitions(): array
    {
        return [
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'format' => 'int32'
                    ],
                    'message' => [
                        'type' => 'string'
                    ]
                ]
            ],
            'ConstructionStage' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'format' => 'int64'
                    ],
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255
                    ],
                    'startDate' => [
                        'type' => 'string',
                        'format' => 'date-time'
                    ],
                    'endDate' => [
                        'type' => 'string',
                        'format' => 'date-time'
                    ],
                    'duration' => [
                        'type' => 'number',
                        'format' => 'float'
                    ],
                    'durationUnit' => [
                        'type' => 'string',
                        'enum' => ['HOURS', 'DAYS', 'WEEKS']
                    ],
                    'color' => [
                        'type' => 'string',
                        'pattern' => '^#([a-f0-9]{3}){1,2}$'
                    ],
                    'externalId' => [
                        'type' => 'string',
                        'maxLength' => 255
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['NEW', 'PLANNED', 'DELETED']
                    ]
                ]
            ]
        ];
    }
} 