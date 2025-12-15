<?php

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     title="AlbumDB API",
 *     description="Simple REST API for managing albums, users, ratings, and reviews",
 *     version="1.0",
 *     @OA\Contact(
 *         email="tarik.hamzic@stu.ibu.edu.ba",
 *         name="Tarik Hamzic"
 *     )
 * ),
 * @OA\Server(
 *     url="http://localhost:8888/backend",
 *     description="Development server"
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */


class OpenApiSetup {}
