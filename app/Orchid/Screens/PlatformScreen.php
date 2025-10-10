<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\AppUser;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Orchid\Layouts\Dashboard\AppointmentsStatusChart;
use App\Orchid\Layouts\Dashboard\AppointmentsTrendChart;
use App\Orchid\Layouts\Dashboard\DoctorAvailabilityChart;
use App\Orchid\Layouts\Dashboard\UserGrowthChart;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'metricsMain'        => $this->buildMainMetrics(),
            'appointmentsTrend'  => $this->buildAppointmentsTrendDataset(),
            'appointmentsStatus' => $this->buildAppointmentsStatusDataset(),
            'doctorAvailability' => $this->buildDoctorAvailabilityDataset(),
            'userGrowth'         => $this->buildUserGrowthDataset(),
            'recentAppointments' => $this->recentAppointments(),
            'recentDoctors'      => $this->recentDoctors(),
            'recentAppUsers'     => $this->recentAppUsers(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Admin Dashboard';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Comprehensive snapshot of clinical operations, team activity, and configuration state.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Manage Appointments')
                ->icon('bs.calendar-week')
                ->route('platform.appointment.list'),

            Link::make('Manage Doctors')
                ->icon('bs.person-gear')
                ->route('platform.doctor.list'),

           
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Appointments' => 'metricsMain.totalAppointments',
                'Pending'            => 'metricsMain.pendingAppointments',
                'App Users'          => 'metricsMain.totalAppUsers',
                'Doctors'            => 'metricsMain.totalDoctors',
            ]),

            Layout::columns([
                AppointmentsTrendChart::make('appointmentsTrend', 'Appointments – Last 7 Days')
                    ->description('Total bookings with acceptance trends collected daily.'),
                UserGrowthChart::make('userGrowth', 'User Growth – Last 6 Weeks')
                    ->description('Track new patient registrations against activated doctors.'),
            ]),

            Layout::columns([
                AppointmentsStatusChart::make('appointmentsStatus', 'Appointment Status Breakdown')
                    ->description('Distribution of appointments by current status.'),
                DoctorAvailabilityChart::make('doctorAvailability', 'Doctor Availability')
                    ->description('Snapshot of providers marked as available for scheduling.'),
            ]),

            Layout::tabs([
                'Recent Appointments' => Layout::table('recentAppointments', [
                    TD::make('id', '#')
                        ->width('80')
                        ->render(fn (Appointment $appointment) => Link::make('#' . $appointment->id)
                            ->route('platform.appointment.detail', $appointment->id)),
                    TD::make('user', 'Patient')
                        ->render(fn (Appointment $appointment) => $appointment->user?->user_name ?? '—'),
                    TD::make('doctor', 'Doctor')
                        ->render(fn (Appointment $appointment) => $appointment->doctor?->name ?? '—'),
                    TD::make('scheduled', 'Scheduled For')
                        ->render(fn (Appointment $appointment) => trim(($appointment->date ?? '') . ' ' . ($appointment->time ?? '')) ?: '—'),
                    TD::make('status', 'Status')
                        ->align(TD::ALIGN_CENTER)
                        ->render(fn (Appointment $appointment) => Str::headline($appointment->status ?? 'pending')),
                    TD::make('updated_at', 'Updated')
                        ->align(TD::ALIGN_RIGHT)
                        ->render(fn (Appointment $appointment) => $appointment->updated_at?->diffForHumans() ?? '—'),
                ]),
                'New Doctors' => Layout::table('recentDoctors', [
                    TD::make('name', 'Name'),
                    TD::make('email', 'Email')
                        ->render(fn (Doctor $doctor) => $doctor->email ?? $doctor->user?->email ?? '—'),
                    TD::make('experience', 'Experience')
                        ->render(fn (Doctor $doctor) => $doctor->experience ? $doctor->experience . ' yrs' : '—'),
                    TD::make('is_available', 'Availability')
                        ->align(TD::ALIGN_CENTER)
                        ->render(fn (Doctor $doctor) => $doctor->is_available ? 'Available' : 'Unavailable'),
                    TD::make('updated_at', 'Updated')
                        ->align(TD::ALIGN_RIGHT)
                        ->render(fn (Doctor $doctor) => optional($doctor->updated_at ?? $doctor->created_at)?->diffForHumans() ?? '—'),
                ]),
                'New App Users' => Layout::table('recentAppUsers', [
                    TD::make('user_name', 'Name'),
                    TD::make('user_email', 'Email'),
                    TD::make('user_phone_number', 'Phone')
                        ->render(fn (AppUser $user) => $user->user_phone_number ?? '—'),
                    TD::make('created_at', 'Joined')
                        ->align(TD::ALIGN_RIGHT)
                        ->render(fn (AppUser $user) => $user->created_at?->diffForHumans() ?? '—'),
                ]),
            ]),
        ];
    }

    /**
     * Build the primary metric cards.
     *
     * @return array<string, array<string, float|string>>
     */
    private function buildMainMetrics(): array
    {
        $now = Carbon::now();
        $lastWeekStart = $now->copy()->subDays(6)->startOfDay();
        $previousWeekStart = $now->copy()->subDays(13)->startOfDay();
        $previousWeekEnd = $lastWeekStart->copy()->subSecond();

        $appointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $appointmentsLastWeek = $this->countCreatedBetween(Appointment::class, $lastWeekStart, $now);
        $appointmentsPrevWeek = $this->countCreatedBetween(Appointment::class, $previousWeekStart, $previousWeekEnd);

        $appUsers = AppUser::count();
        $appUsersLastWeek = $this->countCreatedBetween(AppUser::class, $lastWeekStart, $now);
        $appUsersPrevWeek = $this->countCreatedBetween(AppUser::class, $previousWeekStart, $previousWeekEnd);

        $doctors = Doctor::count();
        $doctorsLastWeek = $this->countCreatedBetween(Doctor::class, $lastWeekStart, $now);
        $doctorsPrevWeek = $this->countCreatedBetween(Doctor::class, $previousWeekStart, $previousWeekEnd);

        return [
            'totalAppointments'   => [
                'value' => number_format($appointments),
                'diff'  => $this->percentageChange($appointmentsLastWeek, $appointmentsPrevWeek),
            ],
            'pendingAppointments' => [
                'value' => number_format($pendingAppointments),
            ],
            'totalAppUsers'       => [
                'value' => number_format($appUsers),
                'diff'  => $this->percentageChange($appUsersLastWeek, $appUsersPrevWeek),
            ],
            'totalDoctors'        => [
                'value' => number_format($doctors),
                'diff'  => $this->percentageChange($doctorsLastWeek, $doctorsPrevWeek),
            ],
        ];
    }

    /**
     * Build appointments trend dataset.
     *
     * @return array<int, array<string, array<int, int>|string>>
     */
    private function buildAppointmentsTrendDataset(): array
    {
        $end = Carbon::now()->endOfDay();
        $start = $end->copy()->subDays(6)->startOfDay();
        $period = $this->generateDailyLabels($start, $end);

        $totals = $this->countsPerDay($start, $end, Appointment::class);
        $accepted = $this->countsPerDay($start, $end, Appointment::class, 'accepted');
        $pending = $this->countsPerDay($start, $end, Appointment::class, 'pending');
        $declined = $this->countsPerDay($start, $end, Appointment::class, 'declined');

        return [
            [
                'name'   => 'Total',
                'values' => $period->map(fn ($label, $date) => $totals[$date] ?? 0)->values()->all(),
                'labels' => $period->values()->all(),
            ],
            [
                'name'   => 'Accepted',
                'values' => $period->map(fn ($label, $date) => $accepted[$date] ?? 0)->values()->all(),
                'labels' => $period->values()->all(),
            ],
            [
                'name'   => 'Pending',
                'values' => $period->map(fn ($label, $date) => $pending[$date] ?? 0)->values()->all(),
                'labels' => $period->values()->all(),
            ],
            [
                'name'   => 'Declined',
                'values' => $period->map(fn ($label, $date) => $declined[$date] ?? 0)->values()->all(),
                'labels' => $period->values()->all(),
            ],
        ];
    }

    /**
     * Build appointments status dataset.
     *
     * @return array<int, array<string, array<int, int>|string>>
     */
    private function buildAppointmentsStatusDataset(): array
    {
        $data = Appointment::selectRaw('COALESCE(status, "pending") as status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        if ($data->isEmpty()) {
            return [[
                'name'   => 'Status',
                'values' => [0],
                'labels' => ['No Data'],
            ]];
        }

        return [[
            'name'   => 'Appointments',
            'values' => $data->pluck('total')->map(fn ($count) => (int) $count)->all(),
            'labels' => $data->pluck('status')->map(fn ($status) => Str::headline($status))->all(),
        ]];
    }

    /**
     * Build doctor availability dataset.
     *
     * @return array<int, array<string, array<int, int>|string>>
     */
    private function buildDoctorAvailabilityDataset(): array
    {
        $available = Doctor::where('is_available', true)->count();
        $unavailable = Doctor::count() - $available;

        return [[
            'name'   => 'Doctors',
            'values' => [$available, max($unavailable, 0)],
            'labels' => ['Available', 'Unavailable'],
        ]];
    }

    /**
     * Build user growth dataset.
     *
     * @return array<int, array<string, array<int, int>|string>>
     */
    private function buildUserGrowthDataset(): array
    {
        $end = Carbon::now()->endOfWeek();
        $start = $end->copy()->subWeeks(5)->startOfWeek();
        $weeks = $this->generateWeeklyLabels($start, $end);

        $userCounts = $this->countsPerWeek($start, $end, AppUser::class);
        $doctorCounts = $this->countsPerWeek($start, $end, Doctor::class);

        return [
            [
                'name'   => 'App Users',
                'values' => $weeks->map(fn ($label, $week) => $userCounts[$week] ?? 0)->values()->all(),
                'labels' => $weeks->values()->all(),
            ],
            [
                'name'   => 'Doctors',
                'values' => $weeks->map(fn ($label, $week) => $doctorCounts[$week] ?? 0)->values()->all(),
                'labels' => $weeks->values()->all(),
            ],
        ];
    }

    /**
     * Recent appointment records.
     */
    private function recentAppointments(): Collection
    {
        return Appointment::with(['user:id,user_name', 'doctor:id,name'])
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    /**
     * Recent doctor records.
     */
    private function recentDoctors(): Collection
    {
        $model = new Doctor();

        $columns = collect(['id', 'user_id', 'name', 'experience', 'is_available', 'updated_at', 'created_at'])
            ->filter(fn (string $column) => $this->modelHasColumn($model, $column))
            ->values();

        if ($this->modelHasColumn($model, 'email')) {
            $columns->push('email');
        }

        if ($columns->isEmpty()) {
            $columns = collect([$model->getKeyName()]);
        }

        $query = Doctor::query()->select($columns->unique()->all());

        if (! $this->modelHasColumn($model, 'email')) {
            $query->with('user:id,email');
        }

        $orderColumn = $this->resolveOrderColumn($model);
        if ($orderColumn) {
            $query->orderByDesc($orderColumn);
        }

        return $query->limit(8)->get();
    }

    /**
     * Recent app user records.
     */
    private function recentAppUsers(): Collection
    {
        return AppUser::select(['id', 'user_name', 'user_email', 'user_phone_number', 'created_at'])
            ->latest('created_at')
            ->limit(8)
            ->get();
    }

    /**
     * Generate daily labels within the supplied window.
     */
    private function generateDailyLabels(Carbon $start, Carbon $end): Collection
    {
        $labels = collect();
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $labels->put($cursor->format('Y-m-d'), $cursor->format('M d'));
            $cursor->addDay();
        }

        return $labels;
    }

    /**
     * Generate weekly labels within the supplied window.
     */
    private function generateWeeklyLabels(Carbon $start, Carbon $end): Collection
    {
        $labels = collect();
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $labels->put($cursor->format('Y-m-d'), $cursor->format('M d'));
            $cursor->addWeek();
        }

        return $labels;
    }

    /**
     * Count rows created between the given dates if timestamps are available.
     */
    private function countCreatedBetween(string $modelClass, Carbon $start, Carbon $end): int
    {
        /** @var Model $model */
        $model = new $modelClass();

        if (!$this->modelHasColumn($model, 'created_at')) {
            return 0;
        }

        return $modelClass::whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * Calculate week-over-week percentage change.
     */
    private function percentageChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Count records per day filtered by status when provided.
     *
     * @return array<string, int>
     */
    private function countsPerDay(Carbon $start, Carbon $end, string $modelClass, ?string $status = null): array
    {
        $query = $modelClass::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end]);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * Count records per week within the provided range.
     *
     * @return array<string, int>
     */
    private function countsPerWeek(Carbon $start, Carbon $end, string $modelClass): array
    {
        $daily = $modelClass::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->get();

        return $daily->groupBy(function ($row) {
            return Carbon::parse($row->day)->startOfWeek()->format('Y-m-d');
        })->map(fn ($rows) => (int) $rows->sum('total'))->all();
    }

    /**
     * Resolve the best column to order recent records by.
     */
    private function resolveOrderColumn(Model $model): ?string
    {
        if ($this->modelHasColumn($model, 'updated_at')) {
            return 'updated_at';
        }

        if ($this->modelHasColumn($model, 'created_at')) {
            return 'created_at';
        }

        return $model->getKeyName();
    }

    /**
     * Determine if the supplied model has the provided column.
     */
    private function modelHasColumn(Model $model, string $column): bool
    {
        $connection = $model->getConnectionName() ?? config('database.default');

        return Schema::connection($connection)->hasColumn($model->getTable(), $column);
    }

}
