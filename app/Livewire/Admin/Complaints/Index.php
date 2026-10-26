<?php

namespace App\Livewire\Admin\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Court;
use App\Support\Countries;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Complaints')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /** A ComplaintStatus value, or "pending" / "final" from the dashboard cards. */
    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $court = '';

    #[Url(except: '')]
    public string $country = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'court', 'country', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'court', 'country', 'from', 'to');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.complaints.index', [
            'complaints' => $this->query()->with(['court', 'citizen'])->latest()->paginate(15),
            'statuses' => ['pending' => 'Pending (submitted or under review)'] + ComplaintStatus::options() + ['final' => 'Resolved or closed'],
            'courts' => Court::query()->ordered()->get()->mapWithKeys(fn (Court $c) => [$c->id => $c->name_en.', '.$c->district_en]),
            'countries' => collect(Countries::ALL)->map(fn ($row) => $row[0])->all(),
        ]);
    }

    private function query(): Builder
    {
        $search = trim($this->search);

        return Complaint::query()
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('tracking_number', 'like', "%{$search}%")
                ->orWhere('case_number', 'like', "%{$search}%")
                ->orWhere('case_title', 'like', "%{$search}%")
                ->orWhereHas('citizen', fn (Builder $q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('cnic_passport', 'like', "%{$search}%"))))
            ->when($this->status !== '', fn (Builder $q) => match ($this->status) {
                'pending' => $q->withStatus(ComplaintStatus::pending()),
                'final' => $q->withStatus([ComplaintStatus::Resolved, ComplaintStatus::Closed]),
                default => $q->withStatus(ComplaintStatus::tryFrom($this->status) ?? []),
            })
            ->when($this->court !== '', fn (Builder $q) => $q->where('court_id', (int) $this->court))
            ->when($this->country !== '', fn (Builder $q) => $q->whereHas('citizen', fn (Builder $q) => $q->where('country', $this->country)))
            ->when($this->validDate($this->from), fn (Builder $q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->validDate($this->to), fn (Builder $q) => $q->whereDate('created_at', '<=', $this->to));
    }

    private function validDate(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
    }
}
