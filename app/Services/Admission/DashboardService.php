<?php

namespace App\Services\Admission;

class DashboardService
{
    public function __construct(protected ApplicationQuery $queries, protected WorkflowService $workflow, protected OfferService $offers)
    {
    }

    /** @return array{metrics: array<string,array{label:string,count:int}>, recent: \Illuminate\Support\Collection} */
    public function summary(int $instituteId, ?int $syear): array
    {
        $this->workflow->syncMissing($instituteId);
        $this->offers->expireDue($instituteId);

        $metrics = [];
        foreach (ApplicationQuery::METRICS as $key => $label) {
            $metrics[$key] = ['label' => $label, 'count' => $this->queries->metric($key, $instituteId, $syear)->count()];
        }

        $recent = $this->queries->listColumns($this->queries->base($instituteId, $syear))->orderByDesc('ae.id')->limit(8)->get();

        return ['metrics' => $metrics, 'recent' => $recent];
    }

    /** Filtered, paginated list behind a metric tile. */
    public function drillDown(int $instituteId, ?int $syear, string $metric, array $filters = [], int $perPage = 25)
    {
        $this->workflow->syncMissing($instituteId);
        $query = $this->queries->listColumns($this->queries->metric($metric, $instituteId, $syear));

        if (! empty($filters['q'])) {
            $term = '%'.trim($filters['q']).'%';
            $query->where(function ($q) use ($term) {
                $q->where('ae.first_name', 'like', $term)->orWhere('ae.last_name', 'like', $term)
                    ->orWhere('ae.mobile', 'like', $term)->orWhere('ae.enquiry_no', 'like', $term);
            });
        }

        return $query->orderByDesc('ae.id')->paginate($perPage)->withQueryString();
    }
}
