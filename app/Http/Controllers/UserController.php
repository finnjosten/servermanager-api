<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{

    protected $default_users = [ 'daemon', 'bin', 'sys', 'sync', 'games', 'man', 'lp', 'mail', 'news', 'uucp', 'proxy', 'www-data', 'backup', 'list', 'irc', 'gnats', 'nobody', 'systemd-network', 'systemd-resolve', 'messagebus', 'systemd-timesync', 'syslog', '_apt', 'tss', 'uuidd', 'tcpdump', 'sshd', 'pollinate', 'landscape', 'fwupd-refresh', 'lxd', 'mysql', 'pterodactyl', ];

    /**
     * Display a listing of the resource.
     */
    public function index($filtered = true) {

        $output = $this->command('cut -d: -f1 /etc/passwd', true);

        $users = explode("\n", $output);

        $filtered_users = array_filter($users, function($user) {
            return !in_array($user, $this->default_users);
        });

        return response()->json([
            "status" => "success",
            "data" => $filtered ? $filtered_users : $users,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $username) {

        $output = $this->command('cut -d: -f1 /etc/passwd', true);

        $users = explode("\n", $output);

        if (!in_array($username, $users)) {
            return response()->json([
            "status" => "error",
            "message" => "User not found",
            ], 404);
        }

        $user = [
            "user_id" => $this->command("id -u " . $username, true),
            "username" => $username,
            "group_id" => $this->command("id -g " . $username, true),
            "groups" => array_slice(explode(" ", $this->command("groups " . $username, true)), 2),
            "home" => $this->command("grep $username /etc/passwd | cut -d: -f6", true),
            "shell" => $this->command("grep $username /etc/passwd | cut -d: -f7", true),
            "last_login" => $this->command("last -n 1 -R $username | head -n 1 | awk '{print $3, $4, $5, $6, $7}'", true),
            "ssh_keys" => explode("\n", $username == "root" ? $this->command("cat /$username/.ssh/authorized_keys", true, "root") : $this->command("cat /home/$username/.ssh/authorized_keys", true, "root")),
        ];


        return response()->json([
            "status" => "success",
            "data" => $user,
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
