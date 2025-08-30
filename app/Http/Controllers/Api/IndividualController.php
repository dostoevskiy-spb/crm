<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Individual\Services\IndividualService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateIndividualRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Individuals', description: 'API для управления физическими лицами')]
class IndividualController extends Controller
{
    private IndividualService $individualService;

    public function __construct(IndividualService $individualService)
    {
        $this->individualService = $individualService;
    }

    #[OA\Post(
        path: '/api/individuals',
        summary: 'Создать физическое лицо',
        description: 'Создает новое физическое лицо в системе',
        tags: ['Individuals'],
        requestBody: new OA\RequestBody(
            description: 'Данные для создания физического лица',
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'middle_name', 'status_id'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', maxLength: 20, example: 'Иван'),
                    new OA\Property(property: 'last_name', type: 'string', maxLength: 20, example: 'Иванов'),
                    new OA\Property(property: 'middle_name', type: 'string', maxLength: 20, example: 'Иванович'),
                    new OA\Property(property: 'status_id', type: 'integer', example: 1),
                    new OA\Property(property: 'position_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'login', type: 'string', minLength: 6, nullable: true, example: 'ivan123'),
                    new OA\Property(property: 'is_company_employee', type: 'boolean', example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Физическое лицо успешно создано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Физическое лицо успешно создано'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'uid', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                                new OA\Property(property: 'first_name', type: 'string', example: 'Иван'),
                                new OA\Property(property: 'last_name', type: 'string', example: 'Иванов'),
                                new OA\Property(property: 'middle_name', type: 'string', example: 'Иванович'),
                                new OA\Property(property: 'full_name', type: 'string', example: 'Иванов Иван Иванович'),
                                new OA\Property(property: 'short_name', type: 'string', example: 'Иванов И.И.'),
                                new OA\Property(property: 'status_id', type: 'integer', example: 1),
                                new OA\Property(property: 'position_id', type: 'integer', nullable: true, example: 1),
                                new OA\Property(property: 'login', type: 'string', nullable: true, example: 'ivan123'),
                                new OA\Property(property: 'is_company_employee', type: 'boolean', example: false),
                                new OA\Property(property: 'creator_uid', type: 'string', format: 'uuid', nullable: true, example: '550e8400-e29b-41d4-a716-446655440111'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-08-08T13:47:00Z')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Ошибка валидации'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'first_name' => ['Имя обязательно для заполнения'],
                                'login' => ['Такой логин уже существует']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка создания',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Login already exists')
                    ]
                )
            )
        ]
    )]
    public function store(CreateIndividualRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            // TODO: получить creator_uid из контекста авторизации; пока опционально
            $data['creator_uid'] = $data['creator_uid'] ?? null;
            
            $individual = $this->individualService->create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Физическое лицо успешно создано',
                'data' => [
                    'uid' => $individual->uid()->value(),
                    'first_name' => $individual->getFirstName(),
                    'last_name' => $individual->getLastName(),
                    'middle_name' => $individual->getMiddleName(),
                    'full_name' => $individual->getFullName(),
                    'short_name' => $individual->getShortName(),
                    'status_id' => $individual->getStatusId(),
                    'position_id' => $individual->positionId(),
                    'login' => $individual->getLogin(),
                    'is_company_employee' => $individual->isCompanyEmployee(),
                    'creator_uid' => $individual->creatorUid()?->value(),
                    'created_at' => $individual->createdAt()->toISOString(),
                ]
            ], 201, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании физического лица'
            ], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }

    #[OA\Get(
        path: '/api/individuals/{uid}',
        summary: 'Получить физическое лицо',
        description: 'Получает данные физического лица по UID',
        tags: ['Individuals'],
        parameters: [
            new OA\Parameter(
                name: 'uid',
                in: 'path',
                required: true,
                description: 'UID физического лица',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные физического лица',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'uid', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                                new OA\Property(property: 'first_name', type: 'string', example: 'Иван'),
                                new OA\Property(property: 'last_name', type: 'string', example: 'Иванов'),
                                new OA\Property(property: 'middle_name', type: 'string', example: 'Иванович'),
                                new OA\Property(property: 'full_name', type: 'string', example: 'Иванов Иван Иванович'),
                                new OA\Property(property: 'short_name', type: 'string', example: 'Иванов И.И.'),
                                new OA\Property(property: 'status_id', type: 'integer', example: 1),
                                new OA\Property(property: 'position_id', type: 'integer', nullable: true, example: 1),
                                new OA\Property(property: 'login', type: 'string', nullable: true, example: 'ivan123'),
                                new OA\Property(property: 'is_company_employee', type: 'boolean', example: false),
                                new OA\Property(property: 'creator_uid', type: 'string', format: 'uuid', nullable: true, example: '550e8400-e29b-41d4-a716-446655440111'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-08-08T13:47:00Z')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Физическое лицо не найдено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Физическое лицо не найдено')
                    ]
                )
            )
        ]
    )]
    public function show(string $uid): JsonResponse
    {
        $individual = $this->individualService->findByUid($uid);

        if (!$individual) {
            return response()->json([
                'status' => 'error',
                'message' => 'Физическое лицо не найдено'
            ], 404, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'uid' => $individual->uid()->value(),
                'first_name' => $individual->getFirstName(),
                'last_name' => $individual->getLastName(),
                'middle_name' => $individual->getMiddleName(),
                'full_name' => $individual->getFullName(),
                'short_name' => $individual->getShortName(),
                'status_id' => $individual->getStatusId(),
                'position_id' => $individual->positionId(),
                'login' => $individual->getLogin(),
                'is_company_employee' => $individual->isCompanyEmployee(),
                'creator_uid' => $individual->creatorUid()?->value(),
                'created_at' => $individual->createdAt()->toISOString(),
            ]
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    #[OA\Get(
        path: '/api/individuals',
        summary: 'Получить список физических лиц',
        description: 'Получает список всех физических лиц с возможностью фильтрации',
        tags: ['Individuals'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Поиск по имени, фамилии или отчеству',
                schema: new OA\Schema(type: 'string', example: 'Иван')
            ),
            new OA\Parameter(
                name: 'status_id',
                in: 'query',
                required: false,
                description: 'Фильтр по статусу',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'is_company_employee',
                in: 'query',
                required: false,
                description: 'Фильтр по сотрудникам компании',
                schema: new OA\Schema(type: 'boolean', example: true)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список физических лиц',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'uid', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                                    new OA\Property(property: 'first_name', type: 'string', example: 'Иван'),
                                    new OA\Property(property: 'last_name', type: 'string', example: 'Иванов'),
                                    new OA\Property(property: 'middle_name', type: 'string', example: 'Иванович'),
                                    new OA\Property(property: 'full_name', type: 'string', example: 'Иванов Иван Иванович'),
                                    new OA\Property(property: 'short_name', type: 'string', example: 'Иванов И.И.'),
                                    new OA\Property(property: 'status_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'position_id', type: 'integer', nullable: true, example: 1),
                                    new OA\Property(property: 'login', type: 'string', nullable: true, example: 'ivan123'),
                                    new OA\Property(property: 'is_company_employee', type: 'boolean', example: false),
                                    new OA\Property(property: 'creator_uid', type: 'string', format: 'uuid', nullable: true, example: '550e8400-e29b-41d4-a716-446655440111'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-08-08T13:47:00Z')
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'status_id' => $request->query('status_id') ? (int) $request->query('status_id') : null,
            'is_company_employee' => $request->query('is_company_employee') !== null 
                ? filter_var($request->query('is_company_employee'), FILTER_VALIDATE_BOOLEAN) 
                : null,
        ], fn($value) => $value !== null);

        $individuals = empty($filters) 
            ? $this->individualService->findAll()
            : $this->individualService->findByFilters($filters);

        $data = array_map(function($individual) {
            return [
                'uid' => $individual->uid()->value(),
                'first_name' => $individual->getFirstName(),
                'last_name' => $individual->getLastName(),
                'middle_name' => $individual->getMiddleName(),
                'full_name' => $individual->getFullName(),
                'short_name' => $individual->getShortName(),
                'status_id' => $individual->getStatusId(),
                'position_id' => $individual->positionId(),
                'login' => $individual->getLogin(),
                'is_company_employee' => $individual->isCompanyEmployee(),
                'creator_uid' => $individual->creatorUid()?->value(),
                'created_at' => $individual->createdAt()->toISOString(),
            ];
        }, $individuals);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
}
