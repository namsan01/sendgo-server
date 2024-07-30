<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\PasswordBroker;

class CustomPasswordController extends Controller
{
    /**
     * Send a password reset link to the user's email address.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No user found with this email address.'], 404);
        }

        // Generate a password reset token
        $token = Password::createToken($user);

        // Create a password reset URL
        $resetUrl = 'http://localhost:5173/reset-password?token=' . $token;

        // Send the reset link via email
        Notification::send($user, new ResetPasswordNotification($resetUrl));

        return response()->json(['message' => 'Password reset link sent.']);
    }

    /**
     * Handle a password reset request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Attempt to reset the password
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        if ($response == PasswordBroker::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully.']);
        }

        return response()->json(['message' => 'Failed to reset password.'], 400);
    }
}
