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

        $cpu_usage = $this->cpu_usage(true);
        $core_usage = $this->core_usage(null, true);
        $ram_usage = $this->ram_usage(true);

        $endTime = microtime(true);

        return response()->json([
            "status" => "success",
            "data" => [
                "cpu" => $cpu_usage,
                "cores" => $core_usage,
                "ram" => $ram_usage,
                "execution_time" => ($endTime - $startTime) * 1000 . "ms", // execution time in milliseconds
            ],
        ]);
    }


    /**
     * CPU package usage
     */
    public function cpu_usage($data_only = false) {

        $usage = $this->command("top -bn1 | grep \"Cpu(s)\" | awk '{print $2 + $4}'", true);

        if ($data_only) {
            return $usage;
        }
        return response()->json([
            "status" => "success",
            "data" => $usage,
        ]);
    }

    /**
     * CPU Core x usage
     */
    public function core_usage($core = null, $data_only = false) {

        $core_count = explode(": ", $this->command("cat /proc/cpuinfo  | grep 'cpu cores' | uniq", true))[1] ?? null;

        if ($core != null) {
            if ($core >= $core_count || $core < 0) {
            return response()->json([
                "status" => "error",
                "message" => "Core $core does not exist",
            ]);
            }
            $usage = $this->command("mpstat -P $core 1 1 | awk '$2 ~ /^[0-9]+$/ && /Average/ {printf \"%.2f\\n\", 100 - \$NF}'", true);
        } else {
            $mpstat_output = $this->command("mpstat -P ALL 1 1", true);
            $lines = explode("\n", $mpstat_output);
            $usage = [];
            foreach ($lines as $line) {
                if (preg_match('/^Average:\s+all/', $line)) {
                    continue;
                }
                if (preg_match('/^Average:\s+(\d+)\s+.*\s+(\d+\.\d+)$/', $line, $matches)) {
                    $usage[(int)$matches[1]] = round(100 - (float)$matches[2], 2);
                }
            }
        }

        if ($data_only) {
            return $usage;
        }
        return response()->json([
            "status" => "success",
            "data" => $usage,
        ]);
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

}
