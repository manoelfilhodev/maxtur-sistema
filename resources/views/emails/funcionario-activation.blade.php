<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Convite de acesso</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f4f5;color:#111;padding:24px;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
        <h2 style="margin:0 0 12px;">Ola, {{ $nome }}.</h2>
        <p style="margin:0 0 12px;">Seu acesso ao sistema Systex Mobility foi criado.</p>
        <p style="margin:0 0 20px;">Clique no botao abaixo para ativar sua conta e definir sua senha.</p>

        <p style="margin:0 0 20px;">
            <a href="{{ $activationLink }}" style="display:inline-block;background:#dc2626;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700;">
                Ativar minha conta
            </a>
        </p>

        <p style="margin:0 0 6px;font-size:13px;color:#52525b;">Link de ativacao:</p>
        <p style="word-break:break-all;margin:0 0 16px;font-size:13px;">
            <a href="{{ $activationLink }}">{{ $activationLink }}</a>
        </p>

        <p style="margin:0;font-size:13px;color:#52525b;">Validade: {{ $expiresAt ?: '48 horas' }}.</p>
    </div>
</body>
</html>

