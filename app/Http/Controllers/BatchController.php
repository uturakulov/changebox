<?php

namespace App\Http\Controllers;

use App\Services\BatchService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Название API",
 *     version="1.0.0",
 *     description="Описание вашего API",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Локальный сервер"
 * )
 */
class BatchController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/batches",
     *     summary="Создание нового батча",
     *     tags={"Batches"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="batch_size", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Батч успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Батч успешно создан")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Нет доступных транзакций",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Нет доступных транзакций для создания батча")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $batchSize = $request->input('batch_size', SettingsService::getBatchSize());

        try {
            BatchService::createBatches($batchSize);

            return response()->json([
                'success' => true,
                'message' => 'Батч успешно создан'
            ], 201);

        } catch (\Exception $e) {
            Log::error("Ошибка создания батча: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Нет доступных транзакций для создания батча'
            ], 422);
        }
    }
}
