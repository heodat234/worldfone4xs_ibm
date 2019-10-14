<div class="container-fluid">
	<div class="row">
	    <div id="side-form" style="width: 35%">
	        <div class="input-group">
	            <input type="text" class="form-control" placeholder="@Search@.." id="search-field" data-bind="events: {keyup: searchField}">
	            <span class="input-group-addon"><i class="fa fa-search"></i></span>
	        </div>
	        <ul class="sidebar-nav">
	        	<li><a class="" data-id="basic-information" data-bind="click: scrollTo" href="javascript:void(0)">@Basic Information@</a></li>
<!--	        	<li><a class="" data-id="additional-information" data-bind="click: scrollTo" href="javascript:void(0)">@Additional Information@</a></li>-->
	        	<li><a class="" data-id="social-information" data-bind="click: scrollTo" href="javascript:void(0)">@Social Information@</a></li>
	        </ul>
	    </div>
		<div id="main-form" style="width: 65%" data-width="65%">
	        <div class="form-group" id="basic-information" data-field="@Name@ | @Main phone@ | @Address@ | @Email@ | @Description@">
	            <h4 class="fieldset-legend"><span>@Basic Information@</span></h4>
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
<!--			<div class="form-group" id="additional-information" data-field="@Other phones@ | @Job@ | @Work Location@">-->
<!--	            <h4 class="fieldset-legend"><span>@Additional Information@</span></h4>-->
<!--	        </div>-->
<!--	        <div class="form-group" data-field="@Other phones@">-->
<!--				<label>@Other phones@</label>-->
<!--				<select data-role="multiselect" name="other_phones"-->
<!--                    data-value-primitive="true"                  -->
<!--                    data-bind="value: item.other_phones, source: item.other_phones, events: {open: otherPhonesOpen}" -->
<!--                    style="width: 100%"></select>-->
<!--			</div>-->
<!--			<div class="form-group" data-field="@Other emails@">-->
<!--				<label>@Other emails@</label>-->
<!--				<select data-role="multiselect" name="other_emails"-->
<!--                    data-value-primitive="true"                  -->
<!--                    data-bind="value: item.other_emails, source: item.other_emails, events: {open: otherPhonesOpen}" -->
<!--                    style="width: 100%"></select>-->
<!--			</div>-->
<!--			<div class="form-group" data-field="@Job@">-->
<!--				<label>@Job@</label>-->
<!--				<input class="k-textbox" style="width: 100%" data-bind="value: item.job">-->
<!--			</div>-->
<!--			<div class="form-group" data-field="@Work Location@">-->
<!--				<label>@Work Location@</label>-->
<!--				<input class="k-textbox" style="width: 100%" data-bind="value: item.work_location">-->
<!--			</div>-->
			<div class="form-group" id="social-information" data-field="Facebook | Twitter | Linkedin">
	            <h4 class="fieldset-legend"><span>@Social Information@</span></h4>
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