<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FuncionarioFeedback;
use Illuminate\Http\Request;

class FuncionarioFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $operadorId = (int) ($user->operador_id ?: 1);

        $feedbacks = FuncionarioFeedback::query()
            ->with('funcionario:id,name,email')
            ->where('operador_id', $operadorId)
            ->latest('id')
            ->paginate(20);

        return view('feedbacks.index', compact('feedbacks'));
    }

    public function show(Request $request, int $feedback)
    {
        $user = $request->user();
        $operadorId = (int) ($user->operador_id ?: 1);

        $feedbackModel = FuncionarioFeedback::query()
            ->with('funcionario:id,name,email')
            ->where('operador_id', $operadorId)
            ->findOrFail($feedback);

        if ($feedbackModel->status === 'novo') {
            $feedbackModel->update(['status' => 'lido']);
        }

        return view('feedbacks.show', ['feedback' => $feedbackModel]);
    }
}
