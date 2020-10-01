@if(config('tag-pkg.DEV'))
    <?php $tag_pkg_prefix = '/packages/abs/tag-pkg/src';?>
@else
    <?php $tag_pkg_prefix = '';?>
@endif

<script type="text/javascript">

	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    when('/tag-pkg/tag/list', {
	        template: '<tag-list></tag-list>',
	        title: 'Tags',
	    }).
	    when('/tag-pkg/tag/add', {
	        template: '<tag-form></tag-form>',
	        title: 'Add Tag',
	    }).
	    when('/tag-pkg/tag/edit/:id', {
	        template: '<tag-form></tag-form>',
	        title: 'Edit Tag',
	    });
	}]);


    var tag_list_template_url = "{{asset($tag_pkg_prefix.'/public/themes/'.$theme.'/tag-pkg/tag/list.html')}}";
    var tag_form_template_url = "{{asset($tag_pkg_prefix.'/public/themes/'.$theme.'/tag-pkg/tag/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($tag_pkg_prefix.'/public/themes/'.$theme.'/tag-pkg/tag/controller.js')}}"></script>
