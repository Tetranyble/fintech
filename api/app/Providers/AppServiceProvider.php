<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        /**
         * @param  $data  array
         * @param  $message  string
         * @param  $status_code  int
         * @param  $status  bool
         * @return JsonResource
         */
        Response::macro('success', function ($data, $message, $status_code = 200, $headers = []) {
            return \response()->json([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ], $status_code, $headers);
        });

        /**
         * @param  $data  array
         * @param  $message  string
         * @param  $status_code  int
         * @param  $status  bool
         * @return JsonResource
         */
        Response::macro('error', function (
            $data = [],
            $message = 'Something went wrong',
            $status_code = 500,
            $headers = []) {
            return \response()->json([
                'status' => false,
                'message' => $message,
                'data' => $data,
            ], $status_code, $headers);
        });

        /**
         * @param  $data  array
         * @param  $message  string
         * @param  $status_code  int
         * @param  $status  bool
         * @return JsonResource
         */
        Response::macro('aborts', function (
            $data = [],
            $message = 'Conflict',
            $status_code = 409,
            $headers = []) {
            return abort(
                response()->json([
                    'status' => false,
                    'message' => $message,
                    'data' => $data,
                ], $status_code, $headers)
            );
        });
    }
}
