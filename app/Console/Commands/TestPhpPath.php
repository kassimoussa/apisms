<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPhpPath extends Command
{
    protected $signature = 'queue:test-php-path';
    
    protected $description = 'Test PHP path detection for queue workers';

    public function handle()
    {
        $this->info('🔍 Test de détection du chemin PHP pour les workers...');
        $this->newLine();
        
        // Même logique que QueueWorkerManager
        $phpPath = null;
        
        $possiblePaths = [
            // macOS (Homebrew)
            '/opt/homebrew/bin/php',
            
            // Ubuntu/Debian standards
            '/usr/bin/php',
            '/usr/bin/php8.3',
            '/usr/bin/php8.2',
            '/usr/bin/php8.1',
            
            // CentOS/RHEL
            '/usr/local/bin/php',
            '/opt/remi/php83/root/usr/bin/php',
            '/opt/remi/php82/root/usr/bin/php',
            
            // Commande which comme fallback
            trim(shell_exec('which php 2>/dev/null') ?: ''),
            trim(shell_exec('which php8.3 2>/dev/null') ?: ''),
            trim(shell_exec('which php8.2 2>/dev/null') ?: ''),
        ];
        
        $this->info('📋 Chemins testés:');
        foreach ($possiblePaths as $path) {
            if (empty($path)) {
                continue;
            }
            
            $exists = is_executable($path);
            $version = $exists ? shell_exec("$path --version 2>/dev/null | head -n1") : null;
            $isPhp = $version && strpos($version, 'PHP') === 0;
            
            $status = $exists ? ($isPhp ? '✅ OK' : '❌ Pas PHP CLI') : '❌ Introuvable';
            $this->line("  $path - $status");
            
            if ($isPhp && !$phpPath) {
                $phpPath = $path;
                $this->line("    └── Sélectionné: $version");
            }
        }
        
        $this->newLine();
        
        if (!$phpPath) {
            $phpPath = PHP_BINARY ?: 'php';
            $this->warn("⚠️  Aucun chemin PHP trouvé, utilisation de PHP_BINARY: $phpPath");
        }
        
        $this->info("✅ Chemin PHP final: $phpPath");
        
        // Test de la commande complète
        $testCommand = sprintf(
            '%s %s/artisan --version',
            $phpPath,
            base_path()
        );
        
        $this->newLine();
        $this->info('🧪 Test de la commande Artisan:');
        $this->line("Command: $testCommand");
        
        $result = shell_exec($testCommand . ' 2>&1');
        if ($result && strpos($result, 'Laravel Framework') !== false) {
            $this->info('✅ Commande Artisan fonctionne correctement');
            $this->line("Résultat: " . trim($result));
        } else {
            $this->error('❌ Échec de la commande Artisan');
            $this->line("Erreur: " . ($result ?: 'Aucune sortie'));
        }
        
        $this->newLine();
        $this->info('📊 Informations système:');
        $this->line("OS: " . PHP_OS);
        $this->line("PHP_BINARY: " . (PHP_BINARY ?: 'Non défini'));
        $this->line("Environment: " . app()->environment());
        
        return Command::SUCCESS;
    }
}