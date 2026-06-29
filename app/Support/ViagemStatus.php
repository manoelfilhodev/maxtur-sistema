<?php

namespace App\Support;

final class ViagemStatus
{
    public const SOLICITADA = 'solicitada';

    public const EM_ANALISE = 'em_analise';

    public const APROVADA = 'aprovada';

    public const PROGRAMADA = 'programada';

    public const CHECKLIST_PENDENTE = 'checklist_pendente';

    public const PRONTA_PARA_EXECUCAO = 'pronta_para_execucao';

    public const EM_ANDAMENTO = 'em_andamento';

    public const ATRASADA = 'atrasada';

    public const FINALIZADA = 'finalizada';

    public const CANCELADA = 'cancelada';

    public const BLOQUEADA = 'bloqueada';

    public static function all(): array
    {
        return [
            self::SOLICITADA,
            self::EM_ANALISE,
            self::APROVADA,
            self::PROGRAMADA,
            self::CHECKLIST_PENDENTE,
            self::PRONTA_PARA_EXECUCAO,
            self::EM_ANDAMENTO,
            self::ATRASADA,
            self::FINALIZADA,
            self::CANCELADA,
            self::BLOQUEADA,
        ];
    }

    public static function terminal(): array
    {
        return [self::FINALIZADA, self::CANCELADA, self::BLOQUEADA];
    }

    public static function labels(): array
    {
        return [
            self::SOLICITADA => 'Solicitada',
            self::EM_ANALISE => 'Em análise',
            self::APROVADA => 'Aprovada',
            self::PROGRAMADA => 'Programada',
            self::CHECKLIST_PENDENTE => 'Checklist pendente',
            self::PRONTA_PARA_EXECUCAO => 'Pronta para execução',
            self::EM_ANDAMENTO => 'Em andamento',
            self::ATRASADA => 'Atrasada',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
            self::BLOQUEADA => 'Bloqueada',
        ];
    }

    public static function label(?string $status): string
    {
        return self::labels()[$status] ?? ucfirst((string) $status);
    }

    public static function podeTransicionar(?string $atual, string $novo): bool
    {
        if ($atual === $novo) {
            return true;
        }

        $transicoes = [
            self::SOLICITADA => [self::EM_ANALISE, self::APROVADA, self::CANCELADA],
            self::EM_ANALISE => [self::APROVADA, self::CANCELADA],
            self::APROVADA => [self::PROGRAMADA, self::CHECKLIST_PENDENTE, self::CANCELADA],
            self::PROGRAMADA => [self::CHECKLIST_PENDENTE, self::CANCELADA],
            self::CHECKLIST_PENDENTE => [self::PRONTA_PARA_EXECUCAO, self::BLOQUEADA, self::CANCELADA],
            self::PRONTA_PARA_EXECUCAO => [self::EM_ANDAMENTO, self::CANCELADA],
            self::EM_ANDAMENTO => [self::ATRASADA, self::FINALIZADA],
            self::ATRASADA => [self::EM_ANDAMENTO, self::FINALIZADA],
            self::BLOQUEADA => [self::CHECKLIST_PENDENTE, self::CANCELADA],
            self::FINALIZADA => [],
            self::CANCELADA => [],
        ];

        return in_array($novo, $transicoes[$atual] ?? [], true);
    }
}
