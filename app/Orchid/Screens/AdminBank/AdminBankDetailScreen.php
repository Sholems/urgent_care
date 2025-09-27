<?php

namespace App\Orchid\Screens\AdminBank;

use App\Models\AdminBankDetail;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AdminBankDetailScreen extends Screen
{
    public $bank;

    public function query(): iterable
    {
        return [
            'bank' => AdminBankDetail::first() ?? new AdminBankDetail(),
        ];
    }

    public function name(): ?string
    {
        return 'Admin Bank Details';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('bank.bank_name')
                    ->title('Bank Name')
                    ->placeholder('Enter bank name'),
                Input::make('bank.account_name')
                    ->title('Account Name')
                    ->placeholder('Enter account holder name'),
                Input::make('bank.account_number')
                    ->title('Account Number')
                    ->placeholder('Enter account number'),
                Input::make('bank.bank_code')
                    ->title('Bank Code / Sort Code')
                    ->placeholder('Enter bank code or sort code'),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'bank.bank_name' => 'nullable|string|max:255',
            'bank.account_name' => 'nullable|string|max:255',
            'bank.account_number' => 'nullable|string|max:255',
            'bank.bank_code' => 'nullable|string|max:255',
        ]);

        $bankDetail = AdminBankDetail::first() ?? new AdminBankDetail();
        $bankDetail->fill($data['bank'] ?? []);
        $bankDetail->save();

        Alert::info('Bank details saved successfully.');

        return redirect()->route('platform.bank.details');
    }
}
