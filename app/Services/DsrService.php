<?php

namespace App\Services;

use App\Models\Dsr;
use App\Models\User;
use App\Mail\DSRSubmittedMail;
use App\Models\Department;
use App\Models\MissedDsr;
use App\Services\Interfaces\DsrServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class DsrService implements DsrServiceInterface
{
    public function create(array $data)
    {
        Log::info('Creating DSR Service', [
            'user_id' => Auth::id(),
            'work_date' => $data['work_date'],
        ]);

        $userId = Auth::id();
        $inputDate = Carbon::parse($data['work_date'])->toDateString();
        $today = now()->toDateString();

        // Only today DSR allowed
        $isApprovedRequest = MissedDsr::where('user_id', $userId)
            ->where('request_date', $inputDate)
            ->where('status', 'approved')
            ->exists();

        if ($inputDate !== $today && !$isApprovedRequest) {

            Log::warning('Invalid DSR Date Attempt', [
                'user_id' => $userId,
                'input_date' => $inputDate,
            ]);

            throw new \Exception(
                'You are not allowed to create DSR for this date.',
                400
            );
        }

        // Check existing submitted DSR
        $existingDsr = Dsr::where('user_id', $userId)
            ->where('work_date', $inputDate)
            ->first();

        if ($existingDsr?->status === 'submitted') {

            Log::warning('Duplicate Submitted DSR Attempt', [
                'user_id' => $userId,
                'work_date' => $inputDate,
            ]);

            throw new \Exception(
                'DSR already submitted for today. You cannot create again.',
                400
            );
        }

        // Create or update draft
        $dsr = Dsr::updateOrCreate(
            [
                'user_id' => $userId,
                'work_date' => $inputDate,
            ],
            [
                'data' => $data['data'],
                'status' => $data['status'] ?? 'draft',
                'approved_by' => $data['approved_by'] ?? null,
                'approved_at' => $data['approved_at'] ?? null,
            ]
        );

        Log::info('DSR Saved Successfully', [
            'dsr_id' => $dsr->id,
            'status' => $dsr->status,
        ]);
    }


    public function getAll()
    {
        return Dsr::with([
            'user:id,name,email',
        ])
            ->select([
                'id',
                'user_id',
                'work_date',
                'data',
                'status',
                'approved_by',
                'approved_at',
                'created_at',
                'updated_at'
            ])
            ->where('status', 'submitted')
            ->latest()
            ->get();
    }


    public function getById(int $id)
    {
        return Dsr::with([
            'user:id,name,email',
            'approver:id,name,email'
        ])
            ->select([
                'id',
                'user_id',
                'work_date',
                'data',
                'status',
                'approved_by',
                'approved_at',
                'created_at',
                'updated_at'
            ])
            ->findOrFail($id);
    }



    public function update(int $id, array $data)
    {
        $dsr = Dsr::findOrFail($id);

        $mentorName = $data['data']['meta']['mentor'] ?? null;
        $codeReviewerName = $data['data']['meta']['codeReviewer'] ?? null;

        // Convert names to IDs
        $mentorId = $mentorName ? User::where('name', $mentorName)->value('id')
            : $dsr->mentor_id;

        $codeReviewerId = $codeReviewerName ? User::where('name', $codeReviewerName)->value('id')
            : $dsr->code_reviewer_id;

        // Update DSR
        $dsr->update([
            'mentor_id' => $mentorId,
            'code_reviewer_id' => $codeReviewerId,
            'work_date' => $data['work_date'] ?? $dsr->work_date,
            'data' => $data['data'] ?? $dsr->data,
            'status' => $data['status'] ?? $dsr->status,
            'approved_by' => $data['approved_by'] ?? $dsr->approved_by,
            'approved_at' => $data['approved_at'] ?? $dsr->approved_at,
        ]);

        return $dsr->load([
            'user:id,name,email',
            'mentor:id,name,email',
            'codeReviewer:id,name,email',
            'approver:id,name,email'
        ]);
    }

    public function send(array $data): array
    {
        try {
            // Current logged-in user
            $sender = Auth::user();

            if (!$sender) {
                return [
                    'status' => false,
                    'message' => 'Unauthorized user.',
                    'status_code' => 401,
                ];
            }

            if (empty($sender->app_password)) {
                return [
                    'status' => false,
                    'message' => 'Email not configured. Please add app password.',
                    'status_code' => 400,
                ];
            }

            Log::info('Sending DSR', [
                'sender_id' => $sender->id,
                'sender_email' => $sender->email,
                'department_id' => $sender->department_id,
                'date' => $data['date'],
            ]);


            $htmlContent = $data['html'] ?? null;

            $dsr = Dsr::where('user_id', $sender->id)
                ->where('work_date', $data['date'])
                ->first();

            if (!$dsr) {
                return [
                    'status' => false,
                    'message' => 'DSR not found for this date.',
                    'status_code' => 404,
                ];
            }

            if ($dsr->status === 'submitted') {
                return [
                    'status' => false,
                    'message' => 'DSR already submitted for this date.',
                    'status_code' => 400,
                ];
            }

            $department = Department::find($sender->department_id);

            if (!$department || empty($department->receiver_email)) {

                Log::warning('No department receiver email found', [
                    'sender_id' => $sender->id,
                    'department_id' => $sender->department_id,
                ]);

                return [
                    'status' => false,
                    'message' => 'No receiver email configured for this department.',
                    'status_code' => 404,
                ];
            }

            $receiverEmail = $department->receiver_email;

            $smtpConfig = [
                'transport' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => $sender->email,
                'password' => Crypt::decryptString($sender->app_password),
                'timeout' => null,
            ];

            $mailer = app('mail.manager')->mailer('smtp');

            $mailer->setSymfonyTransport(
                app('mail.manager')->createSymfonyTransport($smtpConfig)
            );

            /**
             * 📤 Send Mail (Try user email first)
             */
            try {
                $mailer->to($receiverEmail)->send(
                    new DSRSubmittedMail($dsr, $htmlContent)
                );
            } catch (\Exception $mailException) {

                Log::warning('User SMTP failed, fallback to system mail', [
                    'user_id' => $sender->id,
                    'error' => $mailException->getMessage(),
                ]);

                // 🔁 Fallback to default mail (.env)
                Mail::to($receiverEmail)->send(
                    new DSRSubmittedMail($dsr, $htmlContent)
                );
            }

            $dsr->update([
                'status' => 'submitted',
            ]);

            return [
                'status' => true,
                'message' => 'DSR sent successfully.',
                'sender' => [
                    'id' => $sender->id,
                    'name' => $sender->name,
                    'email' => $sender->email,
                ],
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name,
                ],
                'receiver_email' => $receiverEmail,
                'dsr_id' => $dsr->id,
                'status_code' => 200,
            ];
        } catch (\Exception $e) {

            Log::error('DSR Send Failed', [
                'sender_id' => Auth::id(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return [
                'status' => false,
                'message' => 'Failed to send DSR.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ];
        }
    }
}
