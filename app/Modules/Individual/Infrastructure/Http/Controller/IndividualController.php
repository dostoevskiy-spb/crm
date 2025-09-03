<?php

declare(strict_types=1);

namespace App\Modules\Individual\Infrastructure\Http\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateIndividualRequest;
use App\Modules\Individual\Application\Command\CreateIndividualCommand;
use App\Modules\Individual\Application\DTO\CreateIndividualDTO;
use App\Modules\Individual\Application\Handler\CreateIndividualHandler;
use App\Modules\Individual\Application\Handler\GetIndividualHandler;
use App\Modules\Individual\Application\Handler\GetIndividualsHandler;
use App\Modules\Individual\Application\Query\GetIndividualQuery;
use App\Modules\Individual\Application\Query\GetIndividualsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndividualController extends Controller
{
    public function store(
        CreateIndividualRequest $request,
        CreateIndividualHandler $createHandler,
        GetIndividualHandler $getHandler
    ): JsonResponse {
        try {
            $data = $request->validated();
            $dto = new CreateIndividualDTO(
                firstName: (string) $data['first_name'],
                lastName: (string) $data['last_name'],
                middleName: (string) $data['middle_name'],
                status: (string) $data['status'],
                positionId: $data['position_id'] ?? null,
                login: $data['login'] ?? null,
                isCompanyEmployee: (bool) ($data['is_company_employee'] ?? false),
                creatorUid: $data['creator_uid'] ?? null,
            );

            $uid = $createHandler(new CreateIndividualCommand($dto));
            $result = $getHandler(new GetIndividualQuery($uid));

            return response()->json([
                'status' => 'success',
                'message' => 'Физическое лицо успешно создано',
                'data' => $result,
            ], 201, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании физического лица',
            ], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function show(string $uid, GetIndividualHandler $handler): JsonResponse
    {
        $result = $handler(new GetIndividualQuery($uid));

        if (! $result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Физическое лицо не найдено',
            ], 404, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function index(Request $request, GetIndividualsHandler $handler): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'status' => $request->query('status') ? (string) $request->query('status') : null,
            'is_company_employee' => $request->query('is_company_employee') !== null
                ? filter_var($request->query('is_company_employee'), FILTER_VALIDATE_BOOLEAN)
                : null,
        ], fn ($value) => $value !== null);

        $data = $handler(new GetIndividualsQuery($filters));

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
}
