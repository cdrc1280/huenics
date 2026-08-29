<?php

namespace App\Database;

use Illuminate\Database\SQLiteConnection;

class ExtendedSQLiteConnection extends SQLiteConnection
{
    public function getPdo()
    {
        $pdo = parent::getPdo();
        $this->registerSqlitePolyfills($pdo);
        return $pdo;
    }

    public function getReadPdo()
    {
        $pdo = parent::getReadPdo();
        $this->registerSqlitePolyfills($pdo);
        return $pdo;
    }

    protected function registerSqlitePolyfills($pdo): void
    {
        if (!$pdo || !method_exists($pdo, 'sqliteCreateFunction')) {
            return;
        }

        static $registered = [];
        $oid = spl_object_id($pdo);
        if (isset($registered[$oid])) {
            return;
        }
        $registered[$oid] = true;

        // Polyfill json_extract for environments where SQLite was compiled without JSON1 (e.g. AWS Lambda / Vercel PHP)
        $pdo->sqliteCreateFunction('json_extract', function ($json, $path) {
            if (empty($json) || empty($path)) {
                return null;
            }
            $data = json_decode((string) $json, true);
            if (!is_array($data)) {
                return null;
            }

            // Path format: '$."format"' or '$.format' or '$.user.name'
            $cleanPath = trim(str_replace(['$', '"', "'", '[', ']'], '', $path));
            $parts = explode('.', $cleanPath);
            $val = $data;
            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }
                if (is_array($val) && array_key_exists($part, $val)) {
                    $val = $val[$part];
                } else {
                    return null;
                }
            }

            return is_scalar($val) ? $val : json_encode($val);
        });
    }
}
