<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HardwareController extends Controller
{

    private $disk_name;
    private $disk_type;
    private $network_fqdn;
    private $network_traffic;
    private $network_uplink;

    public function __construct() {
        $this->disk_name = vlx_get_env_string('DISK_NAME');
        $this->disk_type = vlx_get_env_string('DISK_TYPE');
        $this->network_fqdn = vlx_get_env_string('NETWORK_FQDN');
        $this->network_traffic = vlx_get_env_string('NETWORK_TRAFFIC');
        $this->network_uplink = vlx_get_env_string('NETWORK_UPLINK');
    }

    /**
     * Return cpu info
     */
    public function cpu($data_only = false) {

        $CPU = [
            'model' => $this->command("cat /proc/cpuinfo  | grep 'model name' | uniq", true) ?? null,
            'cpu_mhz' => $this->command("cat /proc/cpuinfo  | grep 'cpu MHz' | uniq", true) ?? null,
            'cpu_cores' => $this->command("cat /proc/cpuinfo  | grep 'cpu cores' | uniq", true) ?? null,
            'cache_size' => $this->command("cat /proc/cpuinfo  | grep 'cache size' | uniq", true) ?? null,
        ];

        foreach ($CPU as $key => $value) {
            $CPU[$key] = explode(": ", $value)[1] ?? null;
        }

        if ($data_only) {
            return $CPU;
        }
        return response()->json([
            "status" => "success",
            "data" => $CPU,
        ]);
    }

    /**
     * Return memory info
     */
    public function memory($data_only = false) {

        $memory = [
            'size' => $this->command("sudo dmidecode --type memory | grep  'Size:'", true) ?? null,
            'speed' => $this->command("sudo dmidecode --type memory | grep  'Speed:' | grep -v 'Configured'", true) ?? null,
            'type' => $this->command("sudo dmidecode --type memory | grep  'Type:'  | grep -v 'Error'", true) ?? null,
            'form_factor' => $this->command("sudo dmidecode --type memory | grep  'Form Factor:'", true) ?? null,
            'error_correction_type' => $this->command("sudo dmidecode --type memory | grep  'Error Correction Type:'", true) ?? null,
        ];

        foreach ($memory as $key => $value) {
            $memory[$key] = explode(": ", $value)[1] ?? null;
        }

        if ($data_only) {
            return $memory;
        }
        return response()->json([
            "status" => "success",
            "data" => $memory,
        ]);
    }

    /**
     * Return disk info
     */
    public function disk($data_only = false) {

        $memory = [
            'size' => $this->command("lsblk -b -o NAME,SIZE | grep '^sda' | awk '{print $2/1024/1024/1024 \" GB\"}'", true) ?? null,
            'buffer_size' => $this->command("sudo hdparm -I /dev/sda | grep 'Buffer size' || echo 'Unknown'", true) ?? null,
            'type' => $this->disk_type ?? null,
            'disk_name' => $this->disk_name ?? null,
            'mount_point' => $this->command("lsblk -o NAME,MOUNTPOINT | grep 'sda1'", true) ?? null,
        ];

        $memory['mount_point'] =  explode("  ",explode("\n", $memory['mount_point'])[0])[1];

        if ($data_only) {
            return $memory;
        }
        return response()->json([
            "status" => "success",
            "data" => $memory,
        ]);
    }

    /**
     * Return the network info
     */
    public function network($data_only = false) {

        $network = [
            'ipv4' => $this->command('curl -s https://ipinfo.io/ip', true),
            'ipv6' => $this->command('curl -s https://v6.ipinfo.io/ip', true),
            'fqdn' => $this->network_fqdn ?? null,
            'traffic' => $this->network_traffic ?? null,
            'uplink' => $this->network_uplink ?? null,
        ];

        if ($data_only) {
            return $network;
        }
        return response()->json([
            "status" => "success",
            "data" => $network,
        ]);
    }

}
