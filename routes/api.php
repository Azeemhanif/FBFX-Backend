<?php

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\AffiliateLinkController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostSignalController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::controller(UserController::class)->prefix('user/')->group(function () {
    Route::post('signup', 'signup')->name('user.signup');
    Route::post('login', 'login')->name('user.login');
    Route::post('socialSignup', 'socialSignup');
    // Route::post('/socialLogin', 'socialLogin');
    Route::post('forget-password', 'forget');
    Route::get('testCronJob', 'testCronJob');
    Route::get('listing', 'usersListing');
    Route::get('tokenListing', 'tokenListing');
    Route::get('setting', 'setting')->middleware("auth:sanctum");
    Route::get('profile', 'profileDetail')->middleware("auth:sanctum");
    Route::post('feedback', 'feedback')->middleware("auth:sanctum");
    Route::post('logout', 'logout');
    Route::post('contact-us', 'contactUs')->middleware("auth:sanctum");
    Route::post('update/profile', 'updateProfile')->middleware("auth:sanctum")->name('user.updateProfile');
    Route::post('verify/otp', 'verifyOtp')->middleware("auth:sanctum")->name('user.verifyOtp');
    Route::get('regenerate/otp', 'regenerateOtp')->middleware("auth:sanctum")->name('user.regenerateOtp');
    Route::post('risk/calculator', 'riskCalculator')->middleware("auth:sanctum");
    Route::get("notifications/listing", "notificationListing")->middleware("auth:sanctum");
    Route::delete('account', 'deleteAccount')->middleware("auth:sanctum");
    Route::post('validateReceipt/{type}', 'validateReceipt')->middleware("auth:sanctum");
});

Route::middleware('auth:sanctum')->prefix('user/')->group(
    function () {
        Route::controller(PostSignalController::class)->prefix('signals/')->group(
            function () {
                Route::get("recent", "index");
                Route::get("history", "history");
                Route::get("detail/{id}", "edit");
                Route::get("favourite/{id}", "addFavourite");
                Route::get("favourite", "getFavourite");
            }
        );
        Route::controller(MembershipController::class)->prefix('ib-broker/')->group(
            function () {
                Route::post("add", "addIbBroker");
                Route::get("listing", "ibBrokerListing");
            }
        );

        Route::controller(AffiliateLinkController::class)->prefix('affiliate/links/')->group(
            function () {
                Route::get("/{id}", "edit");
            }
        );
        Route::controller(AcademyController::class)->prefix('academy/')->group(
            function () {
                Route::get("listing", "show");
            }
        );
        Route::controller(SubscriptionController::class)->prefix('purchase/')->middleware('auth:sanctum')->group(
            function () {
                Route::post("package", "purchasePackage");
            }
        );
    }
);

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/')->group(
    function () {
        Route::controller(UserController::class)->group(
            function () {
                Route::post("add", "addAdmin");
                Route::get("detail/{id}", "detailAdmin");
                Route::get("listing", "listingAdmin");
                Route::delete('/{id}', 'destroy');
            }
        );

        Route::controller(PostSignalController::class)->prefix('signals/')->group(
            function () {
                Route::post("create", "store");
                Route::post("manual/close", "manualClose");
                Route::delete("delete/{id}", "destroy");
            }
        );

        Route::controller(AffiliateLinkController::class)->prefix('affiliate/links/')->group(
            function () {
                Route::post("add", "store");
                Route::get("/{id}", "edit");
            }
        );

        Route::controller(AcademyController::class)->prefix('academy/')->group(
            function () {
                Route::post("add", "store");
                Route::get("listing", "show");
                Route::get("/{id}", "edit");
                Route::delete('/{id}', 'destroy');
            }
        );

        Route::controller(NotificationController::class)->prefix('notifications/')->group(
            function () {
                Route::post("add", "store");
                Route::delete("delete/{id}", "destroy");
            }
        );

        Route::controller(MembershipController::class)->prefix('membership/')->group(
            function () {
                Route::post("add", "store");
                // Route::post("add/user", "addUsers");
                Route::get("listing/user", "listingUsers");
                Route::post("add/premium/user", "addPremiumUsers");
                Route::get("listing/premium/user", "listingPremiumUsers");
            }
        );
    }
);

//old routes
Route::post("user_login", [ApiController::class, "user_login"]);

Route::get("users", [ApiController::class, "users"]);
Route::post("add_users", [ApiController::class, "add_users"]);
Route::post("forget_password", [ApiController::class, "forget_password"]);

Route::get("edit_user/{id}", [ApiController::class, "edit_user"]);
// Route::post("update_user", [ApiController::class, "update_user"]);
Route::get("user_delete/{id}", [ApiController::class, "user_delete"]);


Route::get("admins", [ApiController::class, "admins"]);
// Route::post("add_admin", [ApiController::class, "add_admin"]);

// Route::get("edit_admin/{id}", [ApiController::class, "edit_admin"]);
// Route::post("update_admin", [ApiController::class, "update_admin"]);
// Route::get("admin_delete/{id}", [ApiController::class, "admin_delete"]);


Route::get("post_signal", [ApiController::class, "post_signal"]);
Route::get("history_signal", [ApiController::class, "history_signal"]);
Route::get("user_post_signal/{id}", [ApiController::class, "user_post_signal"]);
// Route::post("add_post_signal", [ApiController::class, "add_post_signal"]);

// Route::get("edit_post_signal/{id}", [ApiController::class, "edit_post_signal"]);
Route::post("update_post_signal", [ApiController::class, "update_post_signal"]);
Route::post("update_pips", [ApiController::class, "update_pips"]);
Route::get("post_signal_delete/{id}", [ApiController::class, "post_signal_delete"]);


Route::get("announcement", [ApiController::class, "announcement"]);
Route::post("add_announcement", [ApiController::class, "add_announcement"]);

Route::get("edit_announcement/{id}", [ApiController::class, "edit_announcement"]);
Route::post("update_announcement", [ApiController::class, "update_announcement"]);
Route::get("announcement_delete/{id}", [ApiController::class, "announcement_delete"]);


Route::get("notification", [ApiController::class, "notification"]);
Route::post("add_notification", [ApiController::class, "add_notification"]);

Route::get("edit_notification/{id}", [ApiController::class, "edit_notification"]);
Route::post("update_notification", [ApiController::class, "update_notification"]);
Route::get("notification_delete/{id}", [ApiController::class, "notification_delete"]);


Route::get("affiliate_link", [ApiController::class, "affiliate_link"]);
// Route::post("add_affiliate_link", [ApiController::class, "add_affiliate_link"]);

// Route::get("edit_affiliate_link/{id}", [ApiController::class, "edit_affiliate_link"]);
// Route::post("update_affiliate_link", [ApiController::class, "update_affiliate_link"]);
Route::get("affiliate_link_delete/{id}", [ApiController::class, "affiliate_link_delete"]);



Route::get("affiliate_link", [ApiController::class, "affiliate_link"]);
// Route::post("add_affiliate_link", [ApiController::class, "add_affiliate_link"]);

// Route::get("edit_affiliate_link/{id}", [ApiController::class, "edit_affiliate_link"]);
// Route::post("update_affiliate_link", [ApiController::class, "update_affiliate_link"]);
Route::get("affiliate_link_delete/{id}", [ApiController::class, "affiliate_link_delete"]);


Route::get("premium", [ApiController::class, "premium"]);
Route::post("add_premium", [ApiController::class, "add_premium"]);

Route::get("edit_premium/{id}", [ApiController::class, "edit_premium"]);
Route::post("update_premium", [ApiController::class, "update_premium"]);
Route::get("premium_delete/{id}", [ApiController::class, "premium_delete"]);

Route::post("update_premium_detail", [ApiController::class, "update_premium_detail"]);

Route::get("academy", [ApiController::class, "academy"]);
Route::post("add_academy", [ApiController::class, "add_academy"]);

Route::get("edit_academy/{id}", [ApiController::class, "edit_academy"]);
Route::post("update_academy", [ApiController::class, "update_academy"]);
Route::get("academy_delete/{id}", [ApiController::class, "academy_delete"]);


Route::get("analysis", [ApiController::class, "analysis"]);
Route::post("add_analysis", [ApiController::class, "add_analysis"]);

Route::get("edit_analysis/{id}", [ApiController::class, "edit_analysis"]);
Route::post("update_analysis", [ApiController::class, "update_analysis"]);
Route::get("analysis_delete/{id}", [ApiController::class, "analysis_delete"]);

Route::post("send_otp", [ApiController::class, "send_otp"]);

Route::post("add_plan_request", [ApiController::class, "add_plan_request"]);

Route::get("get_package", [ApiController::class, "get_package"]);

Route::post("add_chat", [ApiController::class, "add_chat"]);
Route::get("chat", [ApiController::class, "chat"]);


Route::post("add_fvrt", [ApiController::class, "add_fvrt"]);
Route::get("fvrt", [ApiController::class, "fvrt"]);

Route::post("feedback", [ApiController::class, "feedback"]);



Route::get('get_VPS', [ApiController::class, 'affiliate_get_VPS']);
Route::get('get_trade', [ApiController::class, 'affiliate_get_trade']);
Route::get('get_PAMM', [ApiController::class, 'affiliate_get_PAMM']);
Route::get('get_IB_broker', [ApiController::class, 'affiliate_get_IB_broker']);

Route::get('all_user', [ApiController::class, 'all_user']);
Route::get('free_user', [ApiController::class, 'free_user']);
Route::get('premium_user', [ApiController::class, 'premium_user']);

Route::get('delete_premium/{id}', [ApiController::class, 'delete_premium']);


Route::get("signal_posted/{id}", [ApiController::class, "signal_posted"]);

Route::get("monthly_status", [ApiController::class, "monthly_status"]);
Route::post("worst_pips", [ApiController::class, "worst_pips"]);

Route::post("home_filter", [ApiController::class, "filter"]);


Route::post("closed_signal", [ApiController::class, "closed_signal"]);

Route::post("premium_free", [ApiController::class, "premium_free"]);
