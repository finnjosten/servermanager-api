<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebappController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($data_only) {

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

        try {
            $data = $request->all();
            $folder = '/var/www/vhost/' . $id;

            if (file_exists($folder . '/meta.json')) {
                $meta = json_decode(file_get_contents($folder . '/meta.json'), true);
            } else {
                $meta = [];
            }

            $meta['project_name']   = $data['project_name'];
            $meta['public_address'] = $data['public_address'];
            $meta['description']    = $data['description'];
            $meta['created_at']     = $data['created_at'];
            $meta['repository_url'] = $data['repository_url'];
            $meta['environment']    = $data['environment'];
            $meta['notes']          = $data['notes'];

            file_put_contents($folder . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Error updating project",
            ], 500);
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
