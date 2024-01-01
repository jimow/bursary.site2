<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyBranchRequest;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('branch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = Branch::with(['owner', 'managed_bies'])->select(sprintf('%s.*', (new Branch)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'branch_show';
                $editGate      = 'branch_edit';
                $deleteGate    = 'branch_delete';
                $crudRoutePart = 'branches';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : '';
            });
            $table->addColumn('owner_name', function ($row) {
                return $row->owner ? $row->owner->name : '';
            });

            $table->editColumn('managed_by', function ($row) {
                $labels = [];
                foreach ($row->managed_bies as $managed_by) {
                    $labels[] = sprintf('<span class="label label-info label-many">%s</span>', $managed_by->name);
                }

                return implode(' ', $labels);
            });

            $table->rawColumns(['actions', 'placeholder', 'owner', 'managed_by']);

            return $table->make(true);
        }

        $users = User::get();

        return view('admin.branches.index', compact('users'));
    }

    public function create()
    {
        abort_if(Gate::denies('branch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $owners = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $managed_bies = User::pluck('name', 'id');

        return view('admin.branches.create', compact('managed_bies', 'owners'));
    }

    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->all());
        $branch->managed_bies()->sync($request->input('managed_bies', []));

        return redirect()->route('admin.branches.index');
    }

    public function edit(Branch $branch)
    {
        abort_if(Gate::denies('branch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $owners = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $managed_bies = User::pluck('name', 'id');

        $branch->load('owner', 'managed_bies');

        return view('admin.branches.edit', compact('branch', 'managed_bies', 'owners'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->all());
        $branch->managed_bies()->sync($request->input('managed_bies', []));

        return redirect()->route('admin.branches.index');
    }

    public function show(Branch $branch)
    {
        abort_if(Gate::denies('branch_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branch->load('owner', 'managed_bies');

        return view('admin.branches.show', compact('branch'));
    }

    public function destroy(Branch $branch)
    {
        abort_if(Gate::denies('branch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branch->delete();

        return back();
    }

    public function massDestroy(MassDestroyBranchRequest $request)
    {
        $branches = Branch::find(request('ids'));

        foreach ($branches as $branch) {
            $branch->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
