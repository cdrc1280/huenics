<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class RequestedQuotation extends Quotation
{
    protected $table = 'quotations';

    protected static function booted(): void
    {
        static::addGlobalScope('online_request', function (Builder $builder) {
            $builder->where('is_online_request', true);
        });

        static::creating(function (RequestedQuotation $quotation) {
            $quotation->is_online_request = true;
        });
    }

    /**
     * Convert this online requested quotation to an official internal quotation.
     */
    public function convertToOfficialQuotation(?int $salesAgentId = null): Quotation
    {
        $this->is_online_request = false;
        if ($salesAgentId) {
            $this->sales_agent_id = $salesAgentId;
        }
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = auth()->id();
        $this->approved_at = now();
        $this->notes = ($this->notes ? $this->notes . "\n" : '') .
            "Converted from Online Requested Quotation to Official Quotation by " . (auth()->user()?->name ?? 'System') . " on " . now()->format('Y-m-d H:i');
        $this->save();

        return $this;
    }
}
