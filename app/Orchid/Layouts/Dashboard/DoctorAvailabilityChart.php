<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class DoctorAvailabilityChart extends Chart
{
    /**
     * Chart type.
     *
     * @var string
     */
    protected $type = self::TYPE_PIE;

    /**
     * Chart height.
     *
     * @var int
     */
    protected $height = 300;

    /**
     * Limit slices to keep the pie readable.
     *
     * @var int
     */
    protected $maxSlices = 6;
}
