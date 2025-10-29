<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterLiveLearningRequest;
use App\Models\LiveLearning;
use App\Models\LiveLearningRegistration;
use Illuminate\Http\JsonResponse;

class LiveLearningRegistrationController extends Controller
{
    /**
     * Register user to live learning
     * POST /api/live-learnings/{id}/register
     */
    public function store(RegisterLiveLearningRequest $request, $liveLearningId): JsonResponse
    {
        try {
            // 1. Find live learning
            $liveLearning = LiveLearning::where('id', $liveLearningId)
                ->published()
                ->withCount('registrations')
                ->first();

            if (!$liveLearning) {
                return response()->json([
                    'meta' => [
                        'message' => 'Live Learning tidak ditemukan atau belum dipublikasikan',
                        'code' => 404,
                    ],
                ], 404);
            }

            // 2. Check if registration is still open
            if (!$liveLearning->isRegistrationOpen()) {
                return response()->json([
                    'meta' => [
                        'message' => 'Pendaftaran sudah ditutup atau kuota peserta sudah penuh',
                        'code' => 400,
                    ],
                ], 400);
            }

            // 3. Check duplicate registration (same email)
            if ($liveLearning->isEmailRegistered($request->email)) {
                return response()->json([
                    'meta' => [
                        'message' => 'Email ini sudah terdaftar di Live Learning ini',
                        'code' => 409,
                    ],
                ], 409);
            }

            // 4. Create registration
            $registration = LiveLearningRegistration::create([
                'live_learning_id' => $liveLearning->id,
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'registered_at' => now(),
            ]);

            // 5. Return success response with community group link
            return response()->json([
                'meta' => [
                    'message' => 'Selamat! Kamu telah berhasil bergabung di Live Learning ini',
                    'code' => 201,
                ],
                'data' => [
                    'registration_id' => $registration->id,
                    'live_learning' => [
                        'id' => $liveLearning->id,
                        'title' => $liveLearning->title,
                        'slug' => $liveLearning->slug,
                        'schedule' => $liveLearning->schedule,
                    ],
                    'community_group_link' => $liveLearning->community_group_link,
                    'registered_at' => $registration->registered_at->toISOString(),
                ],
            ], 201);

        } catch (\Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}