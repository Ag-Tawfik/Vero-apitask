<?php
require_once 'Autoloader.php';
Autoloader::register();

/**
 * Main API class that handles routing and request processing
 */
class Api
{
	private static ?PDO $db = null;
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
		if (self::$db === null) {
			self::$db = (new Database())->init();
		}
		return self::$db;
	}

	/**
	 * Constructor that initializes the API
	 */
	public function __construct()
	{
		$this->setSecurityHeaders();
		$this->initializeCache();
		$this->initializeRoutes();

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
		try {
			if ($path === '') {
				$this->sendResponse($this->getWelcomeMessage());
				return;
			}

			if ($path === 'swagger.json') {
				$this->sendSwaggerDocumentation();
				return;
			}

			$route = $this->findRoute($path, $httpVerb);
			if (!$route) {
				$this->sendResponse(['error' => ['code' => 404, 'message' => 'Route not found']], 404);
				return;
			}

			$response = $this->executeRoute($route, $path, $httpVerb);
			$this->sendResponse($response);
		} catch (Exception $e) {
			$code = $e->getCode() ?: 500;
			$this->sendResponse(['error' => ['code' => $code, 'message' => $e->getMessage()]], $code);
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
		foreach ($this->routes as $pattern => $route) {
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
	 */
	private function executeRoute(array $route, string $path, string $httpVerb): array
	{
		$class = $route['class'];
		$method = $route['method'];
		$matches = $route['matches'];
		array_shift($matches);

		$instance = new $class(self::getDb());
		$params = [];

		if (!empty($matches)) {
			$params = [(int)$matches[0]];
		}

		if (in_array($httpVerb, ['POST', 'PATCH'])) {
			$data = $this->getRequestBody();
			if (isset($route['bodyType'])) {
				$bodyType = $route['bodyType'];
				$data = $bodyType::fromObject($data);
			}
			$params[] = $data;
		}

		return $instance->$method(...$params);
	}

	/**
	 * Get the welcome message
	 * @return array
	 */
	private function getWelcomeMessage(): array
	{
		return [
			'message' => 'Welcome to the Construction Stages API',
			'documentation' => '/swagger.json',
			'endpoints' => array_map(
				fn($route) => $route['description'],
				$this->routes
			)
		];
	}

	/**
	 * Set security headers
	 */
	private function setSecurityHeaders(): void
	{
		header('Content-Type: application/json; charset=UTF-8');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header('X-XSS-Protection: 1; mode=block');
	}

	/**
	 * Get the request body
	 * @return object
	 */
	private function getRequestBody(): object
	{
		$json = file_get_contents('php://input');
		if (empty($json)) {
			return (object)[];
		}

		$data = json_decode($json);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception('Invalid JSON in request body', 400);
		}

		return $data;
	}

	/**
	 * Send the response
	 * @param array $response
	 * @param int $statusCode
	 */
	private function sendResponse(array $response, int $statusCode = 200): void
	{
		http_response_code($statusCode);
		echo json_encode($response, self::JSON_OPTIONS);
	}

	/**
	 * Get all routes
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
		$generator = new SwaggerGenerator($this->routes);
		$this->sendResponse($generator->generate());
	}
}

// Initialize the API
new Api();