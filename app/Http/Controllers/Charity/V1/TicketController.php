<?php

namespace App\Http\Controllers\Charity\V1;

use App\Exports\TicketExport;
use App\Http\Controllers\Controller;
use App\Imports\TicketImport;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketController extends Controller
{
    public function export(Activity $activity): BinaryFileResponse
    {
        Gate::authorize('check-charity-source', $activity);
        return Excel::download(new TicketExport($activity), 'ticket.xlsx');
    }

    public function import(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx'
        ]);
        Excel::import(new TicketImport($activity), $request->file('file'));
        return Response::success();
    }
}
