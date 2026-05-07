<?php

namespace App\Http\Controllers;

use App\Models\Dsr;
use App\Models\User;
use App\Services\Interfaces\DsrServiceInterface;
use BcMath\Number;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DSRController extends Controller
{

    public function __construct(
        protected DsrServiceInterface $dsrService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'work_date' => 'required|date',
            'data' => 'required|array',
        ]);

        Log::info("Creating DSR", $request->all());

        $dsr = $this->dsrService->create($request->all());

        return response()->json([
            'message' => 'DSR created successfully',
            'data' => $dsr
        ], 201);
    }


    public function show()
    {
        $dsrs = $this->dsrService->getAll();
        return response()->json([
            'success' => true,
            'message' => 'DSR list fetched successfully',
            'data' => $dsrs,
        ], 200);
    }


    public function index($id)
    {
        $dsr = $this->dsrService->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'DSR fetched successfully',
            'data' => $dsr,
        ], 200);
    }


    /**
     * Update DSR by ID
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'work_date' => 'required|date',
            'data' => 'required|array',
            'status' => 'nullable|string',
            'approved_by' => 'nullable|integer|exists:users,id',
            'approved_at' => 'nullable|date',
        ]);

        $dsr = $this->dsrService->update($id, $validatedData);

        return response()->json([
            'success' => true,
            'message' => 'DSR updated successfully',
            'data' => $dsr,
        ], 200);
    }


    public function submit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'html' => 'required|string',
            ]);

            $response = $this->dsrService->send($validated);
            Log::info($response);
            return response()->json([
                'success' => true,
                'message' => 'DSR submitted successfully',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            Log::error('DSR Submit Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit DSR',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
