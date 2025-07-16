<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\SPMB\SpmbPollData;

class FeedbackPieChart
{
    protected $chart;
    protected $month;
    protected $year;
    protected $chartTitle;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build($month, $year, $chartTitle): \ArielMejiaDev\LarapexCharts\PieChart
    {
        $this->month = $month; // Set your desired month
        $this->year = $year;
        $this->chartTitle = $chartTitle;

        // $pollCode = 1; // Set your desired poll_code

        // Filter data based on the specified month and poll_code
        $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
            ->whereYear('created_at', '=', $year)
            ->where('poll_code', '=', 1)
            ->get();

        // Count the occurrences of poll_code with value 1 in the filtered data
        $kurangPuas = $filteredData->where('poll_code', 1)->count();

        // Filter data based on the specified month and poll_code
        $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
            ->whereYear('created_at', '=', $year)
            ->where('poll_code', '=', 2)
            ->get();

        // Count the occurrences of poll_code with value 1 in the filtered data
        $puas = $filteredData->where('poll_code', 2)->count();

        // Filter data based on the specified month and poll_code
        $filteredData = SpmbPollData::whereMonth('created_at', '=', $month)
            ->whereYear('created_at', '=', $year)
            ->where('poll_code', '=', 3)
            ->get();

        // Count the occurrences of poll_code with value 1 in the filtered data
        $sangatPuas = $filteredData->where('poll_code', 3)->count();

        return $this->chart->pieChart()
            ->setTitle($chartTitle)
            ->setSubtitle('Data ' . $month . '-' . $year)
            ->addData([$kurangPuas, $puas, $sangatPuas])
            ->setColors(['#dd1b1b', '#fbb03b', '#8cc63e'])
            ->setLabels(['Kurang Puas', 'Puas', 'Sangat Puas']);
    }
}
