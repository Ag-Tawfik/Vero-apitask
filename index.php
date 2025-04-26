<?php
require_once 'Autoloader.php';
Autoloader::register();

/**
 * Main API class that handles routing and request processing
 */
class Api
{
	private static $db;
	private const ALLOWED_METHODS = ['GET', 'POST', 'PATCH', 'DELETE'];
	private const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
	private const CACHE_DIR = __DIR__ . '/cache';
	private const CACHE_FILE = self::CACHE_DIR . '/routes.php';

	private array $routes = [];
	private array $routeCache = [];

	/**
	 * Get database connection instance
	 * @return PDO
	 */
	public static function getDb(): PDO
	{
		return self::$db;
	}

	/**
	 * Constructor that initializes the API
	 */
	public function __construct()
	{
		$this->setSecurityHeaders();
		$this->initializeCache();
		self::$db = (new Database())->init();

		// Get the request URI and method
		$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
		$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
		
		// Remove script name from request URI to get the path
		$path = str_replace($scriptName, '', $requestUri);
		$path = trim($path, '/');
		
		$httpVerb = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'CLI';

		if (!in_array($httpVerb, self::ALLOWED_METHODS)) {
			$this->sendResponse(['error' => ['code' => 405, 'message' => 'Method not allowed']], 405);
			return;
		}

		$this->initializeRoutes();
		$this->handleRequest($path, $httpVerb);
	}

	/**
	 * Initialize the routes cache directory
	 */
	private function initializeCache(): void
	{
		if (!is_dir(self::CACHE_DIR)) {
			mkdir(self::CACHE_DIR, 0755, true);
		}
	}

	/**
	 * Initialize the routes
	 */
	private function initializeRoutes(): void
	{
		if (file_exists(self::CACHE_FILE)) {
			$this->routeCache = require self::CACHE_FILE;
		}

		$this->routes = [
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

		$this->cacheRoutes();
	}

	/**
	 * Cache the routes for better performance
	 */
	private function cacheRoutes(): void
	{
		if ($this->routeCache !== $this->routes) {
			file_put_contents(
				self::CACHE_FILE,
				'<?php return ' . var_export($this->routes, true) . ';'
			);
		}
	}

	/**
	 * Handle the incoming request
	 * @param string $path
	 * @param string $httpVerb
	 */
	private function handleRequest(string $path, string $httpVerb): void
	{
		if ($path === 'swagger.json') {
			$this->sendSwaggerDocumentation();
			return;
		}

		if (empty($path)) {
			$this->sendResponse($this->getWelcomeMessage());
			return;
		}

		try {
			$route = $this->findRoute($path, $httpVerb);
			if (!$route) {
				$this->sendResponse(['error' => ['code' => 404, 'message' => 'Route not found']], 404);
				return;
			}

			$response = $this->executeRoute($route, $path, $httpVerb);
			$this->sendResponse($response);
		} catch (Exception $e) {
			$this->sendResponse([
				'error' => [
					'code' => 500,
					'message' => $e->getMessage(),
					'trace' => $e->getTraceAsString()
				]
			], 500);
		}
	}

	/**
	 * Find a matching route for the given path and HTTP verb
	 * @param string $path
	 * @param string $httpVerb
	 * @return array|null
	 */
	private function findRoute(string $path, string $httpVerb): ?array
	{
		$wildcards = [
			':any' => '[^/]+',
			':num' => '[0-9]+',
			':alpha' => '[a-zA-Z]+',
			':alnum' => '[a-zA-Z0-9]+'
		];

		foreach ($this->routes as $pattern => $route) {
			$pattern = str_replace(array_keys($wildcards), array_values($wildcards), $pattern);
			if (preg_match('#^'.$pattern.'$#i', "{$httpVerb} {$path}", $matches)) {
				return array_merge($route, ['matches' => $matches]);
			}
		}

		return null;
	}

	/**
	 * Execute the route handler
	 * @param array $route
	 * @param string $path
	 * @param string $httpVerb
	 * @return array
	 * @throws Exception
	 */
	private function executeRoute(array $route, string $path, string $httpVerb): array
	{
		$params = [];
		$matches = $route['matches'];
		array_shift($matches);

		switch ($httpVerb) {
			case 'GET':
				if (!empty($matches)) {
					$params = [(int)$matches[0]];
				}
				break;
			case 'POST':
				$data = $this->getRequestBody();
				$params = [new $route['bodyType']($data)];
				break;
			case 'PATCH':
				$data = $this->getRequestBody();
				$params = [(int)$matches[0], new $route['bodyType']($data)];
				break;
			case 'DELETE':
				$params = [(int)$matches[0]];
				break;
		}

		return call_user_func_array([new $route['class'], $route['method']], $params);
	}

	/**
	 * Get the welcome message for the root path
	 * @return array
	 */
	private function getWelcomeMessage(): array
	{
		$endpoints = [];
		foreach ($this->routes as $pattern => $route) {
			$method = strtoupper(explode(' ', $pattern)[0]);
			$path = '/constructionStages' . (strpos($pattern, '(:num)') !== false ? '/{id}' : '');
			$endpoints[$method . ' ' . $path] = $route['description'];
		}

		return [
			'message' => 'Welcome to the Construction Stages API',
			'version' => '1.0.0',
			'endpoints' => $endpoints,
			'documentation' => 'See docs/api.md for detailed API documentation',
			'swagger' => 'See /swagger.json for OpenAPI/Swagger documentation'
		];
	}

	/**
	 * Set security headers for the API
	 */
	private function setSecurityHeaders(): void
	{
		header('Content-Type: application/json; charset=utf-8');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header('X-XSS-Protection: 1; mode=block');
		header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
	}

	/**
	 * Get and validate the request body
	 * @return object
	 * @throws Exception
	 */
	private function getRequestBody(): object
	{
		$input = file_get_contents('php://input');
		if (empty($input)) {
			throw new Exception('Request body is empty');
		}

		$data = json_decode($input);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception('Invalid JSON in request body');
		}

		return $data;
	}

	/**
	 * Send the response with appropriate HTTP status code
	 * @param array $response
	 * @param int $statusCode
	 */
	private function sendResponse(array $response, int $statusCode = 200): void
	{
		http_response_code($statusCode);
		echo json_encode($response, self::JSON_OPTIONS);
	}

	/**
	 * Get all registered routes
	 * @return array
	 */
	public function getRoutes(): array
	{
		return $this->routes;
	}

	/**
	 * Send Swagger documentation
	 */
	private function sendSwaggerDocumentation(): void
	{
		$generator = new SwaggerGenerator([
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
		]);
		$swagger = $generator->generate();
		
		header('Content-Type: application/json');
		echo json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
}

new Api();