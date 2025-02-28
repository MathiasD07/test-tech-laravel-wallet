<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateRecurringTransfer;
use App\Exceptions\InsufficientBalance;
use App\Http\Requests\Api\V1\CreateRecurringTransferRequest;
use App\Http\Resources\RecurringTransferResource;
use App\Models\RecurringTransfer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecurringTransferController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $recurringTransfers = $request->user()->recurringTransfers()->latest()->get();

        return RecurringTransferResource::collection($recurringTransfers);
    }

    public function store(CreateRecurringTransferRequest $request, CreateRecurringTransfer $createRecurringTransfer): JsonResponse {
        $recurringTransfer = $createRecurringTransfer->execute(
            $request->user(),
            $request->input('recipient_email'),
            $request->input('amount'),
            $request->input('reason'),
            Carbon::parse($request->input('start_date')),
            Carbon::parse($request->input('end_date')),
            $request->input('frequency_days')
        );

        return response()->json([
            'data' => new RecurringTransferResource($recurringTransfer),
        ], 201);
    }

    public function destroy(Request $request, RecurringTransfer $recurringTransfer): JsonResponse
    {
        // TODO : J'aurai préféré faire une policy mais manque de temps..
        if ($request->user()->id !== $recurringTransfer->user_id) {
            return response()->json([
                'message' => 'You cannot delete this recurring transfer',
            ], 403);
        }

        $recurringTransfer->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
