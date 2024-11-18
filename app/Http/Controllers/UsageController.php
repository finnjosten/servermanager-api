<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsageController extends Controller
{



    /**
     * Get all usage
     */
    public function get_usage() {

        $startTime = microtime(true);

        $cpu_usage = $this->cpu_usage(null, true);
        $ram_usage = $this->ram_usage(true);
        $disk_usage = $this->disk_usage(true);

        $endTime = microtime(true);

        return response()->json([
            "status" => "success",
            "data" => [
                "cpu" => $cpu_usage['cpu'],
                "cores" => $cpu_usage['cores'],
                "ram" => $ram_usage,
                "disks" => $disk_usage,
                "execution_time" => ($endTime - $startTime) * 1000 . "ms", // execution time in milliseconds
            ],
        ]);
    }


    /**
     * CPU package usage
     */
    public function cpu_usage($core = null, $data_only = false) {

        $core_count = explode(": ", $this->command("cat /proc/cpuinfo  | grep 'cpu cores' | uniq", true))[1] ?? null;

        $mpstat_output = $this->command("mpstat -P ALL 1 1", true);
        $lines = explode("\n", $mpstat_output);
        foreach ($lines as $line) {
            if (preg_match('/^Average:\s+all\s+.*\s+(\d+\.\d+)$/', $line, $matches)) {
                $cpu = round(100 - (float)$matches[1], 2);
                continue;
            }
            if (preg_match('/^Average:\s+(\d+)\s+.*\s+(\d+\.\d+)$/', $line, $matches)) {
                $cores[(int)$matches[1]] = round(100 - (float)$matches[2], 2);
                continue;
            }
        }

        if ($core != null) {
            if ($core >= $core_count || $core < 0) {
                return response()->json([
                    "status" => "error",
                    "message" => "Core $core does not exist",
                ]);
            }
            $cores = $cores[$core];
        }

        if ($data_only) {
            return [
                "cpu" => $cpu,
                "cores" => $cores,
            ];
        }
        return response()->json([
            "status" => "success",
            "data" => [
                "cpu" => $cpu,
                "cores" => $cores,
            ],
        ]);
    }

    /**
     * CPU Core x usage
     */
    public function core_usage($core = null, $data_only = false) {
    }


    /**
     * RAM usage
     */
    public function ram_usage($data_only = false) {

        $usage = $this->command("free -m | awk 'NR==2{printf \"%.2f\\n\", $3/1024}'", true);

        if ($data_only) {
            return $usage;
        }
        return response()->json([
            "status" => "success",
            "data" => $usage,
        ]);
    }


    /**
     * Disk Usage
     */
    public function disk_usage($data_only = false) {
        $disks = [];
        $df_output = $this->command("df -BG --output=source,size,used,pcent", true);
        $lines = explode("\n", $df_output);
        foreach ($lines as $line) {
            if (preg_match('/^\/dev\/\S+\s+(\d+)G\s+(\d+)G\s+(\d+)%$/', $line, $matches)) {
            $disks[] = [
                "device" => str_replace("/dev/","",explode("           ", $matches[0])[0]),
                "size_gb" => (int)$matches[1],
                "used_gb" => (int)$matches[2],
                "percentage" => (int)$matches[3],
            ];
            }
        }

        if ($data_only) {
            return $disks;
        }
        return response()->json([
            "status" => "success",
            "data" => $disks,
        ]);
    }

}
