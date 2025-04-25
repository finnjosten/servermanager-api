<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebappController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($data_only = false) {

        // Get all the folders in /var/www/vhost
        $folders = array_filter(glob('/var/www/vhost/*'), 'is_dir');

        // Go through all folders and find out what type of project it is

        $projects = [];

        foreach ($folders as $i => $folder) {

            // if folder starts with __ ignore it
            if (str_starts_with(basename($folder), '__')) {
                continue;
            }

            $project = [
                "name" => basename($folder),
                "type" => "plain",
            ];

            if (file_exists($folder . '/artisan')) {
                // Check if it's a Laravel project
                $project["type"] = "laravel";
            } else if (file_exists($folder . '/wp-config.php')) {
                // Check if it's a WordPress project
                $project["type"] = "wordpress";
            } else if (file_exists($folder . '/package.json')) {
                // Check if it's a React project
                $project["type"] = "react";
            } else if (file_exists($folder . '/index.html') || file_exists($folder . '/index.htm')) {
                // Check if it's a static HTML project
                $project["type"] = "html";
            }

            if (file_exists($folder . '/meta.json')) {
                $meta = json_decode(file_get_contents($folder . '/meta.json'), true);
                $project["meta"] = $meta;
            }

            $project['id'] = $i;
            $project['location'] = $folder;

            $projects[] = $project;
            continue;
        }

        if ($data_only) {
            return $projects;
        }
        return response()->json([
            "status" => "success",
            "data" => $projects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $data = $request->all();

        // Setup for the project

        $validator = Validator::make($data, [
            'subdomain' => 'required',
            'domain' => 'required',
            'github_link' => 'nullable',
            'github_type' => 'nullable',
            'project_name' => 'required',
            'type' => 'required',
            'env_file' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Validation failed",
                "errors" => $validator->errors(),
            ], 400);
        }

        $folder = '/var/www/vhost/' . $data['project_name'];

        $illegal_chars = ['..', '/', '\\', ':', '*', '?', '"', '<', '>', '|', ' ', '#', '@', '$', '%', '^', '&', '(', ')', '{', '}', '[', ']', ';', "'", '`'];
        if (preg_match('/[' . preg_quote(implode('', $illegal_chars), '/') . ']/', $folder)) {
            return response()->json([
            "status" => "error",
            "message" => "Invalid project name",
            ], 400);
        }

        if (is_dir($folder)) {
            return response()->json([
                "status" => "error",
                "message" => "Project already exists",
            ], 400);
        } else {
            $this->command('mkdir ' . $folder);
        }

        $github = $data['github_link'] ?? null;
        $github_is_clone = $data['github_type'] == 'clone' ? true : false;
        $project_name = $data['project_name'];

        // Create the project
        try {
            switch($data['type']) {
                case 'laravel':
                    //$output = $this->command('bash ' . storage_path('/scripts/') . 'laravel.sh', true);
                    break;
                case 'wordpress':
                    //$output = $this->command('wp core download --path=' . $folder, true);
                    break;
                case 'react':
                    //$output = $this->command('npx create-react-app ' . $data['project_name'], true);
                    break;
                case 'html':
                    //file_put_contents($folder . '/index.html', '<!DOCTYPE html><html><head><title>' . $data['project_name'] . '</title></head><body><h1>' . $data['project_name'] . '</h1></body></html>');
                    break;
                default:
                    return response()->json([
                        "status" => "error",
                        "message" => "Invalid project type",
                    ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Error creating project; " . $e->getMessage(),
            ], 500);
        }

        // Create the meta file
        $meta = [
            "project_name" => $data['project_name'],
            "public_address" => "http://" . $data['subdomain'] . '.' . $data['domain'],
            "description" => null,
            "created_at" => date('Y-m-d'),
            "repository_url" => $data['github_link'],
            "environment" => null,
            "notes" => null,
        ];

        file_put_contents($folder . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT));

        return response()->json([
            "status" => "success",
            "message" => "Project created",
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show($project_name) {
        $folder = '/var/www/vhost/' . $project_name;

        if (!is_dir($folder)) {
            $project = null;
        } else {
            $project = [
                "name" => basename($folder),
                "type" => "plain",
            ];

            if (file_exists($folder . '/artisan')) {
                // Check if it's a Laravel project
                $project["type"] = "laravel";
            } else if (file_exists($folder . '/wp-config.php')) {
                // Check if it's a WordPress project
                $project["type"] = "wordpress";
            } else if (file_exists($folder . '/package.json')) {
                // Check if it's a React project
                $project["type"] = "react";
            } else if (file_exists($folder . '/index.html') || file_exists($folder . '/index.htm')) {
                // Check if it's a static HTML project
                $project["type"] = "html";
            }

            if (file_exists($folder . '/meta.json')) {
                $meta = json_decode(file_get_contents($folder . '/meta.json'), true);
                $project["meta"] = $meta;
            } else {
                $project["meta"] = [];
            }

            $project['id'] = 0;
            $project['location'] = $folder;
        }

        if ($project == null) {
            return response()->json([
                "status" => "error",
                "message" => "Project not found",
            ], 404);
        }

        return response()->json([
            "status" => "success",
            "data" => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        /* return response()->json([
            "status" => "error",
            "message" => "Not implemented",
            "data" => $request->all(),
        ], 501); */

        if (!is_dir('/var/www/vhost/' . $id)) {
            return response()->json([
                "status" => "error",
                "message" => "Project not found",
            ], 404);
        }

        if (empty($id)) {
            return response()->json([
                "status" => "error",
                "message" => "Project folder name (id) is required",
            ], 400);
        }

        $data = $request->all();
        $folder = '/var/www/vhost/' . $id;
        $meta = [];

        try {
            // See if the meta file exists if so get the meta and make a backup
            if (file_exists($folder . '/meta.json')) {
                $meta = json_decode(file_get_contents($folder . '/meta.json'), true);

                file_put_contents($folder . '/meta.json.bk', json_encode($meta, JSON_PRETTY_PRINT));
            }
        } catch (\Exception $e) {

            // Retry but now with the shell command
            try {
                if (file_exists($folder . '/meta.json')) {
                    $meta = json_decode(file_get_contents($folder . '/meta.json'), true);
                    $output = $this->command('echo \'' . json_encode($meta, JSON_PRETTY_PRINT) . '\' > ' . $folder . '/meta.json.bk', true);

                    return response()->json([
                        "status" => "error",
                        "message" => $output,
                        "command" => 'sudo cp ' . $folder . '/meta.json ' . $folder . '/meta.json.bk',
                    ], 500);
                }
            } catch (\Exception $e) {
                // Finally thrown an error after even the shell command failed
                return response()->json([
                    "status" => "error",
                    "message" => "Error updating project; " . $e->getMessage(),
                ], 500);
            }

        }

        $meta['project_name']   = $data['project_name'];
        $meta['public_address'] = $data['public_address'];
        $meta['description']    = $data['description'];
        $meta['created_at']     = $data['created_at'];
        $meta['repository_url'] = $data['repository_url'];
        $meta['environment']    = $data['environment'];
        $meta['notes']          = $data['notes'];

        try {
            // Write the meta file
            file_put_contents($folder . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT));
        }  catch (\Exception $e) {

            // Retry but now with the shell command
            try {
                $output = $this->command('echo \'' . json_encode($meta, JSON_PRETTY_PRINT) . '\' > ' . $folder . '/meta.json', true);

                return response()->json([
                    "status" => "error",
                    "message" => $output,
                ], 500);
            } catch (\Exception $e) {
                // Finally thrown an error after even the shell command failed
                return response()->json([
                    "status" => "error",
                    "message" => "Error updating project; " . $e->getMessage(),
                ], 500);
            }

        }

        return response()->json([
            "status" => "success",
            "message" => "Project updated",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
