<?php
/**
 * SYSTEX Base Installer (Shared Hosting Friendly) - v2.1
 * - Baixa um ZIP e instala arquivos no projeto atual
 * - Sobrescreve SOMENTE arquivos definidos em $FORCE_OVERWRITE (ex: routes/web.php)
 * - Cria/garante colunas em users (nivel, ativo)
 * - Cria usuário padrão DEV (se não existir)
 * - Cria um "lock" para impedir execução dupla: /.systex/installed.lock
 *
 * Use 1x e APAGUE o install.php ao final.
 */

declare(strict_types=1);

// ✅ IMPORTS DEVEM FICAR NO TOPO (antes de qualquer código executável)
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

header('Content-Type: text/plain; charset=utf-8');

// ========================
// SEGURANÇA (TOKEN)
// ========================
$SECRET = 'b8f2c0e9d6a14a5f9c1a2f3e8b7c0d91';

if (!isset($_GET['token']) || $_GET['token'] !== $SECRET) {
    http_response_code(403);
    exit("Forbidden\n");
}

// ========================
// CONFIG ZIP
// ========================
$ZIP_URL = 'https://systex.com.br/base-oficial-sistemas-systex/base-systex.zip';

// ========================
// INSTALAÇÃO (COMPORTAMENTO)
// ========================
$OVERWRITE = false;

$FORCE_OVERWRITE = [
    'routes/web.php',
];

$SKIP_PREFIXES = [
    '.env',
    'vendor/',
    'storage/',
    'node_modules/',
    '.git/',
];

// ========================
// USUÁRIO PADRÃO SYSTEX
// ========================
$CREATE_DEFAULT_USER = true;

$DEFAULT_USER = [
    'name'     => 'Systex Dev',
    'email'    => 'dev@systex.com.br',
    'password' => 'nVbb261214!@',
    'nivel'    => 'admin',
    'ativo'    => 1,
];

// ========================
// PATHS
// ========================
$PROJECT_ROOT = __DIR__;
$SYSTEX_DIR   = $PROJECT_ROOT . '/.systex';
$LOCK_FILE    = $SYSTEX_DIR . '/installed.lock';

$TMP_DIR      = $PROJECT_ROOT . '/.systex_tmp';
$ZIP_FILE     = $TMP_DIR . '/base-systex.zip';
$EXTRACT_DIR  = $TMP_DIR . '/extract';

// ========================
// HELPERS
// ========================
function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
    }
    rmdir($dir);
}

function startsWithAny(string $path, array $prefixes): bool {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    foreach ($prefixes as $p) {
        $p = ltrim(str_replace('\\', '/', $p), '/');
        if ($p !== '' && str_starts_with($path, $p)) return true;
    }
    return false;
}

function downloadFile(string $url, string $dest): void {
    if (function_exists('curl_init')) {
        $fp = fopen($dest, 'wb');
        if (!$fp) throw new RuntimeException("Não consegui escrever em: $dest");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERAGENT => 'SystexInstaller/2.1',
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!$ok) throw new RuntimeException("Falha no download (HTTP $code): $err");
        return;
    }

    $data = @file_get_contents($url);
    if ($data === false) throw new RuntimeException("Falha no download via file_get_contents: $url");
    if (file_put_contents($dest, $data) === false) throw new RuntimeException("Falha ao salvar ZIP em: $dest");
}

function ensureDir(string $dir): void {
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException("Não consegui criar diretório: $dir");
    }
}

// ========================
// EXEC
// ========================
try {
    // LOCK
    ensureDir($SYSTEX_DIR);

    if (file_exists($LOCK_FILE)) {
        echo "⛔ Instalação já foi executada neste projeto.\n";
        echo "Lock encontrado em: {$LOCK_FILE}\n";
        echo "Se você quer rodar de novo, apague esse arquivo de lock.\n";
        exit;
    }

    // TMP
    ensureDir($TMP_DIR);
    rrmdir($EXTRACT_DIR);
    ensureDir($EXTRACT_DIR);

    echo "🚀 SYSTEX Installer\n";
    echo "Baixando: $ZIP_URL\n";

    // Baixar ZIP
    if (file_exists($ZIP_FILE)) unlink($ZIP_FILE);
    downloadFile($ZIP_URL, $ZIP_FILE);

    if (!file_exists($ZIP_FILE) || filesize($ZIP_FILE) < 1000) {
        throw new RuntimeException("ZIP inválido ou muito pequeno. Verifique a URL.");
    }

    // Extrair ZIP
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException("ZipArchive não disponível no servidor.");
    }

    $zip = new ZipArchive();
    if ($zip->open($ZIP_FILE) !== true) {
        throw new RuntimeException("Não consegui abrir o ZIP.");
    }
    if (!$zip->extractTo($EXTRACT_DIR)) {
        $zip->close();
        throw new RuntimeException("Falha ao extrair ZIP.");
    }
    $zip->close();

    echo "Extraído em: $EXTRACT_DIR\n";
    echo "Copiando arquivos para o projeto: $PROJECT_ROOT\n\n";

    // Copiar
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($EXTRACT_DIR, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $copied = 0; $skipped = 0; $overwritten = 0; $failed = 0;

    foreach ($it as $item) {
        $src = $item->getPathname();
        $rel = ltrim(str_replace($EXTRACT_DIR, '', $src), DIRECTORY_SEPARATOR);
        $rel = str_replace('\\', '/', $rel);

        if ($rel === '') continue;

        if (startsWithAny($rel, $SKIP_PREFIXES)) {
            $skipped++;
            continue;
        }

        $dst = $PROJECT_ROOT . '/' . $rel;

        if ($item->isDir()) {
            if (!is_dir($dst)) @mkdir($dst, 0755, true);
            continue;
        }

        $exists = file_exists($dst);

        if ($exists) {
            $force = in_array($rel, $FORCE_OVERWRITE, true);
            if (!$force && !$OVERWRITE) {
                $skipped++;
                continue;
            }
            $overwritten++;
        } else {
            $copied++;
        }

        $dir = dirname($dst);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!@copy($src, $dst)) {
            $failed++;
            throw new RuntimeException("Falha ao copiar: $rel (verifique permissões)");
        }
    }

    // ========================
    // BOOTSTRAP LARAVEL (para mexer em DB/Schema e criar usuário)
    // ========================
    $autoload  = $PROJECT_ROOT . '/vendor/autoload.php';
    $bootstrap = $PROJECT_ROOT . '/bootstrap/app.php';

    if (!file_exists($autoload) || !file_exists($bootstrap)) {
        echo "\n⚠️ Não foi possível aplicar ajustes no banco/criar usuário (vendor ou bootstrap ausentes).\n";
        echo "   Você precisa ter o Laravel instalado com vendor no servidor.\n";
    } else {
        require_once $autoload;

        $app = require $bootstrap;
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = Illuminate\Http\Request::capture();
        $kernel->handle($request);

        Illuminate\Support\Facades\Facade::setFacadeApplication($app);

        echo "\n🧱 Ajustando tabela users...\n";

        // ✅ cria colunas se não existirem
        if (!Schema::hasColumn('users', 'nivel')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nivel', 20)->default('user')->after('password');
            });
            echo "✅ Coluna 'nivel' criada\n";
        } else {
            echo "ℹ️ Coluna 'nivel' já existe\n";
        }

        if (!Schema::hasColumn('users', 'ativo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('ativo')->default(true)->after('nivel');
            });
            echo "✅ Coluna 'ativo' criada\n";
        } else {
            echo "ℹ️ Coluna 'ativo' já existe\n";
        }

        // ========================
        // Criar usuário padrão
        // ========================
        if ($CREATE_DEFAULT_USER) {
            echo "\n👤 Criando usuário padrão...\n";

            $userModel = config('auth.providers.users.model') ?: \App\Models\User::class;
            if (!class_exists($userModel)) {
                throw new RuntimeException("Model de usuário não encontrado: {$userModel}");
            }

            $user = $userModel::where('email', $DEFAULT_USER['email'])->first();

            if (!$user) {
                $user = new $userModel();
            }

            // seta campos
            foreach ($DEFAULT_USER as $field => $value) {
                if ($field === 'password') continue;
                $user->{$field} = $value;
            }

            $user->password = Illuminate\Support\Facades\Hash::make($DEFAULT_USER['password']);
            $user->save();

            echo "✅ Usuário pronto: {$DEFAULT_USER['email']} (nivel={$DEFAULT_USER['nivel']})\n";
        }

        $kernel->terminate($request, response('OK'));
    }

    // LOCK
    $lockContent  = "installed_at=" . date('c') . "\n";
    $lockContent .= "zip_url={$ZIP_URL}\n";
    $lockContent .= "force_overwrite=" . implode(',', $FORCE_OVERWRITE) . "\n";
    @file_put_contents($LOCK_FILE, $lockContent);

    // Limpar tmp
    rrmdir($TMP_DIR);

    echo "\n✅ Concluído!\n";
    echo "- Copiados novos: $copied\n";
    echo "- Sobrescritos: $overwritten\n";
    echo "- Ignorados: $skipped\n";
    echo "- Falhas: $failed\n\n";

    echo "🔒 Lock criado em: {$LOCK_FILE}\n";
    echo "⚠️ IMPORTANTE: apague o arquivo install.php agora.\n";

} catch (Throwable $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
