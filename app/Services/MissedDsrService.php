<?php

namespace App\Services;

use App\Models\Dsr;
use App\Models\MissedDsr;
use App\Services\Interfaces\MissedDsrInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MissedDsrService implements MissedDsrInterface
{

    public function store($userId, array $data)
    {
        Log::info('Missed DSR Store Function Started', [
            'user_id' => $userId,
            'data' => $data,
        ]);

        DB::beginTransaction();

        try {

            $requestDate = Carbon::parse(
                $data['request_date']
            )->format('Y-m-d');

            if ($requestDate >= now()->format('Y-m-d')) {

                return [
                    'status' => false,
                    'message' => 'Future date is not allowed',
                    'code' => 422,
                ];
            }

            $existingDsr = Dsr::where('user_id', $userId)
                ->whereDate('created_at', $requestDate)
                ->where('status', '!=', 'draft')
                ->exists();

            if ($existingDsr) {

                return [
                    'status' => false,
                    'message' => 'DSR already submitted for this date',
                    'code' => 422,
                ];
            }

            $existingRequest = MissedDsr::where(
                'user_id',
                $userId
            )
                ->where('request_date', $requestDate)
                ->where('status', 'pending')
                ->exists();

            if ($existingRequest) {

                return [
                    'status' => false,
                    'message' => 'Request already pending for this date',
                    'code' => 422,
                ];
            }

            $request = MissedDsr::create([
                'user_id' => $userId,
                'request_date' => $requestDate,
                'reason' => $data['reason'],
                'status' => 'pending',
            ]);

            Log::info('Missed DSR request created successfully', [
                'request_id' => $request->id,
                'request_data' => $request,
            ]);

            DB::commit();

            Log::info('Database transaction committed successfully');

            return [
                'status' => true,
                'message' => 'Missed DSR request submitted successfully',
                'data' => $request,
                'code' => 201,
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Missed DSR Request Error', [
                'user_id' => $userId,
                'request_data' => $data,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }


    public function getAllMissedDsrRequests()
    {
        Log::info('Fetching all missed DSR requests');

        try {

            $requests = MissedDsr::with('user')
                ->latest()
                ->get();

            return [
                'status' => true,
                'message' => 'Missed DSR requests fetched successfully',
                'data' => $requests,
                'code' => 200,
            ];
        } catch (\Exception $e) {

            Log::error('Error fetching missed DSR requests', [
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => 'Something went wrong',
                'code' => 500,
            ];
        }
    }


    public function updateStatus( $req, $id)
    {
        try {

            $request = MissedDsr::find($id);

            if (!$request) {

                return response()->json([
                    'status' => false,
                    'message' => 'Request not found',
                ], 404);
            }

            $request->update([
                'status' => $req->status,
                'approved_by' => $req->status === 'approved'
                    ? Auth::id()
                    : null,
                'approved_at' => $req->status === 'approved'
                    ? now()
                    : null,
                'rejection_reason' => $req->status === 'rejected'
                    ? $req->rejection_reason
                    : null,
            ]);

            Log::info('Missed DSR request status updated successfully', [
                'request_id' => $id,
                'updated_status' => $req->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Request status updated successfully',
            ], 200);
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


     public function getPendingNotifications(int $userId)
    {
        return MissedDsr::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('dsr')
                    ->whereColumn(
                        'dsr.user_id',
                        'missed_dsr_requests.user_id'
                    )
                    ->whereColumn(
                        'dsr.work_date',
                        'missed_dsr_requests.request_date'
                    );
            })
            ->orderBy('request_date', 'desc')
            ->get([
                'id',
                'request_date',
                'reason',
                'status',
                'approved_at',
            ]);
    }
}
