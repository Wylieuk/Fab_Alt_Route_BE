{if $template_vars->type == "textarea"}
	<div class="field {$template_vars->id} {$template_vars->parent_class}">
		<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if (isset($template_vars->maxlength))} (Max {$template_vars->maxlength} characters){/if}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
		<textarea{foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}>{$template_vars->value}</textarea>
	</div>
{/if}

{if $template_vars->type == "textfield"}
<div class="field {$template_vars->id} {$template_vars->parent_class}">
		<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if (isset($template_vars->maxlength))} (Max {$template_vars->maxlength} characters){/if}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
        <input{foreach $template_vars as $param} {$param@key}="{$param}"{/foreach} />
</div>
{/if}


{if $template_vars->type == "password"}
<div class="field {$template_vars->id} {$template_vars->parent_class}">
		<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
        <input{foreach $template_vars as $param} {$param@key}="{$param}"{/foreach} />
</div>
{/if}


{if $template_vars->type == "button"}
<button{foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}>{$template_vars->label|lang}</button>
{/if}

{if $template_vars->type == "checkbox"}
	<div class="field {$template_vars->id} {$template_vars->parent_class}">
    	<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
		<input{foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}{if isset($template_vars->checked)}{if $template_vars->checked} checked{/if}{/if}>
	</div>
{/if}

{if $template_vars->type == "dropdown"}
	<div class="field {$template_vars->id} {$template_vars->parent_class}">
    	<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
    	<select {foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}>
        {if isset($preset_value['val'])}
        	<option tp="preset_value" value="{$preset_value['val']}">{$preset_value['txt']}</option>
        {/if}
        {if isset($template_vars->default) and $template_vars->default != ""}
        	<option tp="default" value="{$template_vars->default}">{$template_vars->default}</option>
         {/if}
        {foreach $dropdown_vars as $dropDownParam}
        	<option{foreach $dropDownParam as $optionParam} {$optionParam@key}="{$optionParam}"{/foreach}>{$dropDownParam['text']}</option>
        {/foreach}
        </select> 
    </div>
{/if}

{if $template_vars->type == "dropdownMultiple"}
	<div class="field {$template_vars->id} {$template_vars->parent_class}">
    	<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
    	<select multiple="multiple" {foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}>
        {*<option value=""></option>*}
        	{if $template_vars->default != ""}<option value="{$template_vars->default}">{$template_vars->default}</option>{/if}
        {foreach $dropdown_vars as $dropDownParam}
        	<option{foreach $dropDownParam as $optionParam} {$optionParam@key}="{$optionParam}"{/foreach}{if in_array($dropDownParam['text'],$dropdown_preloaded_vars)} selected="selected"{/if}>{$dropDownParam['text']}</option>
        {/foreach}
        </select> 
    </div>
{/if}


{if $template_vars->type == "radio"}

	<div class="field {$template_vars->id} {$template_vars->parent_class}" type="radio">
    	<div><label>{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
         {foreach $radio_vars as $radioParam}
         <div class="choice">
         	<div><label class="sub">{$radioParam|lang}</label></div>
			<input value="{$radioParam}" {foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}{if ($template_vars->value === $radioParam) or ($template_vars->value === $radioParam@key) or ($template_vars->value == '' and $template_vars->default === $radioParam@key)} checked="checked"{/if}>
            </div>
         {/foreach}
	</div>
{/if}

{if $template_vars->type == "file"}
	<div class="field {$template_vars->id} {$template_vars->parent_class}">
    	<div class="thumbnail_holder"  type="{$template_vars->format}">
        	
            {if $template_vars->previous_value != ''}
            	<img class="thumbnail" src="assets/images/filetypes/{$template_vars->format}.png" /><img class="close" src="assets/images/Deep_Delete_red_small.png" />
            	<div class="previous_value">{$template_vars->previous_value}</div> 
            {else}
            	 <img class="thumbnail" src="assets/images/filetypes/no-{$template_vars->format}.png" />    
            {/if}
         </div>	
		<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
        <input{foreach $template_vars as $param} {$param@key}="{$param}"{/foreach} />
	</div>
{/if}

{if $template_vars->type == "image"}
	{assign $template_vars->type "file"}
	<div class="field {$template_vars->id} {$template_vars->parent_class}">
        <div class="thumbnail_holder"  type="{$template_vars->format}">
        	{if $template_vars->thumb != ''}
        		<img class="thumbnail" src="{$template_vars->thumb}" /><img class="close" src="assets/images/Deep_Delete_red_small.png" />
            {else}
            	<img class="thumbnail" src="assets/images/no-image.png" />
        	{/if}
         </div>
       
		<div><label for="{$template_vars->id}">{$template_vars->label|lang}{if $tooltip != ''}<div class="tooltip"><span class="tooltiptext">{$tooltip}</span></div>{/if}</label></div>
        <input type="file" {foreach $template_vars as $param} {if $param@key=='type'}{continue}{/if}{$param@key}="{$param}"{/foreach} />
	</div>
{/if}

{if $template_vars->type == "section"}
	<div class="section {$template_vars->id}">
    	{$template_vars->text}
	</div>
{/if}

{if $template_vars->type == "hidden"}
    <input {foreach $template_vars as $param} {$param@key}="{$param}"{/foreach}>
{/if}

