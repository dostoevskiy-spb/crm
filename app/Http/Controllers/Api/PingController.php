<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Ping', description: 'Test endpoints for API connectivity')]
class PingController extends Controller
{
    #[OA\Get(
        path: '/api/ping',
        summary: 'Ping endpoint',
        description: 'Simple ping endpoint that returns pong with timestamp',
        tags: ['Ping'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful pong response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'pong'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2025-08-08T10:29:29.543305Z'),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                    ]
                )
            ),
        ]
    )]
    public function get(): JsonResponse
    {
        return response()->json([
            'message' => 'pong',
            'timestamp' => now()->toISOString(),
            'status' => 'success',
        ]);
    }

    #[OA\Post(
        path: '/api/ping',
        summary: 'Ping endpoint with data',
        description: 'Ping endpoint that accepts POST data and returns it along with pong response',
        tags: ['Ping'],
        requestBody: new OA\RequestBody(
            description: 'Optional JSON data to echo back',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'test', type: 'string', example: 'data'),
                    new OA\Property(property: 'custom_field', type: 'string', example: 'custom_value'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful pong response with received data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'pong'),
                        new OA\Property(
                            property: 'received_data',
                            type: 'object',
                            example: ['test' => 'data', 'custom_field' => 'custom_value']
                        ),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2025-08-08T10:30:33.919725Z'),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                    ]
                )
            ),
        ]
    )]
    public function post(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'pong',
            'received_data' => $request->all(),
            'timestamp' => now()->toISOString(),
            'status' => 'success',
        ]);
    }
}
