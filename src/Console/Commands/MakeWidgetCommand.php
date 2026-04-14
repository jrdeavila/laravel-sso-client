<?php

namespace CamaradeComercioDeValledupar\SsoClient\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeWidgetCommand extends Command
{
    protected $signature = 'make:widget
                            {name : Nombre del widget en PascalCase o kebab-case (ej: AsistenteVirtual)}
                            {--type=chatbot : Tipo del widget: chatbot | notification | survey | embed}
                            {--logic : Genera clase Check para validación previa (recomendado para survey)}';

    protected $description = 'Crea un widget SSO: vista Blade, clase de check opcional (--logic) y entrada en config/widgets.php';

    private const VALID_TYPES = ['chatbot', 'notification', 'survey', 'embed'];

    public function handle(): int
    {
        $name  = $this->argument('name');
        $logic = $this->option('logic');

        // Si --logic sin --type explícito, inferir 'survey' automáticamente.
        $typeExplicit = $this->input->hasParameterOption('--type');
        $type = $typeExplicit ? $this->option('type') : ($logic ? 'survey' : 'chatbot');

        if (! in_array($type, self::VALID_TYPES)) {
            $this->error("Tipo inválido: '{$type}'. Valores aceptados: " . implode(', ', self::VALID_TYPES));
            return self::FAILURE;
        }

        if ($logic && $type !== 'survey') {
            $this->warn("--logic está pensado para widgets de tipo 'survey'. Se creará la clase de todas formas.");
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
        // ── chatbot ───────────────────────────────────────────────────────────────
        if ($type === 'chatbot') {
            return <<<BLADE
@extends(\$widgetLayout)

@section('widget-content')
{{--
    El lanzador renderiza este iframe dentro de un panel deslizante.
    El iframe ocupa todo el espacio del panel; diseña tu interfaz de chat aquí.
    Usa window.cCVSend('widget:close') para cerrar el panel desde dentro del iframe.
--}}
<div x-data="{
        messages: [{ from: 'bot', text: '¡Hola, {{ auth()->user()->name ?? 'funcionario' }}! ¿En qué puedo ayudarte?' }],
        input: '',
        enviar() {
            if (!this.input.trim()) return;
            this.messages.push({ from: 'user', text: this.input });
            const msg = this.input; this.input = '';
            setTimeout(() => this.messages.push({ from: 'bot', text: 'Recibí: ' + msg }), 500);
        }
    }"
    style="display:flex;flex-direction:column;height:100vh;padding:.75rem;gap:.5rem;box-sizing:border-box;font-family:sans-serif;">

    <div style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:.4rem;">
        <template x-for="(m, i) in messages" :key="i">
            <div :style="m.from==='user'
                ? 'align-self:flex-end;background:#d1e8ff;padding:.4rem .75rem;border-radius:1rem 1rem .2rem 1rem;max-width:80%;font-size:.82rem;'
                : 'align-self:flex-start;background:#f1f5f9;padding:.4rem .75rem;border-radius:1rem 1rem 1rem .2rem;max-width:80%;font-size:.82rem;'"
                x-text="m.text"></div>
        </template>
    </div>

    <div style="display:flex;gap:.4rem;">
        <input x-model="input" @keydown.enter="enviar()" type="text"
            placeholder="Escribe un mensaje..."
            style="flex:1;padding:.45rem .75rem;border:1px solid #cbd5e1;border-radius:.5rem;font-size:.82rem;outline:none;">
        <button @click="enviar()"
            style="padding:.45rem .9rem;background:#3b82f6;color:#fff;border:none;border-radius:.5rem;cursor:pointer;font-size:.82rem;">
            Enviar
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
BLADE;
        }

        // ── notification ──────────────────────────────────────────────────────────
        if ($type === 'notification') {
            return <<<BLADE
@extends(\$widgetLayout)

@section('widget-content')
{{--
    Widget de notificación: no tiene interfaz visual.
    Este iframe se carga de forma invisible; su único propósito es enviar
    mensajes toast al lanzador mediante window.cCVNotify().

    Uso:
        window.cCVNotify(title, message, type, duration)
        type: 'info' | 'success' | 'warning' | 'error'
        duration: milisegundos (0 = solo manual)
--}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // TODO: reemplaza con lógica real (fetch a tu API, etc.)
    window.cCVNotify(
        'Notificación de ejemplo',
        'Tienes elementos pendientes de atención.',
        'info',
        6000
    );
});
</script>
@endpush
BLADE;
        }

        // ── survey ────────────────────────────────────────────────────────────────
        if ($type === 'survey') {
            return <<<BLADE
@extends(\$widgetLayout)

@section('widget-content')
{{--
    Widget de encuesta: modal de ejecución única.
    El lanzador cierra el modal permanentemente cuando recibe 'widget:submitted'.
    El botón ✕ del lanzador (si mandatory=false) cierra sin marcar como completado;
    la encuesta vuelve a aparecer en la próxima visita.
    Llama window.cCVSend('widget:submitted') SOLO cuando el usuario haya respondido.
--}}
<div style="width:100%;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;box-sizing:border-box;background:#fff;font-family:sans-serif;">

    <h2 style="font-size:1.3rem;font-weight:700;color:#1e293b;margin:0 0 .5rem;">{{ \$widgetName }}</h2>
    <p style="color:#64748b;font-size:.88rem;text-align:center;margin:0 0 1.5rem;">
        {{-- TODO: descripción de la encuesta para {{ auth()->user()->name ?? 'el usuario' }} --}}
    </p>

    {{-- TODO: campos de la encuesta --}}

    <button onclick="window.cCVSend('widget:submitted', { source: '{{ \$widgetSlug }}' })"
        style="padding:.6rem 1.5rem;background:#3b82f6;color:#fff;border:none;border-radius:.5rem;cursor:pointer;font-size:.9rem;">
        Enviar respuesta
    </button>

</div>
@endsection
BLADE;
        }

        // ── embed (default) ───────────────────────────────────────────────────────
        return <<<BLADE
@extends(\$widgetLayout)

@section('widget-content')
{{--
    Widget incrustado: tarjeta flotante draggable dentro del lanzador.
    El lanzador renderiza este iframe dentro de una tarjeta redimensionable.
    Diseña el contenido como una vista compacta (recordatorios, tareas, etc.).
    Usa window.cCVSend('widget:close') para cerrarlo desde dentro.
--}}
<div style="width:100%;height:100%;padding:1rem;box-sizing:border-box;font-family:sans-serif;overflow-y:auto;">

    <h3 style="font-size:.9rem;font-weight:700;color:#1e293b;margin:0 0 .75rem;">{{ \$widgetName }}</h3>

    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.5rem;font-size:.82rem;color:#475569;">
        {{-- TODO: lista de elementos del widget --}}
        <li style="padding:.4rem .6rem;background:#f8fafc;border-radius:.4rem;border-left:3px solid #3b82f6;">
            Elemento de ejemplo para {{ auth()->user()->name ?? 'el funcionario' }}
        </li>
    </ul>

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
     * Decide si la encuesta '{$slug}' debe mostrarse en esta visita.
     *
     * El lanzador llama GET /widgets/{slug}/check?token=... server-to-server
     * antes de incluir el widget. Retorna true = mostrar, false = omitir.
     *
     * El lanzador también cierra el modal permanentemente (localStorage) cuando
     * recibe 'widget:submitted', así que este check es solo el filtro de servidor.
     *
     * El token ya fue validado por ValidateSsoToken antes de llegar aquí.
     *
     * @return bool
     */
    public function __invoke(Request \$request): bool
    {
        // TODO: implementa la lógica de visibilidad.
        // Ejemplo: return !DB::table('respuestas')->where('user_id', auth()->id())->exists();
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

        // Localiza el cierre del array 'widgets' buscando la primera aparición de
        // "\n    ]," después de "'widgets' => [".
        // Se usa strpos + substr_replace en lugar de preg_replace para evitar que
        // el contenido del $entry (con \, $ del código PHP) sea interpretado como
        // tokens de reemplazo por el motor de regex.
        $widgetsStart = strpos($content, "'widgets' => [");

        if ($widgetsStart === false) {
            $this->error("  No se encontró el array 'widgets' en config/widgets.php.");
            $this->line("  Agrega manualmente la siguiente entrada:");
            $this->line($entry);
            return;
        }

        $closeMarker = "\n    ],";
        $closePos    = strpos($content, $closeMarker, $widgetsStart);

        if ($closePos === false) {
            $this->error("  No se encontró el cierre del array 'widgets' en config/widgets.php.");
            $this->line("  Agrega manualmente la siguiente entrada:");
            $this->line($entry);
            return;
        }

        $updated = substr($content, 0, $closePos)
            . $entry
            . substr($content, $closePos);

        file_put_contents($configPath, $updated);

        $this->line("  <info>✓</info> Registrado en <comment>config/widgets.php</comment>");
    }
}
