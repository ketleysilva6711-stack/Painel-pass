<?php
// l5-swagger.php - Configuração do Swagger/OpenAPI
# Local: backend/config/l5-swagger.php

return [

    /*
    |--------------------------------------------------------------------------
    | Swagger UI Configuration
    |--------------------------------------------------------------------------
    */

    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
    'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
    'proxy' => env('L5_SWAGGER_PROXY', false),

    'routes' => [
        'api' => 'api/documentation',
        'docs' => 'docs',
        'oauth2_callback' => 'api/oauth2-callback',
        'middleware' => [
            'api' => ['api'],
            'asset' => [],
            'docs' => ['web'],
            'oauth2_callback' => ['api'],
        ],
        'prefix' => 'api',
    ],

    'paths' => [
        'base' => base_path(),
        'docs' => storage_path('api-docs'),
        'annotations' => app_path(),
        'base_path' => env('L5_SWAGGER_BASE_PATH', '/api'),
        'excludes' => [
            'vendor',
            'node_modules',
            'storage',
            'bootstrap/cache',
            'tests',
        ],
    ],

    'scan' => [
        'paths' => [
            app_path('Http/Controllers'),
            app_path('Models'),
        ],
        'include' => ['*.php'],
        'exclude' => [
            '*/Tests/*',
            '*/stubs/*',
            '*/stubs/**/*',
            '*/vendor/*',
        ],
    ],

    'securityDefinitions' => [
        'sanctum' => [
            'type' => 'apiKey',
            'in' => 'header',
            'name' => 'Authorization',
            'description' => 'Bearer token authentication. Format: Bearer {token}',
        ],
        'totp' => [
            'type' => 'apiKey',
            'in' => 'header',
            'name' => 'X-TOTP-Code',
            'description' => 'TOTP code for 2FA. Required for admin/revendedor routes.',
        ],
    ],

    'security' => [
        'sanctum' => [],
    ],

    'info' => [
        'title' => 'The Vault Panel API',
        'description' => 'API documentation for The Vault Panel v2.0 - Admin panel for managing APKs, bypass rules, QR codes, and more.',
        'version' => '2.0.0',
        'termsOfService' => 'https://seu-dominio.com/terms',
        'contact' => [
            'name' => 'The Vault Team',
            'email' => 'support@seu-dominio.com',
            'url' => 'https://seu-dominio.com',
        ],
        'license' => [
            'name' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT',
        ],
    ],

    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost/api'),
            'description' => 'Development server',
        ],
        [
            'url' => 'https://seu-dominio.com/api',
            'description' => 'Production server',
        ],
    ],

    'components' => [
        'schemas' => [
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'name' => ['type' => 'string', 'example' => 'Admin Vault'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'admin@thevault.com'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'revendedor', 'user'], 'example' => 'admin'],
                    'roleLabel' => ['type' => 'string', 'example' => 'Admin'],
                    'eyebrow' => ['type' => 'string', 'example' => 'Gestão'],
                    'initials' => ['type' => 'string', 'example' => 'AD'],
                    'funcionalidades' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'contaStatus' => ['type' => 'string', 'example' => 'active'],
                    'contaStatusLabel' => ['type' => 'string', 'example' => 'Acesso total'],
                    'contaStatusKind' => ['type' => 'string', 'enum' => ['ok', 'warn', 'bad'], 'example' => 'ok'],
                    'contaExpiraEm' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'totpEnabled' => ['type' => 'boolean', 'example' => false],
                ],
            ],
            'AuthResponse' => [
                'type' => 'object',
                'properties' => [
                    'access_token' => ['type' => 'string', 'example' => '1|abc123...'],
                    'token_type' => ['type' => 'string', 'example' => 'Bearer'],
                    'user' => ['$ref' => '#/components/schemas/User'],
                ],
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string', 'example' => 'Usuário ou senha inválidos.'],
                ],
            ],
            'PlayFakeApp' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'developer' => ['type' => 'string', 'nullable' => true],
                    'package_name' => ['type' => 'string', 'nullable' => true],
                    'slug' => ['type' => 'string'],
                    'apk_url' => ['type' => 'string', 'nullable' => true],
                    'file_path' => ['type' => 'string', 'nullable' => true],
                    'icon_path' => ['type' => 'string', 'nullable' => true],
                    'file_size' => ['type' => 'integer', 'nullable' => true],
                    'downloads' => ['type' => 'integer', 'example' => 0],
                    'status' => ['type' => 'string', 'enum' => ['active', 'inactive', 'pending']],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'BypassRule' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'nullable' => true],
                    'target_package' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'processing', 'completed', 'failed']],
                    'progress' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                    'phase' => ['type' => 'string', 'enum' => ['decompile', 'patch', 'recompile', 'sign', 'completed']],
                    'log' => ['type' => 'string', 'nullable' => true],
                    'output_path' => ['type' => 'string', 'nullable' => true],
                    'apk_filename' => ['type' => 'string', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'Consulta' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'user_id' => ['type' => 'integer'],
                    'tipo' => ['type' => 'string', 'example' => 'CPF/CNPJ'],
                    'documento' => ['type' => 'string'],
                    'resultado' => ['type' => 'object', 'nullable' => true],
                    'ip' => ['type' => 'string', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'Ranking' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'user_id' => ['type' => 'integer'],
                    'points' => ['type' => 'integer'],
                    'position' => ['type' => 'integer'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                    'user' => ['$ref' => '#/components/schemas/User'],
                ],
            ],
            'PlayFakeAppPaginated' => [
                'type' => 'object',
                'properties' => [
                    'current_page' => ['type' => 'integer'],
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/PlayFakeApp']],
                    'first_page_url' => ['type' => 'string'],
                    'from' => ['type' => 'integer'],
                    'last_page' => ['type' => 'integer'],
                    'last_page_url' => ['type' => 'string'],
                    'links' => ['type' => 'array'],
                    'next_page_url' => ['type' => 'string', 'nullable' => true],
                    'path' => ['type' => 'string'],
                    'per_page' => ['type' => 'integer'],
                    'prev_page_url' => ['type' => 'string', 'nullable' => true],
                    'to' => ['type' => 'integer'],
                    'total' => ['type' => 'integer'],
                ],
            ],
        ],
        'parameters' => [
            'page' => [
                'name' => 'page',
                'in' => 'query',
                'description' => 'Page number',
                'required' => false,
                'schema' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
            ],
            'per_page' => [
                'name' => 'per_page',
                'in' => 'query',
                'description' => 'Items per page',
                'required' => false,
                'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 15],
            ],
        ],
        'responses' => [
            'Unauthorized' => [
                'description' => 'Unauthorized',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        'example' => [
                            'message' => 'Unauthenticated.'
                        ]
                    ]
                ]
            ],
            'Forbidden' => [
                'description' => 'Forbidden',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        'example' => [
                            'message' => 'Acesso negado. Requer privilégios de Administrador.'
                        ]
                    ]
                ]
            ],
            'ValidationError' => [
                'description' => 'Validation Error',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => ['type' => 'string', 'example' => 'The given data was invalid.'],
                                'errors' => [
                                    'type' => 'object',
                                    'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]
                                ]
                            ]
                        ],
                        'example' => [
                            'message' => 'The given data was invalid.',
                            'errors' => [
                                'email' => ['The email field is required.'],
                                'password' => ['The password field is required.']
                            ]
                        ]
                    ]
                ]
            ],
            'NotFound' => [
                'description' => 'Not Found',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        'example' => ['message' => 'No query results for model.']
                    ]
                ]
            ],
            'RateLimited' => [
                'description' => 'Too Many Requests',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        'example' => ['message' => 'Too many requests. Please try again later.']
                    ]
                ]
            ],
        ],
    ],

    'tags' => [
        ['name' => 'Authentication', 'description' => 'Authentication and 2FA endpoints'],
        ['name' => 'Play Fake', 'description' => 'APK management and public landing pages'],
        ['name' => 'QR Code', 'description' => 'QR code generation and redirection'],
        ['name' => 'Bypass', 'description' => 'Bypass rules and APK processing pipeline'],
        ['name' => 'Inject Bancos', 'description' => 'Bank injection templates'],
        ['name' => 'Templates', 'description' => 'HTML template gallery and downloads'],
        ['name' => 'Consulta', 'description' => 'Document consultation'],
        ['name' => 'Ranking', 'description' => 'User ranking and podium'],
        ['name' => 'Support', 'description' => 'Support tickets'],
        ['name' => 'Admin', 'description' => 'Admin panel (requires admin role + 2FA)'],
    ],

    'authenticators' => [
        'sanctum' => \L5Swagger\Http\Middleware\Authenticate::class,
    ],
];