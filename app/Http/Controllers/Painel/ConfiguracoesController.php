<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class ConfiguracoesController extends Controller
{
    public function __construct(private SettingsService $settings) {}

    public function index()
    {
        return view('painel.configuracoes.index');
    }

    public function ponto(Request $request)
    {
        $clienteId = null;

        $regras = $this->settings->get('ponto.regras', $clienteId, [
            'validar_sequencia' => true,
            'bloquear_duplicado' => true,
            'alertar_fora_janela' => true,
        ]);

        $tolerancia = $this->settings->get('ponto.tolerancia_padrao', $clienteId, [
            'entrada' => 5,
            'saida' => 5,
        ]);

        return view('painel.configuracoes.ponto', compact('regras', 'tolerancia'));
    }

    public function salvarPonto(Request $request)
    {
        $request->validate([
            'validar_sequencia' => 'nullable|boolean',
            'bloquear_duplicado' => 'nullable|boolean',
            'alertar_fora_janela' => 'nullable|boolean',
            'tolerancia_entrada' => 'required|integer|min:0|max:120',
            'tolerancia_saida' => 'required|integer|min:0|max:120',
        ]);

        $clienteId = null;

        $this->settings->set('ponto.regras', [
            'validar_sequencia' => (bool) $request->boolean('validar_sequencia'),
            'bloquear_duplicado' => (bool) $request->boolean('bloquear_duplicado'),
            'alertar_fora_janela' => (bool) $request->boolean('alertar_fora_janela'),
        ], $clienteId, auth()->id());

        $this->settings->set('ponto.tolerancia_padrao', [
            'entrada' => (int) $request->tolerancia_entrada,
            'saida' => (int) $request->tolerancia_saida,
        ], $clienteId, auth()->id());

        return redirect()
            ->route('painel.configuracoes.ponto')
            ->with('success', 'Configuracoes de ponto atualizadas com sucesso!');
    }

    public function jornadas()
    {
        $jornadas = $this->settings->get('ponto.jornadas', null, []);

        if (!is_array($jornadas)) {
            $jornadas = [];
        }

        return view('painel.configuracoes.jornadas', compact('jornadas'));
    }

    public function salvarJornadas(Request $request)
    {
        $data = $request->validate([
            'jornadas_json' => ['required', 'string'],
        ]);

        $decoded = json_decode($data['jornadas_json'], true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()
                ->withErrors(['jornadas_json' => 'JSON invalido.'])
                ->withInput();
        }

        $this->settings->set('ponto.jornadas', $decoded, null, auth()->id());

        return redirect()
            ->route('painel.configuracoes.jornadas')
            ->with('success', 'Jornadas salvas com sucesso!');
    }

    public function seedJornadasRafisa()
    {
        $seed = [
            [
                'id' => 'DIARISTA',
                'nome' => 'Diarista',
                'tipo' => 'DIARISTA',
                'dias_semana' => [1, 2, 3, 4, 5, 6],
                'tolerancia' => ['entrada' => 10, 'saida' => 10],
            ],
            [
                'id' => 'ADM',
                'nome' => 'Administrativo',
                'tipo' => 'FIXA',
                'entrada' => '08:00',
                'saida' => '17:48',
                'dias_semana' => [1, 2, 3, 4, 5],
                'tolerancia' => ['entrada' => 5, 'saida' => 5],
            ],
            [
                'id' => 'OPERACAO',
                'nome' => 'Operacao',
                'tipo' => 'FIXA_TURNOS',
                'dias_semana' => [1, 2, 3, 4, 5, 6],
                'turnos' => [
                    ['id' => 'T1', 'nome' => 'Turno 1', 'entrada' => '06:00', 'saida' => '14:20'],
                    ['id' => 'T2', 'nome' => 'Turno 2', 'entrada' => '14:20', 'saida' => '22:40'],
                    ['id' => 'T3', 'nome' => 'Turno 3', 'entrada' => '22:40', 'saida' => '06:00'],
                ],
                'tolerancia' => ['entrada' => 5, 'saida' => 5],
            ],
        ];

        $this->settings->set('ponto.jornadas', $seed, null, auth()->id());

        return redirect()
            ->route('painel.configuracoes.jornadas')
            ->with('success', 'Jornadas padrao carregadas com sucesso!');
    }
}