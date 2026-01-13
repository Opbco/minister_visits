<?php

namespace App\Service;

class Base64FileExtractor
{

    public function extractBase64String(string $base64Content)
    {

        $data = explode(';base64,', $base64Content);
        $mimeType = explode(':', $data[0]);
        return [$data[1], $mimeType[1]];
    }

    function PUT(string $name): string
    {

        $lines = file('php://input');
        $keyLinePrefix = 'Content-Disposition: form-data; name="';

        $PUT = [];
        $findLineNum = null;

        foreach ($lines as $num => $line) {
            if (strpos($line, $keyLinePrefix) !== false) {
                if ($findLineNum) {
                    break;
                }
                if ($name !== substr($line, 38, -3)) {
                    continue;
                }
                $findLineNum = $num;
            } else if ($findLineNum) {
                $PUT[] = $line;
            }
        }

        array_shift($PUT);
        array_pop($PUT);

        return mb_substr(implode('', $PUT), 0, -2, 'UTF-8');
    }
}
