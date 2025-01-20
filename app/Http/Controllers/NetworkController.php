<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NetworkController extends Controller
{

    private $disallowed_ports = [
        22, // ssh
        25, // smtp
        53, // dns
        67, // dhcp
        68, // dhcp
        80, // http
        123, // ntp
        443, // https
        465, // smtps
        3306, // mysql
        5432, // postgres
        27017, // mongodb
    ];

    /**
     * Display a listing of the resource.
     */
    public function index($data_only = false) {

        // Get all the firewall ports
        $firewall_ports = explode("\n", $this->command("sudo ufw status", true) ?? null);

        // skip the first 5 and last 2 lines
        $firewall_ports = array_slice($firewall_ports, 5); // just some message about the return
        $firewall_ports = array_slice($firewall_ports, 0, -2); // just whitespace


        /// regex to filter the following
        // [ 2] 80                         ALLOW IN    Anywhere
        // filter out the [] and the extra spaces
        // then turn each item into its own array item

        foreach ($firewall_ports as $key => $port) {

            if (str_contains($port, 'v6')) {
                unset($firewall_ports[$key]);
                continue;
            }

            $firewall_ports[$key] = preg_split('/\s+\s+/', $firewall_ports[$key]);
            $firewall_ports[$key] = [
                'port' => $firewall_ports[$key][0],
                'action' => $firewall_ports[$key][1],
                'from' => $firewall_ports[$key][2],
            ];
        }


        if ($data_only) {
            return $firewall_ports;
        } else {
            return response()->json([
                "status" => "success",
                "data" => $firewall_ports,
            ]);
        }

    }

    /**
     * Set a new firewall rule
     */
    public function store(Request $request) {

        $data = $request->all();
        $port = $data['port'];
        $action = $data['action'] ?? "allow";
        $from = $data['from'] ?? "anywhere";

        if (empty($port)) {
            return response()->json([
                "status" => "error",
                "message" => "Port is required",
            ]);
        }

        if (in_array($port, $this->disallowed_ports)) {
            return response()->json([
                "status" => "error",
                "message" => "Port $port is not allowed to be edited",
            ]);
        }

        try {
            $command = "sudo ufw $action $port";
            $output = $this->command($command, true);

            if (str_contains($output, "ERROR")) {
                throw new \Exception($output);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
                "command" => $command,
            ]);
        }

        return response()->json([
            "status" => "success",
            "data" => $output,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($port) {

        // Get all the firewall ports
        $fwports =$this->index(true);
        $found_port = null;

        foreach ($fwports as $fwport) {
            if ($fwport['port'] == $port || $fwport['port'] == $port . "/tcp" || $fwport['port'] == $port . "/udp") {
                $found_port = $fwport;
                break;
            }
        }

        if ($found_port == null) {
            return response()->json([
                "status" => "error",
                "message" => "Port $port does not contain any rules",
            ]);
        }

        return response()->json([
            "status" => "success",
            "data" => $found_port,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function showLocked() {

        return response()->json([
            "status" => "success",
            "data" => $this->disallowed_ports,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($port) {

        if (in_array($port, $this->disallowed_ports)) {
            return response()->json([
                "status" => "error",
                "message" => "Port $port is not allowed to be edited",
            ]);
        }

        // Get all the firewall ports
        $fwports =$this->index(true);
        $found_port = null;

        foreach ($fwports as $fwport) {
            if ($fwport['port'] == $port || $fwport['port'] == $port . "/tcp" || $fwport['port'] == $port . "/udp") {
                $found_port = $fwport;
                break;
            }
        }

        if ($found_port == null) {
            return response()->json([
                "status" => "error",
                "message" => "Port $port does not contain any rules",
            ]);
        }

        try {
            $output = $this->command("sudo ufw delete " . strtolower($found_port['action']) . " " . $found_port['port'], true);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }

        return response()->json([
            "status" => "success",
            "data" => $output,
        ]);

    }
}
