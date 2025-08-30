<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'CRM API Documentation',
    description: 'API documentation for CRM system',
)]
#[OA\Server(
    url: 'http://localhost',
    description: 'Local development server'
)]
abstract class Controller
{
    //
}
