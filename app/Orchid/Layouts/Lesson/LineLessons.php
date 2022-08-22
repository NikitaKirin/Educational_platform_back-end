<?php

namespace App\Orchid\Layouts\Lesson;

use Orchid\Screen\Layouts\Chart;

class LineLessons extends Chart
{
    /**
     * Add a title to the Chart.
     *
     * @var string
     */
    protected $title = 'Динамика уроков за последние 2 недели';

    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'line';

    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the chart.
     *
     * @var string
     */
    protected $target = 'lessons';

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;

    /**
     * Configuring line.
     *
     * @var array
     */
    protected $lineOptions = [
        'spline'     => 1,
        'regionFill' => 1,
        'hideDots'   => 0,
        'hideLine'   => 0,
        'heatline'   => 0,
        'dotSize'    => 0,
    ];
}
