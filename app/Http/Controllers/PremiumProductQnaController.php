<?php

namespace App\Http\Controllers;

use App\Models\PremiumProductQna;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumProductQnaController extends Controller
{
    /**
     * Display a listing of Q&As for a product (Public)
     */
    public function index($productId): JsonResponse
    {
        try {
            $qnas = PremiumProductQna::where('premium_product_id', $productId)
                ->orderBy('sort_order', 'asc')
                ->get();

            return $this->responseSuccess('Get Data Successfully', $qnas, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created Q&A (Admin Only)
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string',
                'answer' => 'required|string',
                'sort_order' => 'nullable|integer',
            ]);

            $validated['premium_product_id'] = $productId;
            $data = PremiumProductQna::create($validated);

            return $this->responseSuccess('Create Data Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified Q&A (Admin Only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $qna = PremiumProductQna::findOrFail($id);

            $validated = $request->validate([
                'question' => 'sometimes|string',
                'answer' => 'sometimes|string',
                'sort_order' => 'nullable|integer',
            ]);

            $qna->update($validated);

            return $this->responseSuccess('Update Data Successfully', $qna, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified Q&A (Admin Only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $qna = PremiumProductQna::findOrFail($id);
            $qna->delete();

            return $this->responseSuccess('Delete Data Successfully', $qna, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }
}