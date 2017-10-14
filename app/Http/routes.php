<?php
  // Are these 2 used?
  Route::get('datatables', 'DataTablesController@index');
  Route::get('datatables/data', array('as' => 'admin.datatables.data', 'uses' => 'DataTablesController@data'));

Route::get('/', function () {
    return view('welcome');
});

Route::get('dash', 'MController@getDashboard');
Route::get('bot/updatebot/warmup', 'BotController@keepUpdateBotWarm');
Route::get('bot/traderbot/warmup', 'BotController@keepTraderBotWarm');
Route::get('twitter', 'TwitterController@getPhirehose');

Route::get('analyze/gallup', 'MController@getGallupDailies');
Route::post('analyze/gallup', 'MController@postGallupDailies');
Route::get('analyze/rasmussen', 'MController@getRasmussenDailies');
Route::post('analyze/rasmussen', 'MController@postRasmussenDailies');

Route::get('pi/ajax/analyze/lines/{contest_id}/{question_id}/{line_index}/{take}/{skip?}', 'MController@getAjaxLines');
Route::get('pi/analyze/lines', 'MController@getLines');
Route::get('pi/ajax/analyze/moflow/{scrape_id}', 'MController@getAjaxMoFlow');
Route::get('pi/analyze/moflow/{pi_contest_id}', 'MController@getMoFlow');
Route::get('pi/analyze/dashboard', 'MController@getAnalysisDashboard');
Route::get('pi/market_depth', 'PiController@analyzeMarketDepth');
