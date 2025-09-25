<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class QueueWorkerManager
{
    private static $runningWorkers = [];
    
    /**
     * Lance un worker temporaire pour traiter les jobs en attente
     */
    public static function ensureWorkerRunning(): void
    {
        // Attendre un peu pour s'assurer que le job est bien dans la queue
        usleep(100000); // 100ms
        
        // Vérifier s'il y a des jobs en attente
        $pendingJobs = \DB::table('jobs')->where('queue', 'bulk-sms')->count();
        
        Log::info('QueueWorkerManager: Verification', [
            'pending_jobs' => $pendingJobs,
            'timestamp' => now()->toISOString()
        ]);
        
        if ($pendingJobs === 0) {
            Log::info('QueueWorkerManager: Aucun job en attente, worker non nécessaire');
            return;
        }
        
        // Toujours lancer un worker (simplifier la logique)
        // La détection de workers existants est parfois défaillante
        Log::info('QueueWorkerManager: Lancement forcé du worker pour ' . $pendingJobs . ' jobs');
        self::startTemporaryWorker();
    }
    
    /**
     * Vérifie si un worker queue tourne déjà
     */
    private static function isWorkerRunning(): bool
    {
        try {
            // Chercher les processus Laravel queue
            $result = Process::run('pgrep -f "artisan queue:work"');
            
            if ($result->successful() && !empty(trim($result->output()))) {
                Log::info('QueueWorkerManager: Worker détecté en cours d\'exécution');
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::warning('QueueWorkerManager: Erreur lors de la vérification du worker', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Lance un worker temporaire qui s'arrête automatiquement quand il n'y a plus de jobs
     */
    private static function startTemporaryWorker(): void
    {
        try {
            // Version avec logs d'erreur pour debug
            $logFile = storage_path('logs/queue-worker.log');
            
            // Détecter automatiquement le chemin PHP
            $phpPath = null;
            
            // Liste des chemins possibles pour PHP CLI selon l'OS
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
            
            // Vérifier chaque chemin et s'assurer que c'est bien PHP CLI
            foreach ($possiblePaths as $path) {
                if ($path && is_executable($path)) {
                    // Tester que c'est bien php CLI et pas php-fpm
                    $testResult = shell_exec("$path --version 2>/dev/null");
                    if ($testResult && strpos($testResult, 'PHP') === 0) {
                        $phpPath = $path;
                        break;
                    }
                }
            }
            
            // Si aucun chemin trouvé, essayer avec PHP_BINARY en dernier recours
            if (!$phpPath) {
                $phpPath = PHP_BINARY ?: 'php';
            }
            
            $command = sprintf(
                '%s %s/artisan queue:work --queue=bulk-sms --timeout=600 --tries=3 --stop-when-empty --sleep=3 >> %s 2>&1 &',
                $phpPath,
                base_path(),
                $logFile
            );
            
            Log::info('QueueWorkerManager: Lancement du worker temporaire', [
                'command' => $command,
                'log_file' => $logFile,
                'php_path' => $phpPath,
                'php_version' => shell_exec("$phpPath --version 2>/dev/null | head -n1"),
                'os' => PHP_OS,
                'environment' => app()->environment()
            ]);
            
            // Exécuter la commande en arrière-plan
            $output = shell_exec($command);
            
            Log::info('QueueWorkerManager: Worker temporaire lancé', [
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            Log::error('QueueWorkerManager: Erreur lors du lancement du worker', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Version alternative avec Process::start() (plus propre)
     */
    public static function startBackgroundWorker(): void
    {
        try {
            $pendingJobs = \DB::table('jobs')->where('queue', 'bulk-sms')->count();
            
            if ($pendingJobs === 0) {
                return;
            }
            
            if (self::isWorkerRunning()) {
                return;
            }
            
            // Commande pour lancer le worker avec arrêt automatique
            $process = Process::start([
                'php',
                base_path('artisan'),
                'queue:work',
                '--queue=bulk-sms',
                '--timeout=600',
                '--tries=3',
                '--stop-when-empty',
                '--sleep=3'
            ]);
            
            Log::info('QueueWorkerManager: Worker background lancé', [
                'pid' => $process->id()
            ]);
            
        } catch (\Exception $e) {
            Log::error('QueueWorkerManager: Erreur Process::start', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback sur la méthode shell_exec
            self::startTemporaryWorker();
        }
    }
}