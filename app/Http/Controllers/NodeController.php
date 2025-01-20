<?php

namespace App\Http\Controllers;

use app\Http\Controllers\HardwareController;
use Illuminate\Http\Request;

class NodeController extends Controller
{

    /**
     * All node info (not usage as its meant to be called differently)
     */
    public function all() {

        $data = [];

        // Check if we have a cache file
        if (file_exists(storage_path('app/node.json'))) {
            $file = file_get_contents(storage_path('app/node.json'));

            // If the file is older than 1 day, delete it (and redo the request)
            if (filemtime(storage_path('app/node.json')) < strtotime('-1 day')) {
                unlink(storage_path('app/node.json'));
                return $this->all();
            }
            $data = json_decode($file, true);
        } else {

            $hardware = app('App\Http\Controllers\HardwareController');

            $os = $this->os(true);

            $cpu = $hardware->cpu(true);
            $memory = $hardware->memory(true);
            $disk = $hardware->disk(true);
            $network = $hardware->network(true);


            $data = [
                "os" => $os,
                "hardware" => [
                    "cpu" => $cpu,
                    "memory" => $memory,
                    "disk" => $disk,
                    "network" => $network,
                ],
            ];

            // Save a cache file
            $json_data = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents(storage_path('app/node.json'), $json_data);
        }

        $uptime = $this->uptime(true);

        $webapp = app('App\Http\Controllers\WebappController');
        $projects = $webapp->index(true);

        $data['uptime'] = $uptime;
        $data['webapps'] = $projects;

        return response()->json([
            "status" => "success",
            "data" => $data,
        ]);
    }













    /**
     * Node uptime
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
     * Node OS
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
     * Node IPv4
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
