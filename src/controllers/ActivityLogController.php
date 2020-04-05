<?php

namespace Abs\EmployeePkg;
use Abs\EmployeePkg\Employee;
use App\ActivityLog;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class EmployeeController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getEmployeeList(Request $request) {
		$employees = Employee::withTrashed()
			->select([
				'employees.id',
				'employees.name',
				DB::raw('COALESCE(employees.description,"--") as description'),
				DB::raw('IF(employees.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('employees.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('employees.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('employees.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('employees.deleted_at');
				}
			})
		// ->orderby('employees.id', 'Desc')
		;

		return Datatables::of($employees)
			->addColumn('name', function ($employee) {
				$status = $employee->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $employee->name;
			})
			->addColumn('action', function ($employee) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-employee')) {
					$output .= '<a href="#!/employee-pkg/employee/edit/' . $employee->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-employee')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#employee-delete-modal" onclick="angular.element(this).scope().deleteEmployee(' . $employee->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getEmployeeFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$employee = new Employee;
			$action = 'Add';
		} else {
			$employee = Employee::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['employee'] = $employee;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveEmployee(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 64 Charachers',
				'description.max' => 'Description is Maximum 255 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:3',
					'max:64',
					'unique:employees,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'description' => 'nullable|max:255',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$employee = new Employee;
				$employee->created_by_id = Auth::user()->id;
				$employee->created_at = Carbon::now();
				$employee->updated_at = NULL;
			} else {
				$employee = Employee::withTrashed()->find($request->id);
				$employee->updated_by_id = Auth::user()->id;
				$employee->updated_at = Carbon::now();
			}
			$employee->fill($request->all());
			$employee->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$employee->deleted_at = Carbon::now();
				$employee->deleted_by_id = Auth::user()->id;
			} else {
				$employee->deleted_by_id = NULL;
				$employee->deleted_at = NULL;
			}
			$employee->save();

			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Employees';
			$activity->entity_id = $employee->id;
			$activity->entity_type_id = 1420;
			$activity->activity_id = $request->id == NULL ? 280 : 281;
			$activity->activity = $request->id == NULL ? 280 : 281;
			$activity->details = json_encode($activity);
			$activity->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Employee Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Employee Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteEmployee(Request $request) {
		DB::beginTransaction();
		try {
			$employee = Employee::withTrashed()->where('id', $request->id)->forceDelete();
			if ($employee) {

				$activity = new ActivityLog;
				$activity->date_time = Carbon::now();
				$activity->user_id = Auth::user()->id;
				$activity->module = 'Employees';
				$activity->entity_id = $request->id;
				$activity->entity_type_id = 1420;
				$activity->activity_id = 282;
				$activity->activity = 282;
				$activity->details = json_encode($activity);
				$activity->save();

				DB::commit();
				return response()->json(['success' => true, 'message' => 'Employee Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
