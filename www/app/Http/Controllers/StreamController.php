<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamController extends Controller
{
    public function subscribe(Request $request)
    {
        // Enforce FPM to never timeout for this stream
        set_time_limit(0);
        // Disable output buffering
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $userId = Auth::id();
        if (!$userId) {
            abort(401);
        }

        $response = new StreamedResponse(function() use ($userId) {
            // Send initial connection event
            echo "event: connected\n";
            echo "data: {\"status\": \"ok\"}\n\n";
            ob_flush();
            flush();

            // We use Redis Pub/Sub directly
            // The channel will be named 'user-channel-{user_id}'
            $channel = "user-channel-{$userId}";

            // Loop and subscribe. Note: Redis::subscribe is blocking.
            // Be careful, it will block this FPM worker indefinitely until client disconnects
            // Normally, connecting via predis or phpredis and subscribing:
            try {
                $redis = Redis::connection();
                $redis->subscribe([$channel], function ($message) {
                    // When a message arrives in redis, we push it to the SSE client
                    echo "event: message\n";
                    echo "data: " . $message . "\n\n";
                    ob_flush();
                    flush();
                });
            } catch (\Exception $e) {
                echo "event: error\n";
                echo "data: {\"error\": \"".$e->getMessage()."\"}\n\n";
                ob_flush();
                flush();
            }

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Critical for Nginx
            'Connection' => 'keep-alive',
        ]);

        return $response;
    }
}
