<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class AppointmentsTrendChart extends Chart
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
     * Custom line options tuned for trend visualisation.
     *
     * @var array<string, int>
     */
    protected $lineOptions = [
        'spline'     => 1,
        'regionFill' => 1,
        'hideDots'   => 0,
        'hideLine'   => 0,
        'heatline'   => 0,
        'dotSize'    => 3,
    ];
}
