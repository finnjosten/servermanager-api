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
}
