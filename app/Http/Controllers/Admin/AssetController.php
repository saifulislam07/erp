<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AssetsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssetRequest;
use App\Models\Asset;
use App\Services\MediaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->indexData($request);
        }

        return view('admin.assets.index');
    }

    /**
     * Server-side DataTables feed for the asset listing.
     */
    protected function indexData(Request $request): JsonResponse
    {
        $query = Asset::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->get('q') ?: $request->input('search.value');

                if (filled($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('asset_id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%");
                    });
                }
            }, true)
            ->editColumn('serial_number', fn (Asset $asset) => e($asset->serial_number ?: '-'))
            ->editColumn('category', fn (Asset $asset) => e($asset->category ?: '-'))
            ->addColumn('state', fn (Asset $asset) => view('admin.assets.partials.status-cell', compact('asset'))->render())
            ->addColumn('actions', fn (Asset $asset) => view('admin.assets.partials.actions', compact('asset'))->render())
            ->orderColumn('state', 'status $1')
            ->rawColumns(['state', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('admin.assets.create');
    }

    public function store(AssetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('invoice_file')) {
            $data['invoice_file'] = $this->media->store($request->file('invoice_file'), 'assets');
        }

        Asset::create($data);

        return redirect()->route('admin.assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset): View
    {
        return view('admin.assets.show', compact('asset'));
    }

    public function edit(Asset $asset): View
    {
        return view('admin.assets.edit', compact('asset'));
    }

    public function update(AssetRequest $request, Asset $asset): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('invoice_file')) {
            $this->media->delete($asset->invoice_file);

            $data['invoice_file'] = $this->media->store($request->file('invoice_file'), 'assets');
        }

        $asset->update($data);

        return redirect()->route('admin.assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function report(Request $request): View
    {
        $assets = $this->filteredReportQuery($request)->get();

        return view('admin.assets.reports.index', [
            'assets' => $assets,
            'status' => $request->status,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);
    }

    public function reportExcel(Request $request)
    {
        $assets = $this->filteredReportQuery($request)->get();

        return Excel::download(new AssetsExport($assets), 'assets-report.xlsx');
    }

    public function reportPdf(Request $request)
    {
        $assets = $this->filteredReportQuery($request)->get();

        $pdf = Pdf::loadView('admin.assets.reports.pdf', [
            'assets' => $assets,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);

        return $pdf->download('assets-report.pdf');
    }

    private function filteredReportQuery(Request $request)
    {
        $query = Asset::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }

        return $query->latest();
    }
}
