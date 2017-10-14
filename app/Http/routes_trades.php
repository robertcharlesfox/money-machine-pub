<?php

Route::get('autotrade/dashboard', 'AutoTradeController@getDashboard');
Route::get('autotrade/check_trades', 'AutoTradeController@getCheckTrades');
Route::get('autotrade/fundraising', 'AutoTradeController@getFundraising');
Route::get('autotrade/polls_clinton_vs_trump', 'AutoTradeController@getTvC2way');
Route::get('autotrade/debates', 'AutoTradeController@getDebates');
Route::get('autotrade/obama', 'AutoTradeController@getObama');
Route::get('autotrade/binary', 'AutoTradeController@getBinary');
Route::get('autotrade/states/dem', 'AutoTradeController@getStatesDem');
Route::get('autotrade/states/gop', 'AutoTradeController@getStatesGop');
Route::post('autotrade/contest/add', 'AutoTradeController@postContest');

Route::get('autotrade/binary/speed/{question_id}/{speed}', 'AutoTradeController@getChangeBinarySpeed');
Route::get('autotrade/contests/speed/{contest_id}/{speed}', 'AutoTradeController@getChangeContestSpeed');
Route::get('autotrade/contests/activate/{contest_id}', 'AutoTradeController@getActivateContest');
Route::get('autotrade/contests/deactivate/{contest_id}', 'AutoTradeController@getDeactivateContest');
Route::get('autotrade/trade/queue', 'AutoTradeController@getTradeQueue');

Route::get('autotrade/{type}', 'AutoTradeController@getOtherCompetitions');

Route::get('bot/droptrades', 'BotController@getRcpDropTrades');
Route::post('bot/droptrades/definition', 'BotController@postRcpDropTradeDefinition');
Route::post('bot/droptrades/values', 'BotController@postRcpDropTradeValues');
Route::get('bot/droptrades/deactivate/{trade_id}', 'BotController@deactivateRcpDropTrade');

Route::get('bot/addtrades', 'BotController@getRcpAddTrades');
Route::post('bot/addtrades/definition', 'BotController@postRcpAddTradeDefinition');
Route::post('bot/addtrades/values', 'BotController@postRcpAddTradeValues');
Route::get('bot/addtrades/deactivate/{trade_id}', 'BotController@deactivateRcpAddTrade');

Route::get('bot/trade', 'BotController@getTrade');

Route::get('pi/scrape/question/{question_id}', 'PiController@scrapeSingleContestFromAdmin');
Route::get('pi/scrape/{contest_type}/{speed}', 'PiController@scrapeMarkets');
Route::get('pi/visit/contest/{contest_id}/{source?}', 'PiController@visitCompetitionQuestions');
Route::get('pi/visit/question/{question_id}/{source?}', 'PiController@visitQuestion');
Route::get('pi/trade/contest/{contest_id}/{source?}', 'PiController@tradeCompetitionQuestions');
Route::get('pi/trade/question/{question_id}/{source?}', 'PiController@tradeQuestion');
Route::get('pi/autotrade/{contest_type}', 'PiController@autotradeQuestions');
Route::get('pi/cancel/obama', 'PiController@cancelObamaOrders');
Route::get('pi/cancel/contest/{contest_id}/{source?}', 'PiController@cancelCompetitionQuestionOrders');
Route::get('pi/cancel/question/{question_id}/{source?}', 'PiController@cancelQuestionOrders');
