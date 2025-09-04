<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Infrastructure\Http\Controller;

use App\Modules\LegalEntity\Application\Command\CreateLegalEntityCommand;
use App\Modules\LegalEntity\Application\DTO\CreateLegalEntityDTO;
use App\Modules\LegalEntity\Application\Handler\CreateLegalEntityHandler;
use App\Modules\LegalEntity\Application\Handler\GetLegalEntitiesHandler;
use App\Modules\LegalEntity\Application\Handler\GetLegalEntityHandler;
use App\Modules\LegalEntity\Application\Query\GetLegalEntitiesQuery;
use App\Modules\LegalEntity\Application\Query\GetLegalEntityQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class LegalEntityController extends Controller
{
    public function store(Request $request, CreateLegalEntityHandler $handler): JsonResponse
    {
        try {
            $dto = new CreateLegalEntityDTO(
                shortName: (string) $request->input('shortName'),
                fullName: (string) $request->input('fullName'),
                ogrn: (string) $request->input('ogrn'),
                inn: (string) $request->input('inn'),
                kpp: (string) $request->input('kpp'),
                legalAddress: $request->input('legalAddress'),
                phoneNumber: $request->input('phoneNumber'),
                email: $request->input('email'),
                creatorUid: $request->input('creatorUid')
            );

            $uid = $handler(new CreateLegalEntityCommand($dto));

            return response()->json(['uid' => $uid], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function show(string $uid, GetLegalEntityHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetLegalEntityQuery($uid));

            if (! $result) {
                return response()->json(['error' => 'Legal entity not found'], 404);
            }

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function index(Request $request, GetLegalEntitiesHandler $handler): JsonResponse
    {
        try {
            $filters = $request->only(['shortName', 'inn', 'phoneNumber', 'email', 'curatorUid']);
            $result = $handler(new GetLegalEntitiesQuery($filters));

            return response()->json($result);
        } catch (\Exception $e) {
            die(var_dump($e->getMessage()));
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
