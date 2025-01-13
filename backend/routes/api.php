<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SeasonController;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SubtitleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WatchHistoryController;
use App\Http\Controllers\Api\WatchListController;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::get('login', [AuthController::class, 'loginFailed'])->name('login'); // response for login failed
    Route::post('/register', [AuthController::class, "register"])->name('auth.register');
    Route::post('/login', [AuthController::class, "login"])->name('auth.login');
    Route::post('/send-reset-password-email', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:60,1');
    Route::post('/reset-password-with-forgot-email', [AuthController::class, 'resetPasswordWithForgotEmail'])->middleware('throttle:60,1')->name('password.reset');
    Route::post('/password-reset', action: [AuthController::class, "resetPassword"])->middleware("auth:api")->name('password.resetPassword');
    Route::post('/logout', [AuthController::class, "logout"])->name('auth.logout')->middleware("auth:api");
});

Route::middleware('auth:api')->group(function () {

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, "index"])->name('users.index');
        Route::get('/{id}', [UserController::class, "show"])->name('users.show');
        Route::put('/{id}', [UserController::class, "update"])->name('users.update');
        Route::delete('/{id}', [UserController::class, "destroy"])->name('users.destroy');
    });

    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionController::class, "index"])->name('subscription.index');
        Route::get('/{id}', [SubscriptionController::class, "show"])->name('subscription.show');
        Route::post('/', [SubscriptionController::class, "store"])->name('subscription.store');
        Route::put('/{id}', [SubscriptionController::class, "update"])->name('subscription.update');
        Route::delete('/{id}', [SubscriptionController::class, "destroy"])->name('subscription.destroy');
    });

    Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, "index"])->name('profiles.index');
        Route::post('/', [ProfileController::class, "store"])->name('profiles.store');
        Route::get('/{id}', [ProfileController::class, "show"])->name('profiles.show');
        Route::put('/{id}', [ProfileController::class, "update"])->name('profiles.update');
        Route::delete('/{id}', [ProfileController::class, "destroy"])->name('profiles.destroy');

        Route::prefix('{id}/preferences')->group(function () {
            Route::get('/', [PreferenceController::class, "show"])->name('preferences.show');
            Route::post('/', [PreferenceController::class, "store"])->name('preferences.store');
            Route::put('/', [PreferenceController::class, "update"])->name('preferences.update');
            Route::delete('/', [PreferenceController::class, "destroy"])->name('PreferenceController.destroy');
        });

        Route::prefix('{id}/watch-history')->group(function () {
            Route::get('/', [WatchHistoryController::class, "index"])->name('watchHistory.index');
            Route::post('/movie/start', [WatchHistoryController::class, "startMovie"])->name('watchHistory.startMovie');
            Route::post('/movie/finish', [WatchHistoryController::class, "finishMovie"])->name('watchHistory.finishMovie');
            Route::delete('/movie/{movieId}', [WatchHistoryController::class, "removeMovie"])->name('watchHistory.removeMovie');
            Route::post('/series/{seriesId}/season/{seasonId}/episode/start', [WatchHistoryController::class, "startEpisode"])->name('watchHistory.startEpisode');
            Route::post('/series/{seriesId}/season/{seasonId}/episode/finish', [WatchHistoryController::class, "finishEpisode"])->name('watchHistory.finishEpisode');
            Route::delete('/series/{seriesId}/season/{seasonId}/episode/{episodeID}', [WatchHistoryController::class, "removeEpisode"])->name('watchHistory.RemoveEpisode');
        });

        Route::prefix('{id}/watch-list')->group(function () {
            Route::get('/', [WatchListController::class, "index"])->name('watchList.index');
            Route::post('/movie', [WatchListController::class, "addMovie"])->name('watchList.addMovie');
            Route::put('/movie', [WatchListController::class, "finishMovie"])->name('watchList.finishMovie');
            Route::delete('/movie/{movieId}', [WatchListController::class, "removeMovie"])->name('watchList.removeMovie');
            Route::post('/series/{seriesId}/season/{seasonId}/episode', [WatchListController::class, "addEpisode"])->name('watchList.addEpisode');
            Route::put('/series/{seriesId}/season/{seasonId}/episode', [WatchListController::class, "finishEpisode"])->name('watchList.finishEpisode');
            Route::delete('/series/{seriesId}/season/{seasonId}/episode/{episodeId}', [WatchListController::class, "removeEpisode"])->name('watchList.removeEpisode');
        });

        Route::prefix('{id}/recommendations')->group(function () {
            Route::get('/', [RecommendationController::class, "index"])->name('recommendations.index');
        });
    });

    // Add separate routes for movies and episodes
    Route::prefix('movies')->group(function () {
        Route::get('/', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/{id}', [MovieController::class, 'show'])->name('movies.show');
        Route::post('/', [MovieController::class, 'store'])->name('movies.store');
        Route::put('/{id}', [MovieController::class, 'update'])->name('movies.update');
        Route::delete('/{id}', [MovieController::class, 'destroy'])->name('movie.destroy');

        Route::prefix('/{id}/subtitles')->group(function () {
            Route::post('/', [SubtitleController::class, "store"])->name('movies.subtitles.store');
            Route::get('/', [SubtitleController::class, "index"])->name('movies.subtitles.index');
            Route::put('/', [SubtitleController::class, "update"])->name('movies.subtitles.update');
            Route::delete('/', [SubtitleController::class, "destroy"])->name('movies.subtitles.destroy');
        });
    });

    Route::prefix('series')->group(function () {
        Route::get('/', [SeriesController::class, 'index'])->name('series.index');
        Route::get('/{id}', [SeriesController::class, 'show'])->name('series.show');
        Route::post('/', [SeriesController::class, 'store'])->name('series.store');
        Route::put('/{id}', [SeriesController::class, 'update'])->name('series.update');
        Route::delete('/{id}', [SeriesController::class, 'destroy'])->name('series.destroy');

        Route::prefix('/{seriesId}/seasons')->group(function () {
            Route::post('/', [SeasonController::class, "store"])->name('seasons.store');
            Route::get('/', [SeasonController::class, "index"])->name('seasons.index');
            Route::put('/{id}', [SeasonController::class, "update"])->name('seasons.update');
            Route::delete('/{id}', [SeasonController::class, "destroy"])->name('seasons.destroy');

            Route::prefix('/{seasonId}/episodes')->group(function () {
                Route::post('/', [EpisodeController::class, "store"])->name('episodes.store');
                Route::get('/', [EpisodeController::class, "index"])->name('episodes.index');
                Route::put('/{id}', [EpisodeController::class, "update"])->name('episodes.update');
                Route::delete('/{id}', [EpisodeController::class, "destroy"])->name('episodes.destroy');

                Route::prefix('/{episodeId}/subtitles')->group(function () {
                    Route::post('/', [SubtitleController::class, "store"])->name('episodes.subtitles.store');
                    Route::get('/', [SubtitleController::class, "index"])->name('episodes.subtitles.index');
                    Route::put('/{id}', [SubtitleController::class, "update"])->name('episodes.subtitles.update');
                    Route::delete('/{id}', [SubtitleController::class, "destroy"])->name('episodes.subtitles.destroy');
                });
            });
        });
    });

});
