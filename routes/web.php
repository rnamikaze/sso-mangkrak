<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use App\Models\SIK\SikJabatanStrukDos;
use Illuminate\Foundation\Application;
use App\Models\Aslip\MailerCounterModel;
use App\Http\Controllers\AdzanControllers;
use App\Http\Controllers\DebugControllers;
use App\Http\Controllers\AccountsControllers;
use App\Http\Controllers\SKU\HomeControllers;
use App\Http\Controllers\SsoGuestControllers;
use App\Http\Controllers\SKU\SurveyController;
use App\Http\Controllers\USL\AdminControllers;
use App\Http\Controllers\USL\ProxyControllers;
use App\Http\Controllers\MailerSendControllers;
use App\Http\Controllers\PublicFileControllers;
use App\Http\Controllers\SIK\BiodataController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\SsoSettingsController;
use App\Http\Controllers\SIK\KpiMainControllers;
use App\Http\Controllers\SPMB\ProfileController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Controllers\MahasiswaDataController;
use App\Http\Controllers\SipekaBridgeControllers;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\USL\ShortUrlControllers;
use App\Http\Controllers\DataIndonesiaControllers;
use App\Http\Controllers\PengajuanCutiControllers;
use App\Http\Controllers\Rnamikaze\DeveloperTools;
use App\Http\Controllers\SikDataPegawaiController;
use App\Http\Controllers\SKU\ChangelogControllers;
use App\Http\Controllers\SKU\PersonDataController;
use App\Http\Controllers\SPMB\HomePollControllers;
use App\Http\Controllers\Payroll4BridgeControllers;
use App\Http\Controllers\SIK\SIKKinerjaControllers;
use App\Http\Controllers\SPMB\DashboardControllers;
use App\Http\Controllers\SPMB\SessionsControllers2;
use App\Http\Controllers\USL\UrlManagerControllers;
use App\Http\Middleware\CheckRememberTokenValidity;
use App\Http\Controllers\SIK\KinerjaDosenController;
use App\Http\Controllers\USL\UserProfileControllers;
use App\Http\Controllers\SIK\SikDashboardControllers;
use App\Http\Controllers\SikJabatanStrukDosController;
use App\Http\Controllers\SPMB\SpmbSettingsControllers;
use App\Http\SIK\Controllers\SikKinerjaTaskController;
use App\Http\Controllers\SupabaseVisitorLogControllers;
use App\Http\Controllers\AutoSlip\AutoSlipMainControllers;
use App\Http\Controllers\AutoSlip\MailerCounterControllers;
use App\Http\Controllers\ExtraApiControllers;
use App\Http\Controllers\PendekinBridgeControllers;
use Laravel\Passport\Http\Controllers\AuthorizationController;

// use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// MISCELANIOUS DEBUG <<= == == == == == == ==
// Route::prefix('debug')->group(function () {
//     Route::get('/ndash', [DebugControllers::class, 'newDashboard']);
// });
Route::prefix('debug')->group(function () {
    Route::get('/mahasiswa', [MahasiswaDataController::class, 'getMahasiswa']);
    Route::get('/supabase', [SupabaseVisitorLogControllers::class, "getAll"]);
    Route::get('/ndash', [DebugControllers::class, 'newDashboard']);

    Route::get('/image-compression', [DebugControllers::class, 'imageCompression']);
});

// Route::get('/slip', function () {
//     return view('mails.slip-2');
// });

Route::get('/', function () {
    return redirect()->route('sso.login');
});

// Route::get('/hashThePassword/{wow?}', function ($wow) {
//     return Hash::make($wow);
// });

// Login Credentials
// Route::get('/google/redirect', [SocialLoginController::class, 'redirectToGoogle'])->name('google.redirect');
// Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback'])->name('google.callback');

// Route::get('/google/redirect-login', [SocialLoginController::class, 'redirectToGoogleFromLogin'])->name('google.redirect-login');
// Route::get('/google/callback-login', [SocialLoginController::class, 'handleGoogleCallbackFromLogin'])->name('google.callback-login');

Route::get('/google/redirect-login', [SocialLoginController::class, 'redirectToGoogleFromLogin'])->name('google.redirect-login');
Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallbackFromLogin'])->name('google.callback-login');

Route::post('dashtest', [AccountsControllers::class, 'dashboardTest']);

// Route::middleware('catch.oauth.exception')->group(function () {
//     Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize']);
// });

// // Define the error warning route
// Route::get('/error-warning', function () {
//     $errorDetails = session('errorDetails', []);

//     return "BS";

//     return view('error.warning', compact('errorDetails'));

//     // return view('error.warning'); // Create a view for the error warning
// })->name('error.warning');

Route::get('/login', function () {
    return to_route('sso.loginWarning');
})->name('login');

Route::get('/loader', function () {
    // return "Test";
    return Inertia::render('LoadingCom/ZigZagLoading');
});

Route::prefix('dev')->middleware('auth')->name('dev.')->group(function () {
    Route::get('/', [DeveloperTools::class, 'parkedLogin'])->name('parked');

    Route::get('home', [DeveloperTools::class, 'home']);
    Route::post('json/sla', [DeveloperTools::class, 'getSuccessLoger']);
    Route::get('json/lfa/{secret?}', [DeveloperTools::class, 'showFailedLoginAttempt']);
    Route::post('json/lfa', [DeveloperTools::class, 'getPostFailedLoginAttempt']);
    Route::post('json/stranger-log', [DeveloperTools::class, 'getStrangerLog']);
    Route::post('json/supa-handle-stranger-log', [DeveloperTools::class, 'supabaseHandleData']);
});

Route::name('sso.')->group(function () {
    Route::get('/privacy-and-policy', [SsoGuestControllers::class, 'privacyAndPolicy']);

    // Legacy Working
    Route::get('/sso-login', [AccountsControllers::class, 'ssoLogin'])->middleware(['throttle:10,1', RedirectIfAuthenticated::class, CheckRememberTokenValidity::class])->name('login');

    // // New Ryuzen Login
    // Route::get('/sso-login', [AccountsControllers::class, 'ryuzenLogin'])->middleware([RedirectIfAuthenticated::class, CheckRememberTokenValidity::class])->name('login');

    Route::get('/sso-social-auth', [SocialLoginController::class, 'redirectBack'])->name('gauth.redirect');
    Route::get('/sso-login-w', [AccountsControllers::class, 'ssoLoginWarning'])->middleware(['throttle:10,1','guest'])->name('loginWarning');

    Route::post('/check-social-account', [SocialLoginController::class, 'checkSocialAccount']);

    // Login 1st Attempt: checng nik or email
    Route::post('/loginEmail', [AccountsControllers::class, 'loginEmail']);
    Route::post('/user-login-email', [AccountsControllers::class, 'userLoginEmail']);
    // Login 2nd attempt: check passwordd
    Route::post('/sso-login', [AccountsControllers::class, 'login']);
    Route::post('/user-sso-login', [AccountsControllers::class, 'userSsoLogin']);
    // Route::post('/sso-login', [AccountsControllers::class, 'login'])->middleware('throttle:50,1');

    Route::get('/sso-dashboard/{status?}', [AccountsControllers::class, 'dashboard'])->middleware([Authenticate::class, CheckRememberTokenValidity::class])->name('dashboard');
    Route::post('/sso-logout', [AccountsControllers::class, 'logout']);
    Route::post('/sso-logout-new', [AccountsControllers::class, 'logoutNew']);

    Route::post('dashtest', [AccountsControllers::class, 'dashboardTest']);
    Route::post('/edit-profile', [AccountsControllers::class, 'editProfile']);

    Route::post('/get-all-users', [SsoSettingsController::class, 'getAllUsers']);
    Route::post('/get-loger', [SsoSettingsController::class, 'getLoger']);

    Route::post('/get-user-profile', [AccountsControllers::class, 'getUserProfile']);

    Route::post('per-user-loger', [AccountsControllers::class, "perUserLoger"]);

    Route::get('/get-all-users', function () {
        return to_route('sso.dashboard');
    });

    Route::post('/unlock-reset-password-button', [AccountsControllers::class, "unlockResetPasswordButton"]);

    Route::post('/do-reset-password-super', [AccountsControllers::class, 'doResetPasswordSuper']);

    // Edit Info Profil
    Route::post('/sik/simpan-ubah-pegawai-2', [SikDashboardControllers::class, 'simpanUbahPegawai2']);
    Route::post('save-profile-image', [SikDashboardControllers::class, 'saveProfileImage']);

    // Ambil Info User
    Route::post('/akses-selected-user', [SsoSettingsController::class, 'aksesSelectedUser']);
    Route::post('/save-akses-selected-user', [SsoSettingsController::class, 'saveAksesSelectedUser']);
    // Route::get('/forbidden', [AccountsControllers::class, 'forbidden'])->name('forbidden');
});

Route::prefix('sso')->middleware('auth')->name('sso.')->group(function () {
    Route::post('/get-struktural', [SikDataPegawaiController::class, 'getStrukturalList']);
    Route::post('check-email', [BiodataController::class, 'checkEmail'])->middleware('cors');
    Route::post('check-nik', [BiodataController::class, 'checkNik'])->middleware('cors');
    Route::post('simpan-ubah-pegawai', [SikDashboardControllers::class, 'simpanUbahPegawai']);
    Route::post('check-image-exist', [PublicFileControllers::class, 'checkItemExist']);
    Route::post('get-img-storage', [SikDashboardControllers::class, 'getImgStorage']);
    Route::post('save-profile-pic', [SikDashboardControllers::class, 'saveImage']);
    Route::post('/change-new-password', [AccountsControllers::class, 'changeNewPass']);
});

Route::get('/sku', [HomeControllers::class, 'guest'])->name('sku.guest');
Route::post('/sku/send-survey', [SurveyController::class, 'sendSurvey']);
Route::get('/sku/qrcode', function () {
    return Inertia::render('SKU/Guest/QrCodeDisplay');
});
Route::get('/sku/unit/{unitId?}', [HomeControllers::class, 'guest']);

Route::prefix('aslip')->name('aslip.')->group(function () {
    Route::get('home', [AutoSlipMainControllers::class, 'home']);
    Route::post('up-file', [AutoSlipMainControllers::class, 'upFile']);
    Route::post('send-mail', [AutoSlipMainControllers::class, 'sendMail']);
    Route::post('get-activity-6d', [MailerSendControllers::class, 'activity']);
    Route::post('load-file', [AutoSlipMainControllers::class, 'loadFile']);

    Route::post('get-daily-quota', [MailerCounterControllers::class, 'checkToday']);
    Route::post('daily-quota-add-one', [MailerCounterControllers::class, 'addOne']);

    Route::post('get-daily-table', [MailerCounterControllers::class, 'getDailyTable']);
});

Route::prefix('sku')->middleware('userlevel')->name('sku.')->group(function () {
    // MY CONTROLLER - RNAMIKAZE
    Route::get('/home', [HomeControllers::class, 'home'])->middleware('auth')->name("home");
    Route::get('/login', [HomeControllers::class, 'login'])->middleware('guest')->name('login');

    Route::post('/login', [HomeControllers::class, 'doLogin']);

    Route::redirect('/logout', '/home');
    Route::post('/logout', [HomeControllers::class, 'logout'])->middleware('auth')->name('logout');

    Route::redirect('/getPersonData', '/home');
    Route::redirect('/getNamaPersonDanUnit', '/home');
    Route::get('/getPersonData/filter/{startDate?}/{endDate?}', function () {
        return redirect('/home');
    });
    Route::post('/getPersonData/filter/{startDate}/{endDate}', [PersonDataController::class, 'getPersonDataFilter'])->middleware('auth');
    Route::post('/getPersonData', [PersonDataController::class, 'getPersonData'])->middleware('auth');
    Route::post('/getNamaPersonDanUnit', [PersonDataController::class, 'getNamaPersonDanUnit'])->middleware('auth');
    Route::post('/grabDataSurveyByNama/{nama_id}/{startDate?}/{endDate?}', [SurveyController::class, 'getDataSurvey'])->middleware('auth');
    // Route::post('/grabDataSurveyByNama/{nama_id}/{startDate}/{endDate}', [SurveyController::class, 'getDataSurvey'])->middleware('auth');

    Route::post('/getDataSummary', [SurveyController::class, 'getDataSummary'])->middleware('auth');
    Route::post('/getDataSummary/filter/{unit}/{startDate}/{endDate}', [SurveyController::class, 'getDataSummaryFilter'])->middleware('auth');
    // Route::post('/getNamaPersonDanUnit/filter/{nama_id}/{startDate}/{endDate}', [PersonDataController::class, 'getNamaPersonDanUnitFilter'])->middleware('auth');


    Route::post('/getExportData/{unit}/{startDate?}/{endDate?}', [SurveyController::class, 'doExport'])->middleware('auth');
    // Route::get('/myget/{unit}/{startDate?}/{endDate?}', [SurveyController::class, 'doExport']);
    Route::get('/getExportData/{unit}/{startDate?}/{endDate?}/download', [SurveyController::class, 'downloadExport'])->middleware('auth');

    Route::get('/changelog', [ChangelogControllers::class, 'index']);

    Route::get('{redir?}', function ($redir) {
        return to_route('sku.home');
    });
});
Route::post('/sku', [HomeControllers::class, 'guestWithException']);


Route::get('/spmb', [HomePollControllers::class, 'home'])->name('spmb.homepoll');
Route::post('/spmb/dopoll', [HomePollControllers::class, 'dopoll']);
Route::post('/spmb/update-poll-info', [HomePollControllers::class, 'updatePollInfo']);

Route::get('/spmb/debug', [HomePollControllers::class, 'debug']);

Route::prefix('spmb')->middleware('userlevel')->name('spmb.')->group(function () {

    Route::get('/json/dummy1', [DashboardControllers::class, 'getJson'])->middleware('corsAllow1');

    Route::get('/admin-harian', [DashboardControllers::class, 'adminHarian'])->middleware('auth')->name('admin-harian');
    Route::post('/admin-harian', [DashboardControllers::class, 'adminHarianFilter'])->middleware('auth');
    Route::get('/admin-bulanan', [DashboardControllers::class, 'adminBulanan'])->middleware('auth')->name('admin-bulanan');
    Route::post('/admin-bulanan', [DashboardControllers::class, 'adminBulananFilter'])->middleware('auth');
    Route::get('/export', [DashboardControllers::class, 'export'])->middleware('auth')->name('export');
    Route::post('/export', [DashboardControllers::class, 'exportFilter'])->middleware('auth');

    Route::get('/pengaturan', [SpmbSettingsControllers::class, 'pengaturan'])->middleware('auth')->name('pengaturan');
    Route::post('/tambah-periode', [SpmbSettingsControllers::class, 'tambahPeriode'])->middleware('auth');
    Route::post('/set-periode-active', [SpmbSettingsControllers::class, 'setPeriodeActive'])->middleware('auth');
    Route::post('/delete-periode', [SpmbSettingsControllers::class, 'deletePeriode'])->middleware('auth');

    Route::post('/do-export', [DashboardControllers::class, 'do_export'])->middleware('auth');
    Route::post('/do-export-bulanan', [DashboardControllers::class, 'do_export_bulanan'])->middleware('auth');
    Route::post('/do-export-periode', [DashboardControllers::class, 'do_export_periode'])->middleware('auth');

    Route::get('/dashboard', [DashboardControllers::class, 'index'])->middleware('auth')->name('dashboard');
    // Route::get('admin', [SessionsControllers2::class, 'create'])->middleware('guest')->name('login');
    // Route::post('admin', [SessionsControllers2::class, 'store'])->middleware('guest');
    // Route::post('verify', [SessionsControllers2::class, 'show'])->middleware('guest');
    // Route::post('reset-password', [SessionsControllers2::class, 'update'])->middleware('guest')->name('password.update');

    // Route::post('sign-out', [SessionsControllers2::class, 'destroy'])->middleware('auth')->name('logout');
    Route::get('profile', [ProfileController::class, 'create'])->middleware('auth')->name('profile');
    Route::post('user-profile', [ProfileController::class, 'update'])->middleware('auth');
});


// Short Link Route
// Route::get('/usl/{url?}', [ShortUrlControllers::class, 'mainShort']);
Route::get('/usl/proxy/data', [ProxyControllers::class, 'fetchData']);
Route::get('/usl', function () {
    return redirect('/');
});

Route::prefix('usl')->middleware('userlevel')->name('usl.')->group(function () {
    // Route::post('/loginEmail', [AdminControllers::class, 'loginEmail']);

    // Auth Needed
    Route::get('/admin/dashboard', [AdminControllers::class, 'dashboard'])->middleware('auth')->name('dashboard');
    Route::post('/createNew', [UrlManagerControllers::class, 'createNew'])->middleware('auth');
    Route::post('/checkAvailability', [UrlManagerControllers::class, 'checkAvailability'])->middleware('auth');
    Route::post('/checkAvailabilityFromEdit', [UrlManagerControllers::class, 'checkAvailabilityFromEdit'])->middleware('auth');
    Route::post('/showProfile', [UserProfileControllers::class, 'showProfile'])->middleware('auth');
    Route::post('/changePassword', [UserProfileControllers::class, 'changePassword'])->middleware('auth');
    Route::post('/deleteLink', [UrlManagerControllers::class, 'deleteLink'])->middleware('auth');
    Route::post('/getAllLinks', [UrlManagerControllers::class, 'getAllLinks'])->middleware('auth');
    Route::post('/getEditLink', [UrlManagerControllers::class, 'getEditLink'])->middleware('auth');
    Route::post('/saveEdit', [UrlManagerControllers::class, 'saveEdit'])->middleware('auth');
    Route::post('/changeLinkActive', [UrlManagerControllers::class, 'changeLinkActive'])->middleware('auth');
    Route::post('/logout', [AdminControllers::class, 'logout'])->middleware('auth');
    Route::post('/getUserList', [AdminControllers::class, 'getUserList'])->middleware('auth');
    Route::post('/createUser', [AdminControllers::class, 'createUser'])->middleware('auth');
    Route::post('/saveEditUser', [AdminControllers::class, 'saveEditUser'])->middleware('auth');
    Route::post('/getEditUser', [AdminControllers::class, 'getEditUser'])->middleware('auth');
    Route::post('/getSelectedLinks', [UrlManagerControllers::class, 'getSelectedLinks'])->middleware('auth');
    Route::post('/deleteUser', [AdminControllers::class, 'deleteUser'])->middleware('auth');
    Route::post('/resetPassword', [AdminControllers::class, 'resetPassword'])->middleware('auth');
    Route::post('/check-nik', [AdminControllers::class, 'checkNik'])->middleware('auth');

    Route::post('/get-user-info', [UrlManagerControllers::class, 'getUserInfo'])->middleware('auth');

    Route::post('/get-links-old', [UrlManagerControllers::class, 'getAllLinksOld']);

    // Get User Active Info
    Route::post('/get-user-active', [UrlManagerControllers::class, 'getUserActiveInfo']);

    // Infinite Scroll
    Route::post('/get-links', [UrlManagerControllers::class, 'getAllLinksNew']);

    // Without Auth
    Route::get('/admin/login', [AdminControllers::class, 'loginGet'])->middleware('guest')->name('loginGet');
    // Route::get('/admin/logout', [AdminControllers::class, 'logout'])->name('logout');
    Route::post('/login', [AdminControllers::class, 'login'])->middleware('guest');

    Route::get('/{url?}', function () {
        return to_route('usl.dashboard');
    });
});

Route::prefix('sik')->name('sik.')->middleware('userlevel')->group(function () {
    Route::get('cuti', function () {
        return Inertia::render('PensiSIK/MainPensiSIK');
    });
    Route::get('/', function () {
        return redirect()->route('sso.loginWarning');
    });

    Route::post('get-active-bio', [SikDashboardControllers::class, 'getActiveBio']);
    Route::post('get-username', [SikDashboardControllers::class, "getUsernameKinerja"]);

    Route::post('determine-user-type', [SikDashboardControllers::class, 'determineUserType']);

    // KINERJA TASK
    Route::post('create-new-task', [SIKKinerjaControllers::class, 'createNewTask']);
    Route::post('create-sub-task', [SIKKinerjaControllers::class, 'createSubTask']);
    Route::post('delete-task', [SIKKinerjaControllers::class, 'deleteTask']);
    Route::post('update-sub-task', [SIKKinerjaControllers::class, 'updateSubTask']);
    Route::post('delete-sub-task', [SIKKinerjaControllers::class, 'deleteSubTask']);
    Route::post('update-periode-task', [SIKKinerjaControllers::class, 'updatePeriode']);

    // KINERJA ADMIN VIEW
    Route::post('get-task-by-unit', [SIKKinerjaControllers::class, 'getTaskByUnit']);
    Route::post('get-task-by-unit-periode', [SIKKinerjaControllers::class, 'getTaskByUnitPeriode']);
    Route::post('get-task-by-fakultas-periode', [KinerjaDosenController::class, 'getTaskFakultyByPeriode']);

    // KINERJA MAIN
    Route::post('get-user-additional-info', [SIKKinerjaControllers::class, 'getUserAdditionalInfo']);
    Route::post('get-task-history', [SIKKinerjaControllers::class, 'getTaskHistory']);

    // OTHER
    Route::post('get-unit-list', [SIKKinerjaControllers::class, 'getUnitList']);
    Route::post('get-demo-status', [SIKKinerjaControllers::class, 'getDemoStatus']);
    Route::post('get-dash-summary', [SIKKinerjaControllers::class, 'getDashSummaryNew']);
    Route::post('get-struktural', [SikDataPegawaiController::class, 'getStrukturalList']);

    Route::get('dashboard', [SikDashboardControllers::class, 'dashboard'])->middleware('auth')->name('dashboard');

    Route::post('data-pegawai-axios', [SikDashboardControllers::class, 'dataPegawaiAxios']);
    Route::post('unit-list', [SikDashboardControllers::class, 'getUnitList']);
    Route::post('fakultas-list', [SikDashboardControllers::class, 'getFakultasList']);
    Route::post('prodi-list', [SikDashboardControllers::class, 'getProgramStudiList']);
    Route::post('data-pegawai', [SikDashboardControllers::class, 'dataPegawai'])->name('data-pegawai');
    Route::post('data-pegawai-terpilih', [SikDashboardControllers::class, 'dataPegawaiTerpilih']);
    Route::post('get-data-pegawai-axios', [SikDashboardControllers::class, 'getDataPegawaiAxios']);
    Route::post('tambah-pegawai', [SikDashboardControllers::class, 'tambahPegawai']);
    Route::post('simpan-tambah-pegawai', [SikDashboardControllers::class, 'simpanTambahPegawai']);
    Route::post('simpan-ubah-pegawai', [SikDashboardControllers::class, 'simpanUbahPegawai']);

    Route::post('get-status-kerja-list', [SikDashboardControllers::class, 'getStatusKerjaList']);
    // Route::post('simpan-ubah-pegawai-2', [SikDashboardControllers::class, 'simpanUbahPegawai2']);

    Route::post('check-email', [BiodataController::class, 'checkEmail'])->middleware('cors');
    Route::post('check-nik', [BiodataController::class, 'checkNik'])->middleware('cors');

    Route::post('save-profile-pic', [SikDashboardControllers::class, 'saveImage']);

    Route::post('save-profile-image', [SikDashboardControllers::class, 'saveProfileImage']);
    Route::post('save-profile-image-temp', [SikDashboardControllers::class, 'saveProfileImageTemp']);

    Route::post('disable-user', [SikDashboardControllers::class, 'disableUser']);

    Route::post('filter-data-pegawai', [SikDataPegawaiController::class, 'filterDataPegawai']);

    Route::post('data-dash', [SikDashboardControllers::class, 'dataDash']);
    Route::post('delete-user', [SikDashboardControllers::class, 'deleteUser']);

    Route::post('extra-dashboard', [SikDashboardControllers::class, 'extraDash']);

    Route::post('home-kinerja', [SIKKinerjaControllers::class, 'homeKinerja']);
    Route::post('get-assigne-name', [SIKKinerjaControllers::class, 'getAssigneName']);

    Route::post('get-img-storage', [SikDashboardControllers::class, 'getImgStorage']);

    Route::post('get-current-id', [SikDashboardControllers::class, 'getCurrentUserId']);
    // KPI =========== START
    Route::post('get-task-percentage', [KpiMainControllers::class, 'getTaskPercentageOnly']);
    Route::post('get-task-percentage-per-user', [KpiMainControllers::class, 'getTaskPercentageOnlyPerUser']);
    // Route::post('get-task-percentage-test', [KpiMainControllers::class, 'getTaskPercentageLoopTest']);
    Route::post('get-task-percentage-list', [KpiMainControllers::class, 'getTaskPercentageLoopList']);

    Route::post('get-jabatan-base-nominal', [KpiMainControllers::class, 'getJabatanBaseNominal']);
    Route::post('update-target-jabatan-base-nominal', [KpiMainControllers::class, 'updateTargetJabatanBaseNominal']);

    Route::post('get-user-kpi', [KpiMainControllers::class, 'getUserKpi']);

    Route::post('get-list-base-nominal', [KpiMainControllers::class, 'getNameAndBaseNominal']);
    Route::post('update-target-nominal', [KpiMainControllers::class, 'updateTargetNominal']);
    Route::post('get-list-w-search', [KpiMainControllers::class, 'getNameAndBaseNominalwSearch']);
    Route::post('get-all-absensi-value', [KpiMainControllers::class, 'getAllAbsensiValue']);

    Route::post('up-import-presensi-admin', [KpiMainControllers::class, 'importAbsensiAdmin']);
    Route::post('save-import-presensi-admin', [KpiMainControllers::class, 'saveImportAbsensiAdmin']);

    Route::post('get-list-karyawan', [KpiMainControllers::class, 'getListKaryawan']);

    Route::get('get-template-absensi/{type?}', [KpiMainControllers::class, 'getTemplateExcel']);

    Route::post('check-image-exist', [PublicFileControllers::class, 'checkItemExist']);

    Route::get('get-kpi-summary/{type}/{periodeStart}/{periodeEnd}', [KpiMainControllers::class, 'downloadKpiSummary']);

    Route::get('{redir?}', function ($redir) {
        return to_route('sik.dashboard');
    });

    // Small POST API Call
    Route::post('jabatan-strukdos-list', [SikJabatanStrukDosController::class, 'getJabatanStrukDosList']);

    // Kinerja Dosen
    Route::post('get-assigne-name-dosen', [KinerjaDosenController::class, 'getAssigneName']);
    Route::post('get-user-additional-info-dosen', [KinerjaDosenController::class, 'getUserAdditionalInfo']);

    Route::post('create-new-task-dosen', [KinerjaDosenController::class, 'createNewTask']);
    Route::post('create-sub-task-dosen', [KinerjaDosenController::class, 'createSubTask']);
    Route::post('delete-task-dosen', [KinerjaDosenController::class, 'deleteTask']);
    Route::post('update-sub-task-dosen', [KinerjaDosenController::class, 'updateSubTask']);
    Route::post('delete-sub-task-dosen', [KinerjaDosenController::class, 'deleteSubTask']);

    Route::post('get-dash-summary-dosen', [KinerjaDosenController::class, 'getDashSummaryNew']);
    Route::post('get-task-history-dosen', [KinerjaDosenController::class, 'getTaskHistory']);

    Route::post('update-periode-task-dosen', [KinerjaDosenController::class, 'updatePeriode']);

    Route::post('data-pegawai-custom', [SikDataPegawaiController::class, 'customDataView']);

    // Pengajuan Cuti Route Start ====<<<

    Route::post('get-kawan-seunit', [PengajuanCutiControllers::class, 'getKawanSeunit']);
    Route::post('send-pengajuan', [PengajuanCutiControllers::class, 'sendPengajuan']);
    Route::post('send-pengajuan-izin', [PengajuanCutiControllers::class, 'sendPengajuanIzin']);
    Route::post('pengajuan-cuti-determine-user-type', [PengajuanCutiControllers::class, 'determineUserType']);
    // => Pegawai
    Route::post('get-pengajuan-terkirim', [PengajuanCutiControllers::class, 'getPengajuanMasuk']);
    Route::post('get-pengajuan-diterima', [PengajuanCutiControllers::class, 'getPengajuanDiterima']);
    Route::post('get-pengajuan-ditolak', [PengajuanCutiControllers::class, 'getPengajuanDitolak']);

    // => Kepala Kotak
    Route::post('get-pengajuan-kepala-kotak', [PengajuanCutiControllers::class, 'getPengajuanKepalaKotak']);
    Route::post('get-terkirim-kepala-kotak', [PengajuanCutiControllers::class, 'getTerkirimKepalaKotak']);

    // => Kepala SDM
    Route::post('get-pengajuan-kepala-kotak-sdm', [PengajuanCutiControllers::class, 'getPengajuanKepalaKotakSdm']);
    Route::post('get-terkirim-kepala-kotak-sdm', [PengajuanCutiControllers::class, 'getTerkirimKepalaKotakSdm']);

    // Cuti Pengajuan Viewer
    Route::post('get-selected-pengajuan-cuti', [PengajuanCutiControllers::class, "getSelectedCuti"]);
    Route::post('approve-pengajuan-action', [PengajuanCutiControllers::class, 'approvePengajuanAction']);
    // Pengajuan Cuti ====<<<<
});

Route::prefix('api')->group(function () {
    Route::get('get-kabupaten/{id}', [DataIndonesiaControllers::class, 'getKabupaten']);
    Route::get('get-kecamatan/{id}', [DataIndonesiaControllers::class, 'getKecamatan']);
    Route::get('get-desa/{id}', [DataIndonesiaControllers::class, 'getDesa']);

    Route::get('get-adzans-by-city', [AdzanControllers::class, 'retrieveAdzanbyCity']);
    Route::post('get-today-prayers-time', [ExtraApiControllers::class, 'getPrayerTimes']);
    Route::post('get-today-prayers-3-days', [ExtraApiControllers::class, 'getPrayerTimes3Days']);
});

Route::prefix('sipeka')->name('sipeka.')->middleware('userlevel')->group(function () {
    Route::get('/', [SipekaBridgeControllers::class, 'home']);
});

Route::prefix('4payroll')->name('4payroll.')->middleware('auth')->group(function () {
    Route::get('/', [Payroll4BridgeControllers::class, 'home']);
});

Route::prefix('pendekin')->name('pendekin.')->middleware('auth')->group(function () {
    Route::get('/', [PendekinBridgeControllers::class, 'home']);
});

Route::get('/{link?}', function ($link) {
    return to_route('sso.dashboard');
});
