<?php
require_once 'db/config.php';

// Simple migration runner
function run_migrations() {
    $pdo = db();
    $migrations_dir = __DIR__ . '/db/migrations';
    
    // Create migrations table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (migration VARCHAR(255) PRIMARY KEY, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

    // Get executed migrations
    $executed_migrations_stmt = $pdo->query("SELECT migration FROM migrations");
    $executed_migrations = $executed_migrations_stmt->fetchAll(PDO::FETCH_COLUMN);

    $migration_files = glob($migrations_dir . '/*.sql');
    sort($migration_files);

    foreach ($migration_files as $file) {
        $migration_name = basename($file);

        if (in_array($migration_name, $executed_migrations)) {
            continue; // Skip already executed migration
        }

        echo "Running migration: {$migration_name}...
";
        $sql = file_get_contents($file);
        
        try {
            $pdo->exec($sql);
            
            // Log the migration
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$migration_name]);
            
            echo "Migration {$migration_name} executed successfully.
";
        } catch (PDOException $e) {
            echo "Error running migration {$migration_name}: " . $e->getMessage() . "
";
            // Stop on error
            return false;
        }
    }
    
    echo "All new migrations have been executed.
";
    return true;
}

// Run it
run_migrations();

