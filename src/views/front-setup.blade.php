@if(config('employee-pkg.DEV'))
    <?php $employee_pkg_prefix = '/packages/abs/employee-pkg/src';?>
@else
    <?php $employee_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var employee_voucher_list_template_url = "{{asset($employee_pkg_prefix.'/public/themes/'.$theme.'/employee-pkg/employee/employee.html')}}";
</script>
<script type="text/javascript" src="{{asset($employee_pkg_prefix.'/public/themes/'.$theme.'/employee-pkg/employee/controller.js')}}"></script>
