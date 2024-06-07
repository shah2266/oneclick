<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ICX\BanglaicxReportController;
use App\Http\Controllers\ICX\ProcessedBanglaIcxCdrFilesController;
use App\Http\Controllers\IGW\BtrcReportController;
use App\Http\Controllers\IGW\CallSummaryController;
use App\Http\Controllers\IGW\IGWDayWiseDataCrossCheckController;
use App\Http\Controllers\IGW\IGWDestinationWiseProfitLossReportController;
use App\Http\Controllers\IGW\IOSReportController;
use App\Http\Controllers\IGW\OSReportController;
use App\Http\Controllers\IGWANDIOS\ComparisonReportController;
use App\Http\Controllers\IGWANDIOS\IofDailySummaryReportController;
use App\Http\Controllers\IGWANDIOS\IofInOutBoundReportController;
use App\Http\Controllers\IGWANDIOS\IofReportController;
use App\Http\Controllers\IofCompanyController;
use App\Http\Controllers\IOS\BtrcController;
use App\Http\Controllers\IOS\IosBtrcMonthlyReportController;
use App\Http\Controllers\IOS\IOSDailyReportController;
use App\Http\Controllers\IOS\IOSDayWiseDataCrossCheckController;
use App\Http\Controllers\Noclick\NoclickCommandController;
use App\Http\Controllers\Noclick\NoclickMailTemplateController;
use App\Http\Controllers\Noclick\NoclickScheduleController;
use App\Http\Controllers\SERVER\ConnectivityController;
use App\Http\Controllers\SERVER\DatabaseStatusController;
use App\Http\Controllers\SERVER\ServerListController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserThemeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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


Route::get('/develop', [TestController::class, 'index']);

Auth::routes([
    'register' => false,
    'reset' => false,
]);

// Redirect registration route to login page
Route::redirect('register', 'login')->name('register')->middleware('guest');

// Redirect password reset route to login page
Route::redirect('password/reset', 'login')->name('password.request')->middleware('guest');


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('testing', [TestController::class, 'testing'])->name('cdr.status.testing');


Route::group([
    'middleware' => 'auth'
], function() {
    Route::resource('users', UserController::class);
    Route::post('/toggle-theme', [UserThemeController::class, 'toggleTheme'])->name('toggleTheme');
    Route::resource('themes', UserThemeController::class)->middleware('check.user.type:2');
});

Route::group([
    'prefix' => 'noclick',
    'middleware' => ['auth', 'check.user.type:2']
], function () {
    Route::resource('mail/templates', NoclickMailTemplateController::class);

    Route::get('/commands/updates', [NoclickCommandController::class, 'getUpdates']);
    Route::post('/commands/{id}/toggle-status', [NoclickCommandController::class, 'toggleStatus'])->name('commands.toggle-status')->middleware('check.user.type:2');
    Route::resource('commands', NoclickCommandController::class);

    Route::get('/schedules/updates', [NoclickScheduleController::class, 'getUpdates']);
    Route::post('/schedules/{id}/toggle-status', [NoclickScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status')->middleware('check.user.type:2');

    Route::get('/schedules/{frequency}/list', [NoclickScheduleController::class, 'getFrequencyWiseSchedule'])->name('schedules.frequency.list');
    Route::get('/schedules/dashboard', [NoclickScheduleController::class, 'dashboard'])->name('schedules.dashboard');

    Route::post('/schedules/set/holiday', [NoclickScheduleController::class, 'setHoliday'])->name('set.holiday.date');

    Route::resource('schedules', NoclickScheduleController::class);
});

Route::group([
    'prefix' => 'server',
    'middleware' => 'auth',
], function() {

    //Logical Disk Space Route
    //Route::get('disk/space', 'servers\BillingServerController@serverStatus');

    //Server connectivity route
    Route::get('info/igw/documentation', [ServerListController::class, 'igwDocumentation']);
    Route::get('info/ios/documentation', [ServerListController::class, 'iosDocumentation']);
    Route::resource('info', ServerListController::class);

    //Database Status Route
    Route::get('database/status/space', [DatabaseStatusController::class,'index']);

    //Server connectivity route
    Route::get('connectivity/status', [ConnectivityController::class, 'index'])->name('server.connectivity.status');
    Route::post('connectivity/ping', [ConnectivityController::class, 'pingServer'])->name('ping.server');


});


//Platform IGW
Route::group([
    'prefix'      => 'platform/igw/report',
    'middleware'  => 'auth',
], function() {

    //IGW daily call summary reports
    Route::get('/callsummary', [CallSummaryController::class, 'index']);
    Route::post('/callsummary', [CallSummaryController::class, 'reports']);
    Route::get('/callsummary/{download}', [CallSummaryController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/callsummary/delete/{delete}', [CallSummaryController::class, 'deleteFile']); /*Finally Delete report*/
    Route::get('/callsummary/zip/download', [CallSummaryController::class, 'zipCreator']); //Download as zip file
    Route::get('/callsummary/clean/directory', [CallSummaryController::class, 'cleanDir']); //Clean IGW call summary directory

    //IOS wise daily report
    Route::get('/ioswise', [IOSReportController::class, 'index']);
    Route::post('/ioswise', [IOSReportController::class, 'reports']);
    Route::get('/ioswise/{download}',[IOSReportController::class, 'getFile']);
    Route::get('/ioswise/delete/{delete}',[IOSReportController::class, 'deleteFile']);
    Route::get('/ioswise/zip/download',[IOSReportController::class, 'zipCreator']);
    Route::get('/ioswise/clean/directory', [IOSReportController::class, 'cleanDir']);

    //OS wise daily report
    Route::get('/oswise',[OSReportController::class, 'index']);
    Route::post('/oswise',[OSReportController::class, 'reports']);
    Route::get('/oswise/{download}',[OSReportController::class, 'getFile']);
    Route::get('/oswise/delete/{delete}',[OSReportController::class, 'deleteFile']);
    Route::get('/oswise/zip/download',[OSReportController::class, 'zipCreator']);
    Route::get('/oswise/clean/directory',[OSReportController::class, 'cleanDir']);

    //BTRC daily report
    Route::get('/btrc', [BtrcReportController::class, 'index']);
    Route::post('/btrc', [BtrcReportController::class, 'reports']);
    Route::get('/btrc/{download}', [BtrcReportController::class, 'getFile']);
    Route::get('/btrc/delete/{delete}', [BtrcReportController::class, 'deleteFile']);
    Route::get('/btrc/zip/download', [BtrcReportController::class, 'zipCreator']);
    Route::get('/btrc/clean/directory', [BtrcReportController::class, 'cleanDir']);

    //IOS Main and Summary Cross-Check Route Description
    Route::get('/crosscheck', [IGWDayWiseDataCrossCheckController::class, 'index']);
    Route::post('/crosscheck', [IGWDayWiseDataCrossCheckController::class, 'crossCheck']);

});

/**
 * IOS Platform (Route Description as inline)
 */
Route::group([
    'prefix'      => 'platform/ios/report',
    'middleware'  => 'auth',
], function() {

    //IOS Daily Reports Route Description
    Route::get('/callsummary', [IOSDailyReportController::class, 'index']); /*View loading*/
    Route::post('/callsummary', [IOSDailyReportController::class, 'reportGenerate']); /*Report Export process (POST)*/
    Route::get('/callsummary/{download}', [IOSDailyReportController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/callsummary/move/{move}', [IOSDailyReportController::class, 'moveFile']); /*Move file to temporary to permanent stored directory*/
    Route::get('/callsummary/delete/{delete}', [IOSDailyReportController::class, 'deleteFile']); /*Finally Delete report*/
    Route::get('/callsummary/zip/download', [IOSDailyReportController::class, 'zipCreator']);
    Route::get('/callsummary/clean/directory', [IOSDailyReportController::class, 'cleanDir']);

    //IOS Daily BTRC Reports Route Description
    Route::get('/btrc', [BtrcController::class, 'index']); /*View Loading*/
    Route::post('/btrc', [BtrcController::class, 'reportGenerate']); /*Report Export process (POST)*/
    Route::get('/btrc/{download}', [BtrcController::class, 'downloadFile']); /*Prepare reports downloadable*/

    //IOS Main and Summary Cross-Check Route Description
    Route::get('/crosscheck', [IOSDayWiseDataCrossCheckController::class, 'index']);
    Route::post('/crosscheck',[IOSDayWiseDataCrossCheckController::class, 'crossCheck']);
});




/**
 * IGW and IOS Platform (Route Description as inline)
 */
Route::group([
    'prefix'      => 'platform/igwandios/report',
    'middleware'  => 'auth',
], function() {
    //IOS Daily IOF Reports Route Description
    Route::get('/iof/callsummary/old', [IofReportController::class, 'index']); /*All generated reports retrieve from report stored directory*/
    Route::get('/iof/callsummary/old/{download}', [IofReportController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/iof/callsummary/old/zip/{download}', [IofReportController::class, 'zipCreator']);
    Route::get('/iof/callsummary/old/delete/{delete}', [IofReportController::class, 'deleteFile']);
    Route::get('/iof/callsummary/old/clean/directory', [IofReportController::class, 'cleanDir']);
    Route::get('/iof/exec/', [IofReportController::class, 'executeReport']);

    Route::resource('/iof/company', IofcompanyController::class);

    //Route::get('/iof/daily/report', 'IGWANDIOS\IofDailySummaryReportController@testing');

    //IOF Daily call summary report new
    Route::get('/iof/daily/call/summary/report', [IofDailySummaryReportController::class, 'index']);
    Route::post('/iof/daily/call/summary/report', [IofDailySummaryReportController::class, 'generateReport']);
    Route::get('/iof/daily/call/summary/report/{download}', [IofDailySummaryReportController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/iof/daily/call/summary/report/delete/{delete}', [IofDailySummaryReportController::class, 'deleteFile']); /*Finally Delete report*/
    Route::get('/iof/daily/call/summary/report/zip/download', [IofDailySummaryReportController::class, 'zipCreator']);
    Route::get('/iof/daily/call/summary/report/clean/directory', [IofDailySummaryReportController::class, 'cleanDir']);

    //IOS Daily In-Out Bound Reports Route Description
    Route::get('/iof/inoutbound', [IofInOutBoundReportController::class, 'index']); /*View Loading*/
    Route::post('/iof/inoutbound', [IofInOutBoundReportController::class, 'reports']);
    Route::get('/iof/inoutbound/{download}', [IofInOutBoundReportController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/iof/inoutbound/delete/{delete}', [IofInOutBoundReportController::class, 'deleteFile']); /*Finally Delete report*/
    Route::get('/iof/inoutbound/zip/download', [IofInOutBoundReportController::class, 'zipCreator']);
    Route::get('/iof/inoutbound/clean/directory', [IofInOutBoundReportController::class, 'cleanDir']);


    //IGW and IOS comparison report
    Route::get('/comparison', [ComparisonReportController::class, 'index']);
    Route::post('/comparison', [ComparisonReportController::class, 'reportGenerate']);
    //Route::get('/comparison', [ComparisonReportController::class, 'files']); /*All generated reports retrieve from report-store directory*/
    Route::get('/comparison/{download}', [ComparisonReportController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/comparison/summary/{download}', [ComparisonReportController::class, 'downloadSummary']); /*Prepare reports downloadable*/
    Route::get('/comparison/delete/{delete}', [ComparisonReportController::class, 'deleteFile']);
    Route::get('/comparison/summary/delete/{delete}', [ComparisonReportController::class, 'deleteSummaryFile']);
    Route::get('/comparison/zip/download', [ComparisonReportController::class, 'zipCreator']);
    Route::get('/comparison/clean/directory', [ComparisonReportController::class, 'cleanDir']);
});

//Route::get('comparison', [CallSummaryController::class, 'dataAttachedInMailBody']);


/**
 * BanglaICX platform
 */

Route::group([
    'prefix'      => 'platform/banglaicx/report',
    'middleware'  => 'auth',
], function() {
    Route::get('/callsummary', [BanglaicxReportController::class, 'index']);
    Route::post('/callsummary', [BanglaicxReportController::class, 'reports']);
    Route::get('/callsummary/{download}', [BanglaicxReportController::class, 'getFile']); /*Prepare reports downloadable*/
    Route::get('/callsummary/delete/{delete}', [BanglaicxReportController::class, 'deleteFile']); /*Finally Delete report*/
    Route::get('/callsummary/zip/download', [BanglaicxReportController::class, 'zipCreator']);
    Route::get('/callsummary/clean/directory', [BanglaicxReportController::class, 'cleanDir']);
});





