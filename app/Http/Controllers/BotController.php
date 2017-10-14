<?php

use Illuminate\Http\Request;

use App\Jobs\TraderBotExecuteAutoTrade;
use App\Jobs\WarmupUpdateBot;

class BotController extends Controller {

    public $tradeable_contests = array(1, 3, 8, 12, 13,);

    public function getRcpDropTrades()
    {
      $drop_trade_contests = array();
      $drop_trade_pollsters = array();
      $drop_trade_questions = array();
      foreach ($this->tradeable_contests as $id) {
        $contest = PiContest::find($id);
        // $contest->checkForDropTrades();
        $these_pollsters = $contest->last_rcp_update()->rcp_contest_pollsters_for_projections();
        $these_questions = $contest->pi_questions()->where('active', '=', 1)->get()->toArray();
        $drop_trade_contests[] = $contest;
        $drop_trade_pollsters = array_merge($drop_trade_pollsters, $these_pollsters);
        $drop_trade_questions = array_merge($drop_trade_questions, $these_questions);
      }
      return View::make('m.rcp.droptrades.index')
                  ->withDropTrades(RcpDropTrade::where('active', '=', 1)->orderBy('id', 'desc')->get())
                  ->withDropTradeContests($drop_trade_contests)
                  ->withDropTradePollsters($drop_trade_pollsters)
                  ->withDropTradeQuestions($drop_trade_questions)
              ;
    }

    public function postRcpDropTradeDefinition(Request $request)
    {
      $dt = new RcpDropTrade();
      $dt->saveDropTradeDefinition($request->input());
      return redirect()->back()
          ->with('success', 'New RcpDropTrade was saved.');
    }

    public function postRcpDropTradeValues(Request $request)
    {
      $dt = RcpDropTrade::find($request->trade_id);
      $dt->saveDropTradeValues($request->input());
      return redirect()->back()
          ->with('success', 'RcpDropTrade values saved.');
    }

    public function deactivateRcpDropTrade($trade_id)
    {
      $dt = RcpDropTrade::find($trade_id);
      $dt->deactivate();
      return redirect()->back();
    }

    public function getRcpAddTrades()
    {
      $add_trade_contests = array();
      $add_trade_pollsters = array();
      $add_trade_questions = array();
      foreach ($this->tradeable_contests as $id) {
        $contest = PiContest::find($id);
        if ($id == 1) {
          // $contest->checkRasmussenAddTrades(RcpContestPollster::find(1349));
        }
        $these_pollsters = $contest->last_rcp_update()->rcp_contest_pollsters_for_projections();
        $these_questions = $contest->pi_questions()->where('active', '=', 1)->get()->toArray();
        $add_trade_contests[] = $contest;
        $add_trade_pollsters = array_merge($add_trade_pollsters, $these_pollsters);
        $add_trade_questions = array_merge($add_trade_questions, $these_questions);
      }
      return View::make('m.rcp.addtrades.index')
                  ->withAddTrades(RcpAddTrade::where('active', '=', 1)
                        ->orderBy('rcp_contest_pollster_id', 'asc')
                        ->orderBy('poll_result', 'desc')
                        ->orderBy('pi_question_id', 'asc')
                        ->get())
                  ->withAddTradeContests($add_trade_contests)
                  ->withAddTradePollsters($add_trade_pollsters)
                  ->withAddTradeQuestions($add_trade_questions)
              ;
    }

    public function postRcpAddTradeDefinition(Request $request)
    {
      $at = new RcpAddTrade();
      $at->saveAddTradeDefinition($request->input());
      return redirect()->back()
          ->with('success', 'New RcpAddTrade was saved.');
    }

    public function postRcpAddTradeValues(Request $request)
    {
      $at = RcpAddTrade::find($request->trade_id);
      $at->saveAddTradeValues($request->input());
      return redirect()->back()
          ->with('success', 'RcpAddTrade values saved.');
    }

    public function deactivateRcpAddTrade($trade_id)
    {
      $at = RcpAddTrade::find($trade_id);
      $at->deactivate();
      return redirect()->back();
    }

    public function keepTraderBotWarm()
    {
        $bot = new TraderBot();
        $bot->keepTraderBotWarm();
    }

    public function keepUpdateBotWarm()
    {
        $job = new WarmupUpdateBot();
        $this->dispatch($job);
    }

    public function getTrade()
    {
        $bot = new TraderBot();
        $bot->autoTrade();
    }
}