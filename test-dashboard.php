<?php

require 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Livewire\Dashboard;

// Bootstrap Laravel
$app = new Application(getcwd());
$app->singleton('path', fn() => getcwd().'/app');
$app->singleton('path.config', fn() => getcwd().'/config');
$app->singleton('path.database', fn() => getcwd().'/database');
$app->singleton('path.lang', fn() => getcwd().'/lang');
$app->singleton('path.public', fn() => getcwd().'/public');
$app->singleton('path.resources', fn() => getcwd().'/resources');
$app->singleton('path.storage', fn() => getcwd().'/storage');

// Test dashboard data
try {
    $dashboard = new Dashboard();
    $dashboard->mount();
    
    echo "Chart Data:\n";
    var_dump($dashboard->chartData);
    
    echo "\nStats:\n";
    var_dump($dashboard->realTimeStats);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}