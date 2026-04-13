<?php

namespace CamaradeComercioDeValledupar\SsoClient\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DeleteWidgetCommand extends Command
{
    protected $signature = 'widget:delete
                            {name : Slug o nombre del widget a eliminar (ej: asistente-virtual)}
                            {--force : No pide confirmación}';

    protected $description = 'Elimina un widget SSO: vista Blade, clase Check y entrada en config/widgets.php';

    public function handle(): int
    {
        $name  = $this->argument('name');
        $slug  = Str::kebab(Str::studly($name));
        $class = 'Check' . Str::studly($name);

        $viewPath  = resource_path("views/widgets/{$slug}.blade.php");
        $classPath = app_path("Widgets/{$class}.php");

        $targets = array_filter([
            file_exists($viewPath)  ? "resources/views/widgets/{$slug}.blade.php" : null,
            file_exists($classPath) ? "app/Widgets/{$class}.php"                  : null,
            $this->existsInConfig($slug) ? 'config/widgets.php (entrada)'         : null,
        ]);

        if (empty($targets)) {
            $this->warn("No se encontró ningún archivo para el widget '{$slug}'.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line("  Se eliminarán los siguientes archivos/entradas:");
        foreach ($targets as $t) {
            $this->line("    <comment>- {$t}</comment>");
        }
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm("¿Confirmas la eliminación del widget '{$slug}'?")) {
            $this->line('  Cancelado.');
            return self::SUCCESS;
        }

        $this->deleteView($slug, $viewPath);
        $this->deleteCheckClass($class, $classPath);
        $this->removeFromConfig($slug);

        $this->newLine();
        $this->info("Widget '{$slug}' eliminado.");

        return self::SUCCESS;
    }

    private function deleteView(string $slug, string $path): void
    {
        if (! file_exists($path)) {
            return;
        }
        unlink($path);
        $this->line("  <info>✓</info> Vista eliminada: <comment>resources/views/widgets/{$slug}.blade.php</comment>");
    }

    private function deleteCheckClass(string $class, string $path): void
    {
        if (! file_exists($path)) {
            return;
        }
        unlink($path);
        $this->line("  <info>✓</info> Clase eliminada: <comment>app/Widgets/{$class}.php</comment>");
    }

    private function existsInConfig(string $slug): bool
    {
        $path = config_path('widgets.php');
        return file_exists($path) && str_contains(file_get_contents($path), "'{$slug}'");
    }

    private function removeFromConfig(string $slug): void
    {
        $path = config_path('widgets.php');

        if (! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        if (! str_contains($content, "'{$slug}'")) {
            return;
        }

        // Localiza el inicio de la entrada del widget.
        $marker = "'{$slug}'";
        $markerPos = strpos($content, $marker);

        if ($markerPos === false) {
            $this->error("  No se pudo encontrar '{$slug}' en config/widgets.php.");
            $this->line("  Elimina manualmente la entrada <comment>'{$slug}'</comment> del array 'widgets'.");
            return;
        }

        // Retrocede hasta el inicio de la línea (incluyendo línea en blanco previa si existe).
        $lineStart = strrpos(substr($content, 0, $markerPos), "\n") ?: 0;
        $prevNewline = strrpos(substr($content, 0, $lineStart), "\n");
        if ($prevNewline !== false && trim(substr($content, $prevNewline + 1, $lineStart - $prevNewline - 1)) === '') {
            $lineStart = $prevNewline;
        }

        // Encuentra el [ que abre el array del widget y cuenta brackets para
        // llegar al ] de cierre — evita falsos positivos con 'middleware' => [].
        $bracketOpen = strpos($content, '[', $markerPos);
        if ($bracketOpen === false) {
            $this->error("  Formato inesperado en config/widgets.php.");
            return;
        }

        $depth = 0;
        $closePos = $bracketOpen;
        $len = strlen($content);
        while ($closePos < $len) {
            if ($content[$closePos] === '[') $depth++;
            if ($content[$closePos] === ']') {
                if (--$depth === 0) break;
            }
            $closePos++;
        }

        // Avanza el cursor hasta después del ], (corchete + coma)
        $end = $closePos + 1;
        if ($end < $len && $content[$end] === ',') $end++;

        $updated = substr($content, 0, $lineStart) . substr($content, $end);

        file_put_contents($path, $updated);
        $this->line("  <info>✓</info> Entrada eliminada de <comment>config/widgets.php</comment>");
    }
}
