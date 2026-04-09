<?php

namespace App\Http\Controllers;

use App\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    protected $forecastService;

    public function __construct(ForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    public function index()
    {
        $salesForecast = $this->forecastService->getSalesForecast(7);
        $demandForecast = $this->forecastService->getProductDemandForecast(7);
        $monthlyForecast = $this->forecastService->getMonthlySalesForecast(3);
        $restockPlan = $this->forecastService->getRestockPlan();

        return view('forecast.index', compact(
            'salesForecast', 
            'demandForecast', 
            'monthlyForecast',
            'restockPlan'
        ));
    }

    public function getApiData()
    {
        return response()->json([
            'sales' => $this->forecastService->getSalesForecast(7),
            'demand' => $this->forecastService->getProductDemandForecast(7),
            'monthly' => $this->forecastService->getMonthlySalesForecast(3),
            'restock_plan' => $this->forecastService->getRestockPlan(),
        ]);
    }
}