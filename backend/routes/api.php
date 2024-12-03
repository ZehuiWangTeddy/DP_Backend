<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', 'AuthController@register')->name('auth.register');
        Route::post('/login', 'AuthController@login')->name('auth.login');
        Route::post('/password-reset', 'AuthController@passwordReset')->name('auth.passwordReset');
        Route::post('/verification', 'AuthController@verify')->name('auth.verification');
        Route::post('/invitation', 'AuthController@invite')->name('auth.invitation');
    });

    Route::middleware('auth:api')->group(function () {

        Route::prefix('users')->group(function () {
            Route::get('/', 'UserController@index')->name('users.index');
            Route::get('/{id}', 'UserController@show')->name('users.show');
            Route::put('/{id}', 'UserController@update')->name('users.update');
            Route::delete('/{id}', 'UserController@destroy')->name('users.destroy');
        });

        Route::prefix('subscription')->group(function () {
            Route::get('/', 'SubscriptionController@index')->name('subscription.index');
            Route::get('/payment', 'SubscriptionController@payment')->name('subscription.payment');
            Route::put('/start-date', 'SubscriptionController@updateStartDate')->name('subscription.updateStartDate');
            Route::put('/end-date', 'SubscriptionController@updateEndDate')->name('subscription.updateEndDate');
            Route::put('/payment-date', 'SubscriptionController@updatePaymentDate')->name('subscription.updatePaymentDate');
        });

        Route::prefix('profiles')->group(function () {
            Route::get('/', 'ProfileController@index')->name('profiles.index');
            Route::get('/{id}', 'ProfileController@show')->name('profiles.show');
            Route::put('/{id}', 'ProfileController@update')->name('profiles.update');
            Route::delete('/{id}', 'ProfileController@destroy')->name('profiles.destroy');

            Route::prefix('{id}/preferences')->group(function () {
                Route::get('/', 'PreferenceController@index')->name('preferences.index');
                Route::post('/', 'PreferenceController@store')->name('preferences.store');
                Route::put('/', 'PreferenceController@update')->name('preferences.update');
            });

            Route::prefix('{id}/watch-history')->group(function () {
                Route::get('/', 'WatchHistoryController@index')->name('watchHistory.index');
                Route::post('/movie/start', 'WatchHistoryController@startMovie')->name('watchHistory.startMovie');
                Route::post('/movie/finish', 'WatchHistoryController@finishMovie')->name('watchHistory.finishMovie');
                Route::post('/series/{seriesId}/season/{seasonId}/episode/start', 'WatchHistoryController@startEpisode')->name('watchHistory.startEpisode');
                Route::post('/series/{seriesId}/season/{seasonId}/episode/finish', 'WatchHistoryController@finishEpisode')->name('watchHistory.finishEpisode');
            });

            Route::prefix('{id}/watch-list')->group(function () {
                Route::get('/', 'WatchListController@index')->name('watchList.index');
                Route::post('/movie', 'WatchListController@addMovie')->name('watchList.addMovie');
                Route::delete('/movie', 'WatchListController@removeMovie')->name('watchList.removeMovie');
                Route::post('/series/{seriesId}/season/{seasonId}/episode', 'WatchListController@addEpisode')->name('watchList.addEpisode');
                Route::delete('/series/{seriesId}/season/{seasonId}/episode', 'WatchListController@removeEpisode')->name('watchList.removeEpisode');
            });

            Route::prefix('{id}/recommendations')->group(function () {
                Route::get('/', 'RecommendationController@index')->name('recommendations.index');
                Route::post('/movie', 'RecommendationController@addMovie')->name('recommendations.addMovie');
                Route::post('/series', 'RecommendationController@addSeries')->name('recommendations.addSeries');
                Route::delete('/movie', 'RecommendationController@removeMovie')->name('recommendations.removeMovie');
                Route::delete('/series', 'RecommendationController@removeSeries')->name('recommendations.removeSeries');
            });
        });

        Route::apiResource('movies', 'MovieController')->except(['edit', 'create'])->names([
            'index' => 'movies.index',
            'store' => 'movies.store',
            'show' => 'movies.show',
            'update' => 'movies.update',
            'destroy' => 'movies.destroy',
        ]);

        Route::prefix('movies/{id}/subtitles')->group(function () {
            Route::post('/', 'SubtitleController@store')->name('movies.subtitles.store');
            Route::get('/', 'SubtitleController@index')->name('movies.subtitles.index');
            Route::put('/', 'SubtitleController@update')->name('movies.subtitles.update');
            Route::delete('/', 'SubtitleController@destroy')->name('movies.subtitles.destroy');
        });

        Route::apiResource('series', 'SeriesController')->except(['edit', 'create'])->names([
            'index' => 'series.index',
            'store' => 'series.store',
            'show' => 'series.show',
            'update' => 'series.update',
            'destroy' => 'series.destroy',
        ]);

        Route::prefix('series/{seriesId}/seasons')->group(function () {
            Route::post('/', 'SeasonController@store')->name('seasons.store');
            Route::put('/', 'SeasonController@update')->name('seasons.update');
            Route::delete('/', 'SeasonController@destroy')->name('seasons.destroy');

            Route::prefix('{seasonId}/episodes')->group(function () {
                Route::post('/', 'EpisodeController@store')->name('episodes.store');
                Route::put('/', 'EpisodeController@update')->name('episodes.update');
                Route::delete('/', 'EpisodeController@destroy')->name('episodes.destroy');

                Route::prefix('{episodeId}/subtitles')->group(function () {
                    Route::post('/', 'SubtitleController@store')->name('episodes.subtitles.store');
                    Route::get('/', 'SubtitleController@index')->name('episodes.subtitles.index');
                    Route::put('/', 'SubtitleController@update')->name('episodes.subtitles.update');
                    Route::delete('/', 'SubtitleController@destroy')->name('episodes.subtitles.destroy');
                });
            });
        });

    });

});
