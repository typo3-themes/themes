/**
 * 
 */
define(['jquery'], function (jQuery) {


	var Themes = {};
	
	Themes.initialize = function() {
		jQuery('.contentVariant input[type=\'checkbox\']').change(function() {
			Themes.contentVariantChange(this);
		});
		jQuery('.contentResponsive input[type=\'radio\']').change(function() {
			Themes.contentResponsiveChange(this);
		});
		jQuery('.contentBehaviour input[type=\'checkbox\']').change(function() {
			Themes.contentBehaviourChange(this);
		});
		jQuery('.contentColumnSettings select').change(function() {
			Themes.contentColumnSettingsChange(this);
		});
		jQuery('.contentEnforceEqualColumnHeight input[type=\'checkbox\']').change(function() {
			Themes.contentEnforceEqualColumnHeightChange(this);
		});
	};

	Themes.contentVariantChange = function(field) {
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

	Themes.contentBehaviourChange = function(field) {
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

	Themes.contentResponsiveChange = function(field) {
		var itemselector = "";
		if(jQuery(field).closest(".t3-form-field-item").index() > 0) {
			itemselector = ".t3-form-field-item";
		}
		else if(jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
			itemselector = ".t3js-formengine-field-item";}
			jQuery.each(jQuery(".contentResponsive input[name=\'"+field.name+"\']"), function(index, node) {
			jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").removeClass(node.value);
		});
		jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").addClass(field.value);
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