<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LineLoginController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $channelId = (string) config('services.line_login.channel_id');

        if (blank($channelId)) {
            return redirect()->route('login')->withErrors([
                'line' => 'LINE 登入尚未完成設定，請稍後再試。',
            ]);
        }

        $state = Str::random(40);
        $request->session()->put('line_login_state', $state);

        return redirect()->away('https://access.line.me/oauth2/v2.1/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $channelId,
            'redirect_uri' => $this->callbackUrl(),
            'state' => $state,
            'scope' => 'profile',
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('line_login_state');
        $receivedState = (string) $request->query('state');

        if (blank($expectedState) || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->route('login')->withErrors(['line' => 'LINE 登入驗證失敗，請再試一次。']);
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->withErrors(['line' => '你已取消 LINE 登入。']);
        }

        $tokenResponse = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->string('code')->toString(),
            'redirect_uri' => $this->callbackUrl(),
            'client_id' => config('services.line_login.channel_id'),
            'client_secret' => config('services.line_login.channel_secret'),
        ]);

        if (! $tokenResponse->successful() || blank($tokenResponse->json('access_token'))) {
            report(new \RuntimeException('LINE Login access-token exchange failed.'));

            return redirect()->route('login')->withErrors(['line' => 'LINE 登入暫時無法完成，請稍後再試。']);
        }

        $profileResponse = Http::withToken($tokenResponse->json('access_token'))
            ->get('https://api.line.me/v2/profile');

        if (! $profileResponse->successful() || blank($profileResponse->json('userId'))) {
            report(new \RuntimeException('LINE Login profile request failed.'));

            return redirect()->route('login')->withErrors(['line' => '無法取得 LINE 個人資料，請稍後再試。']);
        }

        $lineUserId = (string) $profileResponse->json('userId');
        $displayName = Str::limit((string) $profileResponse->json('displayName', 'LINE 山友'), 255, '');

        $user = User::firstOrCreate(
            ['line_user_id' => $lineUserId],
            [
                'name' => $displayName,
                'email' => 'line_'.$lineUserId.'@line.local',
                'password' => Str::random(40),
            ],
        );

        $user->forceFill([
            'line_display_name' => $displayName,
            'line_picture_url' => $profileResponse->json('pictureUrl'),
        ])->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('lotteries.yushan', absolute: false));
    }

    private function callbackUrl(): string
    {
        return (string) config('services.line_login.callback_url', url('/auth/line/callback'));
    }
}
