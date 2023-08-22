<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\addMedia;
use App\Http\Requests\idValidate;
use App\Imports\PeriodsImport;
use App\Models\admin_log;
use App\Http\Controllers\GeneralController;
use App\Models\GeneralModel as model_class;
use App\Models\adminUser;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class adminUsers extends GeneralController
{

    /////////////
    public function chart($id)
    {

        if (isValidValue($id) != true) {
            return back()->withErrors(["خطایی رخ داده است"]);
        }
        $admin_user = adminUser::where('id', $id)->first();
        if ($admin_user != null) {
            $this_year = strval(verta()->today()->format('Y'));
            $this_month = strval(verta()->today()->format('m'));
            $this_day = strval(verta()->today()->format('d'));
            $today = strval(verta()->today()->format('Y-m-d'));
            $yesterday = strval(verta()->yesterday()->format('Y-m-d'));
            /////////////////////////////////////////
            $trafficPerYear =
                intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-%-%")
                ->where('admin_id', $id)
                ->where('operator', "+")
                ->get()->sum("amount"))
                -
                intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-%-%")
                    ->where('admin_id', $id)
                    ->where('operator', "-")
                    ->get()->sum("amount"));

            $trafficPerMount =
                intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-" . $this_month . "-%")
                ->where('admin_id', $id)
                ->where('operator', "+")
                ->get()->sum("amount"))
            -
            intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-" . $this_month . "-%")
                ->where('admin_id', $id)
                ->where('operator', "-")
                ->get()->sum("amount"));

            $trafficPerYesterday =
                intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $yesterday)
                    ->where('admin_id', $id)
                    ->where('operator', "+")
                    ->get()->sum("amount"))
                -
                intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $yesterday)
                    ->where('admin_id', $id)
                    ->where('operator', "-")
                    ->get()->sum("amount"));

            $trafficPerToday =
            intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $today)
                ->where('admin_id', $id)
                ->where('operator', "+")
                ->get()->sum("amount"))
            -
            intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $today)
                ->where('admin_id', $id)
                ->where('operator', "-")
                ->get()->sum("amount"));

            //////////////
            $trafficAll =
                intval(getGeneralModel("traffic_logs")
                    ->where('admin_id', $id)
                    ->where('operator', "+")
                    ->get()->sum("amount"))
                -
                intval(getGeneralModel("traffic_logs")
                    ->where('admin_id', $id)
                    ->where('operator', "-")
                    ->get()->sum("amount"));
            //////////////////
            $chart_month_index = [];
            $chart_month_traffics = [];
            for ($index = 1; $index <= 12; $index++) {
                $indexNum = $index;
                if (strlen(strval($index)) == 1) {
                    $indexNum = '0' . strval($index);
                }
                array_push($chart_month_index, $this_year . "/" . $indexNum);
                array_push($chart_month_traffics,
                    (intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-" . $indexNum . "-%")
                        ->where('admin_id', $id)
                        ->where('operator', "+")
                        ->get()->sum("amount"))
                    -
                    intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-" . $indexNum . "-%")
                        ->where('admin_id', $id)
                        ->where('operator', "-")
                        ->get()->sum("amount")))
                );
            }
            /////////////////////////////////////
            $chart_daily_index = [];
            $chart_daily_traffics = [];
            for ($index = 1; $index <= 30; $index++) {
                $indexNum = $index;
                if (strlen(strval($index)) == 1) {
                    $indexNum = '0' . strval($index);
                }
                array_push($chart_daily_index, $this_year . "/" . $this_month . "/" . $indexNum);
                array_push($chart_daily_traffics,

                    (intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-" . $this_month . "-" . $indexNum)
                            ->where('admin_id', $id)
                            ->where('operator', "+")
                            ->get()->sum("amount"))
                        -
                        intval(getGeneralModel("traffic_logs")->where('date', "LIKE", $this_year . "-" . $this_month . "-" . $indexNum)
                            ->where('admin_id', $id)
                            ->where('operator', "-")
                            ->get()->sum("amount")))
                );
            }
            return view( 'admin.pages.adminUsers.chart', compact("admin_user",
                'trafficPerYear', 'trafficPerMount', 'trafficPerYesterday', 'trafficPerToday'
                , 'trafficAll', 'admin_user', 'chart_month_index', 'chart_daily_index', 'chart_daily_traffics', 'chart_month_traffics'
            ));
        } else {
            return back()->withErrors('خطایی رخ داده است');
        }
    }
    ///// end admin Users

}
