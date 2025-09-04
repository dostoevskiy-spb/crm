<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Http\Controller;

use App\Modules\User\Application\Command\CreateUserCommand;
use App\Modules\User\Application\DTO\CreateUserDTO;
use App\Modules\User\Application\Handler\CreateUserHandler;
use App\Modules\User\Application\Handler\GetUserHandler;
use App\Modules\User\Application\Handler\GetUsersHandler;
use App\Modules\User\Application\Query\GetUserQuery;
use App\Modules\User\Application\Query\GetUsersQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UserController extends Controller
{
    public function store(Request $request, CreateUserHandler $handler): JsonResponse
    {
        try {
            $email = (string) $request->input('email');
            $password = (string) $request->input('password');
            $status = (string) $request->input('status', 'active');

            if ($email === '' || $password === '') {
                return response()->json(['error' => 'Email and password are required'], 400);
            }

            $dto = new CreateUserDTO(
                email: $email,
                password: $password,
                status: $status,
            );

            $uid = $handler(new CreateUserCommand($dto));

            return response()->json(['uid' => $uid], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function show(string $uid, GetUserHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetUserQuery($uid));

            if (! $result) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function index(Request $request, GetUsersHandler $handler): JsonResponse
    {
        try {
            $filters = $request->only(['email', 'status']);
            $result = $handler(new GetUsersQuery($filters));

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
