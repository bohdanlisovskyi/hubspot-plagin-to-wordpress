<?php if(!defined( 'ABSPATH')) exit; ?>
<div class="cf7hsfi">
	<h2>Hubspot Forms Integration Settings</h2>
	<fieldset>
	  <legend>
	  Enter your HubSpot Api Key below. In order for your Contact Form 7 form submissions to work with HubSpot Contact and Deal, you must fill the required fields below.
	  </legend>
		<table class="form-table">
		  <tbody>
            <tr>
                <th scope="row">API Key</th>
                <td>
                    <input type="text" name="cf7hsfi_api_key" class="large-text code" value="{api_key}" placeholder="e.g. Api Key">
                    <p class="description">HubSpot API Key<b>(Required)</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row">Deal name</th>
                <td>
                    <input type="text" name="cf7hsfi_deal_name" class="large-text code" value="{deal_name}" placeholder="e.g. Deal name">
                    <p class="description">New Deal Name<b>(Required)</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row">Deal price</th>
                <td>
                    <input type="text" name="cf7hsfi_deal_price" class="large-text code" value="{deal_price}" placeholder="e.g. Deal price">
                    <p class="description">New Deal Price<b>(Optional)</b></p>
                </td>
            </tr>
		    <tr>
		      <th scope="row">Form Fields <b>(Required)</b></th>
		      <td class="valign-top">
			      <div class="cf7hsfi_form_field_names_wrap">
			      	<span class="cf7hsfi_form_fields"></span>
			        {form_fields_html}
			      </div>
		      </td>
		    </tr>
		    <tr>
		    	<td colspan="2" align="right">
			    	<div>
			    		<script id='fb3f6pm'>(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//button.flattr.com/view/?fid=kx1rex&button=compact&url='+encodeURIComponent(document.URL);f.title='Flattr';f.height=20;f.width=110;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('fb3f6pm');</script>
			    	</div>
						<a class="button" href="javascript:;" onclick="jQuery('.cf7hsfi .debug_log').toggle()"><b>Display Last Form Submission LOG</b></a>
						<textarea class="large-text debug_log" rows="8" cols="100" placeholder="Last form submission debug log." readonly>{debug_log}</textarea>
		    	</td>
		    </tr>
		  </tbody>
		</table>
	</fieldset>	
</div>
