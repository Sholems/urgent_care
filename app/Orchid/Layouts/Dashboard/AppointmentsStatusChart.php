<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class AppointmentsStatusChart extends Chart
{
    /**
     * Chart type.
     *
     * @var string
     */
    protected $type = self::TYPE_BAR;

    /**
     * Chart height.
     *
     * @var int
     */
    protected $height = 320;

    /**
     * Adjust bar configuration for readability.
     *
     * @var array<string, int|float>
     */
    protected $barOptions = [
        'spaceRatio' => 0.4,
        'stacked'    => 0,
        'height'     => 20,
        'depth'      => 2,
    ];
}
