<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Product\Command\CreateProductCommand;
use App\Application\Product\DTO\CreateProductDTO;
use App\Application\Product\Handler\CreateProductHandler;
use App\Application\Product\Handler\GetProductHandler;
use App\Application\Product\Handler\GetProductsHandler;
use App\Application\Product\Query\GetProductQuery;
use App\Application\Product\Query\GetProductsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ProductController extends Controller
{
    public function store(Request $request, CreateProductHandler $handler): JsonResponse
    {
        try {
            $dto = new CreateProductDTO(
                name: (string) $request->input('name'),
                status: (string) $request->input('status', 'active'),
                type: (string) $request->input('type'),
                unit: (string) $request->input('unit'),
                sku: (string) $request->input('sku'),
                groupName: $request->input('groupName'),
                subgroupName: $request->input('subgroupName'),
                code1c: $request->input('code1c'),
                salePrice: $request->input('salePrice'),
                creatorUid: $request->input('creatorUid')
            );

            $uid = $handler(new CreateProductCommand($dto));

            return response()->json(['uid' => $uid], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function show(string $uid, GetProductHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetProductQuery($uid));

            if (!$result) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function index(Request $request, GetProductsHandler $handler): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'status', 'type', 'unit', 'group', 'subgroup', 'code1c', 'sku']);
            $result = $handler(new GetProductsQuery($filters));

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
