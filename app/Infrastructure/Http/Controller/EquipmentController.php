<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Equipment\Handler\GetEquipmentHandler;
use App\Application\Equipment\Handler\GetEquipmentsHandler;
use App\Application\Equipment\Query\GetEquipmentQuery;
use App\Application\Equipment\Query\GetEquipmentsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class EquipmentController extends Controller
{
    public function show(string $uid, GetEquipmentHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetEquipmentQuery($uid));

            if (!$result) {
                return response()->json(['error' => 'Equipment not found'], 404);
            }

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function index(Request $request, GetEquipmentsHandler $handler): JsonResponse
    {
        try {
            $filters = $request->only(['uid','name','status','transportUid','warehouse','issuedToUid']);
            $result = $handler(new GetEquipmentsQuery($filters));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
