<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Exception;

class DatabaseBackupService
{
    private string $backupDisk;
    private int $retentionDays;
    private bool $compressionEnabled;
    
    public function __construct()
    {
        $this->backupDisk = config('backup.disk', 'local');
        $this->retentionDays = config('backup.retention_days', 30);
        $this->compressionEnabled = config('backup.compression', true);
    }

    /**
     * Create a full database backup
     */
    public function createBackup(array $options = []): array
    {
        $startTime = microtime(true);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "apisms_backup_{$timestamp}";
        
        try {
            Log::info('Starting database backup', [
                'filename' => $filename,
                'disk' => $this->backupDisk,
                'timestamp' => $timestamp
            ]);

            // Create backup directory if it doesn't exist
            $backupPath = 'backups/database/' . now()->format('Y/m');
            Storage::disk($this->backupDisk)->makeDirectory($backupPath);

            // Generate SQL dump
            $sqlContent = $this->generateSqlDump($options);
            
            // Compress if enabled
            if ($this->compressionEnabled) {
                $sqlContent = gzencode($sqlContent, 9);
                $filename .= '.sql.gz';
            } else {
                $filename .= '.sql';
            }

            $fullPath = $backupPath . '/' . $filename;
            
            // Store backup file
            Storage::disk($this->backupDisk)->put($fullPath, $sqlContent);
            
            $fileSize = Storage::disk($this->backupDisk)->size($fullPath);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Create backup metadata
            $metadata = [
                'filename' => $filename,
                'path' => $fullPath,
                'size' => $fileSize,
                'size_human' => $this->formatBytes($fileSize),
                'compressed' => $this->compressionEnabled,
                'created_at' => now()->toISOString(),
                'duration_ms' => $duration,
                'tables_included' => $this->getTableList($options),
                'checksum' => hash('sha256', $sqlContent),
                'disk' => $this->backupDisk,
            ];

            // Store metadata
            Storage::disk($this->backupDisk)->put(
                $backupPath . '/' . $filename . '.meta.json',
                json_encode($metadata, JSON_PRETTY_PRINT)
            );

            Log::info('Database backup completed successfully', $metadata);
            
            return [
                'success' => true,
                'metadata' => $metadata,
                'message' => "Backup created successfully: {$filename}",
            ];

        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Database backup failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
            ];
        }
    }

    /**
     * Generate SQL dump of the database
     */
    private function generateSqlDump(array $options = []): string
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $tables = $options['tables'] ?? $this->getAllTables();
        
        $sql = "-- ApiSMS Gateway Database Backup\n";
        $sql .= "-- Generated on: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database: {$database}\n\n";
        
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            $sql .= $this->dumpTable($table, $options);
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        return $sql;
    }

    /**
     * Dump a single table
     */
    private function dumpTable(string $table, array $options = []): string
    {
        $connection = DB::connection();
        
        $sql = "-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for table `{$table}`\n";
        $sql .= "-- --------------------------------------------------------\n\n";
        
        // Drop table statement
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
        
        // Create table statement
        $createTable = $connection->select("SHOW CREATE TABLE `{$table}`")[0];
        $sql .= $createTable->{'Create Table'} . ";\n\n";
        
        // Skip data for certain tables if specified
        $skipData = $options['skip_data'] ?? ['sessions', 'cache', 'failed_jobs'];
        if (in_array($table, $skipData)) {
            return $sql;
        }
        
        // Insert data
        $rows = $connection->table($table)->get();
        if ($rows->isNotEmpty()) {
            $sql .= "-- Dumping data for table `{$table}`\n";
            $sql .= "INSERT INTO `{$table}` VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ((array)$row as $value) {
                    if (is_null($value)) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }
            
            $sql .= implode(",\n", $values) . ";\n\n";
        }
        
        return $sql;
    }

    /**
     * Get list of all tables
     */
    private function getAllTables(): array
    {
        $tables = [];
        $results = DB::select('SHOW TABLES');
        
        foreach ($results as $result) {
            $table = (array)$result;
            $tables[] = array_values($table)[0];
        }
        
        return $tables;
    }

    /**
     * Get table list for metadata
     */
    private function getTableList(array $options = []): array
    {
        $tables = $options['tables'] ?? $this->getAllTables();
        $tableInfo = [];
        
        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $tableInfo[] = [
                'name' => $table,
                'rows' => $count,
            ];
        }
        
        return $tableInfo;
    }

    /**
     * Clean up old backups
     */
    public function cleanupOldBackups(): array
    {
        $cutoffDate = Carbon::now()->subDays($this->retentionDays);
        $deletedFiles = [];
        $deletedSize = 0;
        
        try {
            $backupFiles = Storage::disk($this->backupDisk)->allFiles('backups/database');
            
            foreach ($backupFiles as $file) {
                $lastModified = Carbon::createFromTimestamp(
                    Storage::disk($this->backupDisk)->lastModified($file)
                );
                
                if ($lastModified->lt($cutoffDate)) {
                    $fileSize = Storage::disk($this->backupDisk)->size($file);
                    Storage::disk($this->backupDisk)->delete($file);
                    
                    $deletedFiles[] = $file;
                    $deletedSize += $fileSize;
                    
                    Log::info('Deleted old backup file', [
                        'file' => $file,
                        'size' => $this->formatBytes($fileSize),
                        'last_modified' => $lastModified->toDateTimeString(),
                    ]);
                }
            }
            
            return [
                'success' => true,
                'deleted_files' => count($deletedFiles),
                'deleted_size' => $this->formatBytes($deletedSize),
                'files' => $deletedFiles,
            ];
            
        } catch (Exception $e) {
            Log::error('Backup cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List all available backups
     */
    public function listBackups(): array
    {
        try {
            $backupFiles = Storage::disk($this->backupDisk)->allFiles('backups/database');
            $backups = [];
            
            foreach ($backupFiles as $file) {
                if (str_ends_with($file, '.meta.json')) {
                    continue; // Skip metadata files in listing
                }
                
                if (str_ends_with($file, '.sql') || str_ends_with($file, '.sql.gz')) {
                    $metadata = null;
                    $metaFile = $file . '.meta.json';
                    
                    if (Storage::disk($this->backupDisk)->exists($metaFile)) {
                        $metadata = json_decode(
                            Storage::disk($this->backupDisk)->get($metaFile), 
                            true
                        );
                    }
                    
                    $backups[] = [
                        'file' => $file,
                        'size' => Storage::disk($this->backupDisk)->size($file),
                        'size_human' => $this->formatBytes(Storage::disk($this->backupDisk)->size($file)),
                        'last_modified' => Carbon::createFromTimestamp(
                            Storage::disk($this->backupDisk)->lastModified($file)
                        )->toDateTimeString(),
                        'metadata' => $metadata,
                    ];
                }
            }
            
            // Sort by creation time, newest first
            usort($backups, function($a, $b) {
                return strtotime($b['last_modified']) - strtotime($a['last_modified']);
            });
            
            return [
                'success' => true,
                'backups' => $backups,
                'total_count' => count($backups),
                'total_size' => $this->formatBytes(array_sum(array_column($backups, 'size'))),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackup(string $filename): array
    {
        try {
            $backupPath = "backups/database/{$filename}";
            
            if (!Storage::disk($this->backupDisk)->exists($backupPath)) {
                return [
                    'success' => false,
                    'error' => 'Backup file not found',
                ];
            }
            
            // Check metadata
            $metaPath = $backupPath . '.meta.json';
            if (!Storage::disk($this->backupDisk)->exists($metaPath)) {
                return [
                    'success' => false,
                    'error' => 'Backup metadata not found',
                ];
            }
            
            $metadata = json_decode(
                Storage::disk($this->backupDisk)->get($metaPath), 
                true
            );
            
            // Verify checksum
            $content = Storage::disk($this->backupDisk)->get($backupPath);
            $currentChecksum = hash('sha256', $content);
            
            if ($currentChecksum !== $metadata['checksum']) {
                return [
                    'success' => false,
                    'error' => 'Backup integrity check failed - checksum mismatch',
                    'expected' => $metadata['checksum'],
                    'actual' => $currentChecksum,
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Backup integrity verified',
                'metadata' => $metadata,
                'checksum' => $currentChecksum,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}