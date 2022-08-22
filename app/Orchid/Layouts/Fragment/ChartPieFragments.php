<?php

namespace App\Orchid\Layouts\Fragment;

use Orchid\Screen\Layouts\Chart;

class ChartPieFragments extends Chart
{
    /**
     * Add a title to the Chart.
     *
     * @var string
     */
    protected $title = 'Распределение типов фрагментов, созданных на платформе';

    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'pie';

    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the chart.
     *
     * @var string
     */
    protected $target = 'fragmentsType';

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}
