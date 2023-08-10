<?php

namespace RKWhmcsUtils;

class Util
{
    public static function arrayEvery(array $arr, callable $predicate)
    {
        foreach ($arr as $e) {
            if (!call_user_func($predicate, $e)) {
                return false;
            }
        }
        return true;
    }

    public static function arrayFind(array $arr, callable $func): array
    {
        $results = [];

        foreach ($arr as $value)
            if ($func($value))
                $results[] = $value;

        return $results;
    }

    public static function arrayFindOne(array $arr, callable $func): bool
    {
        foreach ($arr as $value)
            if ($func($value))
                return $value;

        return false;
    }

    public static function makeCsv(array $rows): string
    {
        $fh = fopen('php://output', 'w');
        ob_start();
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        return ob_get_clean();
    }

    public static function exitWithJsonError($message, $code = 500) {
        http_response_code($code);
        header('content-type: application/json');
        echo json_encode(['error' => $message]);
        exit();
    }
}
