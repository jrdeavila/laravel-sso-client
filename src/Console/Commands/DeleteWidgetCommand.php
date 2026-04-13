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

        // Elimina el bloque completo del widget: desde la línea con el slug
        // hasta el cierre de su array ], inclusive la línea en blanco anterior.
        $updated = preg_replace(
            "/\n\s*'{$slug}'\s*=>\s*\[.*?\],/s",
            '',
            $content,
            1
        );

        if ($updated === null || $updated === $content) {
            $this->error("  No se pudo modificar config/widgets.php automáticamente.");
            $this->line("  Elimina manualmente la entrada <comment>'{$slug}'</comment> del array 'widgets'.");
            return;
        }

        file_put_contents($path, $updated);
        $this->line("  <info>✓</info> Entrada eliminada de <comment>config/widgets.php</comment>");
    }
}
