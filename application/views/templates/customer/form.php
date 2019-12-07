<div class="container-fluid">
	<div class="row">
	    <div id="side-form" style="width: 35%">
	        <div class="input-group">
	            <input type="text" class="form-control" placeholder="@Search@.." id="search-field" data-bind="events: {keyup: searchField}">
	            <span class="input-group-addon"><i class="fa fa-search"></i></span>
	        </div>
	        <ul class="sidebar-nav">
	        	<li><a class="" data-id="basic-information" data-bind="click: scrollTo" href="javascript:void(0)">@Basic Information@</a></li>
	        	<li><a class="" data-id="additional-information" data-bind="click: scrollTo" href="javascript:void(0)">@Additional Information@</a></li>
	        	<li><a class="" data-id="social-information" data-bind="click: scrollTo" href="javascript:void(0)">@Social Information@</a></li>
	        </ul>
	    </div>
		<div id="main-form" style="width: 65%" data-width="65%">
	        <div class="form-group" id="basic-information" data-field="@Name@ | @Main phone@ | @Email@ | @Description@">
	            <h4 class="fieldset-legend text-muted"><span>@Basic Information@</span></h4>
	        </div>
			<div class="form-group" data-field="@Name@">
				<label>@Name@</label>
				<input class="k-textbox upper-case-input" style="width: 100%" data-bind="value: item.name">
			</div>
			<div class="form-group" data-field="@Phone@">
				<label>@Phone@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.phone">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Email@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.email">
			</div>
			<div class="form-group" data-field="@Description@">
				<label>@Description@</label>
				<textarea class="k-textbox" style="width: 100%" data-bind="value: item.description"></textarea>
			</div>
			<?php $this->load->library("mongo_private"); 
			$customerFields = $this->mongo_private->where(["collection"=>getCT('Customer')])->get("Model"); 
			$titles = array_column($customerFields, "title"); ?>
			<div class="form-group" id="additional-information" data-field="<?= implode(' | ', $titles) ?>">
	            <h4 class="fieldset-legend text-muted"><span>@Additional Information@</span></h4>
	        </div>
	        <?php
	        foreach ($customerFields as $fieldDoc) {
	        	if(in_array($fieldDoc["field"], ["name","phone","email","description","createdAt"])) continue;
	        	switch ($fieldDoc["type"]) {
	        		case 'timestamp':
						echo "
						<div class='form-group' data-field='{$fieldDoc['title']}'>
							<label>{$fieldDoc['title']}</label>
							<input data-role='datepicker' style='width: 100%' data-bind='value: item.{$fieldDoc['field']}'>
						</div>";
						break;

					case 'arrayPhone': case 'array':
						echo "
						<div class='form-group' data-field='{$fieldDoc['title']}'>
							<label>{$fieldDoc['title']}</label>
							<select data-role='multiselect' style='width: 100%' data-bind='value: item.{$fieldDoc['field']}, source: item.{$fieldDoc['field']}'></select>
						</div>";
						break;
					
					default:
						echo "
						<div class='form-group' data-field='{$fieldDoc['title']}'>
							<label>{$fieldDoc['title']}</label>
							<input class='k-textbox' style='width: 100%' data-bind='value: item.{$fieldDoc['field']}'>
						</div>";
						break;
	        	}
	        } 
	        ?>
			<div class="form-group" id="social-information" data-field="Facebook | Twitter | Linkedin">
	            <h4 class="fieldset-legend text-muted"><span>@Social Information@</span></h4>
	        </div>
			<div class="form-group" data-field="Facebook">
				<label>Facebook</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.facebook" placeholder="https://">
			</div>
			<div class="form-group" data-field="Twitter">
				<label>Twitter</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.twitter" placeholder="https://">
			</div>
			<div class="form-group" data-field="Linkedin">
				<label>Linkedin</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.linkedin" placeholder="https://">
			</div>
	    </div>
	</div>
	<div class="side-form-bottom">
		<div class="text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>