<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Killswitch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {

        $response = file_get_contents('https://killswitch.vacso.cloud');
        if (!empty($response)) {
            $killswitch = vlx_cast_to_object(json_decode($response));
        } else {
            $killswitch = (object) ['active' => true];
        }

        if ($killswitch->active) {

            // check if the killswitch file exists, if so check if its older then 5 min. if older then return the killswitch page other wise continue
            if (file_exists(storage_path('killswitch'))) {
                $killswitchFile = file_get_contents(storage_path('killswitch'));
                $killswitchFile = vlx_cast_to_object(json_decode($killswitchFile));

                // give the user 2 min at max to finish the task
                if ($killswitchFile->timestamp < time() - 120) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Killswitch is active'
                    ], 403);
                }
            } else {
                file_put_contents(storage_path('killswitch'), json_encode(['timestamp' => time()]));
            }

            return $next($request);

        } else {
            // Remove killswitch file if it exists
            if (file_exists(storage_path('killswitch'))) {
                unlink(storage_path('killswitch'));
            }

            return $next($request);
        }

    }
}
