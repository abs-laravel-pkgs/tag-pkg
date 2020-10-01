@if(config('tag-pkg.DEV'))
    <?php $tag_pkg_prefix = '/packages/abs/tag-pkg/src';?>
@else
    <?php $tag_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var tag_list_template_url = "{{asset($tag_pkg_prefix.'/public/themes/'.$theme.'/tag-pkg/tag/tags.html')}}";
</script>
<script type="text/javascript" src="{{asset($tag_pkg_prefix.'/public/themes/'.$theme.'/tag-pkg/tag/controller.js')}}"></script>
