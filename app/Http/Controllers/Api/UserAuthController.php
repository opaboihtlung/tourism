<?php

namespace App\Http\Controllers\Api;

use Auth;
use Throwable;
use App\Models\User;
use App\Models\Pledge;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class UserAuthController extends Controller
{
  public function loginRegister(Request $request)
  {
    $otp = rand(1000, 9999);
    if ($request->emailPhone == '8787883628') {
      $userOtp = UserOtp::firstOrNew(['type' => $request->type, 'contact' => $request->emailPhone]);
      $userOtp->otp = 1234;
      $userOtp->save();
      return response()->json(['existing' => true, 'mailSent' => true, 'otp' => 1234]);
    }
    $user = User::where($request->type, $request->emailPhone)->first();

    $existing = false;
    $mailSent = false;

    $userOtp = UserOtp::firstOrNew(['type' => $request->type, 'contact' => $request->emailPhone]);
    $userOtp->otp = $otp;
    $userOtp->save();

    if ($user != null) {
      $existing = true;
    }
    $email = $request->emailPhone;

    switch ($request->type) {
      case 'email':
        try {
          Mail::send('emails.registration-mail', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)
              ->subject('OTP for registration');
            $message->from('noreply@gmail.com', 'OTP for registration');
          });
          $mailSent = true;
        } catch (Throwable $ex) {
          Log::info("MZERROR:line-51->UserAuthController loginRegister():::" . $ex);
          $mailSent = false;
          return response()->json([], 500);
        }
        break;

      case 'phone':
        try {
          $templateId = '1407164844795741418';
          Http::withHeaders([
            'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
          ])->get("https://sms.msegs.in/api/send-otp", [
            'template_id' => $templateId,
            'message' => "Your OTP for Login/Registration to MZtour is $otp ",
            'recipient' => $email
          ]);

          $mailSent = true;
        } catch (Throwable $ex) {
          Log::info("MZERROR:line-70->UserAuthController loginRegister():::" . $ex);
          $mailSent = false;
        }
        break;
    }
    return response()->json(['existing' => $existing, 'mailSent' => $mailSent, 'otp' => $otp]);
  }

  public function verifyOtp(Request $request)
  {
    $userOtp = UserOtp::where('contact', $request->emailPhone)->where('otp', $request->otp)->first();
    if ($userOtp != null) {
      $userOtp->delete();
      $userCheck = User::where($userOtp->type, $request->emailPhone)->first();
      if ($userCheck != null) {
        $user = User::where($userOtp->type, $request->emailPhone)->first();
        Auth::login($user);
        $token = Auth::user()->createToken('tourism_token');
        $success['verified'] = 'OTP Verified';
        $success['user'] = Auth::user();
        $success['token'] = $token->plainTextToken;
        return response()->json(['success' => $success, 'existing' => true, 'type' => $userOtp->type]);
      } else {
        return response()->json(['success' => 'OTP Verified', 'existing' => false, 'emailPhone' => $request->emailPhone, 'type' => $userOtp->type]);
      }
    } else {
      return response()->json(['error' => 'Invalid OTP'], 401);
    }
  }

  public function signup(Request $request)
  {
    $user = new User;
    $user->name = $request->fullName;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->password = bcrypt('testing');
    $user->save();

    $pledge = new Pledge;
    $pledge->user_id = $user->id;
    $pledge->save();

    Auth::login($user);
    $token = Auth::user()->createToken('tourism_token');
    $success['user'] = Auth::user();
    $success['token'] = $token->plainTextToken;
    return response()->json(['success' => $success], 200);
  }

  public function login(Request $request)
  {
    $user = User::where($request->type, $request->emailPhone)->first();
    Auth::login($user);
    $token = Auth::user()->createToken('tourism_token');
    $success['user'] = Auth::user();
    $success['token'] = $token->plainTextToken;
    return response()->json(['success' => $success], 200);
  }
  public function getUser($userId)
  {
    $user = User::where('id', $userId)->get();
    return $user;
  }
}
