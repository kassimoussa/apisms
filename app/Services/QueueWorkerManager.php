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
        // Vérifier s'il y a des jobs en attente
        $pendingJobs = \DB::table('jobs')->where('queue', 'bulk-sms')->count();
        
        if ($pendingJobs === 0) {
            Log::info('QueueWorkerManager: Aucun job en attente, worker non nécessaire');
            return;
        }
        
        // Vérifier si un worker tourne déjà
        if (self::isWorkerRunning()) {
            Log::info('QueueWorkerManager: Worker déjà en cours, aucune action nécessaire');
            return;
        }
        
        // Lancer un nouveau worker
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
            $command = sprintf(
                'php %s/artisan queue:work --queue=bulk-sms --timeout=600 --tries=3 --stop-when-empty --sleep=3 > /dev/null 2>&1 &',
                base_path()
            );
            
            Log::info('QueueWorkerManager: Lancement du worker temporaire', [
                'command' => $command
            ]);
            
            // Exécuter la commande en arrière-plan
            shell_exec($command);
            
            Log::info('QueueWorkerManager: Worker temporaire lancé avec succès');
            
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