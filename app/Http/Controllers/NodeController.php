<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NodeController extends Controller
{

    /**
     * Return the uptime
     */
    public function uptime($filtered = true) {

        $output = $this->command('uptime', true);

        $uptime = vlx_get_uptime($output);

        return response()->json([
            "status" => "success",
            "data" => $uptime,
        ]);
    }

    /**
     * Return the OS
     */
    public function os($filtered = true) {

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
