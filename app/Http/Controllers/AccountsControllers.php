<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\LoginLoger;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use App\Models\SIK\SikProdi;
use Illuminate\Http\Request;
use App\Models\DesaIndonesia;
use App\Models\SKU\SkuSurvey;
use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikFakultas;
use App\Models\StrangerCounter;
use App\Models\SIK\SikUnitKerja;
use App\Models\ProvinsiIndonesia;
use App\Models\SKU\SkuPersonData;
use App\Models\FailedLoginAttempt;
use App\Models\KabupatenIndonesia;
use App\Models\KecamatanIndonesia;
use Laravel\Passport\RefreshToken;
use App\Models\SIK\SikExtraBiodata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\SikStaffFakultasModel;
use Illuminate\Support\Facades\Storage;
use App\Models\SIK\SikJabatanFungsional;
use App\Models\SIK\SikJabatanStruktural;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\SKU\PersonDataController;

$DEBUG_MODE = true;

function endsWithRnamikaze($string, $adder = "-rnamikaze")
{
    $suffix = $adder;
    $length = strlen($suffix);

    if (substr($string, -$length) === $suffix) {
        return true;
    } else {
        return false;
    }
}

function getActiveUserInfo()
{
    $user = User::find(intval(Auth::id()));
    $userInfo = [$user->id, $user->email, $user->name];

    return $userInfo;
}

function formatDateTime($datetime)
{
    // Parse the input datetime string using Carbon
    $carbonDatetime = Carbon::parse($datetime);

    // Format the datetime as per your requirement
    $formattedDatetime = $carbonDatetime->format('H:i d/m/Y');

    return $formattedDatetime;
}

function formatDateTimeNew($createdAt)
{
    Carbon::setLocale('id');

    $dateTime = Carbon::parse($createdAt);
    $currentDateTime = Carbon::now();

    $diff = $currentDateTime->diff($dateTime);

    // Indonesian
    // 3 char month array
    $monthSimplifiedArray = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agst",
        "Sept",
        "Okt",
        "Nov",
        "Des",
    ];

    // 3 char day array
    $daySimplifiedArray = [
        "Min",
        "Sen",
        "Sel",
        "Rab",
        "Kam",
        "Jum",
        "Sab"
    ];

    // day of the week
    $rnDayNmr = intval($dateTime->format('w'));
    // month digit without leading zero
    $rnMonthNmr = intval($dateTime->format('n'));

    // day 3 char representation based day of the week
    $currentDay3 = $daySimplifiedArray[$rnDayNmr];

    // month 3 char rep
    $currentMonth3 = $monthSimplifiedArray[$rnMonthNmr - 1];

    if ($diff->y > 0) {
        // More than 1 year ago
        return $currentDay3 . ', ' . $dateTime->format('j ') . $currentMonth3 . $dateTime->format(', H.i. Y') . ' (' . $diff->days . ' hari lalu)';
        // return $dateTime->format('D, j M, H.i. Y') . ' (' . $diff->days . ' hari lalu)';
    } elseif ($diff->days > 0) {
        // More than 1 day ago
        return $currentDay3 . ', ' . $dateTime->format('j ') . $currentMonth3 . $dateTime->format(', H.i') . ' (' . $diff->days . ' hari lalu)';
        // return $dateTime->format('D, j M, H.i') . ' (' . $diff->days . ' hari lalu)';
    } else {
        // Less than 1 day ago
        return $currentDay3 . ', ' . $dateTime->format('j ') . $currentMonth3 . $dateTime->format(', H.i') . ' (' . $diff->h . ' jam lalu)';
        // return $dateTime->format('D, j M, H.i') . ' (' . $diff->h . ' jam lalu)';
    }
}

function filterPhoneNumber($phoneNumber)
{
    // Check if the first two digits are already "62"
    if (substr($phoneNumber, 0, 2) !== "62") {
        // If not, replace the first digit with "62"
        $phoneNumber = "62" . substr($phoneNumber, 1);
    }
    return $phoneNumber;
}

function removeNonNumeric($input)
{
    // Use preg_replace to remove all characters except digits (0-9)
    $output = preg_replace('/\D/', '', $input);
    return $output;
}

// function getAllActiveShortLinks($isDescending = true)
// {
//     $allLinks = null;

//     if ($isDescending) {
//         $allLinks = ShortUrl::orderBy('created_at', 'desc')->get();
//     } else {
//         $allLinks = ShortUrl::get();
//     }

//     for ($i = 0; $i < sizeof($allLinks); $i++) {
//         $formattedDate = Carbon::parse($allLinks[$i]['updated_at'])->format('l, d/m/Y');
//         $allLinks[$i]['formatted_date'] = $formattedDate;

//         $formattedDate = Carbon::parse($allLinks[$i]['created_at'])->format('l, d/m/Y');
//         $allLinks[$i]['created_at_formatted'] = $formattedDate;
//     }

//     return $allLinks;
// }

// function getAllUsersAccount()
// {
//     $allUser = User::orderBy('created_at', 'desc')->get();

//     for ($i = 0; $i < sizeof($allUser); $i++) {
//         $allUser[$i]['total_links'] = ShortUrl::where('owned_by', intval($allUser[$i]['id']))->count();
//     }

//     return $allUser;
// }

function calculatePercentage($counts)
{
    // Calculate the total count
    $totalCount = array_sum($counts);

    $level1 = $counts[0] / $totalCount * 100;
    // echo round((($level1 * $counts[0])/100), 2)." ";
    $level2 = $counts[1] / $totalCount * 100;
    // echo (($level2 * $counts[1])/100)." ";
    $level3 = $counts[2] / $totalCount * 100;
    // echo (($level3 * $counts[2])/100)." ";

    $weight = round(($level1 * $counts[0]) / 100, 2) + round(($level2 * $counts[1]) / 100, 2) + round(($level3 * $counts[2]) / 100, 2);

    return round($weight, 2);
}

function isNumericString($input)
{
    // Check if the input is a string and has more than 5 characters
    if (is_string($input) && strlen($input) > 5) {
        // Check if all characters in the string are numbers
        if (ctype_digit($input)) {
            return true; // Return true if it meets the conditions
        }
    }
    return false; // Return false otherwise
}

function isEmail($email)
{
    // Regular expression for a valid email address
    $emailPattern = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    // Use preg_match to check if the email matches the pattern
    if (preg_match($emailPattern, $email)) {
        return true; // Valid email
    } else {
        return false; // Not a valid email
    }
}

function validateLatLon($coordinate)
{
    if ($coordinate === null) return null;
    // Latitude must be a number between -90 and 90
    $latitudePattern = '/^(\-?\d+(\.\d+)?),$/';

    // Longitude must be a number between -180 and 180
    $longitudePattern = '/^(\-?\d+(\.\d+)?)$/';

    // Check if the coordinate matches the latitude pattern
    if (preg_match($latitudePattern, $coordinate)) {
        $value = floatval($coordinate);
        if ($value >= -90 && $value <= 90) {
            return $coordinate;
        }
    }

    // Check if the coordinate matches the longitude pattern
    if (preg_match($longitudePattern, $coordinate)) {
        $value = floatval($coordinate);
        if ($value >= -180 && $value <= 180) {
            return $coordinate;
        }
    }

    // If it doesn't match either pattern, return null
    return null;
}

class AccountsControllers extends Controller
{
    //
    public $byPassLogerADD = "-nobypass-you-stupid-ass";

    public function ssoLogin(Request $req)
    {
        // Get user's IP address
        $ipAddress = $req->ip();

        // Get user agent
        $userAgent = $req->header('User-Agent');

        $shutDownStrangerLogger = env('RN_STRANGER_LOGER_SHUTDOWN', true);

        if ($shutDownStrangerLogger === false) {
            // Get user's IP address
            $ipAddress = $req->ip();

            // Get user agent
            $userAgent = $req->header('User-Agent');

            // Save user device info =====>
            // Create an instance of the Agent class
            $agent = new Agent();

            // Get the user agent string from the request
            $userAgent = $req->header('User-Agent');

            // Set the user agent string for the Agent instance
            $agent->setUserAgent($userAgent);

            // Get browser name
            $browser = ucwords($agent->browser());

            // Get device type
            $deviceType = ucwords($agent->deviceType());

            // Get the operating system. (Ubuntu, Windows, OS X, ...)
            $platform = $agent->platform();
            // Save user device info =====>

            // Is Robot
            $robot = 'false';

            if ($agent->isRobot()) {
                $robot = $agent->robot();
            }

            $mobileDevice = "false";
            $mobileType = "other";
            if (
                $agent->isMobile()
            ) {
                if ($agent->isTablet()) {
                    $mobileType = "Tablet";
                } else if ($agent->isPhone()) {
                    $mobileType = "Phone";
                }
                $mobileDevice = $agent->device();
            }

            // Temp Commented
            // $newStrangerConter = new StrangerCounter;

            // $newStrangerConter->ip_address = strval($ipAddress);
            // $newStrangerConter->user_agent = strval($userAgent);
            // $newStrangerConter->browser = $browser;
            // $newStrangerConter->device_type = $deviceType;
            // $newStrangerConter->is_robot = $robot;
            // $newStrangerConter->is_mobile = $mobileDevice;
            // $newStrangerConter->platform = $platform;

            // $newStrangerConter->save();
        }

        // OSC Report-In
        $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

        $appIdentifier = env("APP_OSC_IDENTIFIER");
        $oscBase = env("OSC_BASE");

        $agent = new Agent();
        $agent->setUserAgent($req->userAgent());

        $extraPayload = [
            'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
            'utm_source'    => $req->query('utm_source'),
            'utm_medium'    => $req->query('utm_medium'),
            'utm_campaign'  => $req->query('utm_campaign'),
            'platform'      => $agent->platform(),
            'browser'       => $agent->browser(),
            'device'        => $agent->device(),
            'is_mobile'     => $agent->isMobile(),
        ];

        $response = Http::withToken($oscExtLogToken)
            ->post($oscBase . '/api/log/report-in', [
                "what" => "Guest Visit at " . $appIdentifier,
                "ip" => $req->ip(),
                "extra" => json_encode($extraPayload)
            ]);

        if (Auth::viaRemember()) {
            $newLoger = new LoginLoger;

            $newLoger->master_id = Auth::id();
            $newLoger->action = "Login via Remember - " . Auth::user()->username;
            $newLoger->ip_log = strval($ipAddress);
            $newLoger->user_agent = strval($userAgent);
            $newLoger->save();

            return redirect()->route('sso.dashboard');
        }

        // return response()->json($req->header());

        return Inertia::render('Guest/NewLogin', ['loginStatus' => null]);
    }

    public function ssoLoginWarning(Request $req)
    {
        // OSC Report-In
        $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

        $appIdentifier = env("APP_OSC_IDENTIFIER");
        $oscBase = env("OSC_BASE");

        $agent = new Agent();
        $agent->setUserAgent($req->userAgent());

        $extraPayload = [
            'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
            'utm_source'    => $req->query('utm_source'),
            'utm_medium'    => $req->query('utm_medium'),
            'utm_campaign'  => $req->query('utm_campaign'),
            'platform'      => $agent->platform(),
            'browser'       => $agent->browser(),
            'device'        => $agent->device(),
            'is_mobile'     => $agent->isMobile(),
        ];

        $response = Http::withToken($oscExtLogToken)
            ->post($oscBase . '/api/log/report-in', [
                "what" => "Guest Visit at " . $appIdentifier . " [W]",
                "ip" => $req->ip(),
                "extra" => json_encode($extraPayload)
            ]);

        return Inertia::render('Guest/NewLogin', ['loginStatus' => null, 'warningText' => ['Anda Harus Login Dulu !', 'warning']]);
    }

    public function loginEmail(Request $req)
    {
        // return response()->json(["lat" => $req->latitude]);
        $cred = $req->validate([
            'email' => ['min:6', 'required'],
        ]);

        $latitude = validateLatLon($req->latitude);
        $longitude = validateLatLon($req->longitude);

        $emailVar = $cred['email'];

        $findUser = null;

        // By Pass Loger For Development
        $byPassLoger = false;

        if (endsWithRnamikaze(strval($cred['email']), $this->byPassLogerADD)) {
            $byPassLoger = true;

            $suffix = $this->byPassLogerADD;
            $length = strlen($suffix);

            $emailVar = substr($cred['email'], 0, -$length);
            $byPassLoger = true;
        }
        // =============================

        if (isNumericString($emailVar)) {
            $findUser = User::where(['nik' => $emailVar, 'active' => 1])->first();
        } else {
            if (isEmail($emailVar)) {
                $findUser = User::where(['email' => $emailVar, 'active' => 1])->first();
            }
            // else {
            //     $findUser = User::where('username', $cred['email'])->first();
            // }
        }

        // Save user device info =====>
        // Create an instance of the Agent class
        $agent = new Agent();

        // Get the user agent string from the request
        $userAgent = $req->header('User-Agent');

        // Set the user agent string for the Agent instance
        $agent->setUserAgent($userAgent);

        // Get browser name
        $browser = ucwords($agent->browser());

        // Get device type
        $deviceType = ucwords($agent->deviceType());

        // User IP Address & Port
        $ipAddress = $req->ip();
        $port = $req->getPort();
        // Save user device info =====>

        $username = 0;
        $loginPhase = [0, Str::random(10)];
        $isEmailRegistered = 0;

        $username_found = false;

        if ($findUser) {
            $username = $findUser->name;
            $loginPhase = [1, Str::random(10)];
            $isEmailRegistered = 1;
            $username_found = true;
        }

        return Inertia::render('Guest/NewLogin', [
            'username' => $username,
            'loginPhase' => $loginPhase,
            'emailWrong' => [$isEmailRegistered, Str::random(10)],
            'reqProcessing' => [false, Str::random(10)],
            'byPassLoger' => $byPassLoger
        ]);
    }

    public function userLoginEmail(Request $req)
    {
        $cred = $req->validate([
            "by" => "required|in:@rnamikaze",
            'email' => "min:6|required",
        ]);

        $latitude = validateLatLon($req->latitude);
        $longitude = validateLatLon($req->longitude);

        $emailVar = $cred['email'];

        $findUser = null;


        // By Pass Loger For Development
        $byPassLoger = false;

        if (endsWithRnamikaze(strval($cred['email']), $this->byPassLogerADD)) {
            $byPassLoger = true;

            $suffix = $this->byPassLogerADD;
            $length = strlen($suffix);

            $emailVar = substr($cred['email'], 0, -$length);
            $byPassLoger = true;
        }
        // =============================

        if (isNumericString($emailVar)) {
            $findUser = User::where(['nik' => $emailVar, 'active' => 1])->first();
        } else {
            if (isEmail($emailVar)) {
                $findUser = User::where(['email' => $emailVar, 'active' => 1])->first();
            }
        }

        // OSC Report-In
        $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

        $appIdentifier = env("APP_OSC_IDENTIFIER");
        $oscBase = env("OSC_BASE");

        $agent = new Agent();
        $agent->setUserAgent($req->userAgent());

        $extraPayload = [
            'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
            'utm_source'    => $req->query('utm_source'),
            'utm_medium'    => $req->query('utm_medium'),
            'utm_campaign'  => $req->query('utm_campaign'),
            'platform'      => $agent->platform(),
            'browser'       => $agent->browser(),
            'device'        => $agent->device(),
            'is_mobile'     => $agent->isMobile(),
        ];

        if ($findUser) {
            $username = $findUser->name;
            $isEmailRegistered = 1;

            $response = Http::withToken($oscExtLogToken)
                ->post($oscBase . '/api/log/report-in', [
                    "what" => "Email Check Success at " . $appIdentifier . " Login [$emailVar/$username]",
                    "ip" => $req->ip(),
                    "extra" => json_encode($extraPayload)
                ]);

            return response()->json([
                "success" => true,
                'username' => $username,
                "byPass" => $byPassLoger
            ], 201);
        }

        $response = Http::withToken($oscExtLogToken)
            ->post($oscBase . '/api/log/report-in', [
                "what" => "Email Check Fail at " . $appIdentifier . " Login [$emailVar]",
                "ip" => $req->ip(),
                "extra" => json_encode($extraPayload)
            ]);

        return response()->json([
            "success" => false,
            "reason" => "Email not Found " . $emailVar
        ]);
    }

    public function login(Request $req)
    {
        // global $DEBUG_MODE;
        $LOGER = env("APP_LOGER", true);

        // Get user's IP address
        $ipAddress = $req->ip();

        // Get user agent
        $userAgent = $req->header('User-Agent');

        // $credentials = $req->validate([
        //     'email' => ['min:5'],
        //     'password' => ['required'],
        //     'remember_me' => ['boolean']
        // ]);

        $validator = Validator::make($req->all(), [
            'email' => 'required|min:5',
            'password' => 'required|required',
            'remember_me' => 'required|boolean',
            'byPassLoger' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('sso.dashboard');
        }

        // Retrieve the validated input...
        $credentials = $validator->validated();

        $remember = $credentials['remember_me'];
        $emailVar = $credentials['email'];
        $specialByPass = false;

        if (endsWithRnamikaze($emailVar, $this->byPassLogerADD)) {
            $suffix = $this->byPassLogerADD;
            $length = strlen($suffix);

            $emailVar = substr($credentials['email'], 0, -$length);

            $specialByPass = true;
        }

        // Save user device info =====>
        // Create an instance of the Agent class
        $agent = new Agent();

        // Get the user agent string from the request
        $userAgent = $req->header('User-Agent');

        // Set the user agent string for the Agent instance
        $agent->setUserAgent($userAgent);

        // Get browser name
        $browser = ucwords($agent->browser());

        // Get device type
        $deviceType = ucwords($agent->deviceType());
        // Save user device info =====>

        // return response()->json($credentials);

        if (isNumericString($emailVar)) {
            if (Auth::attempt(['nik' => $emailVar, 'password' => $credentials['password'], 'active' => 1], $remember)) {

                $req->session()->regenerate();

                if ($LOGER === true) {
                    if ($specialByPass === false) {
                        $newLoger = new LoginLoger;

                        $newLoger->master_id = Auth::id();
                        $newLoger->action = "Login - " . $emailVar;
                        $newLoger->ip_log = strval($ipAddress);
                        $newLoger->user_agent = strval($userAgent);
                        $newLoger->device_name = $deviceType;
                        $newLoger->browser_name = $browser;
                        $newLoger->save();
                    }
                }

                $user = Auth::user();

                // Revoke all tokens issued to the user
                $tokens = $user->tokens;

                foreach ($tokens as $token) {
                    $token->revoke();

                    // Also delete refresh tokens associated with this access token
                    RefreshToken::where('access_token_id', $token->id)->delete();

                    // Delete the token
                    $token->delete();
                }

                // If account is Developer account
                // Redirect to Master Multi Account
                if ($emailVar === "3515132411980001") {
                    return to_route('dev.parked');
                }

                return redirect()->route('sso.dashboard');
            }
        } else {
            if (isEmail($emailVar)) {
                if (Auth::attempt(['email' => $emailVar, 'password' => $credentials['password'], 'active' => 1], $remember)) {
                    $req->session()->regenerate();

                    if ($LOGER === true) {
                        if ($specialByPass === false) {
                            $newLoger = new LoginLoger;

                            $newLoger->master_id = Auth::id();
                            $newLoger->action = "Login - " . $emailVar;
                            $newLoger->ip_log = strval($ipAddress);
                            $newLoger->user_agent = strval($userAgent);
                            $newLoger->device_name = $deviceType;
                            $newLoger->browser_name = $browser;
                            $newLoger->save();
                        }
                    }

                    $user = Auth::user();

                    // Revoke all tokens issued to the user
                    $tokens = $user->tokens;

                    foreach ($tokens as $token) {
                        $token->revoke();

                        // Also delete refresh tokens associated with this access token
                        RefreshToken::where('access_token_id', $token->id)->delete();

                        // Delete the token
                        $token->delete();
                    }

                    return redirect()->route('sso.dashboard');
                }
            }
            // if (Auth::attempt(['username' => $emailVar, 'password' => $credentials['password']])) {
            //     $req->session()->regenerate();

            //     return redirect()->route('sso.dashboard');
            // }
        }

        $findUser = null;

        if (isNumericString($emailVar)) {
            $findUser = User::where('nik', $emailVar)->first();
        } else {
            $findUser = User::where('email', $emailVar)->first();
        }

        $username = $findUser ? $findUser->name : "Selamat Bekerja";

        // return Inertia::render('Login', [
        //     'loginStatus' => false,
        //     "respon" => Str::random(15)
        // ]);


        return Inertia::render('Guest/NewLogin', [
            'username' => $username,
            'loginPhase' => $loginPhase = [1, Str::random(10)],
            'passwordTest' => Str::random(15)
        ]);
    }

    public function userSsoLogin(Request $req)
    {
        // global $DEBUG_MODE;
        $LOGER = env("APP_LOGER", true);

        // Get user's IP address
        $ipAddress = $req->ip();

        // Get user agent
        $userAgent = $req->header('User-Agent');

        $validator = Validator::make($req->all(), [
            'email' => 'required|min:5',
            'password' => 'required|required',
            'remember_me' => 'required|boolean',
            'byPassLoger' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('sso.dashboard');
        }

        // Retrieve the validated input...
        $credentials = $validator->validated();

        $remember = $credentials['remember_me'];
        $emailVar = $credentials['email'];
        $specialByPass = false;

        if (endsWithRnamikaze($emailVar, $this->byPassLogerADD)) {
            $suffix = $this->byPassLogerADD;
            $length = strlen($suffix);

            $emailVar = substr($credentials['email'], 0, -$length);

            $specialByPass = true;
        }

        // Save user device info =====>
        // Create an instance of the Agent class
        $agent = new Agent();

        // Get the user agent string from the request
        $userAgent = $req->header('User-Agent');

        // Set the user agent string for the Agent instance
        $agent->setUserAgent($userAgent);

        // Get browser name
        $browser = ucwords($agent->browser());

        // Get device type
        $deviceType = ucwords($agent->deviceType());
        // Save user device info =====>

        // return response()->json($credentials);

        // OSC Report-In
        $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

        $appIdentifier = env("APP_OSC_IDENTIFIER");
        $oscBase = env("OSC_BASE");

        $agent = new Agent();
        $agent->setUserAgent($req->userAgent());

        $extraPayload = [
            'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
            'utm_source'    => $req->query('utm_source'),
            'utm_medium'    => $req->query('utm_medium'),
            'utm_campaign'  => $req->query('utm_campaign'),
            'platform'      => $agent->platform(),
            'browser'       => $agent->browser(),
            'device'        => $agent->device(),
            'is_mobile'     => $agent->isMobile(),
        ];

        if (isNumericString($emailVar)) {
            if (Auth::attempt(['nik' => $emailVar, 'password' => $credentials['password'], 'active' => 1], $remember)) {

                $req->session()->regenerate();

                if ($LOGER === true && env('APP_ENV', 'local') === "production") {
                    if ($specialByPass === false) {
                        $newLoger = new LoginLoger;

                        $newLoger->master_id = Auth::id();
                        $newLoger->action = "Login - " . $emailVar;
                        $newLoger->ip_log = strval($ipAddress);
                        $newLoger->user_agent = strval($userAgent);
                        $newLoger->device_name = $deviceType;
                        $newLoger->browser_name = $browser;
                        $newLoger->save();
                    }
                }

                $user = Auth::user();

                // Revoke all tokens issued to the user
                $tokens = $user->tokens;

                foreach ($tokens as $token) {
                    $token->revoke();

                    // Also delete refresh tokens associated with this access token
                    RefreshToken::where('access_token_id', $token->id)->delete();

                    // Delete the token
                    $token->delete();
                }

                // If account is Developer account
                // Redirect to Master Multi Account
                if ($emailVar === "3515132411980001") {
                    // return to_route('dev.parked');
                    $response = Http::withToken($oscExtLogToken)
                        ->post($oscBase . '/api/log/report-in', [
                            "what" => "Auth Success at " . $appIdentifier . " Login [Developer Account]",
                            "ip" => $req->ip(),
                            "extra" => json_encode($extraPayload)
                        ]);

                    return response()->json([
                        "success" => true,
                        "is_dev" => true
                    ]);
                }

                $response = Http::withToken($oscExtLogToken)
                    ->post($oscBase . '/api/log/report-in', [
                        "what" => "Auth Success at " . $appIdentifier . " Login [UniqueID][$emailVar/$user->name]",
                        "ip" => $req->ip(),
                        "extra" => json_encode($extraPayload)
                    ]);

                // return redirect()->route('sso.dashboard');
                return response()->json([
                    "success" => true,
                    "is_dev" => false
                ]);
            }
        } else {
            if (isEmail($emailVar)) {
                if (Auth::attempt(['email' => $emailVar, 'password' => $credentials['password'], 'active' => 1], $remember)) {
                    $req->session()->regenerate();

                    if ($LOGER === true) {
                        if ($specialByPass === false) {
                            $newLoger = new LoginLoger;

                            $newLoger->master_id = Auth::id();
                            $newLoger->action = "Login - " . $emailVar;
                            $newLoger->ip_log = strval($ipAddress);
                            $newLoger->user_agent = strval($userAgent);
                            $newLoger->device_name = $deviceType;
                            $newLoger->browser_name = $browser;
                            $newLoger->save();
                        }
                    }

                    $user = Auth::user();

                    // Revoke all tokens issued to the user
                    $tokens = $user->tokens;

                    foreach ($tokens as $token) {
                        $token->revoke();

                        // Also delete refresh tokens associated with this access token
                        RefreshToken::where('access_token_id', $token->id)->delete();

                        // Delete the token
                        $token->delete();
                    }

                    $response = Http::withToken($oscExtLogToken)
                        ->post($oscBase . '/api/log/report-in', [
                            "what" => "Auth Success at " . $appIdentifier . " Login [Email][$emailVar/$user->name]",
                            "ip" => $req->ip(),
                            "extra" => json_encode($extraPayload)
                        ]);

                    // return redirect()->route('sso.dashboard');
                    return response()->json([
                        "success" => true,
                        "is_dev" => false
                    ]);
                }
            }
        }
        $findUser = null;

        if (isNumericString($emailVar)) {
            $findUser = User::where('nik', $emailVar)->first();
        } else {
            $findUser = User::where('email', $emailVar)->first();
        }

        $username = $findUser ? $findUser->name : "Selamat Bekerja";

        // return Inertia::render('Guest/NewLogin', [
        //     'username' => $username,
        //     'loginPhase' => $loginPhase = [1, Str::random(10)],
        //     'passwordTest' => Str::random(15)
        // ]);

        $response = Http::withToken($oscExtLogToken)
            ->post($oscBase . '/api/log/report-in', [
                "what" => "Auth Fail at " . $appIdentifier . " Login [$emailVar]",
                "ip" => $req->ip(),
                "extra" => json_encode($extraPayload)
            ]);

        return response()->json([
            "success" => false,
            "reason" => "account not found",
            "is_dev" => false
        ]);
    }

    public function getUserProfile()
    {
        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $step_1 = 0;
        $step_2 = 0;
        $SkuFinalValue = 0;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();

            // Calculate Rating SKU Value
            $step_1 = ($getSkuLevel2 + $getSkuLevel1 + $getSkuLevel3) * 3;
            $step_2 = ($getSkuLevel1 * 1) + ($getSkuLevel2 * 2) + ($getSkuLevel3 * 3);

            if ($step_1 < 1) {
                $SkuFinalValue = 0;
            } else {
                $SkuFinalValue = $step_2 * (100 / $step_1);
            }
        }



        if ($getSKU) {
            $surveySKUValue = [$getSkuLevel1, $getSkuLevel2, $getSkuLevel3];
        }

        $kab = "";
        $kec = "";
        $desa = "";
        $jastur = "";
        $telepon = "";
        $profile_img = null;

        if ($activeUserId === 1 || $activeUserId === 2 || $activeUserId === 3) {
            $kab = "Sidoarjo";
            $kec = "Sidoarjo";
            $desa = "Sidoarjo";

            $telepon = null;

            if ($activeUserId === 2 || $activeUserId === 3) {
                $jastur = "Admin";
            } else {
                $jastur = "Super Admin [UPT-TI]";
            }
        } else {
            $getBiodata = SikBiodata::where('master_id', intval($activeUserId))->select('id', 'fakultas_id', 'staff_fakultas_id', 'status', 'jabatan_struktural_id', 'telepon', 'master_id', 'img_storage')->first();
            $getExtraBiodata = SikExtraBiodata::where('biodata_id', intval($getBiodata->id))->select('desa_kel', 'kecamatan', 'kota_kab')->first();
            $getJabatanStruktural = SikJabatanStruktural::find($getBiodata->jabatan_struktural_id);
            $selectKota = KabupatenIndonesia::where('name_id', intval($getExtraBiodata->kota_kab))->first();
            $selectKecam = KecamatanIndonesia::where('name_id', intval($getExtraBiodata->kecamatan))->first();
            $selectDesa = DesaIndonesia::where('name_id', intval($getExtraBiodata->desa_kel))->first();

            $jastur = "Kosong";

            if ($getBiodata->status == 5) {
                $targetFakultas = SikFakultas::where('id', $getBiodata->fakultas_id)->select('name')->first();
                $targetStaffFakultas = SikStaffFakultasModel::find(intval($getBiodata->staff_fakultas_id));

                // Set default values if data is not found
                $fakultasName = $targetFakultas?->name ?? "Fakultas Kosong";
                $staffName = $targetStaffFakultas?->name ?? "Staff Fakultas Kosong";

                // Construct the string
                $jastur = $staffName . ", Fakultas " . ucwords($fakultasName);
            } else {
                $jastur = !$getJabatanStruktural ? "Kosong" : $getJabatanStruktural->name;
            }

            // $jastur = $getJabatanStruktural->name === null ? "Kosong" : $getJabatanStruktural->name;

            if ($getBiodata['img_storage'] !== null) {

                // $url = Storage::url('public/profile/' . $getBiodata->nik . "_" . $getBiodata->master_id . "/" . $getBiodata->img_storage);

                $url =  $getBiodata->img_storage;
                $profile_img =  $url;
            }

            if ($getExtraBiodata->kota_kab === null) {
                $kab = "Sidoarjo";
                $kec = "Sidoarjo";
                $desa = "Sidoarjo";
                // $jastur = "Kosong";
            } else {
                $kab = $selectKota->name === null ? "Kosong" : $selectKota->name;
                $kec = $selectKecam->name === null ? "Kosong" : $selectKecam->name;
                $desa = "Tunggu";
                if ($selectDesa) {
                    $desa = $selectDesa->name === null ? "Kosong" : $selectDesa->name;
                }
                // $jastur = $getJabatanStruktural->name === null ? "Kosong" : $getJabatanStruktural->name;
            }
            // echo "Tes";
            // $telepon = null;
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber($getBiodata->telepon);
        }



        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue, $SkuFinalValue];
        // $profileLoad = ["", "", "", "", [$selectKota, $selectKecam, $selectDesa, $getExtraBiodata]];

        // $profileLoad = "";

        for ($i = 0; $i < sizeof($userActive); $i++) {
            $userActive[$i]['allowed_app'] = unserialize($userActive[$i]['allowed_app_arr']);
        }

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            "userData" => $profileLoad,
        ]);
    }

    public function dashboardTest($status = null)
    {
        $activeUserId = Auth::id();

        // return response()->json($activeUserId);

        $userActive = User::where('id', intval($activeUserId))->get();

        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $step_1 = 0;
        $step_2 = 0;
        $SkuFinalValue = 0;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();

            // Calculate Rating SKU Value
            $step_1 = ($getSkuLevel2 + $getSkuLevel1 + $getSkuLevel3) * 3;
            $step_2 = ($getSkuLevel1 * 1) + ($getSkuLevel2 * 2) + ($getSkuLevel3 * 3);

            if ($step_1 < 1) {
                $SkuFinalValue = 0;
            } else {
                $SkuFinalValue = $step_2 * (100 / $step_1);
            }
        }

        if ($getSKU) {
            $surveySKUValue = [$getSkuLevel1, $getSkuLevel2, $getSkuLevel3];
        }

        $kab = "";
        $kec = "";
        $desa = "";
        $jastur = "";
        $telepon = "";
        $profile_img = null;

        if ($activeUserId === 1 || $activeUserId === 2 || $activeUserId === 3) {
            $kab = "Sidoarjo";
            $kec = "Sidoarjo";
            $desa = "Sidoarjo";

            $telepon = null;

            if ($activeUserId === 2 || $activeUserId === 3) {
                $jastur = "Admin";
            } else {
                $jastur = "Super Admin [UPT-TI]";
            }
        } else {
            $getBiodata = SikBiodata::where('master_id', intval($activeUserId))->select('id', 'jabatan_struktural_id', 'telepon', 'master_id', 'img_storage')->first();
            $getExtraBiodata = SikExtraBiodata::where('biodata_id', intval($getBiodata->id))->select('desa_kel', 'kecamatan', 'kota_kab')->first();
            $getJabatanStruktural = SikJabatanStruktural::find($getBiodata->jabatan_struktural_id);
            $selectKota = KabupatenIndonesia::where('name_id', intval($getExtraBiodata->kota_kab))->first();
            $selectKecam = KecamatanIndonesia::where('name_id', intval($getExtraBiodata->kecamatan))->first();
            $selectDesa = DesaIndonesia::where('name_id', intval($getExtraBiodata->desa_kel))->first();

            return response()->json($getExtraBiodata->desa_kel);

            $jastur = $getJabatanStruktural->name === null ? "Kosong" : $getJabatanStruktural->name;

            if ($getBiodata['img_storage'] !== null) {

                // $url = Storage::url('public/profile/' . $getBiodata->nik . "_" . $getBiodata->master_id . "/" . $getBiodata->img_storage);

                $url =  $getBiodata->img_storage;
                $profile_img =  $url;
            }

            if ($getExtraBiodata->kota_kab === null) {
                $kab = "Sidoarjo";
                $kec = "Sidoarjo";
                $desa = "Sidoarjo";
                // $jastur = "Kosong";
            } else {
                $kab = $selectKota->name === null ? "Kosong" : $selectKota->name;
                $kec = $selectKecam->name === null ? "Kosong" : $selectKecam->name;

                $desa = "Tunggu";
                if ($selectDesa) {
                    $desa = $selectDesa->name === null ? "Kosong" : $selectDesa->name;
                }
            }
            // echo "Tes";
            // $telepon = null;
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber($getBiodata->telepon);
        }



        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue, $SkuFinalValue];
        // $profileLoad = ["", "", "", "", [$selectKota, $selectKecam, $selectDesa, $getExtraBiodata]];

        // $profileLoad = "";

        for ($i = 0; $i < sizeof($userActive); $i++) {
            $userActive[$i]['allowed_app'] = unserialize($userActive[$i]['allowed_app_arr']);
        }

        $notAllowed = $status === null ? false : true;

        if ($status === null) {
            return Inertia::render('Sso/NewSsoDashboard', [
                "userAccount" => $userActive,
                "userData" => $profileLoad,
                "notAllowedAlert" => $notAllowed
            ]);
        } else {
            if ($status === "forbidden") {
                return Inertia::render('Sso/NewSsoDashboard', [
                    "userAccount" => $userActive,
                    "userData" => $profileLoad,
                    "notAllowedAlert" => $notAllowed
                ]);
            } else {
                return redirect('/not-found');
            }
        }




        // return Inertia::render('Sso/NewSsoDashboard', ["userAccount" => $userActive]);

    }

    public function dashboard($status = null)
    {
        $activeUserId = Auth::id();

        $userActive = User::where('id', intval($activeUserId))->get();

        $getSkuLevel3 = null;
        $getSkuLevel2 = null;
        $getSkuLevel1 = null;

        $step_1 = 0;
        $step_2 = 0;
        $SkuFinalValue = 0;

        $surveySKUValue = null;

        $getSKU = SkuPersonData::where(
            'nik',
            $userActive[0]['nik']
        )->first();

        if ($getSKU) {
            $getSkuLevel3 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 3)->count();
            $getSkuLevel2 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 2)->count();
            $getSkuLevel1 = SkuSurvey::where('nama_id', $getSKU->id)->where('level_survey_id', 1)->count();

            // Calculate Rating SKU Value
            $step_1 = ($getSkuLevel2 + $getSkuLevel1 + $getSkuLevel3) * 3;
            $step_2 = ($getSkuLevel1 * 1) + ($getSkuLevel2 * 2) + ($getSkuLevel3 * 3);

            if ($step_1 < 1) {
                $SkuFinalValue = 0;
            } else {
                $SkuFinalValue = $step_2 * (100 / $step_1);
            }
        }

        if ($getSKU) {
            $surveySKUValue = [$getSkuLevel1, $getSkuLevel2, $getSkuLevel3];
        }

        $kab = "";
        $kec = "";
        $desa = "";
        $jastur = "";
        $telepon = "";
        $profile_img = null;
        $unitId = null;

        if ($activeUserId === 1 || $activeUserId === 2 || $activeUserId === 3) {
            $kab = "Sidoarjo";
            $kec = "Sidoarjo";
            $desa = "Sidoarjo";

            $telepon = null;

            if ($activeUserId === 2 || $activeUserId === 3) {
                $jastur = "Admin";
            } else {
                $jastur = "Super Admin [UPT-TI]";
            }
        } else {
            $getBiodata = SikBiodata::where('master_id', intval($activeUserId))->select('id', 'fakultas_id', 'staff_fakultas_id', 'jabatan_struktural_id', 'status', 'telepon', 'master_id', 'img_storage', 'unit_id')->first();
            $getExtraBiodata = SikExtraBiodata::where('biodata_id', intval($getBiodata->id))->select('desa_kel', 'kecamatan', 'kota_kab')->first();
            $getJabatanStruktural = SikJabatanStruktural::find($getBiodata->jabatan_struktural_id);
            $selectKota = KabupatenIndonesia::where('name_id', intval($getExtraBiodata->kota_kab))->first();
            $selectKecam = KecamatanIndonesia::where('name_id', intval($getExtraBiodata->kecamatan))->first();
            $selectDesa = DesaIndonesia::where('name_id', intval($getExtraBiodata->desa_kel))->first();

            $jastur = "Kosong";

            if ($getBiodata->status == 5) {
                $targetFakultas = SikFakultas::where('id', $getBiodata->fakultas_id)->select('name')->first();
                $targetStaffFakultas = SikStaffFakultasModel::find(intval($getBiodata->staff_fakultas_id));

                // Set default values if data is not found
                $fakultasName = $targetFakultas?->name ?? "Fakultas Kosong";
                $staffName = $targetStaffFakultas?->name ?? "Staff Tidak di isi";

                // Construct the string
                $jastur = $staffName . ", Fakultas " . ucwords($fakultasName);
            } else {
                $jastur = !$getJabatanStruktural ? "Kosong" : $getJabatanStruktural->name;
            }

            $unitId = $getBiodata->unit_id;

            if ($getBiodata['img_storage'] !== null) {

                // $url = Storage::url('public/profile/' . $getBiodata->nik . "_" . $getBiodata->master_id . "/" . $getBiodata->img_storage);

                $url =  $getBiodata->img_storage;
                $profile_img =  $url;
            }

            if ($getExtraBiodata->kota_kab === null) {
                $kab = "Sidoarjo";
                $kec = "Sidoarjo";
                $desa = "Sidoarjo";
                // $jastur = "Kosong";
            } else {
                $kab = $selectKota->name === null ? "Kosong" : $selectKota->name;
                $kec = $selectKecam->name === null ? "Kosong" : $selectKecam->name;

                $desa = "Tunggu";
                if ($selectDesa) {
                    $desa = $selectDesa->name === null ? "Kosong" : $selectDesa->name;
                }
            }
            // echo "Tes";
            // $telepon = null;
            $telepon = strlen($getBiodata->telepon) < 5 ? null : filterPhoneNumber($getBiodata->telepon);
        }

        $profileLoad = [$jastur, Str::title($kab), Str::title($kec), Str::title($desa), $telepon, $profile_img, $surveySKUValue, $SkuFinalValue, $unitId];
        // $profileLoad = ["", "", "", "", [$selectKota, $selectKecam, $selectDesa, $getExtraBiodata]];

        // $profileLoad = "";

        for ($i = 0; $i < sizeof($userActive); $i++) {
            $userActive[$i]['allowed_app'] = unserialize($userActive[$i]['allowed_app_arr']);
        }

        $notAllowed = $status === null ? false : true;

        if ($status === null) {
            return Inertia::render('Sso/NewSsoDashboard', [
                "userAccount" => $userActive,
                "userData" => $profileLoad,
                "notAllowedAlert" => $notAllowed,
                "allowedApp" => unserialize($userActive[0]->allowed_app_arr),
            ]);
        } else {
            if ($status === "forbidden") {
                return Inertia::render('Sso/NewSsoDashboard', [
                    "userAccount" => $userActive,
                    "userData" => $profileLoad,
                    "notAllowedAlert" => $notAllowed,
                    "allowedApp" => unserialize($userActive[0]->allowed_app_arr),
                ]);
            } else {
                return redirect('/not-found');
            }
        }




        // return Inertia::render('Sso/NewSsoDashboard', ["userAccount" => $userActive]);

    }

    // public function forbidden()
    // {
    //     $activeUserId = Auth::id();

    //     $userActive = User::where('id', intval($activeUserId))->get();

    //     for ($i = 0; $i < sizeof($userActive); $i++) {
    //         $userActive[$i]['allowed_app'] = unserialize($userActive[$i]['allowed_app_arr']);
    //     }

    //     return Inertia::render('Sso/NewSsoDashboard', [
    //         "userAccount" => $userActive,
    //         "notAllowedAlert" => true
    //     ]);
    // }



    public function logout(Request $req)
    {
        // OSC Report-In
        $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

        $appIdentifier = env("APP_OSC_IDENTIFIER");
        $oscBase = env("OSC_BASE");

        $agent = new Agent();
        $agent->setUserAgent($req->userAgent());

        $extraPayload = [
            'referrer'      => $req->headers->get('origin') ?? $req->headers->get('referer'),
            'utm_source'    => $req->query('utm_source'),
            'utm_medium'    => $req->query('utm_medium'),
            'utm_campaign'  => $req->query('utm_campaign'),
            'platform'      => $agent->platform(),
            'browser'       => $agent->browser(),
            'device'        => $agent->device(),
            'is_mobile'     => $agent->isMobile(),
        ];
        $user = Auth::user();

        $response = Http::withToken($oscExtLogToken)
            ->post($oscBase . '/api/log/report-in', [
                "what" => "Logout Success at " . $appIdentifier . " Logout [$user->email/$user->name]",
                "ip" => $req->ip(),
                "extra" => json_encode($extraPayload)
            ]);

        Auth::logout();
        return redirect()->route('sso.login');
    }

    public function logoutNew(Request $request)
    {
        // OSC Report-In
        $oscExtLogToken = env("OSC_EXT_LOG_TOKEN");

        $appIdentifier = env("APP_OSC_IDENTIFIER");
        $oscBase = env("OSC_BASE");

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $extraPayload = [
            'referrer'      => $request->headers->get('origin') ?? $request->headers->get('referer'),
            'utm_source'    => $request->query('utm_source'),
            'utm_medium'    => $request->query('utm_medium'),
            'utm_campaign'  => $request->query('utm_campaign'),
            'platform'      => $agent->platform(),
            'browser'       => $agent->browser(),
            'device'        => $agent->device(),
            'is_mobile'     => $agent->isMobile(),
        ];

        $user = Auth::user();

        $response = Http::withToken($oscExtLogToken)
            ->post($oscBase . '/api/log/report-in', [
                "what" => "Logout Success at " . $appIdentifier . " Logout [$user->email/$user->name]",
                "ip" => $request->ip(),
                "extra" => json_encode($extraPayload)
            ]);

        $user = $request->user();

        // Revoke all tokens issued to the user
        $tokens = $user->tokens;

        foreach ($tokens as $token) {
            $token->revoke();

            // Also delete refresh tokens associated with this access token
            RefreshToken::where('access_token_id', $token->id)->delete();

            // Delete the token
            $token->delete();
        }

        Auth::logout();
        // return redirect()->route('sso.login');
    }

    public function editProfile()
    {
        $activeId = Auth::id();

        $userActive = User::where('id', intval($activeId))->get();

        if ($userActive === null) {
            return redirect('/sso-dashboard');
        }

        $data = SikBiodata::where('master_id', $activeId)->first();

        if ($data === null) {
            $newBiodata = new SikBiodata;

            $newBiodata->fullname = $userActive[0]->name;
            $newBiodata->nik = $userActive[0]->nik;
            $newBiodata->master_id = $activeId;

            if ($newBiodata->save()) {
                $data = SikBiodata::where('master_id', $activeId)->first();

                $newExtraBiodata = new SikExtraBiodata;

                $newExtraBiodata->biodata_id = $data->id;

                if ($newExtraBiodata->save()) {
                    $dataExtra = SikExtraBiodata::where('biodata_id', intval($data->id))->first();
                } else {
                    return redirect('/sso-dashboard');
                }
            } else {
                return redirect('/sso-dashboard');
            }
        } else {
            $dataExtra = SikExtraBiodata::where('biodata_id', intval($data->id))->first();
        }

        $getUnit = SikUnitKerja::orderBy('name')->get();
        $getJabFun = SikJabatanFungsional::orderBy('name')->get();
        $getJastur = SikJabatanStruktural::orderBy('name')->get();
        $getFakultas = SikFakultas::orderBy('name')->get();
        $getProdi = SikProdi::orderBy('name')->get();
        $getProvinsi = ProvinsiIndonesia::orderBy('name')->get();

        $url = "";

        if ($data['img_storage'] !== null) {

            $url = Storage::url('public/profile/' . $data->nik . '_' . $data->master_id . '/' . $data->img_storage);
            $data['profile_img_path'] =  $url;
        } else {
            $data['profile_img_path'] = null;
        }

        if ($dataExtra->provinsi !== null) {
            $provinsi = $dataExtra->provinsi;
            $provinsi_2 = $dataExtra->provinsi_2;
            $kota_kab = $dataExtra->kota_kab;
            $kota_kab_2 = $dataExtra->kota_kab_2;
            $kecamatan = $dataExtra->kecamatan;
            $kecamatan_2 = $dataExtra->kecamatan_2;
        }

        $getKabupaten = [];
        $getKabupaten2 = [];
        $getKecamatan = [];
        $getKecamatan2 = [];
        $getDesa = [];
        $getDesa2 = [];

        if ($dataExtra->provinsi !== null) {
            $getKabupaten = KabupatenIndonesia::where('provinsi_id', $provinsi)->get();
            $getKabupaten2 = KabupatenIndonesia::where('provinsi_id', $provinsi_2)->get();
            $getKecamatan = KecamatanIndonesia::where('kabupaten_id', $kota_kab)->get();
            $getKecamatan2 = KecamatanIndonesia::where('kabupaten_id', $kota_kab_2)->get();
            $getDesa = DesaIndonesia::where('kecamatan_id', $kecamatan)->get();
            $getDesa2 = DesaIndonesia::where('kecamatan_id', $kecamatan_2)->get();
        }

        // $allUnit = SikUnitKerja::all();

        // for ($i = 0; $i < sizeof($simpleBiodata); $i++) {

        //     for ($j = 0; $j < sizeof($allUnit); $j++) {
        //         if (intval($allUnit[$j]['id']) === intval($simpleBiodata[$i]['unit_id'])) {
        //             $simpleBiodata[$i]['unit_name'] = $allUnit[$j]['unit_name'];
        //         }
        //     }
        // }

        // return $provinsi;

        return Inertia::render('Sso/NewSsoDashboard', [
            "userAccount" => $userActive,
            'dataPegawai' => false,
            'selectedDataPegawai' => [$data, $dataExtra],
            'unitKerja' => $getUnit,
            'jabatanFun' => $getJabFun,
            'jabatanStr' => $getJastur,
            'fakultas' => $getFakultas,
            'prodi' => $getProdi,
            'list_provinsi' => $getProvinsi,
            'list_kabupaten' => $getKabupaten,
            'list_kabupaten2' => $getKabupaten2,
            'list_kecamatan' => $getKecamatan,
            'list_desa' => $getDesa,
            'list_kecamatan2' => $getKecamatan2,
            'list_desa2' => $getDesa2,
        ]);
    }

    public function changeNewPass(Request $data)
    {
        $oldPass = $data->oldPass;
        $newPass = $data->newPassword;

        $targetId = Auth::id();

        $targetUser = User::find($targetId);
        $hashedPass = $targetUser->password;

        if (Hash::check($oldPass, $hashedPass)) {
            // The passwords match...
            $targetUser = User::find($targetId);

            $newHashPass = Hash::make($newPass);

            $targetUser->password = $newHashPass;

            if ($targetUser->save()) {
                return response()->json(["good" => true, "match" => true]);
            }
            return response()->json(["good" => false, "match" => true]);
        }
        return response()->json(["good" => true, "match" => false]);
    }

    public function perUserLoger()
    {
        $targetUserId = intval(Auth::id());

        $perUserLoger = LoginLoger::where("master_id", $targetUserId)->select("created_at", "master_id", "ip_log", "device_name", "browser_name")->orderBy("created_at", "DESC")->take(5)->get();

        for ($i = 0; $i < sizeof($perUserLoger); $i++) {
            $perUserLoger[$i]->dmy_date = formatDateTime($perUserLoger[$i]->created_at);
            $perUserLoger[$i]->formated_date = formatDateTimeNew($perUserLoger[$i]->created_at);
        }

        return response()->json(["loger" => $perUserLoger]);
    }

    public function unlockResetPasswordButton(Request $req)
    {
        $validator = $req->validate([
            "user_id" => "required",
            "password" => "required"
        ]);

        $password = $validator['password'];
        $userId = intval($validator['user_id']);

        $targetUser = User::where('username', 'superadmin')->where('active', 1)->first();

        if ($targetUser) {
            if (Hash::check($password, $targetUser->password)) {

                return response()->json(["success" => true, "pass_match" => true]);
            }
            return response()->json(["success" => true, "pass_match" => false]);
        }

        return response()->json(["success" => false, "pass_match" => false]);
    }

    public function doResetPasswordSuper(Request $req)
    {
        $validator = $req->validate([
            "user_id" => "required",
        ]);

        $userId = intval($validator['user_id']);

        $targetUser = User::where('id', $userId)->where('active', 1)->first();

        if ($targetUser) {
            $targetUser->password = Hash::make(removeNonNumeric($targetUser->nik));

            if ($targetUser->save()) {
                return response()->json(["success" => true], 201);
            }
            return response()->json(["success" => false]);
        }
        return response()->json(["success" => false]);
    }
}
