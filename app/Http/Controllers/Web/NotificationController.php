<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NotificationMvp;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function open(Request $request, int $notification)
    {
        $user = $request->user();
        $operadorId = (int) ($user->operador_id ?: 1);

        $item = NotificationMvp::query()
            ->where('operador_id', $operadorId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->findOrFail($notification);

        $item->users()->updateExistingPivot($user->id, ['read_at' => now()]);

        if ($item->type === 'FUNCIONARIO_FEEDBACK') {
            $feedbackId = (int) data_get($item->payload_json, 'feedback_id', data_get($item->payload_json, 'reference_id'));
            if ($feedbackId > 0) {
                if ($user->isMaster()) {
                    return redirect()->route('painel.feedbacks.show', $feedbackId);
                }

                return redirect()->route('tenant.feedbacks.show', $feedbackId);
            }
        }

        if ($user->isMaster()) {
            return redirect()->route('painel.dashboard');
        }

        return redirect()->route('tenant.home');
    }
}
