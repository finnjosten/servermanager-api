<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use function Illuminate\Log\log;

class WebserverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        // Get all files from the /etc/nginx/sites-enabled directory
        $enabled_files = scandir('/etc/nginx/sites-enabled');

        // Remove the first two elements from the array
        $enabled_files = array_slice($enabled_files, 2);

        $enabled = [];
        if (!empty($enabled_files)) {
            foreach ($enabled_files as $file) {
                if (str_contains($file, 'default') || str_contains($file, '.bak')) {
                    continue;
                }

                $content = file_get_contents('/etc/nginx/sites-enabled/' . $file);
                $data = $this->vlx_process_file($content);

                $enabled[$file] = [
                    'root' => $data['root'] ?? null,
                    'proxy' => $data['proxy'] ?? null,
                    'ports' => $data['ports'] ?? null,
                    'server_name' => $data['server_name'] ?? null,

                    'ssl' => [
                        'enabled' => $data['ssl'] ?? false,
                        'cert' => $data['ssl_cert_location'] ?? null,
                        'key' => $data['ssl_key_location'] ?? null,
                    ],

                    'content' => $content,
                ];
            }
        }


        // Get all files from the /etc/nginx/sites-available directory
        $disabled_files = scandir('/etc/nginx/sites-available');

        // Remove the first two elements from the array
        $disabled_files = array_slice($disabled_files, 2);

        // remove all the files that are already enabled (there should be none but just making sure)
        if (!empty($enabled_files)) {
            foreach ($enabled_files as $file) {
                if (($key = array_search($file, $disabled_files)) !== false) {
                    unset($disabled_files[$key]);
                }
            }
        }

        $disabled = [];
        if (!empty($disabled_files)) {
            foreach ($disabled_files as $file) {
                if (str_contains($file, 'default') || str_contains($file, '.bak')) {
                    continue;
                }

                $content = file_get_contents('/etc/nginx/sites-available/' . $file);
                $data = $this->vlx_process_file($content);

                $disabled[$file] = [
                    'root' => $data['root'] ?? null,
                    'proxy' => $data['proxy'] ?? null,
                    'ports' => $data['ports'] ?? null,
                    'server_name' => $data['server_name'] ?? null,

                    'ssl' => [
                        'enabled' => $data['ssl'] ?? false,
                        'cert' => $data['ssl_cert_location'] ?? null,
                        'key' => $data['ssl_key_location'] ?? null,
                    ],

                    'content' => '$content',
                ];
            }
        }


        return response()->json([
            "status" => "success",
            "data" => [
                'enabled' => $enabled,
                'disabled' => $disabled,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {

        $data = $request->all();

        if (empty($data['content'])) {
            return response()->json([
                "status" => "error",
                "message" => "Content is required"
            ]);
        }

        if (empty($data['file_name'])) {
            return response()->json([
                "status" => "error",
                "message" => "File name is required"
            ]);
        }

        if (str_contains($data['file_name'], '..') || str_contains($data['file_name'], '/') || str_contains($data['file_name'], '\\')) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid file name"
            ]);
        }

        $enabled = boolval($data['enabled'] ?? false);
        if ($enabled) {
            $file = "/etc/nginx/sites-enabled/{$data['file_name']}";
            $temp_file = "/etc/nginx/sites-available/{$data['file_name']}";

            if (file_exists($file)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Config already exists in enabled configs"
                ]);
            }

            if (file_exists($temp_file)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Config already exists in disabled configs"
                ]);
            }
        } else {
            $file = "/etc/nginx/sites-available/{$data['file_name']}";
            $temp_file = "/etc/nginx/sites-enabled/{$data['file_name']}";

            if (file_exists($temp_file)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Config already exists in enabled configs"
                ]);
            }

            if (file_exists($file)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Config already exists in disabled configs"
                ]);
            }
        }

        try {
            $content = $data['content'];

            // make a new file and write the content
            $output = $this->command("touch $file", true);

            $output = $this->command("tee $file > /dev/null <<'EOF'\n$content\nEOF", true);

            // test the new config if it fails revert to the backup
            $output = $this->command("nginx -t", true);

            if (str_contains($output, '[warn]') && !str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);

                // get the message from nginx return between [warn] and the new line
                $warning = explode('[warn]', $output)[1];
                $warning = explode("\n", $warning)[0];

                return response()->json([
                    "status" => "warning",
                    "message" => "Nginx test returned a warning, but the config was created",
                    "warning" => trim($warning),
                ]);
            } else if (!str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
            } else {
                $this->command("rm $file", true);

                return response()->json([
                    "status" => "error",
                    "message" => "Nginx test failed"
                ]);
            }

        } catch (\Exception $e) {
            log()->error($e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to create config",
                "error" => $e->getMessage()
            ]);
        }


        return response()->json([
            "status" => "success",
            "message" => "Config created"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $file_name) {

        // Check if the file exists in the /etc/nginx/sites-enabled or /etc/nginx/sites-available directory
        $file = "/etc/nginx/sites-enabled/$file_name";
        if (!file_exists($file)) {
            $file = "/etc/nginx/sites-available/$file_name";
        }

        if (!file_exists($file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config could not be found"
            ]);
        }

        $content = file_get_contents($file);
        $data = $this->vlx_process_file($content);

        return response()->json([
            "status" => "success",
            "data" => [
                'id' => $file_name,
                'root' => $data['root'] ?? null,
                'proxy' => $data['proxy'] ?? null,
                'ports' => $data['ports'] ?? null,
                'server_name' => $data['server_name'] ?? null,

                'ssl' => [
                    'enabled' => $data['ssl'] ?? false,
                    'cert' => $data['ssl_cert_location'] ?? null,
                    'key' => $data['ssl_key_location'] ?? null,
                ],

                'content' => $content,
            ]
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $file_name) {

        if (!$request->has('content')) {
            return response()->json([
                "status" => "error",
                "message" => "Content is required"
            ]);
        }

        if (str_contains($file_name, '..') || str_contains($file_name, '/') || str_contains($file_name, '\\')) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid file name"
            ]);
        }

        // Check if the file exists in the /etc/nginx/sites-enabled or /etc/nginx/sites-available directory
        $enabled = true;
        $file = "/etc/nginx/sites-enabled/$file_name";
        if (!file_exists($file)) {
            $enabled = false;
            $file = "/etc/nginx/sites-available/$file_name";
        }

        if (!file_exists($file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config could not be found"
            ]);
        }

        try {
            $content = $request->input('content');

            /**
             * Copy the file to a .bak file
             * then rm the file and create a new one with the same name
             * then write the content to the new file
             *
             * check if nginx is fine with the new config
             * if not, restore the backup
             * otherwise reload nginx
             */

            // make a backup of the file
            $output = $this->command("cp $file $file.bak", true);
            log($output);

            // remove the old file then create a new one
            $output = $this->command("rm $file", true);
            log($output);

            // make a new file and write the content
            $output = $this->command("touch $file", true);
            log($output);

            $output = $this->command("tee $file > /dev/null <<'EOF'\n$content\nEOF", true);
            log($output);

            // test the new config if it fails revert to the backup
            $output = $this->command("nginx -t", true);

            if (str_contains($output, '[warn]') && !str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
                $this->command("rm $file.bak", true);

                // get the message from nginx return between [warn] and the new line
                $warning = explode('[warn]', $output)[1];
                $warning = explode("\n", $warning)[0];

                return response()->json([
                    "status" => "warning",
                    "message" => "Nginx test returned a warning, but the config was created",
                    "warning" => trim($warning),
                ]);
            } else if (!str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
                $this->command("rm $file.bak", true);
            } else {
                $this->command("cp $file.bak $file", true);

                return response()->json([
                    "status" => "error",
                    "message" => "Nginx test failed"
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to update config"
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Config updated"
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $file_name) {

        if (str_contains($file_name, '..') || str_contains($file_name, '/') || str_contains($file_name, '\\')) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid file name"
            ]);
        }

        // Check if the file exists in the /etc/nginx/sites-enabled or /etc/nginx/sites-available directory
        $file = "/etc/nginx/sites-enabled/$file_name";
        if (!file_exists($file)) {
            $file = "/etc/nginx/sites-available/$file_name";
        }

        if (!file_exists($file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config could not be found"
            ]);
        }

        try {
            $output = $this->command("cp $file $file.bak", true);

            // remove the file
            $output = $this->command("rm $file", true);

            // test the new config if it fails revert to the backup
            $output = $this->command("nginx -t", true);

            if (str_contains($output, '[warn]') && !str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);

                // get the message from nginx return between [warn] and the new line
                $warning = explode('[warn]', $output)[1];
                $warning = explode("\n", $warning)[0];

                return response()->json([
                    "status" => "warning",
                    "message" => "Nginx test returned a warning, but the config was created",
                    "warning" => trim($warning),
                ]);
            } else if (!str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
            } else {
                $this->command("cp $file.bak $file", true);

                return response()->json([
                    "status" => "error",
                    "message" => "Nginx test failed"
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to delete config"
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Config deleted"
        ]);
    }

    /**
     * Cerbot the specified domain
     */
    public function certbot(string $file_name) {
        // Check if the file exists in the /etc/nginx/sites-enabled or /etc/nginx/sites-available directory

        $file = "/etc/nginx/sites-enabled/$file_name";
        if (!file_exists($file)) {
            $file = "/etc/nginx/sites-available/$file_name";
        }

        if (!file_exists($file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config could not be found"
            ]);
        }

        // get the server_name from the file

        $content = file_get_contents($file);
        $data = $this->vlx_process_file($content);

        $server_name = $data['server_name'] ?? null;

        if (empty($server_name)) {
            return response()->json([
                "status" => "error",
                "message" => "Server name could not be found",
                "content" => $data,
            ]);
        }

        // run certbot
        $output = $this->command("certbot --nginx -d $server_name", true);

        if (str_contains($output, 'Congratulations!')) {
            return response()->json([
                "status" => "success",
                "message" => "Certbot ran successfully",
            ]);
        } elseif (str_contains($output, 'No vhost exists with servername or alias')) {
            return response()->json([
                "status" => "error",
                "message" => "No vhost exists with servername or alias",
            ]);
        } elseif (str_contains($output, 'Certificate not yet due for renewal')) {
            return response()->json([
                "status" => "warning",
                "message" => "Certificate exists and is not yet due for renewal",
                "warning" => $output,
            ]);
        } elseif (str_contains($output, 'DNS problem')) {
            return response()->json([
                "status" => "error",
                "message" => "DNS doesnt appear to be setup for this domain",
            ]);
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Certbot failed",
                "error" => $output,
            ]);
        }
    }

    /**
     * enable the specified config
     */
    public function enable(string $file_name) {

        if (str_contains($file_name, '..') || str_contains($file_name, '/') || str_contains($file_name, '\\')) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid file name"
            ]);
        }

        // Check if the file exists in the /etc/nginx/sites-enabled or /etc/nginx/sites-available directory
        $file = "/etc/nginx/sites-available/$file_name";
        $disabled_file = "/etc/nginx/sites-enabled/$file_name";

        if (!file_exists($file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config could not be found"
            ]);
        }

        if (file_exists($disabled_file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config already exists in enabled configs"
            ]);
        }

        try {
            $output = $this->command("cp $file $file.bak", true);

            $output = $this->command("mv $file $disabled_file", true);

            // test the new config if it fails revert to the backup
            $output = $this->command("nginx -t", true);

            if (str_contains($output, '[warn]') && !str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
                $this->command("rm $file.bak", true);

                // get the message from nginx return between [warn] and the new line
                $warning = explode('[warn]', $output)[1];
                $warning = explode("\n", $warning)[0];

                return response()->json([
                    "status" => "warning",
                    "message" => "Nginx test returned a warning, but the config was created",
                    "warning" => trim($warning),
                ]);
            } else if (!str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
                $this->command("rm $file.bak", true);
            } else {
                $this->command("mv $file.bak $file", true);
                $this->command("rm $disabled_file", true);

                return response()->json([
                    "status" => "error",
                    "message" => "Nginx test failed"
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to disable config"
            ]);
        }
    }

    /**
     * disable the specified config
     */
    public function disable(string $file_name) {

        if (str_contains($file_name, '..') || str_contains($file_name, '/') || str_contains($file_name, '\\')) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid file name"
            ]);
        }

        // Check if the file exists in the /etc/nginx/sites-enabled or /etc/nginx/sites-available directory
        $file = "/etc/nginx/sites-enabled/$file_name";
        $disabled_file = "/etc/nginx/sites-available/$file_name";

        if (!file_exists($file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config could not be found"
            ]);
        }

        if (file_exists($disabled_file)) {
            return response()->json([
                "status" => "error",
                "message" => "Config already exists in disabled configs"
            ]);
        }

        try {
            $output = $this->command("cp $file $file.bak", true);

            $output = $this->command("mv $file $disabled_file", true);

            // test the new config if it fails revert to the backup
            $output = $this->command("nginx -t", true);

            if (str_contains($output, '[warn]') && !str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
                $this->command("rm $file.bak", true);

                // get the message from nginx return between [warn] and the new line
                $warning = explode('[warn]', $output)[1];
                $warning = explode("\n", $warning)[0];

                return response()->json([
                    "status" => "warning",
                    "message" => "Nginx test returned a warning, but the config was created",
                    "warning" => trim($warning),
                ]);
            } else if (!str_contains($output, '[emerg]')) {
                $this->command("systemctl reload nginx", true);
                $this->command("rm $file.bak", true);
            } else {
                $this->command("mv $file.bak $file", true);
                $this->command("rm $disabled_file", true);

                return response()->json([
                    "status" => "error",
                    "message" => "Nginx test failed"
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to disable config"
            ]);
        }
    }







    private function vlx_process_file($content) {

        $data = [];

        if (str_contains($content, '# managed by Certbot')) {
            $data['ssl'] = true;
        }

        // loop over all the lines
        $lines = explode("\n", $content);
        foreach ($lines as $line) {

            if (str_contains($line, '#')) {
                $line = trim(explode('#', $line)[0]);
            }

            if (str_contains($line, 'root ')) {
                $root = explode('root ', $line)[1];
                $root = str_replace(';', '', $root);

                $data['root'] = trim($root);
            }

            if (str_contains($line, 'proxy_pass ')) {
                $proxy = explode('proxy_pass ', $line)[1];
                $proxy = str_replace(';', '', $proxy);

                $data['proxy'] = trim($proxy);
            }

            // Grab SSL cert locations
            if (str_contains($line, 'ssl_certificate ')) {
                $ssl_cert = explode('ssl_certificate ', $line)[1];
                $ssl_cert = str_replace(';', '', $ssl_cert);

                $data['ssl_cert_location'] = trim($ssl_cert);
            }

            if (str_contains($line, 'ssl_certificate_key ')) {
                $ssl_key = explode('ssl_certificate_key ', $line)[1];
                $ssl_key = str_replace(';', '', $ssl_key);

                $data['ssl_key_location'] = trim($ssl_key);
            }

            // Grab ports
            if (str_contains($line, 'listen ')) {
                $port = explode('listen ', $line)[1];

                if (str_contains($port, 'ssl')) {
                    $port = explode(' ', $port)[0];
                }

                if (str_contains($port, '[::]:')) {
                    $port = str_replace('[::]:', '', $port) . " (IPv6)";
                }

                if (str_contains($port, 'default_server')) {
                    $port = str_replace(' default_server', '', $port);
                }

                $port = str_replace(';', '', $port);


                $data['ports'][] = trim($port);
            }

            // Grab server_name
            if (str_contains($line, 'server_name ')) {
                $server_name = explode('server_name ', $line)[1];
                $server_name = str_replace(';', '', $server_name);

                $data['server_name'] = trim($server_name);
            }
        }

        return $data;
    }
}
