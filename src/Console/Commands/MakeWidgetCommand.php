<?php

namespace CamaradeComercioDeValledupar\SsoClient\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeWidgetCommand extends Command
{
    protected $signature = 'make:widget
                            {name : Nombre del widget en PascalCase o kebab-case (ej: AsistenteVirtual)}
                            {--type=chatbot : Tipo del widget: chatbot | survey | notification | announcement}
                            {--logic : Genera clase Check para validación previa (recomendado para announcement)}';

    protected $description = 'Crea un widget SSO: vista Blade, clase de check opcional (--logic) y entrada en config/widgets.php';

    private const VALID_TYPES = ['chatbot', 'survey', 'notification', 'announcement'];

    public function handle(): int
    {
        $name   = $this->argument('name');
        $type   = $this->option('type');
        $logic  = $this->option('logic');

        if (! in_array($type, self::VALID_TYPES)) {
            $this->error("Tipo inválido: '{$type}'. Valores aceptados: " . implode(', ', self::VALID_TYPES));
            return self::FAILURE;
        }

        if ($logic && $type !== 'announcement') {
            $this->warn("--logic está pensado para widgets de tipo 'announcement'. Se creará la clase de todas formas.");
        }

        $slug      = Str::kebab(Str::studly($name));   // AsistenteVirtual → asistente-virtual
        $className = Str::studly($name);                // asistente-virtual → AsistenteVirtual
        $viewName  = "widgets.{$slug}";

        $this->newLine();
        $this->line("  Creando widget <comment>{$slug}</comment> (<info>{$type}</info>)");
        $this->newLine();

        $this->createView($slug, $type, $className);

        if ($logic) {
            $this->createCheckClass($className, $slug);
        }

        $this->addToConfig($slug, $className, $type, $viewName, $logic);

        $this->newLine();
        $this->info("Widget '{$slug}' listo.");
        $this->newLine();

        $this->line("  Próximo paso: sincroniza el manifest desde el lanzador para que lo detecte.");

        return self::SUCCESS;
    }

    // ─── Vista ────────────────────────────────────────────────────────────────

    private function createView(string $slug, string $type, string $className): void
    {
        $path = resource_path("views/widgets/{$slug}.blade.php");

        if (file_exists($path)) {
            $this->warn("  Vista ya existe, omitiendo: resources/views/widgets/{$slug}.blade.php");
            return;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $this->viewStub($slug, $type, $className));

        $this->line("  <info>✓</info> Vista: <comment>resources/views/widgets/{$slug}.blade.php</comment>");
    }

    private function viewStub(string $slug, string $type, string $className): string
    {
        if ($type === 'chatbot') {
            return <<<BLADE
@extends(\$widgetLayout)

@push('styles')
<style>
    /*
     * El lanzador posiciona este iframe en una esquina (420×640 px).
     * pointer-events:none ya está en body (layout). Solo #ccv-widget-root captura eventos.
     * Diseña el botón toggle y el panel dentro de este contenedor.
     */
    #ccv-widget-root {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: flex-end;
        padding: 1rem;
        box-sizing: border-box;
    }
</style>
@endpush

@section('widget-content')
<div x-data="{ open: false }">

    {{-- Panel del chat --}}
    <div x-show="open" x-cloak
        style="width:380px;height:460px;background:#fff;border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,.18);margin-bottom:.75rem;display:flex;flex-direction:column;overflow:hidden;">

        <div style="background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;padding:.75rem 1rem;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
            <strong style="font-size:.9rem;">{{ \$widgetName }}</strong>
            <button @click="open = false; window.cCVSend('widget:close')"
                title="Cerrar"
                style="background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:.85rem;">
                ✕
            </button>
        </div>

        <div style="flex:1;padding:1rem;overflow-y:auto;font-size:.85rem;color:#475569;">
            {{-- TODO: contenido del chatbot --}}
            <p>Hola, <strong>{{ auth()->user()->name ?? 'funcionario' }}</strong>. ¿En qué puedo ayudarte?</p>
        </div>

    </div>

    {{-- Botón toggle (siempre visible en la esquina) --}}
    <button @click="open = !open"
        :title="open ? 'Minimizar' : '{{ \$widgetName }}'"
        style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;border:none;cursor:pointer;font-size:1.3rem;box-shadow:0 4px 18px rgba(59,130,246,.45);transition:transform .2s;">
        <i :class="open ? 'fas fa-chevron-down' : 'fas fa-comment-dots'"></i>
    </button>

</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
BLADE;
        }

        // survey / notification / announcement — ocupa pantalla completa, el lanzador pone el botón ✕
        $closeHint = $type !== 'announcement'
            ? '{{-- El lanzador ya provee un botón ✕ fuera del iframe. No añadas otro aquí. --}}'
            : '{{-- Para announcements sin close externo, usa cCVSend(\'widget:submitted\') al completar. --}}';

        return <<<BLADE
@extends(\$widgetLayout)

@section('widget-content')
<div style="width:100%;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;box-sizing:border-box;background:#fff;font-family:sans-serif;">

    {$closeHint}

    <h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 .75rem;">{{ \$widgetName }}</h2>

    <p style="color:#475569;font-size:.9rem;text-align:center;margin:0 0 1.5rem;">
        {{-- TODO: contenido del widget para {{ auth()->user()->name ?? 'el usuario' }} --}}
    </p>

    <button onclick="window.cCVSend('widget:submitted', { source: '{{ \$widgetSlug }}' })"
        style="padding:.6rem 1.5rem;background:#3b82f6;color:#fff;border:none;border-radius:.5rem;cursor:pointer;font-size:.9rem;">
        Confirmar
    </button>

</div>
@endsection
BLADE;
    }

    // ─── Clase de check ───────────────────────────────────────────────────────

    private function createCheckClass(string $className, string $slug): void
    {
        $checkClass = "Check{$className}";
        $path       = app_path("Widgets/{$checkClass}.php");

        if (file_exists($path)) {
            $this->warn("  Clase ya existe, omitiendo: app/Widgets/{$checkClass}.php");
            return;
        }

        if (! is_dir(app_path('Widgets'))) {
            mkdir(app_path('Widgets'), 0755, true);
        }

        file_put_contents($path, $this->checkClassStub($checkClass, $slug));

        $this->line("  <info>✓</info> Clase de check: <comment>app/Widgets/{$checkClass}.php</comment>");
    }

    private function checkClassStub(string $checkClass, string $slug): string
    {
        return <<<PHP
<?php

namespace App\Widgets;

use Illuminate\Http\Request;

class {$checkClass}
{
    /**
     * Decide si el widget '{$slug}' debe mostrarse al usuario en esta visita.
     *
     * El lanzador llama este endpoint server-to-server con un token SSO firmado
     * (sub=0) antes de incluir el widget en el panel. Devuelve true para mostrar,
     * false para omitir.
     *
     * El token ya fue validado por ValidateSsoToken antes de llegar aquí.
     * Puedes leer \$request->query('token') si necesitas datos extra del payload.
     *
     * @return bool
     */
    public function __invoke(Request \$request): bool
    {
        // TODO: implementa la lógica de visibilidad.
        // Ejemplo: return \\DB::table('anuncios')->where('activo', true)->exists();
        return true;
    }
}
PHP;
    }

    // ─── config/widgets.php ───────────────────────────────────────────────────

    private function addToConfig(string $slug, string $className, string $type, string $viewName, bool $logic): void
    {
        $configPath = config_path('widgets.php');

        if (! file_exists($configPath)) {
            $this->error("  No se encontró config/widgets.php.");
            $this->line("  Publícalo primero: <comment>php artisan vendor:publish --tag=ccv-widgets-config</comment>");
            return;
        }

        $content = file_get_contents($configPath);

        if (str_contains($content, "'{$slug}'")) {
            $this->warn("  El slug '{$slug}' ya existe en config/widgets.php, omitiendo.");
            return;
        }

        $checkLine = $logic
            ? "\n            'check_class' => \\App\\Widgets\\Check{$className}::class,"
            : '';

        $humanName = Str::title(str_replace('-', ' ', $slug));

        $entry = <<<PHP

        '{$slug}' => [
            'name'       => '{$humanName}',
            'type'       => '{$type}',
            'view'       => '{$viewName}',
            'layout'     => 'sso-client::widgets.layout',
            'middleware' => [],{$checkLine}
            'enabled'    => true,
        ],
PHP;

        // Inserta antes del cierre ], del array 'widgets'.
        // El cierre tiene 4 espacios de indentación en el formato estándar publicado.
        $updated = preg_replace(
            "/('widgets'\s*=>\s*\[)(.*?)(\n\s{4}\],)/s",
            '$1$2' . $entry . "\n    ],",
            $content,
            1
        );

        if ($updated === null || $updated === $content) {
            $this->error("  No se pudo modificar config/widgets.php automáticamente.");
            $this->line("  Agrega manualmente la siguiente entrada en el array 'widgets':");
            $this->line($entry);
            return;
        }

        file_put_contents($configPath, $updated);

        $this->line("  <info>✓</info> Registrado en <comment>config/widgets.php</comment>");
    }
}
