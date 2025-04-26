<?php return array (
  'get constructionStages' => 
  array (
    'class' => 'ConstructionStages',
    'method' => 'getAll',
    'description' => 'Get all construction stages',
    'parameters' => 
    array (
    ),
    'response' => 
    array (
      'type' => 'array',
      'items' => 
      array (
        'type' => 'object',
        'properties' => 
        array (
          'id' => 
          array (
            'type' => 'integer',
          ),
          'name' => 
          array (
            'type' => 'string',
          ),
          'startDate' => 
          array (
            'type' => 'string',
            'format' => 'date-time',
          ),
          'endDate' => 
          array (
            'type' => 'string',
            'format' => 'date-time',
          ),
          'duration' => 
          array (
            'type' => 'number',
          ),
          'durationUnit' => 
          array (
            'type' => 'string',
            'enum' => 
            array (
              0 => 'HOURS',
              1 => 'DAYS',
              2 => 'WEEKS',
            ),
          ),
          'color' => 
          array (
            'type' => 'string',
            'pattern' => '^#([a-f0-9]{3}){1,2}$',
          ),
          'externalId' => 
          array (
            'type' => 'string',
          ),
          'status' => 
          array (
            'type' => 'string',
            'enum' => 
            array (
              0 => 'NEW',
              1 => 'PLANNED',
              2 => 'DELETED',
            ),
          ),
        ),
      ),
    ),
  ),
  'get constructionStages/(:num)' => 
  array (
    'class' => 'ConstructionStages',
    'method' => 'getSingle',
    'description' => 'Get a specific construction stage',
    'parameters' => 
    array (
      'id' => 
      array (
        'type' => 'integer',
        'description' => 'The ID of the construction stage',
      ),
    ),
    'response' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'id' => 
        array (
          'type' => 'integer',
        ),
        'name' => 
        array (
          'type' => 'string',
        ),
        'startDate' => 
        array (
          'type' => 'string',
          'format' => 'date-time',
        ),
        'endDate' => 
        array (
          'type' => 'string',
          'format' => 'date-time',
        ),
        'duration' => 
        array (
          'type' => 'number',
        ),
        'durationUnit' => 
        array (
          'type' => 'string',
          'enum' => 
          array (
            0 => 'HOURS',
            1 => 'DAYS',
            2 => 'WEEKS',
          ),
        ),
        'color' => 
        array (
          'type' => 'string',
          'pattern' => '^#([a-f0-9]{3}){1,2}$',
        ),
        'externalId' => 
        array (
          'type' => 'string',
        ),
        'status' => 
        array (
          'type' => 'string',
          'enum' => 
          array (
            0 => 'NEW',
            1 => 'PLANNED',
            2 => 'DELETED',
          ),
        ),
      ),
    ),
  ),
  'post constructionStages' => 
  array (
    'class' => 'ConstructionStages',
    'method' => 'post',
    'bodyType' => 'ConstructionStagesCreate',
    'description' => 'Create a new construction stage',
    'request' => 
    array (
      'type' => 'object',
      'required' => 
      array (
        0 => 'name',
        1 => 'startDate',
      ),
      'properties' => 
      array (
        'name' => 
        array (
          'type' => 'string',
          'maxLength' => 255,
        ),
        'startDate' => 
        array (
          'type' => 'string',
          'format' => 'date-time',
        ),
        'endDate' => 
        array (
          'type' => 'string',
          'format' => 'date-time',
        ),
        'durationUnit' => 
        array (
          'type' => 'string',
          'enum' => 
          array (
            0 => 'HOURS',
            1 => 'DAYS',
            2 => 'WEEKS',
          ),
        ),
        'color' => 
        array (
          'type' => 'string',
          'pattern' => '^#([a-f0-9]{3}){1,2}$',
        ),
        'externalId' => 
        array (
          'type' => 'string',
          'maxLength' => 255,
        ),
        'status' => 
        array (
          'type' => 'string',
          'enum' => 
          array (
            0 => 'NEW',
            1 => 'PLANNED',
            2 => 'DELETED',
          ),
        ),
      ),
    ),
  ),
  'patch constructionStages/(:num)' => 
  array (
    'class' => 'ConstructionStages',
    'method' => 'update',
    'bodyType' => 'ConstructionStagesUpdate',
    'description' => 'Update a construction stage',
    'parameters' => 
    array (
      'id' => 
      array (
        'type' => 'integer',
        'description' => 'The ID of the construction stage',
      ),
    ),
    'request' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'name' => 
        array (
          'type' => 'string',
          'maxLength' => 255,
        ),
        'startDate' => 
        array (
          'type' => 'string',
          'format' => 'date-time',
        ),
        'endDate' => 
        array (
          'type' => 'string',
          'format' => 'date-time',
        ),
        'durationUnit' => 
        array (
          'type' => 'string',
          'enum' => 
          array (
            0 => 'HOURS',
            1 => 'DAYS',
            2 => 'WEEKS',
          ),
        ),
        'color' => 
        array (
          'type' => 'string',
          'pattern' => '^#([a-f0-9]{3}){1,2}$',
        ),
        'externalId' => 
        array (
          'type' => 'string',
          'maxLength' => 255,
        ),
        'status' => 
        array (
          'type' => 'string',
          'enum' => 
          array (
            0 => 'NEW',
            1 => 'PLANNED',
            2 => 'DELETED',
          ),
        ),
      ),
    ),
  ),
  'delete constructionStages/(:num)' => 
  array (
    'class' => 'ConstructionStages',
    'method' => 'delete',
    'description' => 'Delete a construction stage',
    'parameters' => 
    array (
      'id' => 
      array (
        'type' => 'integer',
        'description' => 'The ID of the construction stage',
      ),
    ),
    'response' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'success' => 
        array (
          'type' => 'object',
          'properties' => 
          array (
            'code' => 
            array (
              'type' => 'integer',
            ),
            'message' => 
            array (
              'type' => 'string',
            ),
          ),
        ),
      ),
    ),
  ),
);