<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCorsManually
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = [
            'http://localhost:8080', // Pastikan ini adalah origin frontend Anda
        ];
        $origin = $request->headers->get('Origin');

        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, Authorization, Accept, X-XSRF-TOKEN',
        ];

        if (in_array($origin, $allowedOrigins)) {
            $headers['Access-Control-Allow-Origin'] = $origin; // Spesifik, bukan '*'
            $headers['Access-Control-Allow-Credentials'] = 'true'; // Penting untuk withCredentials
        }
        // else {
            // Jika Anda ingin lebih ketat, Anda bisa mengembalikan error di sini jika origin tidak cocok
            // return response('Origin not allowed', 403);
        // }

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            // Hanya tambahkan header jika belum ada atau jika kita ingin menimpanya
            // (terutama Access-Control-Allow-Origin dan Access-Control-Allow-Credentials)
            if ($key === 'Access-Control-Allow-Origin' || $key === 'Access-Control-Allow-Credentials') {
                 if (isset($headers[$key])) { // Pastikan kita punya nilai untuk diset dari logika di atas
                    $response->headers->set($key, $headers[$key]);
                 }
            } else {
                $response->headers->set($key, $value);
            }
        }
        return $response;
    }
}
