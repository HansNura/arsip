<?php

namespace App\Http\Controllers;

use App\Enums\CertificateStatus;
use App\Enums\ReissueStatus;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\CertificateReissue;
use App\Models\Participant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'stats' => [
                'participants' => Participant::count(),
                'batches' => Batch::count(),
                'certificates' => Certificate::where('status', CertificateStatus::Aktif)->count(),
                'pending_reissues' => CertificateReissue::where('status', ReissueStatus::Diajukan)->count(),
            ],
        ]);
    }
}
