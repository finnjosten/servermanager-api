<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Process;

use function Illuminate\Log\log;

abstract class Controller {
    public function command($command, $sudo = false, $user = 'servermanager') {

        if ($sudo) {
            $command = "sudo $command";
        }
        $output = Process::run("$command", null);

        return $output->output();
    }

    public function apiCall($endpoint) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            return [ 'error' => curl_error($ch) ];
        }

        curl_close($ch);

        return vlx_cast_to_object(json_decode($output, true));
    }
}
