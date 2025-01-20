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

        foreach ($folders as $folder) {

            // if folder starts with __ ignore it
            if (str_starts_with(basename($folder), '__')) {
                continue;
            }

            $project = [
                "name" => basename($folder),
                "type" => "plain",
            ];

            // Check if it's a Laravel project
            if (file_exists($folder . '/artisan')) {
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
