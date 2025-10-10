<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class UserGrowthChart extends Chart
{
    /**
     * Chart type.
     *
     * @var string
     */
    protected $type = self::TYPE_LINE;

    /**
     * Chart height.
     *
     * @var int
     */
    protected $height = 320;

    /**
     * Highlight growth points.
     *
     * @var int
     */
    protected $valuesOverPoints = 1;

    /**
     * Line styling.
     *
     * @var array<string, int>
     */
    protected $lineOptions = [
        'spline'     => 1,
        'regionFill' => 0,
        'hideDots'   => 0,
        'hideLine'   => 0,
        'heatline'   => 0,
        'dotSize'    => 3,
    ];
}
