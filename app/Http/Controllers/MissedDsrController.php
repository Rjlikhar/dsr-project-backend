<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\MissedDsrInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MissedDsrController extends Controller
{

    protected $missedDsr;

    public function __construct(
        MissedDsrInterface $missedDsr
    ) {
        $this->missedDsr = $missedDsr;
    }


    public function store(Request $request)
    {
        Log::info($request);

        $request->validate([
            'request_date' => 'required|date',
            'reason' => 'required|string|max:1000',
        ]);

        $response = $this->missedDsr->store(Auth::id(), $request->all());

        return response()->json($response, $response['code']);
    }



    public function getAllMissedDsrRequests()
    {
        Log::info('Fetching all missed DSR requests');

        try {
             if (Auth::user()->role_id !== 1) {

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 403);
            }

            $response = $this->missedDsr->getAllMissedDsrRequests();

            return response()->json([
                'status' => $response['status'],
                'message' => $response['message'],
                'data' => $response['data'] ?? null,
            ], $response['code']);
        } catch (\Exception $e) {

            Log::error('Error fetching missed DSR requests', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    // Controller

    public function updateStatus(Request $req, $id)
    {
        Log::info('Update missed DSR request status called', [
            'request_id' => $id,
            'status' => $req->status,
        ]);

        try {

            if (Auth::user()->role_id !== 1) {

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 403);
            }

            $response = $this->missedDsr->updateStatus($req, $id);

            return response()->json([
                'status' => $response['status'],
                'message' => $response['message'],
                'data' => $response['data'] ?? null,
            ], $response['code']);
        } catch (\Exception $e) {

            Log::error('Error updating missed DSR request status', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function notification()
    {
        try {

            $notifications = $this->missedDsr->getPendingNotifications(Auth::id());

            return response()->json([
                'status' => true,
                'message' => 'Notifications fetched successfully',
                'data' => $notifications,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
