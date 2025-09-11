<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="ApiSMS Gateway",
 *     version="1.0.0",
 *     description="Modern SMS Gateway API for Kannel integration - Djibouti DPCR Fleet Management System",
 *     contact={
 *         "name": "DPCR Technical Team",
 *         "email": "tech@dpcr.dj"
 *     },
 *     license={
 *         "name": "Proprietary",
 *         "url": "https://dpcr.dj"
 *     }
 * )
 * 
 * @OA\Server(
 *     url="http://apisms.test",
 *     description="Development Server"
 * )
 * @OA\Server(
 *     url="https://sms-gateway.dj",
 *     description="Production Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="API Key",
 *     description="Enter your API key in the format: Bearer {api_key}"
 * )
 * 
 * @OA\Tag(
 *     name="SMS",
 *     description="SMS sending and management operations"
 * )
 * 
 * @OA\Tag(
 *     name="Statistics",
 *     description="SMS statistics and analytics"
 * )
 * 
 * @OA\Tag(
 *     name="Health",
 *     description="System health and monitoring"
 * )
 */
abstract class Controller
{
    //
}
