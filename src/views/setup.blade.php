@if(config('employee-pkg.DEV'))
    <?php $employee_pkg_prefix = '/packages/abs/employee-pkg/src';?>
@else
    <?php $employee_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    //EMPLOYEE
	    when('/employee-pkg/employee/list', {
	        template: '<employee-list></employee-list>',
	        title: 'Employees',
	    }).
	    when('/employee-pkg/employee/add', {
	        template: '<employee-form></employee-form>',
	        title: 'Add Employee',
	    }).
	    when('/employee-pkg/employee/edit/:id', {
	        template: '<employee-form></employee-form>',
	        title: 'Edit Employee',
	    });
	}]);

	//EMPLOYEES
    var employee_list_template_url = "{{asset($employee_pkg_prefix.'/public/themes/'.$theme.'/employee-pkg/employee/list.html')}}";
    var employee_form_template_url = "{{asset($employee_pkg_prefix.'/public/themes/'.$theme.'/employee-pkg/employee/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($employee_pkg_prefix.'/public/themes/'.$theme.'/employee-pkg/employee/controller.js')}}"></script>
