<?php

namespace SayaWRT\Unik;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * IdGenerator Class
 * @since 1.0.0
 */
class IdGenerator
{
    /**
     * Get type of a field
     *
     * @param string $table table name
     * @param string $field field name
     *
     * @throws Exception if field type return null
     * @return array
     *
     * @since 1.0.0
     */
    private function getFieldType(string $table, string $field): array
    {
        $connection = config('database.default');
        $driver = DB::connection($connection)->getDriverName();
        $database = DB::connection($connection)->getDatabaseName();

        $sql = match ($driver) {
            'mysql' => 'SELECT column_name AS "column_name",data_type AS "data_type",column_type AS "column_type" FROM information_schema.columns WHERE table_schema=:database AND table_name=:table',
            default => 'SELECT column_name AS "column_name",data_type AS "data_type" FROM information_schema.columns WHERE table_catalog=:database AND table_name=:table',
        };

        $rows = DB::select($sql, ['database' => $database, 'table' => $table]);
        $fieldType = null;
        $fieldLength = 20;

        foreach ($rows as $col) {
            if ($field === $col->column_name) {
                $fieldType = $col->data_type;
                if ($driver === 'mysql') {
                    preg_match("/(?<=\().+?(?=\))/", $col->column_type, $tblFieldLength);
                    if (count($tblFieldLength)) {
                        $fieldLength = $tblFieldLength[0];
                    }
                }
                break;
            }
        }

        if (is_null($fieldType)) {
            throw new Exception("$field not found in $table table");
        }
        return ['type' => $fieldType, 'length' => $fieldLength];
    }

    /**
     * Main function to generate ID
     *
     * @param array $configArr configuration to generate ID
     *
     * @throws Exception if id generation config invalid
     * @return string|int
     *
     * @since 1.0.0
     */
    public static function generate(array $configArr): string|int
    {
        if (empty($configArr['table'])) {
            throw new Exception('Must need a table name');
        }
        if (empty($configArr['length'])) {
            throw new Exception('Must specify the length of ID');
        }
        if (empty($configArr['prefix'])) {
            throw new Exception('Must specify a prefix of your ID');
        }

        if (isset($configArr['where'])) {
            if (!is_array($configArr['where'])) {
                throw new Exception('where clause must be an array, you provided string');
            }
            if (count($configArr['where']) === 0) {
                throw new Exception('where clause must need at least an array');
            }
        }

        $table = $configArr['table'];
        $field = $configArr['field'] ?? 'id';
        $prefix = $configArr['prefix'];
        $resetOnPrefixChange = $configArr['reset_on_prefix_change'] ?? false;
        $length = $configArr['length'];

        $fieldInfo = (new self)->getFieldType($table, $field);
        $tableFieldType = $fieldInfo['type'];
        $tableFieldLength = $fieldInfo['length'];

        if (in_array($tableFieldType, ['int', 'integer', 'bigint', 'numeric']) && !is_numeric($prefix)) {
            throw new Exception("$field field type is $tableFieldType but prefix is string");
        }

        if ($length > $tableFieldLength) {
            throw new Exception('Generated ID length is bigger than table field length');
        }

        $prefixLength = strlen($prefix);
        $idLength = $length - $prefixLength;
        $whereString = '';

        if (isset($configArr['where'])) {
            $whereString .= " WHERE ";
            foreach ($configArr['where'] as $row) {
                $whereString .= $row[0] . "=" . $row[1] . " AND ";
            }
        }
        $whereString = rtrim($whereString, 'AND ');

        $totalQuery = sprintf("SELECT count(%s) total FROM %s %s", $field, $configArr['table'], $whereString);
        $total = DB::select(trim($totalQuery));

        if ($total[0]->total) {
            if ($resetOnPrefixChange) {
                $maxQuery = sprintf("SELECT MAX(%s) AS maxid FROM %s WHERE %s LIKE '%s%%'", $field, $table, $field, $prefix);
            } else {
                $maxQuery = sprintf("SELECT MAX(%s) AS maxid FROM %s", $field, $table);
            }

            $queryResult = DB::select($maxQuery);
            $maxFullId = $queryResult[0]->maxid;

            $maxId = substr($maxFullId, $prefixLength, $idLength);
            return $prefix . str_pad((int)$maxId + 1, $idLength, '0', STR_PAD_LEFT);
        } else {
            return $prefix . str_pad(1, $idLength, '0', STR_PAD_LEFT);
        }
    }
}
