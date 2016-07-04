/**
 * 
 */
define(['jquery'], function (jQuery) {


	var Themes = {};
	
	Themes.initialize = function() {
		Themes.initSelectBoxValues();
		jQuery('.contentVariant select').change(function() {
			Themes.contentVariantChangeSelectbox(this);
		});
		jQuery('.contentVariant input[type=\'checkbox\']').change(function() {
			Themes.contentVariantChangeCheckbox(this);
		});
		jQuery('.contentResponsive select').change(function() {
			Themes.contentResponsiveChangeSelectbox(this);
		});
		jQuery('.contentBehaviour select').change(function() {
			Themes.contentBehaviourChangeSelectbox(this);
		});
		jQuery('.contentBehaviour input[type=\'checkbox\']').change(function() {
			Themes.contentBehaviourChangeCheckbox(this);
		});
		jQuery('.contentColumnSettings select').change(function() {
			Themes.contentColumnSettingsChange(this);
		});
		jQuery('.contentEnforceEqualColumnHeight input[type=\'checkbox\']').change(function() {
			Themes.contentEnforceEqualColumnHeightChange(this);
		});
	};

	Themes.initSelectBoxValues = function() {
		jQuery.each(jQuery('.contentBehaviour select'), function() {
			// Is already a value selected?
			var alreadySelected = false;
			jQuery.each(jQuery(this).find('option'), function() {
				if(jQuery(this).attr('selected') == 'selected') {
					alreadySelected = true;
				}
			});
			if(!alreadySelected) {
				jQuery(this).find('option:first-child').attr('selected', 'selected');
				Themes.contentBehaviourChangeSelectbox(jQuery(this));
			}
		});
		jQuery.each(jQuery('.contentVariant select'), function() {
			// Is already a value selected?
			var alreadySelected = false;
			jQuery.each(jQuery(this).find('option'), function() {
				if(jQuery(this).attr('selected') == 'selected') {
					alreadySelected = true;
				}
			});
			if(!alreadySelected) {
				jQuery(this).find('option:first-child').attr('selected', 'selected');
				Themes.contentVariantChangeSelectbox(jQuery(this));
			}
		});
		jQuery.each(jQuery('.contentResponsive select'), function() {
			// Is already a value selected?
			var alreadySelected = false;
			jQuery.each(jQuery(this).find('option'), function() {
				if(jQuery(this).attr('selected') == 'selected') {
					alreadySelected = true;
				}
			});
			if(!alreadySelected) {
				jQuery(this).find('option:first-child').attr('selected', 'selected');
				Themes.contentResponsiveChangeSelectbox(jQuery(this));
			}
		});
	};
	
	Themes.contentVariantChangeSelectbox = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		var value = jQuery(field).val();
		var prefix = jQuery(field).attr('name');
		var classes = jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr('class').split(' ');
		// Remove all classes with the same prefix
		for (var i=0; i<classes.length; i++) {
			if(classes[i].substr(0, prefix.length+1) == prefix + '-') {
				jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").removeClass(classes[i]);
			}
		}
		// Add the selected value
		jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").addClass(value);
		var values = jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("value", values);
	};

	Themes.contentVariantChangeCheckbox = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		if (field.checked) {
			jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").addClass(field.name);
		}
		else {
			jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").removeClass(field.name);
		}
		var values = jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("value", values);
	};

	Themes.contentBehaviourChangeSelectbox = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		var value = jQuery(field).val();
		var prefix = jQuery(field).attr('name');
		var classes = jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr('class').split(' ');
		// Remove all classes with the same prefix
		for (var i=0; i<classes.length; i++) {
			if(classes[i].substr(0, prefix.length+1) == prefix + '-') {
				jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").removeClass(classes[i]);
			}
		}
		// Add the selected value
		jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").addClass(value);
		var values = jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("value", values);
	};

	Themes.contentBehaviourChangeCheckbox = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		if (field.checked) {
			jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").addClass(field.name);
		}
		else {
			jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").removeClass(field.name);
		}
		var values = jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("value", values);
	};

	Themes.contentResponsiveChangeSelectbox = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		var value = jQuery(field).val();
		var prefix = jQuery(field).attr('name');
		var classes = jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").attr('class').split(' ');
		// Remove all classes with the same prefix
		for (var i=0; i<classes.length; i++) {
			if(classes[i].substr(0, prefix.length+1) == prefix + '-') {
				jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").removeClass(classes[i]);
			}
		}
		// Add the selected value
		jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").addClass(value);
		var values = jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").attr("value", values);
	};

	Themes.contentColumnSettingsChange = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		jQuery.each(jQuery(".contentColumnSettings select[name=\'"+field.name+"\'] option"), function(index, node) {
			jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").removeClass(node.value);
		});
		jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").addClass(field.value);
		var values = jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").attr("value", values);
	};

	Themes.contentEnforceEqualColumnHeightChange = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";
		}
		if (field.checked) {
			jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").addClass(field.name);
		}
		else {
			jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").removeClass(field.name);
		}
		var values = jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").attr("class");
		values = Themes.convertClassesForInputValue(values);
		jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").attr("value", values);
	};
	
	Themes.convertClassesForInputValue = function(values) {
		values = values.replace(/form-control/g, "").trim();
		values = values.replace(/themes-hidden-admin-field/g, "").trim();
		values = values.replace(/\ /g, ",");
		return values;
	};

	/**
	 * initialize function
	 * */
	return function () {
		Themes.initialize();
		return Themes;
	}();
});