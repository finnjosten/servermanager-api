<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Process;

abstract class Controller {
    public function command($command, $sudo = false, $user = 'servermanager') {

        if ($sudo) {
            $command = "sudo -u $user $command";
        }
        $output = Process::run("$command");


        return trim($output->successful() ? $output->output() : $output->errorOutput());
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
