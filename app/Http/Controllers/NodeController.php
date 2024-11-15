<?php

namespace App\Http\Controllers;

use app\Http\Controllers\HardwareController;
use Illuminate\Http\Request;

class NodeController extends Controller
{

    /**
     * Return everything
     */
    public function all() {

        $hardware = app('App\Http\Controllers\HardwareController');

        $uptime = $this->uptime(true);
        $os = $this->os(true);

        $cpu = $hardware->cpu(true);
        $memory = $hardware->memory(true);
        $disk = $hardware->disk(true);
        $network = $hardware->network(true);

        return response()->json([
            "status" => "success",
            "data" => [
                "uptime" => $uptime,
                "os" => $os,
                "hardware" => [
                    "cpu" => $cpu,
                    "memory" => $memory,
                    "disk" => $disk,
                    "network" => $network,
                ],
            ],
        ]);
    }













    /**
     * Return the uptime
     */
    public function uptime($data_only = false) {

        $output = $this->command('uptime', true);

        $uptime = vlx_get_uptime($output);

        if ($data_only) {
            return $uptime;
        }
        return response()->json([
            "status" => "success",
            "data" => $uptime,
        ]);
    }

    /**
     * Return the OS
     */
    public function os($data_only = false) {

        $output = $this->command('uname -a', true);

        $output = strtolower($output);

        // Return nice name based on the output
        if (str_contains($output, 'ubuntu')) {
            $os = 'ubuntu';
        } elseif (str_contains($output, 'debian')) {
            $os = 'debian';
        } elseif (str_contains($output, 'fedora')) {
            $os = 'fedora';
        } elseif (str_contains($output, 'suse')) {
            $os = 'suse';
        } elseif (str_contains($output, 'redhat')) {
            $os = 'redhat';
        } elseif (str_contains($output, 'centos')) {
            $os = 'centos';
        } else {
            $os = $output;
        }

        if ($data_only) {
            return $os;
        }
        return response()->json([
            "status" => "success",
            "data" => $os,
        ]);
    }


    /**
     * Return the IP
     */
    public function ip() {

        $output = $this->command('curl -s https://ipinfo.io/ip', true);

        $ip = $output;

        return response()->json([
            "status" => "success",
            "data" => $ip,
        ]);
    }

}
