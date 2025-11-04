<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Services\RAB\CalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CalculationController extends Controller
{
    protected $calculationService;

    public function __construct(CalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    public function projectTotals($projectId): JsonResponse
    {
        try {
            $totals = $this->calculationService->calculateProjectTotals($projectId);

            return $this->responseSuccess('Calculate Successfully', $totals, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function projectSummary($projectId): JsonResponse
    {
        try {
            $summary = $this->calculationService->getProjectSummary($projectId);

            return $this->responseSuccess('Get Summary Successfully', $summary, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function ahspBreakdown($projectAhspId): JsonResponse
    {
        try {
            $breakdown = $this->calculationService->getAhspBreakdown($projectAhspId);

            return $this->responseSuccess('Get Breakdown Successfully', $breakdown, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function categoryTotal($categoryId): JsonResponse
    {
        try {
            $total = $this->calculationService->getCategoryTotal($categoryId);

            return $this->responseSuccess('Calculate Successfully', [
                'total' => $total,
                'formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function compareProjects(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_id_1' => 'required|exists:projects,id',
                'project_id_2' => 'required|exists:projects,id',
            ]);

            $comparison = $this->calculationService->compareProjects(
                $validated['project_id_1'],
                $validated['project_id_2']
            );

            return $this->responseSuccess('Compare Successfully', $comparison, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function recalculateProject($projectId): JsonResponse
    {
        try {
            $result = $this->calculationService->recalculateProject($projectId);

            return $this->responseSuccess('Recalculate Successfully', $result, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
