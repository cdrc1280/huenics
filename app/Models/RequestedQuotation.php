<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class RequestedQuotation extends Quotation
{
    protected $table = 'quotations';

    protected $guarded = ['id'];

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
     * Accept this online requested quotation and transfer to Quotations as Pending (for formal review & approval).
     */
    public function acceptAndTransferToQuotations(?int $salesAgentId = null, ?string $remarks = null): Quotation
    {
        $this->is_online_request = false;
        if ($salesAgentId) {
            $this->sales_agent_id = $salesAgentId;
        }

        // Starts on the first step: Pending review and approval in Quotations
        $this->status = self::STATUS_PENDING;
        $this->approved_by = null;
        $this->approved_at = null;

        $user = auth()->user()?->name ?? 'System';
        $note = "Accepted from Online Requested Quotation by {$user} on ".now()->format('Y-m-d H:i').'. Transferred to Quotations (Pending Review & Approval).';
        if (! empty($remarks)) {
            $note .= " Notes: {$remarks}";
        }
        $this->notes = ($this->notes ? $this->notes."\n" : '').$note;

        // Recalculate financial totals & profit
        $this->recalculateFinancials(false);
        $this->save();

        return $this;
    }

    /**
     * Legacy alias for acceptAndTransferToQuotations.
     */
    public function convertToOfficialQuotation(?int $salesAgentId = null): Quotation
    {
        return $this->acceptAndTransferToQuotations($salesAgentId);
    }
}
