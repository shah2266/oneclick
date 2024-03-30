<!-- Session message -->
	@if(Session::get('success'))
	<p class="callout bg-inverse-success alert alert-dismissible">
		{{ Session::get('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	</p>
	@elseif(Session::get('info'))
	<p class="callout bg-inverse-info alert alert-dismissible">
		{{ Session::get('info') }}
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	</p>
	@elseif(Session::get('danger'))
	<p class="callout bg-inverse-danger alert alert-dismissible">
		{{ Session::get('danger') }}
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	</p>
	@endif
<!-- #Session message -->

